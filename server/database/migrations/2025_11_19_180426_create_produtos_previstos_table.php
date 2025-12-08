<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('produtos_previstos', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('codprodprev_snk')
                ->unique()
                ->index();

            $table->foreignId('ordem_servico_id')
                ->constrained('ordens_servico')
                ->cascadeOnDelete();

            $table->foreignId('servico_id')
                ->constrained('servicos')
                ->cascadeOnDelete();

            $table->foreignId('praga_id')
                ->nullable()
                ->constrained('pragas')
                ->nullOnDelete();

            $table->foreignId('produto_id')
                ->nullable()
                ->constrained('produtos')
                ->nullOnDelete();

            $table->foreignId('metodologia_id')
                ->nullable()
                ->constrained('metodologias')
                ->nullOnDelete();

            $table->decimal('qtdneg', 10, 3)->nullable();
            $table->string('lote', 50)->nullable();

            $table->date('dtfab')->nullable();
            $table->date('dtval')->nullable();

            $table->decimal('calda', 10, 3)->nullable();
            $table->decimal('concentracao', 10, 3)->nullable();

            $table->string('grupoquim')->nullable();
            $table->string('principioatv')->nullable();
            $table->string('sintomas')->nullable();
            $table->string('antidoto')->nullable();
            $table->string('codregmapa')->nullable();
            $table->string('acaotoxica')->nullable();

            $table->string('diluente')->nullable();
            $table->decimal('qtdnegdiluente', 10, 3)->nullable();
            $table->string('lotediluente')->nullable();

            $table->string('tecnicaexec')->nullable();
            $table->string('tpaplicacao')->nullable();
            $table->string('ambiente')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('produtos_previstos');
    }
};
