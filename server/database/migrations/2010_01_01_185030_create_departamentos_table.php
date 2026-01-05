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
            $table->string('descricao')->unique();
            $table->timestamps();
        });

        DB::table('departamentos')->insert([
            ['descricao' => 'Administrativo', 'created_at' => now(), 'updated_at' => now()],
            ['descricao' => 'Operacional',   'created_at' => now(), 'updated_at' => now()],
            ['descricao' => 'Comercial',     'created_at' => now(), 'updated_at' => now()],
            ['descricao' => 'Financeiro',    'created_at' => now(), 'updated_at' => now()],
            ['descricao' => 'TI',            'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('departamentos');
    }
}
