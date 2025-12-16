<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    protected $fillable = ['codprod_snk', 'descricao'];

    public function produtosPrevistos()
    {
        return $this->hasMany(ProdutoPrevisto::class);
    }
}



