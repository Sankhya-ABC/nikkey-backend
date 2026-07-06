<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\SankhyaAuthService;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Models\TipoEvidencia;
use App\Services\SyncLogService;

class SankhyaSyncTiposEvidencia extends Command
{
    protected $signature = 'sankhya:sync-tipos-evidencia';
    protected $description = 'Busca tipos de evidencia no Sankhya e sincroniza a base local.';

    public function handle(): int
    {
        $syncLog = (new SyncLogService('tipos-evidencia'))->start();
        try {
            $this->info('Iniciando sincronizacao de tipos de evidencia...');
            $inicio = microtime(true);

            $token = (new SankhyaAuthService())->login();
            if (!$token) {
                throw new \RuntimeException('Falha ao autenticar no Sankhya.');
            }

            $service = new SankhyaLoadRecordsService();

            $records = $service->fetchAll(
                token: $token,
                rootEntity: 'AD_TIPEVIDENCIAS',
                fields: ['' => ['ID', 'DESCRICAO', 'IMAGEMPROD', 'IMAGEMID']]
            );

            $tiposEvidencia = collect($records)
                ->map(function ($row) {
                    return [
                        'codenvidencia_snk'    => $row['f0']['$'] ?? null,
                        'descricao'            => $row['f1']['$'] ?? null,
                        'imagem_produto'       => $this->decodeImage($row['f2']['$'] ?? null),
                        'imagem_identificacao' => $this->decodeImage($row['f3']['$'] ?? null),
                        'created_at'           => now(),
                        'updated_at'           => now(),
                    ];
                })
                ->filter(fn($e) => !empty($e['codenvidencia_snk']));

            if ($tiposEvidencia->isNotEmpty()) {
                TipoEvidencia::upsert(
                    $tiposEvidencia->toArray(),
                    ['codenvidencia_snk'],
                    ['descricao', 'imagem_produto', 'imagem_identificacao', 'updated_at']
                );
            }

            $duracao = round(microtime(true) - $inicio, 2);
            $this->info("Total sincronizado: {$tiposEvidencia->count()} tipos de evidencia em {$duracao}s.");

            $syncLog->finish($tiposEvidencia->count());
            return 0;
        } catch (\Throwable $e) {
            $syncLog->fail($e);
            $this->error('Erro: ' . $e->getMessage());
            return 1;
        }
    }

    private function decodeImage(?string $value): ?string
    {
        if (!$value) return null;
        if (str_starts_with($value, '[B@')) return null;
        return base64_decode($value);
    }
}
