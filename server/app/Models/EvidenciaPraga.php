<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EvidenciaPraga extends Model
{
    use HasFactory;

    protected $table = 'evidencias_pragas';

    protected $fillable = [
        'codevidencia_snk',
        'ordem_servico_id',
        'ordem_servico_ambiente_id',
        'praga_id',
        'tipo_praga_id',
        'tipo_evidencia_id',
        'individuo_id',
        'evidenciador_id',
        'data_evidencia',
        'quantidade',
        'fase_praga',
        'evidenciador_nome',
        'observacoes',
    ];

    protected $casts = [
        'data_evidencia' => 'date',
    ];

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class);
    }

    public function ordemServicoAmbiente()
    {
        return $this->belongsTo(OrdemServicoAmbiente::class);
    }

    public function praga()
    {
        return $this->belongsTo(Praga::class);
    }

    public function tipoPraga()
    {
        return $this->belongsTo(TipoPraga::class);
    }

    public function tipoEvidencia()
    {
        return $this->belongsTo(TipoEvidencia::class);
    }

    public function individuo()
    {
        return $this->belongsTo(Individuo::class);
    }

    public function evidenciador()
    {
        return $this->belongsTo(Evidenciador::class);
    }

    public function ambiente()
    {
        return $this->belongsTo(OrdemServicoAmbiente::class, 'ordem_servico_ambiente_id');
    }
}
