<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificado extends Model
{
    protected $fillable = [
        'numos', 'nunota', 'codemp_snk', 'codvend_snk', 'dt_garantia', 'dias_garantia',
    ];

    protected $casts = [
        'dt_garantia' => 'date',
    ];

    public function empresa(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'codemp_snk', 'codemp_snk');
    }

    public function tecnico(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tecnico::class, 'codvend_snk', 'codvend_snk');
    }
}