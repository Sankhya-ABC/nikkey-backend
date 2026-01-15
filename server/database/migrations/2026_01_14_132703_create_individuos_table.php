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
        Schema::create('individuos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 1); // V, M, F
            $table->string('descricao');
            $table->timestamps();
        });

        DB::table('individuos')->insert([
            ['codigo' => 'V', 'descricao' => 'Vivo'],
            ['codigo' => 'M', 'descricao' => 'Morto'],
            ['codigo' => 'F', 'descricao' => 'Fragmentos'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('individuos');
    }
};
