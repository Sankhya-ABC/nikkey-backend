<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Evidenciador extends Model
{
    use HasFactory;

    protected $table = 'evidenciadores';

    protected $fillable = [
        'codigo',
        'descricao',
    ];

    public function evidenciasPragas()
    {
        return $this->hasMany(EvidenciaPraga::class);
    }
}
