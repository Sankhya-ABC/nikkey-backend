<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\SankhyaAuthService;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Models\Praga;
use App\Services\SyncLogService;

class SankhyaSyncPragas extends Command
{
    protected $signature = 'sankhya:sync-pragas';
    protected $description = 'Busca pragas no Sankhya e atualiza a base local.';

    public function handle(): int
    {
        $syncLog = (new SyncLogService('pragas'))->start();
        try {
            $this->info('Iniciando sincronizacao de pragas Sankhya...');
            $inicio = microtime(true);

            $token = (new SankhyaAuthService())->login();
            if (!$token) {
                throw new \RuntimeException('Falha ao autenticar no Sankhya.');
            }

            $service = new SankhyaLoadRecordsService();

            $records = $service->fetchAll(
                token: $token,
                rootEntity: 'AD_TABPRAGAS',
                fields: ['' => ['CODPRAGA', 'NOME_PRAGA', 'GRU_PRAGAS']]
            );

            $pragas = collect($records)
                ->map(fn($row) => [
                    'codpraga_snk'   => $row['f0']['$'] ?? null,
                    'nome_praga'     => $row['f1']['$'] ?? null,
                    'grupo_praga_id' => $row['f2']['$'] ?? null,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ])
                ->filter(fn($p) => !empty($p['codpraga_snk']));

            if ($pragas->isNotEmpty()) {
                Praga::upsert($pragas->toArray(), ['codpraga_snk'], ['nome_praga', 'grupo_praga_id', 'updated_at']);
            }

            $duracao = round(microtime(true) - $inicio, 2);
            $this->info("Total sincronizado: {$pragas->count()} pragas em {$duracao}s.");

            $syncLog->finish($pragas->count());
            return 0;
        } catch (\Throwable $e) {
            $syncLog->fail($e);
            $this->error('Erro: ' . $e->getMessage());
            return 1;
        }
    }
}
