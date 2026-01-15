<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ambiente extends Model
{
    use HasFactory;

    protected $table = 'ambientes';

    protected $fillable = [
        'descricao',
        'codsetor_snk'
    ];

    public function ordensServico()
    {
        return $this->belongsToMany(
            OrdemServico::class,
            'ordem_servico_ambientes'
        )->withPivot('atividades_termicas')
         ->withTimestamps();
    }
}
