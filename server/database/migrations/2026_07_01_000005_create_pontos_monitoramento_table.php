<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pontos_monitoramento', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('codptmon_snk')->index();

            $table->foreignId('ordem_servico_id')
                ->constrained('ordens_servico')
                ->cascadeOnDelete();

            $table->foreignId('praga_id')
                ->nullable()
                ->constrained('pragas')
                ->nullOnDelete();

            $table->integer('tpmonit')->nullable()->index();
            $table->string('idequp')->nullable();
            $table->string('amb')->nullable();
            $table->string('local_ponto')->nullable();
            $table->string('setor')->nullable();
            $table->string('consumo')->nullable();
            $table->string('consumometade')->nullable();
            $table->timestamps();

            $table->unique(['codptmon_snk', 'ordem_servico_id'], 'uq_ptmon_snk_os');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pontos_monitoramento');
    }
};
