<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\SankhyaAuthService;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Models\Servico;
use App\Services\SyncLogService;

class SankhyaSyncServicos extends Command
{
    protected $signature = 'sankhya:sync-servicos';
    protected $description = 'Busca servicos no Sankhya e atualiza a base local.';

    public function handle(): int
    {
        $syncLog = (new SyncLogService('servicos'))->start();
        try {
            $this->info('Iniciando sincronizacao de servicos Sankhya...');
            $inicio = microtime(true);

            $token = (new SankhyaAuthService())->login();
            if (!$token) {
                throw new \RuntimeException('Falha ao autenticar no Sankhya.');
            }

            $service = new SankhyaLoadRecordsService();

            $records = $service->fetchAll(
                token: $token,
                rootEntity: 'Produto',
                fields: ['' => ['CODPROD', 'DESCRPROD']],
                criteria: [
                    ['field' => 'USOPROD', 'value' => 'S', 'operator' => '=', 'type' => 'S']
                ]
            );

            $servicos = collect($records)
                ->map(fn($srv) => [
                    'codprod_snk' => $srv['f0']['$'] ?? null,
                    'descricao'   => $srv['f1']['$'] ?? null,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ])
                ->filter(fn($s) => !empty($s['codprod_snk']));

            if ($servicos->isNotEmpty()) {
                Servico::upsert($servicos->toArray(), ['codprod_snk'], ['descricao', 'updated_at']);
            }

            $duracao = round(microtime(true) - $inicio, 2);
            $this->info("Total sincronizado: {$servicos->count()} servicos em {$duracao}s.");

            $syncLog->finish($servicos->count());
            return 0;
        } catch (\Throwable $e) {
            $syncLog->fail($e);
            $this->error('Erro: ' . $e->getMessage());
            return 1;
        }
    }
}
