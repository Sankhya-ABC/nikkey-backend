<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('metodologias', function (Blueprint $table) {
            $table->id();

            $table->string('descricao')->unique();

            // Pode vir NULL do Sankhya
            $table->foreignId('tecexecucao_id')
                ->nullable()
                ->constrained('tecnica_execucao')
                ->nullOnDelete();

            // Também pode vir NULL do Sankhya
            $table->foreignId('tipoequip_id')
                ->nullable()
                ->constrained('tipo_equipamento')
                ->nullOnDelete();

            $table->unsignedBigInteger('codmetodologia_snk')
                ->unique()
                ->index();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('metodologias');
    }
};
