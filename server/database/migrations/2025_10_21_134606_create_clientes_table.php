<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();

            $table->integer('codparc_snk')->unique();
            $table->integer('codparc_matriz_snk')->nullable(); 
            $table->string('razao_social')->nullable();
            $table->string('nome_fantasia')->nullable();
            $table->string('cnpj_cpf')->nullable();
            $table->date('validade_certificado')->nullable();
            $table->string('tipo_atividade')->nullable();
            $table->boolean('tem_contrato')->default(false);

            $table->string('logradouro')->nullable();
            $table->string('complemento')->nullable();
            $table->string('numero', 20)->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();
            $table->string('cep', 10)->nullable();

            // Contato
            $table->string('contato')->nullable();
            $table->string('telefone', 20)->nullable();
            $table->string('email')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
