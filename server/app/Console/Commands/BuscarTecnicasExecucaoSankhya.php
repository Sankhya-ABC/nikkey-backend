<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\AuthSankhya;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Models\TecnicaExecucao;

class BuscarTecnicasExecucaoSankhya extends Command
{
    protected $signature = 'sankhya:buscar-tecnica-execucao';
    protected $description = 'Busca técnicas de execução no Sankhya e atualiza a base local.';

    public function handle(): int
    {
        $this->info('🔍 Buscando Técnicas de Execução...');
        $inicio = microtime(true);

        $token = (new AuthSankhya())->login();
        if (!$token) {
            $this->error('❌ Falha ao autenticar no Sankhya.');
            return 1;
        }

        $service = new SankhyaLoadRecordsService();

        $records = $service->fetchAll(
            token: $token,
            rootEntity: 'AD_TECNICAEXEC',
            fields: [
                '' => ['ID', 'DESCRICAO']
            ]
        );

        $dados = collect($records)
            ->map(fn($row) => [
                'codtecexec_snk' => $row['f0']['$'] ?? null,
                'descricao'       => $row['f1']['$'] ?? null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ])
            ->filter(fn($i) => !empty($i['codtecexec_snk']));

        if ($dados->isNotEmpty()) {
            TecnicaExecucao::upsert(
                $dados->toArray(),
                ['codtecexec_snk'],
                ['descricao', 'updated_at']
            );
        }

        $duracao = round(microtime(true) - $inicio, 2);
        $this->info("🎯 Total sincronizado: {$dados->count()} técnicas em {$duracao}s.");

        return 0;
    }
}
