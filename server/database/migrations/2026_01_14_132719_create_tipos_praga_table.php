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
        Schema::create('tipos_praga', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 1); // A, R
            $table->string('descricao');
            $table->timestamps();
        });

        DB::table('tipos_praga')->insert([
            ['codigo' => 'A', 'descricao' => 'Aéreo'],
            ['codigo' => 'R', 'descricao' => 'Rasteiro'],
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
