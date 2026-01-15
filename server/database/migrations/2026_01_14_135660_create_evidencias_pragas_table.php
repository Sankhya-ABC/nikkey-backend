<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('evidencias_pragas', function (Blueprint $table) {
            $table->id();

            $table->string('codevidencia_snk')->unique()->index();

            $table->foreignId('ordem_servico_id')
                ->constrained('ordens_servico')
                ->cascadeOnDelete();

            // Pode não existir ambiente cadastrado ainda
            $table->foreignId('ordem_servico_ambiente_id')
                ->nullable()
                ->constrained('ordem_servico_ambientes')
                ->nullOnDelete();

            $table->string('setor')->nullable();

            $table->foreignId('praga_id')
                ->nullable()
                ->constrained('pragas')
                ->nullOnDelete();

            $table->foreignId('tipo_praga_id')
                ->nullable()
                ->constrained('tipos_praga')
                ->nullOnDelete();

            $table->foreignId('tipo_evidencia_id')
                ->nullable()
                ->constrained('tipos_evidencia')
                ->nullOnDelete();

            $table->foreignId('individuo_id')
                ->nullable()
                ->constrained('individuos')
                ->nullOnDelete();

            $table->foreignId('evidenciador_id')
                ->nullable()
                ->constrained('evidenciadores')
                ->nullOnDelete();

            $table->date('data_evidencia')->nullable();
            $table->integer('quantidade')->default(0);

            $table->string('fase_praga')->nullable();
            $table->string('evidenciador_nome')->nullable();

            $table->text('observacoes')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('evidencias_pragas');
    }
};
