<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pragas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('codpraga_snk')->unique()->index();
            $table->string('nome_praga')->nullable();
            $table->unsignedBigInteger('grupo_praga_id')->nullable();
            $table->timestamps();

            $table->foreign('grupo_praga_id')->references('id')->on('grupo_pragas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pragas');
    }
};
