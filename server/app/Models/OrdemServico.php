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

    // Cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // Técnico
    public function tecnico()
    {
        return $this->belongsTo(Tecnico::class);
    }

    // Serviços aplicados
    public function servicos()
    {
        return $this->belongsToMany(
            Servico::class,
            'os_servico',
            'ordem_servico_id',
            'servico_id'
        )->withTimestamps();
    }

    public function produtosPrevistos()
    {
        return $this->hasMany(ProdutoPrevisto::class);
    }
}