<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrdemServicoAmbiente extends Model
{
    use HasFactory;

    protected $table = 'ordem_servico_ambientes';

    protected $fillable = [
        'codoseamb_snk',
        'ordem_servico_id',
        'ambiente_id',
        'atividades_termicas',
    ];

    protected $casts = [
        'atividades_termicas' => 'boolean',
    ];

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class);
    }

    public function ambiente()
    {
        return $this->belongsTo(Ambiente::class);
    }

    public function evidenciasPragas()
    {
        return $this->hasMany(EvidenciaPraga::class);
    }
}
