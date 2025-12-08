<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('os_servico', function (Blueprint $table) {
            $table->foreignId('ordem_servico_id')->constrained('ordens_servico')->cascadeOnDelete();
            $table->foreignId('servico_id')->constrained('servicos')->cascadeOnDelete();

            $table->timestamps();

            $table->primary(['ordem_servico_id', 'servico_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('os_servico');
    }
};
