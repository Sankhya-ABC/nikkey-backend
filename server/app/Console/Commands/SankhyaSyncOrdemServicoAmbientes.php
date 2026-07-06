<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\SankhyaAuthService;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Models\OrdemServico;
use App\Models\Ambiente;
use App\Models\OrdemServicoAmbiente;
use App\Services\SyncLogService;

class SankhyaSyncOrdemServicoAmbientes extends Command
{
    protected $signature = 'sankhya:sync-os-ambientes';
    protected $description = 'Busca ambientes das ordens de servico no Sankhya e sincroniza a base local.';

    public function handle(): int
    {
        $syncLog = (new SyncLogService('os-ambientes'))->start();
        try {
            $this->info('Iniciando sincronizacao de ambientes da OS...');
            $inicio = microtime(true);

            $token = (new SankhyaAuthService())->login();
            if (!$token) {
                throw new \RuntimeException('Falha ao autenticar no Sankhya.');
            }

            $service = new SankhyaLoadRecordsService();

            $records = $service->fetchAll(
                token: $token,
                rootEntity: 'AD_VGFOSESET',
                fields: ['' => ['NUMOS', 'ID', 'AMBIENTE', 'SETOR', 'ATVTEM']]
            );

            $osAmbientes = collect($records)
                ->map(function ($row) {
                    $numos       = $row['f0']['$'] ?? null;
                    $codSnk      = $row['f1']['$'] ?? null;
                    $ambienteSnk = $row['f2']['$'] ?? null;

                    if (!$numos) return null;

                    $ordemServicoId = OrdemServico::where('numos', $numos)->value('id');
                    if (!$ordemServicoId) {
                        \Log::warning('AMBIENTE DESCARTADO: OS nao encontrada', ['numos' => $numos, 'cod_snk' => $codSnk]);
                        return null;
                    }

                    $ambienteId = $ambienteSnk ? Ambiente::where('codsetor_snk', $ambienteSnk)->value('id') : null;

                    return [
                        'codoseamb_snk'       => $codSnk,
                        'ordem_servico_id'    => $ordemServicoId,
                        'ambiente_id'         => $ambienteId,
                        'setor'               => $row['f3']['$'] ?? null,
                        'atividades_termicas' => ($row['f4']['$'] ?? 'N') === 'S',
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ];
                })
                ->filter(fn($x) => $x !== null);

            if ($osAmbientes->isNotEmpty()) {
                OrdemServicoAmbiente::upsert(
                    $osAmbientes->toArray(),
                    ['codoseamb_snk'],
                    ['ordem_servico_id', 'ambiente_id', 'setor', 'atividades_termicas', 'updated_at']
                );
            }

            $duracao = round(microtime(true) - $inicio, 2);
            $this->info("Total sincronizado: {$osAmbientes->count()} ambientes de OS em {$duracao}s.");

            $syncLog->finish($osAmbientes->count());
            return 0;
        } catch (\Throwable $e) {
            $syncLog->fail($e);
            $this->error('Erro: ' . $e->getMessage());
            return 1;
        }
    }
}
