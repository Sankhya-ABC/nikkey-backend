<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_nao_conformidade', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('codtiponc_snk')->unique()->index();
            $table->string('descricao');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_nao_conformidade');
    }
};
