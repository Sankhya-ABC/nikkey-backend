<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrevisaoExecucaoOs extends Model
{
    protected $table = 'previsoes_execucao_os';

    protected $fillable = [
        'codprevisao_snk',
        'ordem_servico_id',

        'inst_temp_prev',
        'ins_dias_prev',
        'ins_pessoas_prev',

        'mon_temp_prev',
        'mon_dias_prev',
        'mon_pessoas_prev',

        'hrini',
        'hrfin',
    ];

    protected $casts = [
        'hrini' => 'datetime',
        'hrfin' => 'datetime',
    ];

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class);
    }
}
