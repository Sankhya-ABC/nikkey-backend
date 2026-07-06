<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\SankhyaAuthService;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Models\TecnicaExecucao;
use App\Services\SyncLogService;

class SankhyaSyncTecnicasExecucao extends Command
{
    protected $signature = 'sankhya:sync-tecnicas-execucao';
    protected $description = 'Busca tecnicas de execucao no Sankhya e atualiza a base local.';

    public function handle(): int
    {
        $syncLog = (new SyncLogService('tecnicas-execucao'))->start();
        try {
            $this->info('Buscando Tecnicas de Execucao...');
            $inicio = microtime(true);

            $token = (new SankhyaAuthService())->login();
            if (!$token) {
                throw new \RuntimeException('Falha ao autenticar no Sankhya.');
            }

            $service = new SankhyaLoadRecordsService();

            $records = $service->fetchAll(
                token: $token,
                rootEntity: 'AD_TECNICAEXEC',
                fields: ['' => ['ID', 'DESCRICAO']]
            );

            $dados = collect($records)
                ->map(fn($row) => [
                    'codtecexec_snk' => $row['f0']['$'] ?? null,
                    'descricao'      => $row['f1']['$'] ?? null,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ])
                ->filter(fn($i) => !empty($i['codtecexec_snk']));

            if ($dados->isNotEmpty()) {
                TecnicaExecucao::upsert($dados->toArray(), ['codtecexec_snk'], ['descricao', 'updated_at']);
            }

            $duracao = round(microtime(true) - $inicio, 2);
            $this->info("Total sincronizado: {$dados->count()} tecnicas em {$duracao}s.");

            $syncLog->finish($dados->count());
            return 0;
        } catch (\Throwable $e) {
            $syncLog->fail($e);
            $this->error('Erro: ' . $e->getMessage());
            return 1;
        }
    }
}
