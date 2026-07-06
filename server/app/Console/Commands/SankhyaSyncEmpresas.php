<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\SankhyaAuthService;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Models\Empresa;
use App\Services\SyncLogService;

class SankhyaSyncEmpresas extends Command
{
    protected $signature   = 'sankhya:sync-empresas';
    protected $description = 'Sincroniza empresas (filiais Nikkey) do Sankhya para o banco local.';

    public function handle(): int
    {
        $syncLog = (new SyncLogService('empresas'))->start();
        try {
            $this->info('Iniciando sync de empresas...');
            $inicio = microtime(true);

            $token = (new SankhyaAuthService())->login();
            if (!$token) {
                throw new \RuntimeException('Falha ao autenticar no Sankhya.');
            }

            $service = new SankhyaLoadRecordsService();

            $records = $service->fetchAll(
                token:      $token,
                rootEntity: 'Empresa',
                fields:     [
                    '' => [
                        'CODEMP', 'NOMEFANTASIA', 'RAZAOSOCIAL', 'CGC',
                        'NUMEND', 'COMPLEMENTO', 'CEP', 'TELEFONE',
                        'AD_ALVARAVIG', 'AD_ALVARAVIGDTVAL',
                    ],
                    'Endereco'                => ['TIPO', 'NOMEEND'],
                    'Bairro'                  => ['NOMEBAI'],
                    'Cidade'                  => ['NOMECID'],
                    'Cidade.UnidadeFederativa'=> ['UF'],
                ]
            );

            $data = collect($records)->map(function ($row) {
                $codemp = (int) ($row['f0']['$'] ?? 0);
                if (!$codemp) return null;

                $tipo      = trim($row['f10']['$'] ?? '');
                $nomeEnd   = trim($row['f11']['$'] ?? '');
                $logradouro = $tipo ? "{$tipo} {$nomeEnd}" : $nomeEnd;

                return [
                    'codemp_snk'      => $codemp,
                    'nome_fantasia'   => $row['f1']['$'] ?? null,
                    'razao_social'    => $row['f2']['$'] ?? null,
                    'cgc'             => $row['f3']['$'] ?? null,
                    'numero'          => $row['f4']['$'] ?? null,
                    'complemento'     => $row['f5']['$'] ?? null,
                    'cep'             => $row['f6']['$'] ?? null,
                    'telefone'        => $row['f7']['$'] ?? null,
                    'alvara'          => $row['f8']['$'] ?? null,
                    'alvara_vencimento'=> $this->parseDate($row['f9']['$'] ?? null),
                    'logradouro'      => $logradouro ?: null,
                    'bairro'          => $row['f12']['$'] ?? null,
                    'cidade'          => $row['f13']['$'] ?? null,
                    'estado'          => $row['f14']['$'] ?? null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            })->filter();

            if ($data->isNotEmpty()) {
                Empresa::upsert(
                    $data->toArray(),
                    ['codemp_snk'],
                    [
                        'nome_fantasia', 'razao_social', 'cgc', 'logradouro',
                        'numero', 'bairro', 'cidade', 'estado', 'complemento',
                        'cep', 'telefone', 'alvara', 'alvara_vencimento', 'updated_at',
                    ]
                );
            }

            $duracao = round(microtime(true) - $inicio, 2);
            $this->info("Sincronizadas: {$data->count()} empresas em {$duracao}s.");

            $syncLog->finish($data->count());
            return 0;
        } catch (\Throwable $e) {
            $syncLog->fail($e);
            $this->error('Erro: ' . $e->getMessage());
            return 1;
        }
    }

    private function parseDate(?string $value): ?string
    {
        if (!$value) return null;
        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }
}