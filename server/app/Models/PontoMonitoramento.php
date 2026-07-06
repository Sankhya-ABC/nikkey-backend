<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PontoMonitoramento extends Model
{
    protected $table = 'pontos_monitoramento';

    protected $fillable = [
        'codptmon_snk', 'ordem_servico_id', 'praga_id',
        'tpmonit', 'idequp', 'amb', 'local_ponto', 'setor',
        'consumo', 'consumometade',
    ];
}
