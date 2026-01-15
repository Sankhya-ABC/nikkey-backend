<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProdutoUtilizado extends Model
{
    use HasFactory;

    protected $table = 'produtos_utilizados';

    protected $fillable = [
        'ordem_servico_id',
        'servico_id',
        'praga_id',
        'produto_id',
        'metodologia_id',
        'codprodutil_snk',
        'qtdneg',
        'lote',
        'dtfab',
        'dtval',
        'calda',
        'concentracao',
        'grupoquim',
        'principioatv',
        'sintomas',
        'antidoto',
        'codregmapa',
        'acaotoxica',
        'diluente',
        'qtdnegdiluente',
        'lotediluente',
        'tecnicaexec',
        'tpaplicacao',
        'ambiente',
    ];

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class);
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class);
    }

    public function praga()
    {
        return $this->belongsTo(Praga::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function metodologia()
    {
        return $this->belongsTo(Metodologia::class);
    }    
}
