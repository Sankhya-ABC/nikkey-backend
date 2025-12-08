<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sankhya\AuthSankhya;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Models\TipoEquipamento;

class BuscarTiposEquipamentoSankhya extends Command
{
    protected $signature = 'sankhya:buscar-tipo-equipamento';
    protected $description = 'Busca tipos de equipamento no Sankhya e atualiza a base local.';

    public function handle(): int
    {
        $this->info('🔍 Buscando Tipos de Equipamento...');
        $inicio = microtime(true);

        $token = (new AuthSankhya())->login();
        if (!$token) {
            $this->error('❌ Falha ao autenticar no Sankhya.');
            return 1;
        }

        $service = new SankhyaLoadRecordsService();

        $records = $service->fetchAll(
            token: $token,
            rootEntity: 'AD_TIPOEQUIPAMENTOS',
            fields: [
                '' => ['ID', 'DESCRICAO']
            ]
        );

        $dados = collect($records)
            ->map(fn($row) => [
                'codtipoequip_snk' => $row['f0']['$'] ?? null,
                'descricao'         => $row['f1']['$'] ?? null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ])
            ->filter(fn($i) => !empty($i['codtipoequip_snk']));

        if ($dados->isNotEmpty()) {
            TipoEquipamento::upsert(
                $dados->toArray(),
                ['codtipoequip_snk'],
                ['descricao', 'updated_at']
            );
        }

        $duracao = round(microtime(true) - $inicio, 2);
        $this->info("🎯 Total sincronizado: {$dados->count()} tipos de equipamento em {$duracao}s.");

        return 0;
    }
}
