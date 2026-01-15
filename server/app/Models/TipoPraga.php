<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoPraga extends Model
{
    use HasFactory;

    protected $table = 'tipos_praga';

    protected $fillable = [
        'codigo',
        'descricao',
    ];

    public function evidenciasPragas()
    {
        return $this->hasMany(EvidenciaPraga::class);
    }
}
