<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\SankhyaAuthService;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Models\TipoEquipamento;
use App\Services\SyncLogService;

class SankhyaSyncTiposEquipamento extends Command
{
    protected $signature = 'sankhya:sync-tipos-equipamento';
    protected $description = 'Busca tipos de equipamento no Sankhya e atualiza a base local.';

    public function handle(): int
    {
        $syncLog = (new SyncLogService('tipos-equipamento'))->start();
        try {
            $this->info('Buscando Tipos de Equipamento...');
            $inicio = microtime(true);

            $token = (new SankhyaAuthService())->login();
            if (!$token) {
                throw new \RuntimeException('Falha ao autenticar no Sankhya.');
            }

            $service = new SankhyaLoadRecordsService();

            $records = $service->fetchAll(
                token: $token,
                rootEntity: 'AD_TIPOEQUIPAMENTOS',
                fields: [
                    '' => ['ID', 'DESCRICAO']
                ]
            );

            $dados = collect($records)
                ->map(fn($row) => [
                    'codtipoequip_snk' => $row['f0']['$'] ?? null,
                    'descricao'         => $row['f1']['$'] ?? null,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ])
                ->filter(fn($i) => !empty($i['codtipoequip_snk']));

            if ($dados->isNotEmpty()) {
                TipoEquipamento::upsert(
                    $dados->toArray(),
                    ['codtipoequip_snk'],
                    ['descricao', 'updated_at']
                );
            }

            $duracao = round(microtime(true) - $inicio, 2);
            $this->info("Total sincronizado: {$dados->count()} tipos de equipamento em {$duracao}s.");

            $syncLog->finish($dados->count());
            return 0;
        } catch (\Throwable $e) {
            $syncLog->fail($e);
            $this->error('Erro: ' . $e->getMessage());
            return 1;
        }
    }
}
