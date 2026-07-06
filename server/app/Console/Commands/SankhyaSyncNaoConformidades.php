<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\SankhyaAuthService;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Models\NaoConformidade;
use App\Models\OrdemServico;
use App\Models\TipoNaoConformidade;
use App\Services\SyncLogService;

class SankhyaSyncNaoConformidades extends Command
{
    protected $signature   = 'sankhya:sync-nao-conformidades';
    protected $description = 'Sincroniza nao-conformidades das OSs (AD_VGFOSENC).';

    public function handle(): int
    {
        $syncLog = (new SyncLogService('nao-conformidades'))->start();
        try {
            $this->info('Iniciando sync de nao-conformidades...');
            $inicio = microtime(true);

            $token = (new SankhyaAuthService())->login();
            if (!$token) {
                throw new \RuntimeException('Falha ao autenticar no Sankhya.');
            }

            $service = new SankhyaLoadRecordsService();

            $records = $service->fetchAll(
                token:      $token,
                rootEntity: 'AD_VGFOSENC',
                fields:     ['' => ['NUMOS', 'ID', 'TIPONC', 'SETOR', 'TIPORES', 'STATUSNC', 'CRITICIDADENC']]
            );

            $data = collect($records)->map(function ($row) {
                $numos     = $row['f0']['$'] ?? null;
                $codEncSnk = $row['f1']['$'] ?? null;

                if (!$numos || !$codEncSnk) return null;

                $osId = OrdemServico::where('numos', $numos)->value('id');
                if (!$osId) return null;

                $tipoNcSnk = $row['f2']['$'] ?? null;
                $tipoNcId  = $tipoNcSnk
                    ? TipoNaoConformidade::where('codtiponc_snk', $tipoNcSnk)->value('id')
                    : null;

                return [
                    'codenc_snk'               => (int) $codEncSnk,
                    'ordem_servico_id'         => $osId,
                    'tipo_nao_conformidade_id' => $tipoNcId,
                    'setor'                    => $row['f3']['$'] ?? null,
                    'tipores'                  => $row['f4']['$'] ?? null,
                    'statusnc'                 => $row['f5']['$'] ?? null,
                    'criticidadenc'            => $row['f6']['$'] ?? null,
                    'created_at'               => now(),
                    'updated_at'               => now(),
                ];
            })->filter();

            if ($data->isNotEmpty()) {
                NaoConformidade::upsert(
                    $data->toArray(),
                    ['codenc_snk'],
                    ['ordem_servico_id', 'tipo_nao_conformidade_id', 'setor', 'tipores', 'statusnc', 'criticidadenc', 'updated_at']
                );
            }

            $duracao = round(microtime(true) - $inicio, 2);
            $this->info("Sincronizados: {$data->count()} nao-conformidades em {$duracao}s.");

            $syncLog->finish($data->count());
            return 0;
        } catch (\Throwable $e) {
            $syncLog->fail($e);
            $this->error('Erro: ' . $e->getMessage());
            return 1;
        }
    }
}
