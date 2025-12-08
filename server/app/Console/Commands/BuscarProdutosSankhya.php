<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\AuthSankhya;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Models\Produto;

class BuscarProdutosSankhya extends Command
{
    protected $signature = 'sankhya:buscar-produtos';
    protected $description = 'Busca produtos no Sankhya com USOPROD diferente de "S" e atualiza a base local.';

    public function handle(): int
    {
        $this->info('🚀 Iniciando sincronização de produtos Sankhya...');
        $inicio = microtime(true);

        $token = (new AuthSankhya())->login();
        if (!$token) {
            $this->error('❌ Falha ao autenticar no Sankhya.');
            return 1;
        }

        $service = new SankhyaLoadRecordsService();

        // 🔹 Payload com USOPROD <> 'S'
        $records = $service->fetchAll(
            token: $token,
            rootEntity: 'Produto',
            fields: [
                '' => ['CODPROD', 'DESCRPROD']
            ],
            criteria: [
                ['field' => 'USOPROD', 'value' => 'S', 'operator' => '<>', 'type' => 'S']
            ]
        );

        $produtos = collect($records)
            ->map(fn($p) => [
                'codprod_snk' => $p['f0']['$'] ?? null,
                'descricao'   => $p['f1']['$'] ?? null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ])
            ->filter(fn($p) => !empty($p['codprod_snk']));

        if ($produtos->isNotEmpty()) {
            Produto::upsert(
                $produtos->toArray(),
                ['codprod_snk'],
                ['descricao', 'updated_at']
            );
        }

        $duracao = round(microtime(true) - $inicio, 2);
        $this->info("🎯 Total sincronizado: {$produtos->count()} produtos em {$duracao}s.");

        return 0;
    }
}
