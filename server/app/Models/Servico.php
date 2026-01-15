<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servico extends Model
{
    protected $fillable = ['codprod_snk', 'descricao'];

    public function ordensServico()
    {
        return $this->belongsToMany(
            OrdemServico::class,
            'os_servico',
            'servico_id',
            'ordem_servico_id'
        )->withTimestamps();
    }

    public function produtosPrevistos()
    {
        return $this->hasMany(ProdutoPrevisto::class);
    }

    public function produtosUtilizados()
    {
        return $this->hasMany(ProdutosUtilizados::class);
    }
}

