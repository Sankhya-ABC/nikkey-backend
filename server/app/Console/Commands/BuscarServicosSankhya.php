<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\AuthSankhya;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Models\Servico;

class BuscarServicosSankhya extends Command
{
    protected $signature = 'sankhya:buscar-servicos';
    protected $description = 'Busca serviços no Sankhya e atualiza a base local.';

    public function handle(): int
    {
        $this->info('🚀 Iniciando sincronização de serviços Sankhya...');
        $inicio = microtime(true);

        $token = (new AuthSankhya())->login();
        if (!$token) {
            $this->error('❌ Falha ao autenticar no Sankhya.');
            return 1;
        }

        $service = new SankhyaLoadRecordsService();

        // 🔁 REUTILIZAÇÃO TOTAL
        $records = $service->fetchAll(
            token: $token,
            rootEntity: 'Produto',
            fields: [
                '' => ['CODPROD', 'DESCRPROD']
            ],
            criteria: [
                ['field' => 'USOPROD', 'value' => 'S', 'operator' => '=', 'type' => 'S']
            ]
        );

        // Transformação rápida e clara
        $servicos = collect($records)
            ->map(fn($srv) => [
                'codprod_snk' => $srv['f0']['$'] ?? null,
                'descricao'   => $srv['f1']['$'] ?? null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ])
            ->filter(fn($s) => !empty($s['codprod_snk']));

        if ($servicos->isNotEmpty()) {
            Servico::upsert(
                $servicos->toArray(),
                ['codprod_snk'],
                ['descricao', 'updated_at']
            );
        }

        $duracao = round(microtime(true) - $inicio, 2);
        $this->info("🎯 Total sincronizado: {$servicos->count()} serviços em {$duracao}s.");

        return 0;
    }
}
