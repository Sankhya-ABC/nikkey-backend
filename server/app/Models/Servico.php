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

    public function pragas()
    {
        return $this->belongsToMany(
            Praga::class,
            'os_servico_pragas',
            'os_servico_servico_id',
            'praga_id'
        )->withTimestamps();
    }

    public function produtosPrevistos()
    {
        return $this->hasMany(ProdutoPrevisto::class);
    }
}

