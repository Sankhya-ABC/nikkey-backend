<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\SankhyaAuthService;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Models\PontoMonitoramento;
use App\Models\OrdemServico;
use App\Models\Praga;
use App\Services\SyncLogService;

class SankhyaSyncPontosMonitoramento extends Command
{
    protected $signature   = 'sankhya:sync-pontos-monitoramento';
    protected $description = 'Sincroniza pontos de monitoramento das OSs (AD_VGFOSESERPRGPTMON).';

    public function handle(): int
    {
        $syncLog = (new SyncLogService('pontos-monitoramento'))->start();
        try {
            $this->info('Iniciando sync de pontos de monitoramento...');
            $inicio = microtime(true);

            $token = (new SankhyaAuthService())->login();
            if (!$token) {
                throw new \RuntimeException('Falha ao autenticar no Sankhya.');
            }

            $service = new SankhyaLoadRecordsService();

            $records = $service->fetchAll(
                token:      $token,
                rootEntity: 'AD_VGFOSESERPRGPTMON',
                fields:     ['' => [
                    'NUMOS', 'ID', 'PRAGA', 'TPMONIT', 'IDEQUP',
                    'AMB', 'LOCAL', 'SETOR', 'CONSUMO', 'CONSUMOMETADE',
                ]]
            );

            $total = 0;

            foreach (collect($records)->chunk(500) as $chunk) {
                $data = $chunk->map(function ($row) {
                    $numos       = $row['f0']['$'] ?? null;
                    $codPtMonSnk = $row['f1']['$'] ?? null;

                    if (!$numos || !$codPtMonSnk) return null;

                    $osId = OrdemServico::where('numos', $numos)->value('id');
                    if (!$osId) return null;

                    $pragaSnk = $row['f2']['$'] ?? null;
                    $pragaId  = $pragaSnk ? Praga::where('codpraga_snk', $pragaSnk)->value('id') : null;

                    return [
                        'codptmon_snk'    => (int) $codPtMonSnk,
                        'ordem_servico_id'=> $osId,
                        'praga_id'        => $pragaId,
                        'tpmonit'         => $row['f3']['$'] ?? null,
                        'idequp'          => $row['f4']['$'] ?? null,
                        'amb'             => $row['f5']['$'] ?? null,
                        'local_ponto'     => $row['f6']['$'] ?? null,
                        'setor'           => $row['f7']['$'] ?? null,
                        'consumo'         => $row['f8']['$'] ?? null,
                        'consumometade'   => $row['f9']['$'] ?? null,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ];
                })->filter();

                if ($data->isNotEmpty()) {
                    PontoMonitoramento::upsert(
                        $data->toArray(),
                        ['codptmon_snk', 'ordem_servico_id'],
                        ['praga_id', 'tpmonit', 'idequp', 'amb', 'local_ponto', 'setor', 'consumo', 'consumometade', 'updated_at']
                    );
                    $total += $data->count();
                }
            }

            $duracao = round(microtime(true) - $inicio, 2);
            $this->info("Sincronizados: {$total} pontos de monitoramento em {$duracao}s.");

            $syncLog->finish($total);
            return 0;
        } catch (\Throwable $e) {
            $syncLog->fail($e);
            $this->error('Erro: ' . $e->getMessage());
            return 1;
        }
    }
}
