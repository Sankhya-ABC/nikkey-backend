<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoEvidencia extends Model
{
    use HasFactory;

    protected $table = 'tipos_evidencia';

    protected $fillable = [
        'codenvidencia_snk',
        'descricao',
        'imagem_produto',
        'imagem_identificacao',
    ];

    protected $casts = [
        'imagem_produto' => 'binary',
        'imagem_identificacao' => 'binary',
    ];

    public function evidenciasPragas()
    {
        return $this->hasMany(EvidenciaPraga::class);
    }
}
