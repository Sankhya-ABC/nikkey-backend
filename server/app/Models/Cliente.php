<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\OrdemServico;   
use App\Models\Endereco;

class Cliente extends Model
{
    use HasFactory;

       protected $fillable = [
        'codparc_snk',
        'codparc_matriz_snk',
        'nome_fantasia',
        'razao_social',
        'endereco_id',
        'bairro_id',
        'cidade_id',
        'numero',
        'complemento',
        'cep',
        'ativo'
    ];

    protected $casts = [
        'tem_contrato' => 'boolean',
        'ativo' => 'boolean',
        'validade_certificado' => 'date',
    ];

    public function usuarios()
    {
        return $this->hasMany(User::class);
    }

    public function ordensServico()
    {
        return $this->hasMany(OrdemServico::class, 'cliente_id');
    }

     public function endereco()
    {
        return $this->belongsTo(Endereco::class);
    }

    public function bairro()
    {
        return $this->belongsTo(Bairro::class);
    }

    public function cidade()
    {
        return $this->belongsTo(Cidade::class);
    }

    public function uf()
    {
        return $this->hasOneThrough(
            Uf::class,
            Cidade::class,
            'id',
            'id',
            'cidade_id',
            'uf_id'
        );
    }
}

