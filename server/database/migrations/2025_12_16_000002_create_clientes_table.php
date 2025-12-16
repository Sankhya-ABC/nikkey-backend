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

            $table->unsignedBigInteger('codparc_snk')->unique()->index();
            $table->unsignedBigInteger('codparc_matriz_snk')->nullable()->index();

            $table->string('razao_social')->nullable();
            $table->string('nome_fantasia')->nullable();
            $table->string('cnpj_cpf', 18)->nullable()->index();
            $table->date('validade_certificado')->nullable();
            $table->string('tipo_atividade')->nullable();
            $table->boolean('tem_contrato')->default(false);
            $table->foreignId('endereco_id')
                ->nullable()
                ->constrained('enderecos');

            $table->foreignId('bairro_id')
                ->nullable()
                ->constrained('bairros');

            $table->foreignId('cidade_id')
                ->nullable()
                ->constrained('cidades');
            $table->string('complemento')->nullable();
            $table->string('numero', 20)->nullable();
            $table->string('cep', 10)->nullable();
            $table->boolean('ativo')->default(true);

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
