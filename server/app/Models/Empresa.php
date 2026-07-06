<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $fillable = [
        'codemp_snk', 'nome_fantasia', 'razao_social', 'cgc',
        'logradouro', 'numero', 'bairro', 'cidade', 'estado',
        'complemento', 'cep', 'telefone', 'alvara', 'alvara_vencimento',
    ];

    protected $casts = [
        'alvara_vencimento' => 'date',
    ];
}