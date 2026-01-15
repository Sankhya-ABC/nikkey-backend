<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ordens_servico', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('numos')->unique()->index();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('tecnico_id')->nullable()->constrained('tecnicos')->nullOnDelete();

            $table->string('tipoos')->nullable();
            $table->string('statusos')->nullable();
            $table->dateTime('hrini')->nullable();
            $table->dateTime('hrfin')->nullable();
            $table->string('asscli')->nullable();
            $table->string('asstec')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('ad_numnikkey')->nullable();
            $table->string('latitudeini')->nullable();
            $table->string('longitudeini')->nullable();
            $table->dateTime('dhprevista')->nullable();
            $table->dateTime('dhprevistafin')->nullable();
            $table->string('duracao')->nullable();
            $table->text('servico')->nullable();
            $table->unsignedBigInteger('codvei')->nullable();   
            $table->boolean('confage')->default(false)->nullable();
            $table->string('idcliente')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordens_servico');
    }
};
