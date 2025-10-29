<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'codparc_snk',
        'codparc_matriz_snk',
        'cliente_pai_id',
        'razao_social',
        'nome_fantasia',
        'cnpj_cpf',
        'validade_certificado',
        'tipo_atividade',
        'tem_contrato',
        'logradouro',
        'complemento',
        'numero',
        'bairro',
        'cidade',
        'estado',
        'cep',
        'contato',
        'telefone',
        'email',
    ];

    public function grupo()
    {
        return $this->belongsTo(Cliente::class, 'cliente_pai_id');
    }

    public function filiais()
    {
        return $this->hasMany(Cliente::class, 'cliente_pai_id');
    }

    public function usuarios()
    {
        return $this->hasMany(\App\Models\User::class, 'cliente_id');
    }
}
