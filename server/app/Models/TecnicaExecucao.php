<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TecnicaExecucao extends Model
{
    protected $table = 'tecnica_execucao';

    protected $fillable = [
        'descricao',
        'codtecexec_snk'
    ];

    public function metodologias()
    {
        return $this->hasMany(Metodologia::class, 'tecexecucao_id');
    }
}
