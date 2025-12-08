<?php

namespace App\Jobs;

use App\Models\Produto;

class SyncProdutosJob extends SyncBaseJob
{
    public function __construct(int $pageSize = 200, int $startOffset = 0)
    {
        parent::__construct($pageSize, $startOffset);
        $this->rootEntity = 'Produto';
        $this->fields = ['' => ['CODPROD','DESCRPROD']];
        $this->criteria = [['field'=>'USOPROD','value'=>'S','operator'=>'<>','type'=>'S']];
    }

    protected function mapRecord(array $rec): ?array
    {
        return [
            'codprod_snk' => $rec['f0']['$'] ?? null,
            'descricao' => $rec['f1']['$'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    protected function persistChunk(array $mappedChunk): void
    {
        Produto::upsert(
            $mappedChunk,
            ['codprod_snk'],
            ['descricao','updated_at']
        );
    }
}
