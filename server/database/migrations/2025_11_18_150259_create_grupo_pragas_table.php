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
        Schema::create('grupo_pragas', function (Blueprint $table) {
            $table->id();
            $table->string('descricao', 100);
            $table->timestamps();
        });

        DB::table('grupo_pragas')->insert([
            ['id' => 1,  'descricao' => 'Roedores',           'created_at' => now(), 'updated_at' => now()],
            ['id' => 2,  'descricao' => 'Insetos',            'created_at' => now(), 'updated_at' => now()],
            ['id' => 3,  'descricao' => 'Voadores',           'created_at' => now(), 'updated_at' => now()],
            ['id' => 4,  'descricao' => 'Cupim',              'created_at' => now(), 'updated_at' => now()],
            ['id' => 5,  'descricao' => 'Vegetação',          'created_at' => now(), 'updated_at' => now()],
            ['id' => 6,  'descricao' => 'Bactérias',          'created_at' => now(), 'updated_at' => now()],
            ['id' => 7,  'descricao' => 'Fungos',             'created_at' => now(), 'updated_at' => now()],
            ['id' => 8,  'descricao' => 'Vermes',             'created_at' => now(), 'updated_at' => now()],
            ['id' => 9,  'descricao' => 'Insetos Voadores',   'created_at' => now(), 'updated_at' => now()],
            ['id' => 10, 'descricao' => 'Insetos termíticos', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 11, 'descricao' => 'Insetos brocadores', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 12, 'descricao' => 'Aracnídeos',         'created_at' => now(), 'updated_at' => now()],
            ['id' => 13, 'descricao' => 'Morcegos',           'created_at' => now(), 'updated_at' => now()],
            ['id' => 14, 'descricao' => 'Moluscos',           'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('grupo_pragas');
    }

};
