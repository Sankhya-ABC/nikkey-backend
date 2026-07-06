<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoNaoConformidade extends Model
{
    protected $table = 'tipos_nao_conformidade';

    protected $fillable = ['codtiponc_snk', 'descricao'];
}
