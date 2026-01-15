<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\AuthSankhya;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Models\Ambiente;

class BuscarAmbientesSankhya extends Command
{
    protected $signature = 'sankhya:buscar-ambientes';

    protected $description = 'Busca ambientes (setores) no Sankhya e sincroniza a base local.';

    public function handle(): int
    {
        $this->info('Iniciando sincronização de ambientes...');

        $inicio = microtime(true);

        $token = (new AuthSankhya())->login();
        if (!$token) {
            $this->error('Falha ao autenticar no Sankhya.');
            return 1;
        }

        $service = new SankhyaLoadRecordsService();

        $records = $service->fetchAll(
            token: $token,
            rootEntity: 'AD_SETORES',
            fields: [
                '' => [
                    'ID',
                    'DESCRICAO',
                ]
            ]
        );

        if (empty($records)) {
            $this->info('Nenhum ambiente encontrado.');
            return 0;
        }

        $ambientes = collect($records)
            ->map(function ($row) {
                return [
                    'codsetor_snk' => $row['f0']['$'] ?? null,
                    'descricao'    => $row['f1']['$'] ?? null,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];
            })
            ->filter(fn ($a) => !empty($a['codsetor_snk']));

        if ($ambientes->isNotEmpty()) {
            Ambiente::upsert(
                $ambientes->toArray(),
                ['codsetor_snk'],
                ['descricao', 'updated_at']
            );
        }

        $duracao = round(microtime(true) - $inicio, 2);

        $this->info("🎯 Total sincronizado: {$ambientes->count()} ambientes em {$duracao}s.");

        return 0;
    }
}
