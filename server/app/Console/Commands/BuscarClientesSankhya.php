<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\AuthSankhya;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Models\Cliente;

class BuscarClientesSankhya extends Command
{
    protected $signature = 'sankhya:buscar-clientes';
    protected $description = 'Busca clientes no Sankhya e atualiza a base local usando fetchAll().';

    public function handle(): int
    {
        $this->info('🚀 Iniciando sincronização de clientes Sankhya...');
        $inicio = microtime(true);

        $token = (new AuthSankhya())->login();
        if (!$token) {
            $this->error('❌ Falha ao autenticar no Sankhya.');
            return 1;
        }

        $service = new SankhyaLoadRecordsService();

        $records = $service->fetchAll(
            token: $token,
            rootEntity: 'Parceiro',
            fields: [
                ''        => ['CODPARC','NOMEPARC','RAZAOSOCIAL','CODPARCMATRIZ','NUMEND','COMPLEMENTO','CEP','ATIVO'],
                'Endereco'=> ['NOMEEND'],
                'Bairro'  => ['NOMEBAI'],
                'Cidade'  => ['NOMECID'],
                'Cidade.UnidadeFederativa' => ['UF']
            ],
            criteria: [
                ['field' => 'CLIENTE', 'value' => 'S', 'type' => 'S']
            ]
        );

        if (empty($records)) {
            $this->info('⚠️ Nenhum cliente encontrado.');
            return 0;
        }

        $clean = function ($value) {
            if (!is_string($value)) {
                return $value; 
            }

            $value = trim(preg_replace('/\s+/', ' ', $value));

            return $value === '' ? null : $value;
        };

        $clientes = collect($records)->map(function ($cli) use ($clean) {

            return [
                'codparc_snk'        => $clean($cli['f0']['$'] ?? null),
                'nome_fantasia'      => $clean($cli['f1']['$'] ?? null),
                'razao_social'       => $clean($cli['f2']['$'] ?? null),
                'codparc_matriz_snk' => $clean($cli['f3']['$'] ?? null),
                'numero'             => $clean($cli['f4']['$'] ?? null),
                'complemento'        => $clean($cli['f5']['$'] ?? null),
                'cep'                => $clean($cli['f6']['$'] ?? null),
                'ativo'              => isset($cli['f7']['$']) && $cli['f7']['$'] === 'S' ? 1 : 0,
                'logradouro'         => $clean($cli['f8']['$'] ?? null),
                'bairro'             => $clean($cli['f9']['$'] ?? null),
                'cidade'             => $clean($cli['f10']['$'] ?? null),
                'estado'             => $clean($cli['f11']['$'] ?? null),
                'created_at'         => now(),
                'updated_at'         => now(),
            ];
        })->filter(fn($c) => !empty($c['codparc_snk']));

        $total = $clientes->count();

        if ($total > 0) {
            $clientes->chunk(500)->each(function ($chunk) {
                Cliente::upsert(
                    $chunk->toArray(),
                    ['codparc_snk'], 
                    ['nome_fantasia','logradouro','bairro','cidade','estado','numero','cep','updated_at']
                );
            });
        }

        $duracao = round(microtime(true) - $inicio, 2);
        $this->info("🎯 Concluído! Total sincronizado: {$total} clientes em {$duracao}s.");

        return 0;
    }
}
