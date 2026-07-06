<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\SankhyaAuthService;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Models\OrdemServico;
use App\Models\PrevisaoExecucaoOs;
use Carbon\Carbon;
use App\Services\SyncLogService;

class SankhyaSyncPrevisoesExecucao extends Command
{
    protected $signature = 'sankhya:sync-previsoes-execucao';
    protected $description = 'Busca previsoes e execucao de OS no Sankhya e sincroniza a base local.';

    public function handle(): int
    {
        $syncLog = (new SyncLogService('previsoes-execucao'))->start();
        try {
            $this->info('Iniciando sincronizacao de previsoes de execucao...');
            $inicio = microtime(true);

            $token = (new SankhyaAuthService())->login();
            if (!$token) {
                throw new \RuntimeException('Falha ao autenticar no Sankhya.');
            }

            $service = new SankhyaLoadRecordsService();

            $records = $service->fetchAll(
                token: $token,
                rootEntity: 'AD_VGFOSEPREV',
                fields: ['' => [
                    'NUMOS', 'ID', 'INSTEMPPREV', 'INSDIASPREV', 'INSPESSPREV',
                    'MONTEMPPREV', 'MONDIASPREV', 'MONPESSPREV', 'HRINI', 'HRFIN',
                ]]
            );

            $previsoes = collect($records)
                ->map(function ($row) {
                    $numos   = $row['f0']['$'] ?? null;
                    $codSnk  = $row['f1']['$'] ?? null;

                    if (!$numos || !$codSnk) return null;

                    $ordemServicoId = OrdemServico::where('numos', $numos)->value('id');
                    if (!$ordemServicoId) return null;

                    return [
                        'codprevisao_snk'  => $codSnk,
                        'ordem_servico_id' => $ordemServicoId,
                        'inst_temp_prev'   => $row['f2']['$'] ?? null,
                        'ins_dias_prev'    => $row['f3']['$'] ?? null,
                        'ins_pessoas_prev' => $row['f4']['$'] ?? null,
                        'mon_temp_prev'    => $row['f5']['$'] ?? null,
                        'mon_dias_prev'    => $row['f6']['$'] ?? null,
                        'mon_pessoas_prev' => $row['f7']['$'] ?? null,
                        'hrini'            => $this->parseDate($row['f8']['$'] ?? null),
                        'hrfin'            => $this->parseDate($row['f9']['$'] ?? null),
                        'created_at'       => now(),
                        'updated_at'       => now(),
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
            $this->info("Total sincronizado: {$previsoes->count()} previsoes em {$duracao}s.");

            $syncLog->finish($previsoes->count());
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
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Exception) {
            return null;
        }
    }
}
