<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\Sankhya\SankhyaAuthService;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Models\Cliente;
use App\Models\Uf;
use App\Models\Cidade;
use App\Models\Bairro;
use App\Models\Endereco;
use App\Services\SyncLogService;

class SankhyaSyncClientes extends Command
{
    protected $signature = 'sankhya:sync-clientes';
    protected $description = 'Busca clientes no Sankhya e sincroniza a base local.';

    public function handle(): int
    {
        $syncLog = (new SyncLogService('clientes'))->start();
        try {
            $this->info('Iniciando sincronizacao de clientes...');
            $inicio = microtime(true);

            $token = (new SankhyaAuthService())->login();
            if (!$token) {
                throw new \RuntimeException('Falha ao autenticar no Sankhya.');
            }

            $service = new SankhyaLoadRecordsService();
            $records = $service->fetchAll(
                token: $token,
                rootEntity: 'Parceiro',
                fields: [
                    '' => [
                        'CODPARC', 'NOMEPARC', 'RAZAOSOCIAL', 'CODPARCMATRIZ',
                        'CGC_CPF', 'NUMEND', 'COMPLEMENTO', 'CEP', 'ATIVO'
                    ],
                    'Endereco'              => ['NOMEEND'],
                    'Bairro'               => ['NOMEBAI'],
                    'Cidade'               => ['NOMECID'],
                    'Cidade.UnidadeFederativa' => ['UF'],
                ],
                criteria: [
                    ['field' => 'CLIENTE', 'value' => 'S', 'type' => 'S']
                ]
            );

            $now    = now();
            $buffer = [];

            $ufs       = Uf::pluck('id', 'sigla')->toArray();
            $cidades   = Cidade::all()->mapWithKeys(fn($c) => [$c->nome . '|' . $c->uf_id => $c->id])->toArray();
            $bairros   = Bairro::pluck('id', 'nome')->toArray();
            $enderecos = Endereco::pluck('id', 'logradouro')->toArray();

            $val = function (array $cli, string $key, bool $int = false) {
                if (!isset($cli[$key]['$'])) return null;
                $value = trim($cli[$key]['$']);
                if ($value === '') return null;
                return $int ? (int) $value : $value;
            };

            $normalize = function (string $value): string {
                $value = trim($value);
                $value = preg_replace('/\s+/', ' ', $value);
                return strtoupper($value);
            };

            DB::transaction(function () use (
                $records, $now, &$buffer,
                &$ufs, &$cidades, &$bairros, &$enderecos,
                $val, $normalize
            ) {
                foreach ($records as $cli) {
                    $logradouro = $val($cli, 'f9');
                    $bairroNom  = $val($cli, 'f10');
                    $cidadeNom  = $val($cli, 'f11');
                    $ufSigla    = $val($cli, 'f12');

                    if ($ufSigla)    $ufSigla    = $normalize($ufSigla);
                    if ($cidadeNom)  $cidadeNom  = $normalize($cidadeNom);
                    if ($bairroNom)  $bairroNom  = $normalize($bairroNom);
                    if ($logradouro) $logradouro = $normalize($logradouro);

                    $ufId = null;
                    if ($ufSigla) {
                        if (!isset($ufs[$ufSigla])) {
                            $uf = Uf::firstOrCreate(['sigla' => $ufSigla], ['nome' => $ufSigla]);
                            $ufs[$ufSigla] = $uf->id;
                        }
                        $ufId = $ufs[$ufSigla];
                    }

                    $cidadeId = null;
                    if ($cidadeNom && $ufId) {
                        $key = $cidadeNom . '|' . $ufId;
                        if (!isset($cidades[$key])) {
                            $cidade = Cidade::firstOrCreate(['nome' => $cidadeNom, 'uf_id' => $ufId]);
                            $cidades[$key] = $cidade->id;
                        }
                        $cidadeId = $cidades[$key];
                    }

                    $bairroId = null;
                    if ($bairroNom) {
                        if (!isset($bairros[$bairroNom])) {
                            $bairro = Bairro::firstOrCreate(['nome' => $bairroNom]);
                            $bairros[$bairroNom] = $bairro->id;
                        }
                        $bairroId = $bairros[$bairroNom];
                    }

                    $enderecoId = null;
                    if ($logradouro) {
                        if (!isset($enderecos[$logradouro])) {
                            $endereco = Endereco::firstOrCreate(['logradouro' => $logradouro]);
                            $enderecos[$logradouro] = $endereco->id;
                        }
                        $enderecoId = $enderecos[$logradouro];
                    }

                    $buffer[] = [
                        'codparc_snk'        => $val($cli, 'f0', true),
                        'nome_fantasia'       => $val($cli, 'f1'),
                        'razao_social'        => $val($cli, 'f2'),
                        'codparc_matriz_snk'  => $val($cli, 'f3', true),
                        'cnpj_cpf'            => $val($cli, 'f4'),
                        'numero'              => $val($cli, 'f5'),
                        'complemento'         => $val($cli, 'f6'),
                        'cep'                 => $val($cli, 'f7'),
                        'ativo'               => ($val($cli, 'f8') === 'S'),
                        'endereco_id'         => $enderecoId,
                        'bairro_id'           => $bairroId,
                        'cidade_id'           => $cidadeId,
                        'created_at'          => $now,
                        'updated_at'          => $now,
                    ];

                    if (count($buffer) === 500) {
                        Cliente::upsert($buffer, ['codparc_snk'], [
                            'nome_fantasia', 'razao_social', 'cnpj_cpf',
                            'endereco_id', 'bairro_id', 'cidade_id',
                            'numero', 'complemento', 'cep', 'ativo', 'updated_at',
                        ]);
                        $buffer = [];
                    }
                }

                if (!empty($buffer)) {
                    Cliente::upsert($buffer, ['codparc_snk'], [
                        'nome_fantasia', 'razao_social', 'cnpj_cpf',
                        'endereco_id', 'bairro_id', 'cidade_id',
                        'numero', 'complemento', 'cep', 'ativo', 'updated_at',
                    ]);
                }
            });

            $duracao = round(microtime(true) - $inicio, 2);
            $total   = count($records);
            $this->info("Clientes sincronizados: {$total} em {$duracao}s.");

            $syncLog->finish($total);
            return 0;
        } catch (\Throwable $e) {
            $syncLog->fail($e);
            $this->error('Erro: ' . $e->getMessage());
            return 1;
        }
    }
}
