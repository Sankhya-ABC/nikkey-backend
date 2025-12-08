<?php

namespace App\Jobs;

use App\Models\Praga;

class SyncPragasJob extends SyncBaseJob
{
    public function __construct(int $pageSize = 200, int $startOffset = 0)
    {
        parent::__construct($pageSize, $startOffset);
        $this->rootEntity = 'AD_TABPRAGAS';
        $this->fields = ['' => ['CODPRAGA','NOME_PRAGA','GRU_PRAGAS']];
    }

    protected function mapRecord(array $rec): ?array
    {
        return [
            'codpraga_snk' => $rec['f0']['$'] ?? null,
            'nome_praga'   => $rec['f1']['$'] ?? null,
            'grupo_praga_id' => $rec['f2']['$'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    protected function persistChunk(array $mappedChunk): void
    {
        Praga::upsert(
            $mappedChunk,
            ['codpraga_snk'],
            ['nome_praga','grupo_praga_id','updated_at']
        );
    }
}
