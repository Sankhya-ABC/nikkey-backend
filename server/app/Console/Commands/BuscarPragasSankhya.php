<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\AuthSankhya;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Models\Praga;

class BuscarPragasSankhya extends Command
{
    protected $signature = 'sankhya:buscar-pragas';
    protected $description = 'Busca pragas no Sankhya e atualiza a base local.';

    public function handle(): int
    {
        $this->info('🚀 Iniciando sincronização de pragas Sankhya...');
        $inicio = microtime(true);

        $token = (new AuthSankhya())->login();
        if (!$token) {
            $this->error('❌ Falha ao autenticar no Sankhya.');
            return 1;
        }

        $service = new SankhyaLoadRecordsService();

        $records = $service->fetchAll(
            token: $token,
            rootEntity: 'AD_TABPRAGAS',
            fields: [
                '' => ['CODPRAGA', 'NOME_PRAGA', 'GRU_PRAGAS']
            ]
        );

        $pragas = collect($records)
            ->map(fn($row) => [
                'codpraga_snk' => $row['f0']['$'] ?? null,
                'nome_praga'   => $row['f1']['$'] ?? null,
                'grupo_praga_id' => $row['f2']['$'] ?? null,
                'created_at'   => now(),
                'updated_at'   => now(),
            ])
            ->filter(fn($p) => !empty($p['codpraga_snk']));

        if ($pragas->isNotEmpty()) {
            Praga::upsert(
                $pragas->toArray(),
                ['codpraga_snk'],
                ['nome_praga', 'grupo_praga_id', 'updated_at']
            );
        }

        $duracao = round(microtime(true) - $inicio, 2);
        $this->info("🎯 Total sincronizado: {$pragas->count()} pragas em {$duracao}s.");

        return 0;
    }
}
