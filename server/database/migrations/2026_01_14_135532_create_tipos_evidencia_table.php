<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tipos_evidencia', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('codenvidencia_snk')->unique()->index();
            $table->string('descricao');

            $table->binary('imagem_produto')->nullable();
            $table->binary('imagem_identificacao')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tipos_evidencia');
    }
};
