<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\SankhyaAuthService;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Models\Produto;
use App\Services\SyncLogService;

class SankhyaSyncProdutos extends Command
{
    protected $signature = 'sankhya:sync-produtos';
    protected $description = 'Busca produtos no Sankhya com USOPROD diferente de "S" e atualiza a base local.';

    public function handle(): int
    {
        $syncLog = (new SyncLogService('produtos'))->start();
        try {
            $this->info('Iniciando sincronizacao de produtos Sankhya...');
            $inicio = microtime(true);

            $token = (new SankhyaAuthService())->login();
            if (!$token) {
                throw new \RuntimeException('Falha ao autenticar no Sankhya.');
            }

            $service = new SankhyaLoadRecordsService();

            $records = $service->fetchAll(
                token: $token,
                rootEntity: 'Produto',
                fields: ['' => ['CODPROD', 'DESCRPROD', 'CODVOL']],
                criteria: [
                    ['field' => 'USOPROD', 'value' => 'S', 'operator' => '<>', 'type' => 'S']
                ]
            );

            $produtos = collect($records)
                ->map(fn($p) => [
                    'codprod_snk' => $p['f0']['$'] ?? null,
                    'descricao'   => $p['f1']['$'] ?? null,
                    'codvol'      => $p['f2']['$'] ?? null,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ])
                ->filter(fn($p) => !empty($p['codprod_snk']));

            if ($produtos->isNotEmpty()) {
                Produto::upsert($produtos->toArray(), ['codprod_snk'], ['descricao', 'codvol', 'updated_at']);
            }

            $duracao = round(microtime(true) - $inicio, 2);
            $this->info("Total sincronizado: {$produtos->count()} produtos em {$duracao}s.");

            $syncLog->finish($produtos->count());
            return 0;
        } catch (\Throwable $e) {
            $syncLog->fail($e);
            $this->error('Erro: ' . $e->getMessage());
            return 1;
        }
    }
}
