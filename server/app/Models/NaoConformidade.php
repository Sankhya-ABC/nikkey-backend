<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NaoConformidade extends Model
{
    protected $table = 'nao_conformidades';

    protected $fillable = [
        'codenc_snk', 'ordem_servico_id', 'tipo_nao_conformidade_id',
        'setor', 'tipores', 'statusnc', 'criticidadenc',
    ];
}
