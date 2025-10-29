<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servico extends Model
{
    use HasFactory;

    protected $table = 'servicos';

    protected $fillable = [
        'nome',
        'descricao',
    ];

    /**
     * Um serviço pode ter várias requisições.
     */
    public function requisicoes()
    {
        return $this->hasMany(Requisicao::class, 'servico_id');
    }
}
