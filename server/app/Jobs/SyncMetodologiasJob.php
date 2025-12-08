<?php

namespace App\Jobs;

use App\Models\Metodologia;
use App\Models\TecnicaExecucao;
use App\Models\TipoEquipamento;

class SyncMetodologiasJob extends SyncBaseJob
{
    public function __construct(int $pageSize = 200, int $startOffset = 0)
    {
        parent::__construct($pageSize, $startOffset);
        $this->rootEntity = 'AD_METODOLOGIA';
        $this->fields = ['' => ['ID','DESCRICAO','TECEXECUCAO','TIPOEQP']];
    }

    protected function mapRecord(array $rec): ?array
    {
        $snkId = $rec['f0']['$'] ?? null;
        if (!$snkId) return null;

        return [
            'codmetodologia_snk' => $snkId,
            'descricao' => $rec['f1']['$'] ?? null,
            'tecexecucao_id' => TecnicaExecucao::where('codtecexec_snk', $rec['f2']['$'] ?? null)->value('id'),
            'tipoequip_id' => TipoEquipamento::where('codtipoequip_snk', $rec['f3']['$'] ?? null)->value('id'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    protected function persistChunk(array $mappedChunk): void
    {
        Metodologia::upsert(
            $mappedChunk,
            ['codmetodologia_snk'],
            ['descricao','tecexecucao_id','tipoequip_id','updated_at']
        );
    }
}
