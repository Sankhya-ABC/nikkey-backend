<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\SankhyaAuthService;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Models\Metodologia;
use App\Models\TecnicaExecucao;
use App\Models\TipoEquipamento;
use App\Services\SyncLogService;

class SankhyaSyncMetodologias extends Command
{
    protected $signature = 'sankhya:sync-metodologias';
    protected $description = 'Busca metodologias no Sankhya e atualiza a base local.';

    public function handle(): int
    {
        $syncLog = (new SyncLogService('metodologias'))->start();
        try {
            $this->info('Iniciando sincronizacao de metodologias Sankhya...');
            $inicio = microtime(true);

            $token = (new SankhyaAuthService())->login();
            if (!$token) {
                throw new \RuntimeException('Falha ao autenticar no Sankhya.');
            }

            $service = new SankhyaLoadRecordsService();

            $records = $service->fetchAll(
                token: $token,
                rootEntity: 'AD_METODOLOGIA',
                fields: ['' => ['ID', 'DESCRICAO', 'TECEXECUCAO', 'TIPOEQP']]
            );

            $metodologias = collect($records)
                ->map(function ($row) {
                    $snkId     = $row['f0']['$'] ?? null;
                    $descricao = $row['f1']['$'] ?? null;
                    $tecExec   = $row['f2']['$'] ?? null;
                    $tipoEqp   = $row['f3']['$'] ?? null;

                    if (!$snkId) return null;

                    return [
                        'codmetodologia_snk' => $snkId,
                        'descricao'          => $descricao,
                        'tecexecucao_id'     => TecnicaExecucao::where('codtecexec_snk', $tecExec)->value('id'),
                        'tipoequip_id'       => TipoEquipamento::where('codtipoequip_snk', $tipoEqp)->value('id'),
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ];
                })
                ->filter();

            if ($metodologias->isNotEmpty()) {
                Metodologia::upsert(
                    $metodologias->toArray(),
                    ['codmetodologia_snk'],
                    ['descricao', 'tecexecucao_id', 'tipoequip_id', 'updated_at']
                );
            }

            $duracao = round(microtime(true) - $inicio, 2);
            $this->info("Total sincronizado: {$metodologias->count()} metodologias em {$duracao}s.");

            $syncLog->finish($metodologias->count());
            return 0;
        } catch (\Throwable $e) {
            $syncLog->fail($e);
            $this->error('Erro: ' . $e->getMessage());
            return 1;
        }
    }
}
