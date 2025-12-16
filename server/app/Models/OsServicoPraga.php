<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OsServicoPraga extends Model
{
    protected $table = 'os_servico_pragas';
    public $incrementing = false; // chave composta
    protected $primaryKey = ['ordem_servico_id', 'servico_id', 'praga_id'];

    protected $fillable = [
        'ordem_servico_id',
        'servico_id',
        'praga_id',
    ];

    public function ordemServico()
    {
        return $this->belongsTo(\App\Models\OrdemServico::class, 'ordem_servico_id');
    }

    public function servico()
    {
        return $this->belongsTo(\App\Models\Servico::class, 'servico_id');
    }

    public function praga()
    {
        return $this->belongsTo(\App\Models\Praga::class, 'praga_id');
    }
}
