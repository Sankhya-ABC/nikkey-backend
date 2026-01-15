<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\AuthSankhya;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Models\OrdemServico;
use App\Models\PrevisaoExecucaoOs;
use Carbon\Carbon;

class BuscarPrevisoesExecucaoSankhya extends Command
{
    protected $signature = 'sankhya:buscar-previsoes-execucao';

    protected $description = 'Busca previsões e execução de OS no Sankhya e sincroniza a base local.';

    public function handle(): int
    {
        $this->info('🚀 Iniciando sincronização de previsões de execução...');

        $inicio = microtime(true);

        $token = (new AuthSankhya())->login();
        if (!$token) {
            $this->error('❌ Falha ao autenticar no Sankhya.');
            return 1;
        }

        $service = new SankhyaLoadRecordsService();

        $records = $service->fetchAll(
            token: $token,
            rootEntity: 'AD_VGFOSEPREV',
            fields: [
                '' => [
                    'NUMOS',
                    'ID',
                    'INSTEMPPREV',
                    'INSDIASPREV',
                    'INSPESSPREV',
                    'MONTEMPPREV',
                    'MONDIASPREV',
                    'MONPESSPREV',
                    'HRINI',
                    'HRFIN',
                ]
            ]
        );

        if (empty($records)) {
            $this->info('Nenhuma previsão encontrada.');
            return 0;
        }

        $previsoes = collect($records)
            ->map(function ($row) {

                $numos = $row['f0']['$'] ?? null;
                $codSnk = $row['f1']['$'] ?? null;

                if (!$numos || !$codSnk) {
                    return null;
                }

                $ordemServicoId = OrdemServico::where('numos', $numos)->value('id');
                if (!$ordemServicoId) {
                    return null;
                }

                return [
                    'codprevisao_snk' => $codSnk,
                    'ordem_servico_id' => $ordemServicoId,

                    'inst_temp_prev'   => $row['f2']['$'] ?? null,
                    'ins_dias_prev'    => $row['f3']['$'] ?? null,
                    'ins_pessoas_prev' => $row['f4']['$'] ?? null,

                    'mon_temp_prev'    => $row['f5']['$'] ?? null,
                    'mon_dias_prev'    => $row['f6']['$'] ?? null,
                    'mon_pessoas_prev' => $row['f7']['$'] ?? null,

                    'hrini' => $this->parseDate($row['f8']['$'] ?? null),
                    'hrfin' => $this->parseDate($row['f9']['$'] ?? null),

                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
            ->filter();

        if ($previsoes->isNotEmpty()) {
            PrevisaoExecucaoOs::upsert(
                $previsoes->toArray(),
                ['codprevisao_snk', 'ordem_servico_id'],
                array_keys($previsoes->first())
            );
        }

        $duracao = round(microtime(true) - $inicio, 2);

        $this->info("🎯 Total sincronizado: {$previsoes->count()} previsões em {$duracao}s.");

        return 0;
    }

    /**
     * Converte datas do Sankhya para DateTime
     */
    private function parseDate(?string $value): ?string
    {
        if (!$value) return null;

        try {
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Exception) {
            return null;
        }
    }
}
