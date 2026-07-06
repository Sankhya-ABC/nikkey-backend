<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\SankhyaAuthService;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Models\TipoNaoConformidade;
use App\Services\SyncLogService;

class SankhyaSyncTiposNaoConformidade extends Command
{
    protected $signature   = 'sankhya:sync-tipos-nao-conformidade';
    protected $description = 'Sincroniza tipos de nao-conformidade (AD_TIPNAOCONFORM).';

    public function handle(): int
    {
        $syncLog = (new SyncLogService('tipos-nao-conformidade'))->start();
        try {
            $this->info('Iniciando sync de tipos de nao-conformidade...');
            $inicio = microtime(true);

            $token = (new SankhyaAuthService())->login();
            if (!$token) {
                throw new \RuntimeException('Falha ao autenticar no Sankhya.');
            }

            $service = new SankhyaLoadRecordsService();

            $records = $service->fetchAll(
                token:      $token,
                rootEntity: 'AD_TIPNAOCONFORM',
                fields:     ['' => ['ID', 'DESCRICAO']]
            );

            $data = collect($records)->map(fn($r) => [
                'codtiponc_snk' => (int) ($r['f0']['$'] ?? 0),
                'descricao'     => $r['f1']['$'] ?? '',
                'created_at'    => now(),
                'updated_at'    => now(),
            ])->filter(fn($r) => $r['codtiponc_snk'] > 0);

            if ($data->isNotEmpty()) {
                TipoNaoConformidade::upsert($data->toArray(), ['codtiponc_snk'], ['descricao', 'updated_at']);
            }

            $duracao = round(microtime(true) - $inicio, 2);
            $this->info("Sincronizados: {$data->count()} tipos em {$duracao}s.");

            $syncLog->finish($data->count());
            return 0;
        } catch (\Throwable $e) {
            $syncLog->fail($e);
            $this->error('Erro: ' . $e->getMessage());
            return 1;
        }
    }
}