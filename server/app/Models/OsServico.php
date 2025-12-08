<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OsServico extends Model
{
    protected $table = 'os_servico';

    public $incrementing = false; // porque é chave composta

    protected $primaryKey = ['numos', 'codserv']; // apenas para referência

    protected $fillable = [
        'numos',
        'codserv'
    ];

    // Relacionamentos (opcional)
    public function os()
    {
        return $this->belongsTo(OrdemServico::class, 'numos', 'numos_snk');
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class, 'codserv', 'codprod_snk');
    }
}
