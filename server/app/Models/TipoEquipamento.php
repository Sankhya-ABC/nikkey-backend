<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoEquipamento extends Model
{
    protected $table = 'tipo_equipamento';

    protected $fillable = [
        'descricao',
        'codtipoequip_snk'
    ];

    public function metodologias()
    {
        return $this->hasMany(Metodologia::class, 'tipoequip_id');
    }
}
