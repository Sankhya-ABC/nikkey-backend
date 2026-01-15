<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdemServico extends Model
{
    use HasFactory;

    protected $table = 'ordens_servico';

    protected $fillable = [
        'numos',
        'cliente_id',
        'tecnico_id',
        'tipoos',
        'statusos',
        'hrini',
        'hrfin',
        'asscli',
        'asstec',
        'latitude',
        'longitude',
        'ad_numnikkey',
        'latitudeini',
        'longitudeini',
        'dhprevista',
        'dhprevistafin',
        'duracao',
        'servico',
        'codvei',
        'confage',
        'idcliente'
    ];

    protected $casts = [
        'hrini' => 'datetime',
        'hrfin' => 'datetime',
        'dhprevista' => 'datetime',
        'dhprevistafin' => 'datetime'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function tecnico()
    {
        return $this->belongsTo(Tecnico::class);
    }

    public function previsoesExecucao()
    {
        return $this->hasMany(PrevisaoExecucaoOs::class);
    }

    public function produtosPrevistos()
    {
        return $this->hasMany(ProdutoPrevisto::class);
    }

    public function produtosUtilizados()
    {
        return $this->hasMany(ProdutosUtilizados::class);
    }

    public function ambientes()
    {
        return $this->hasMany(OrdemServicoAmbiente::class);
    }

    public function evidenciasPragas()
    {
        return $this->hasMany(EvidenciaPraga::class);
    }
}