<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\AuthSankhya;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Models\TipoEvidencia;

class BuscarTiposEvidenciaSankhya extends Command
{
    protected $signature = 'sankhya:buscar-tipos-evidencia';

    protected $description = 'Busca tipos de evidência no Sankhya e sincroniza a base local.';

    public function handle(): int
    {
        $this->info('Iniciando sincronização de tipos de evidência...');

        $inicio = microtime(true);

        $token = (new AuthSankhya())->login();
        if (!$token) {
            $this->error('Falha ao autenticar no Sankhya.');
            return 1;
        }

        $service = new SankhyaLoadRecordsService();

        $records = $service->fetchAll(
            token: $token,
            rootEntity: 'AD_TIPEVIDENCIAS',
            fields: [
                '' => [
                    'ID',
                    'DESCRICAO',
                    'IMAGEMPROD',
                    'IMAGEMID',
                ]
            ]
        );

        if (empty($records)) {
            $this->info('Nenhum tipo de evidência encontrado.');
            return 0;
        }

        $tiposEvidencia = collect($records)
            ->map(function ($row) {

                return [
                    'codenvidencia_snk' => $row['f0']['$'] ?? null,
                    'descricao'         => $row['f1']['$'] ?? null,
                    'imagem_produto'    => $this->decodeImage($row['f2']['$'] ?? null),
                    'imagem_identificacao' => $this->decodeImage($row['f3']['$'] ?? null),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];
            })
            ->filter(fn ($e) => !empty($e['codenvidencia_snk']));

        if ($tiposEvidencia->isNotEmpty()) {
            TipoEvidencia::upsert(
                $tiposEvidencia->toArray(),
                ['codenvidencia_snk'],
                ['descricao', 'imagem_produto', 'imagem_identificacao', 'updated_at']
            );
        }

        $duracao = round(microtime(true) - $inicio, 2);

        $this->info("🎯 Total sincronizado: {$tiposEvidencia->count()} tipos de evidência em {$duracao}s.");

        return 0;
    }

    private function decodeImage(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        if (str_starts_with($value, '[B@')) {
            return null;
        }

        return base64_decode($value);
    }
}
