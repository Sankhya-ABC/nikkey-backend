<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\AuthSankhya;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Models\Metodologia;
use App\Models\TecnicaExecucao;
use App\Models\TipoEquipamento;

class BuscarMetodologiasSankhya extends Command
{
    protected $signature = 'sankhya:buscar-metodologias';
    protected $description = 'Busca metodologias no Sankhya e atualiza a base local.';

    public function handle(): int
    {
        $this->info('🚀 Iniciando sincronização de metodologias Sankhya...');
        $inicio = microtime(true);

        // Autenticação
        $token = (new AuthSankhya())->login();
        if (!$token) {
            $this->error('❌ Falha ao autenticar no Sankhya.');
            return 1;
        }

        $service = new SankhyaLoadRecordsService();

        // Buscar dados
        $records = $service->fetchAll(
            token: $token,
            rootEntity: 'AD_METODOLOGIA',
            fields: [
                '' => ['ID', 'DESCRICAO', 'TECEXECUCAO', 'TIPOEQP']
            ]
        );

        $metodologias = collect($records)
            ->map(function ($row) {

                $snkId = $row['f0']['$'] ?? null;
                $descricao = $row['f1']['$'] ?? null;
                $tecExec = $row['f2']['$'] ?? null;
                $tipoEqp = $row['f3']['$'] ?? null;

                if (!$snkId) return null;

                return [
                    'codmetodologia_snk' => $snkId,
                    'descricao' => $descricao,
                    'tecexecucao_id' =>
                        TecnicaExecucao::where('codtecexec_snk', $tecExec)->value('id'),
                    'tipoequip_id' =>
                        TipoEquipamento::where('codtipoequip_snk', $tipoEqp)->value('id'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
            ->filter(); // remove nulos

        if ($metodologias->isNotEmpty()) {
            Metodologia::upsert(
                $metodologias->toArray(),
                ['codmetodologia_snk'],
                ['descricao', 'tecexecucao_id', 'tipoequip_id', 'updated_at']
            );
        }

        $duracao = round(microtime(true) - $inicio, 2);

        $this->info("🎯 Total sincronizado: {$metodologias->count()} metodologias em {$duracao}s.");

        return 0;
    }
}
