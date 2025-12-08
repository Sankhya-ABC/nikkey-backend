<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateDepartamentosTable extends Migration
{
    public function up()
    {
        Schema::create('departamentos', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->timestamps();
        });

        DB::table('departamentos')->insert([
            ['nome' => 'Administrativo', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Operacional',   'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Comercial',     'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Financeiro',    'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'TI',            'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('departamentos');
    }
}
