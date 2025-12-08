<?php

namespace App\Jobs;

use App\Models\Cliente;
use App\Jobs\SyncBaseJob;
use Illuminate\Support\Facades\Log;

class SyncClientesPageJob extends SyncBaseJob
{
    public function __construct(int $pageSize = 500, int $startOffset = 0)
    {
        parent::__construct($pageSize, $startOffset);

        $this->rootEntity = 'Parceiro';
        $this->fields = [
            ''        => ['CODPARC','NOMEPARC','RAZAOSOCIAL','CODPARCMATRIZ','NUMEND','COMPLEMENTO','CEP','ATIVO'],
            'Endereco'=> ['NOMEEND'],
            'Bairro'  => ['NOMEBAI'],
            'Cidade'  => ['NOMECID'],
            'Cidade.UnidadeFederativa' => ['UF']
        ];
        $this->criteria = [
            ['field' => 'CLIENTE', 'value' => 'S', 'type' => 'S']
        ];

        // Retry e backoff
        $this->tries = 5;
        $this->backoff = [60, 120, 300]; // 1min, 2min, 5min
    }

    protected function mapRecord(array $rec): ?array
    {
        return [
            'codparc_snk'        => $rec['f0']['$'] ?? null,
            'nome_fantasia'      => $rec['f1']['$'] ?? null,
            'razao_social'       => $rec['f2']['$'] ?? null,
            'codparc_matriz_snk' => $rec['f3']['$'] ?? null,
            'numero'             => $rec['f4']['$'] ?? null,
            'complemento'        => $rec['f5']['$'] ?? null,
            'cep'                => $rec['f6']['$'] ?? null,
            'ativo'              => isset($rec['f7']['$']) && $rec['f7']['$'] === 'S' ? 1 : 0,
            'logradouro'         => $rec['f8']['$'] ?? null,
            'bairro'             => $rec['f9']['$'] ?? null,
            'cidade'             => $rec['f10']['$'] ?? null,
            'estado'             => $rec['f11']['$'] ?? null,
            'created_at'         => now(),
            'updated_at'         => now(),
        ];
    }

    protected function persistChunk(array $mappedChunk): void
    {
        if (empty($mappedChunk)) return;

        try {
            Cliente::upsert(
                $mappedChunk,
                ['codparc_snk'],
                ['nome_fantasia','logradouro','bairro','cidade','estado','numero','cep','updated_at','ativo','razao_social','codparc_matriz_snk','complemento']
            );

            Log::info("✅ Chunk Clientes persistido: " . count($mappedChunk) . " registros (offset {$this->startOffset})");

        } catch (\Throwable $e) {
            Log::error("❌ Erro persistindo chunk Clientes: " . $e->getMessage());
            throw $e;
        }
    }
}
