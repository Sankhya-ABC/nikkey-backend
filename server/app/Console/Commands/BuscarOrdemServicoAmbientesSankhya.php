<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\AuthSankhya;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Models\OrdemServico;
use App\Models\Ambiente;
use App\Models\OrdemServicoAmbiente;

class BuscarOrdemServicoAmbientesSankhya extends Command
{
    protected $signature = 'sankhya:buscar-os-ambientes';

    protected $description = 'Busca ambientes das ordens de serviço no Sankhya e sincroniza a base local.';

    public function handle(): int
    {
        $this->info('🚀 Iniciando sincronização de ambientes da OS...');

        $inicio = microtime(true);

        $token = (new AuthSankhya())->login();
        if (!$token) {
            $this->error('❌ Falha ao autenticar no Sankhya.');
            return 1;
        }

        $service = new SankhyaLoadRecordsService();

        $records = $service->fetchAll(
            token: $token,
            rootEntity: 'AD_VGFOSESET',
            fields: [
                '' => [
                    'NUMOS',
                    'ID',
                    'AMBIENTE',
                    'SETOR',
                    'ATVTEM',
                ]
            ]
        );

        if (empty($records)) {
            $this->info('Nenhum ambiente de OS encontrado.');
            return 0;
        }

    $osAmbientes = collect($records)
        ->map(function ($row) {
            $numos = $row['f0']['$'] ?? null;
            $codSnk = $row['f1']['$'] ?? null;
            $ambienteSnk = $row['f2']['$'] ?? null;

            if (!$numos) {
                return null; // descarta apenas se a OS não existir
            }

            $ordemServicoId = OrdemServico::where('numos', $numos)->value('id');
            if (!$ordemServicoId) {
                \Log::warning('AMBIENTE DESCARTADO: OS não encontrada', ['numos' => $numos, 'cod_snk' => $codSnk]);
                return null; // descarta se OS não existir
            }

            $ambienteId = $ambienteSnk ? Ambiente::where('codsetor_snk', $ambienteSnk)->value('id') : null;

            return [
                'codoseamb_snk' => $codSnk,
                'ordem_servico_id' => $ordemServicoId, // obrigatória
                'ambiente_id'      => $ambienteId,      // pode ser null
                'setor'            => $row['f3']['$'] ?? null,
                'atividades_termicas' => ($row['f4']['$'] ?? 'N') === 'S',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })
        ->filter(fn($x) => $x !== null); 

        if ($osAmbientes->isNotEmpty()) {
            OrdemServicoAmbiente::upsert(
                $osAmbientes->toArray(),
                ['codoseamb_snk'],
                [
                    'ordem_servico_id',
                    'ambiente_id',
                    'setor',
                    'atividades_termicas',
                    'updated_at',
                ]
            );
        }

        $duracao = round(microtime(true) - $inicio, 2);

        $this->info("🎯 Total sincronizado: {$osAmbientes->count()} ambientes de OS em {$duracao}s.");

        return 0;
    }
}
