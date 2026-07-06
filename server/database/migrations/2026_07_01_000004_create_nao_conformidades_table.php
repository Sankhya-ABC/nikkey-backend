<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nao_conformidades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('codenc_snk')->unique()->index();

            $table->foreignId('ordem_servico_id')
                ->constrained('ordens_servico')
                ->cascadeOnDelete();

            $table->foreignId('tipo_nao_conformidade_id')
                ->nullable()
                ->constrained('tipos_nao_conformidade')
                ->nullOnDelete();

            $table->string('setor')->nullable();
            $table->text('tipores')->nullable();
            $table->string('statusnc')->nullable();
            $table->string('criticidadenc')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nao_conformidades');
    }
};
