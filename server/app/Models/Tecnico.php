<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tecnico extends Model
{
    use HasFactory;

    protected $fillable = ['nome', 'codtec_snk'];

    public function ordensServico()
    {
        return $this->hasMany(OrdemServico::class, 'tecnico_id');
    }
}