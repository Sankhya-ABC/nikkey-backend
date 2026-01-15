<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('previsoes_execucao_os', function (Blueprint $table) {
            $table->id();

            $table->string('codprevisao_snk')->index();

            $table->foreignId('ordem_servico_id')
                ->constrained('ordens_servico')
                ->cascadeOnDelete();

            $table->integer('inst_temp_prev')->nullable();
            $table->integer('ins_dias_prev')->nullable();
            $table->integer('ins_pessoas_prev')->nullable();

            $table->integer('mon_temp_prev')->nullable();
            $table->integer('mon_dias_prev')->nullable();
            $table->integer('mon_pessoas_prev')->nullable();

            $table->dateTime('hrini')->nullable();
            $table->dateTime('hrfin')->nullable();

            $table->timestamps();

            $table->unique(['codprevisao_snk', 'ordem_servico_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('previsoes_execucao_os');
    }
};
