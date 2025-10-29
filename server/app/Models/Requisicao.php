<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requisicao extends Model
{
    use HasFactory;

    protected $table = 'requisicoes';

    protected $fillable = [
        'servico_id',
        'corpo',
    ];

    /**
     * Uma requisição pertence a um serviço.
     */
    public function servico()
    {
        return $this->belongsTo(Servico::class, 'servico_id');
    }
}
