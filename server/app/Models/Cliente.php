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
        'ativo'
    ];

    protected $casts = [
        'tem_contrato' => 'boolean',
        'ativo' => 'boolean',
        'validade_certificado' => 'date',
    ];

    // Usuario(s) vinculados
    public function usuarios()
    {
        return $this->hasMany(User::class);
    }

    // Ordens de serviço do cliente
    public function ordensServico()
    {
        return $this->hasMany(OrdemServico::class, 'cliente_id');
    }
}

