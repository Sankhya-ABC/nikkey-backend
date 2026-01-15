<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Metodologia extends Model
{
    protected $table = 'metodologias';

    protected $fillable = [
        'descricao',
        'tecexecucao_id',
        'tipoequip_id',
        'codmetodologia_snk'
    ];

    public function tecnicaExecucao()
    {
        return $this->belongsTo(TecnicaExecucao::class, 'tecexecucao_id');
    }

    public function tipoEquipamento()
    {
        return $this->belongsTo(TipoEquipamento::class, 'tipoequip_id');
    }

    public function produtosPrevistos()
    {
        return $this->hasMany(ProdutoPrevisto::class);
    }

    public function produtosUtilizados()
    {
        return $this->hasMany(ProdutosUtilizados::class);
    }
}
