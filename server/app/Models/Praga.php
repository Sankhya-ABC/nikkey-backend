<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Praga extends Model
{
    protected $fillable = ['codpraga_snk', 'nome_praga', 'grupo_praga_id'];

    public function grupo()
    {
        return $this->belongsTo(GrupoPraga::class, 'grupo_praga_id');
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
