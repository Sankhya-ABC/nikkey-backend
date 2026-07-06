<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\SankhyaAuthService;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Models\Ambiente;
use App\Services\SyncLogService;

class SankhyaSyncAmbientes extends Command
{
    protected $signature = 'sankhya:sync-ambientes';
    protected $description = 'Busca ambientes (setores) no Sankhya e sincroniza a base local.';

    public function handle(): int
    {
        $syncLog = (new SyncLogService('ambientes'))->start();
        try {
            $this->info('Iniciando sincronizacao de ambientes...');
            $inicio = microtime(true);

            $token = (new SankhyaAuthService())->login();
            if (!$token) {
                throw new \RuntimeException('Falha ao autenticar no Sankhya.');
            }

            $service = new SankhyaLoadRecordsService();

            $records = $service->fetchAll(
                token: $token,
                rootEntity: 'AD_SETORES',
                fields: ['' => ['ID', 'DESCRICAO']]
            );

            $ambientes = collect($records)
                ->map(function ($row) {
                    return [
                        'codsetor_snk' => $row['f0']['$'] ?? null,
                        'descricao'    => $row['f1']['$'] ?? null,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ];
                })
                ->filter(fn($a) => !empty($a['codsetor_snk']));

            if ($ambientes->isNotEmpty()) {
                Ambiente::upsert($ambientes->toArray(), ['codsetor_snk'], ['descricao', 'updated_at']);
            }

            $duracao = round(microtime(true) - $inicio, 2);
            $this->info("Total sincronizado: {$ambientes->count()} ambientes em {$duracao}s.");

            $syncLog->finish($ambientes->count());
            return 0;
        } catch (\Throwable $e) {
            $syncLog->fail($e);
            $this->error('Erro: ' . $e->getMessage());
            return 1;
        }
    }
}
