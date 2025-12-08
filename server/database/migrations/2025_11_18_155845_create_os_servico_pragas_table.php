<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('os_servico_pragas', function (Blueprint $table) {
            $table->foreignId('ordem_servico_id')->constrained('ordens_servico')->cascadeOnDelete();
            $table->foreignId('servico_id')->constrained('servicos')->cascadeOnDelete();
            $table->foreignId('praga_id')->constrained('pragas')->cascadeOnDelete();

            $table->timestamps();

            // Chave primária composta
            $table->primary(['ordem_servico_id', 'servico_id', 'praga_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('os_servico_pragas');
    }
};
