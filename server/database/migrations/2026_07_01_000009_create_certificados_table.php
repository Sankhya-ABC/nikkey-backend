<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificados', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('numos')->unique()->index();
            $table->unsignedBigInteger('nunota')->nullable();
            $table->unsignedInteger('codemp_snk')->nullable();
            $table->unsignedInteger('codvend_snk')->nullable();
            $table->date('dt_garantia')->nullable();
            $table->unsignedInteger('dias_garantia')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificados');
    }
};