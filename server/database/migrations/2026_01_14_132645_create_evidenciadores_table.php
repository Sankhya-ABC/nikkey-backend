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
        Schema::create('evidenciadores', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20); 
            $table->string('descricao');
            $table->timestamps();
        });

        DB::table('evidenciadores')->insert([
            ['codigo' => 'CLIENTE', 'descricao' => 'Cliente'],
            ['codigo' => 'OPERADOR', 'descricao' => 'Operador'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tipos_praga');
    }
};
