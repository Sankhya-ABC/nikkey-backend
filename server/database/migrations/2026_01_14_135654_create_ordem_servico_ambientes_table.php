<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ordem_servico_ambientes', function (Blueprint $table) {
            $table->id();
            $table->string('codoseamb_snk')->index();

            $table->foreignId('ordem_servico_id')
                ->constrained('ordens_servico')
                ->cascadeOnDelete();

            $table->foreignId('ambiente_id')
                ->constrained('ambientes');

            $table->string('setor')->nullable();

            $table->boolean('atividades_termicas')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ordem_servico_ambientes');
    }
};
