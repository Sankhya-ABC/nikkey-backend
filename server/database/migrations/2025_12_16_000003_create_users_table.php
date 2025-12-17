<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->unsignedBigInteger('tipo_usuario_id')->nullable();
            $table->unsignedBigInteger('departamento_id')->nullable();

            $table->string('name');
            $table->string('email')->unique();

            // ➕ Novos campos
            $table->string('telefone')->nullable();
            $table->boolean('ativo')->default(true);

            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            $table->timestamps();

            // Relacionamentos
            $table->foreign('cliente_id')
                ->references('id')->on('clientes')
                ->onDelete('cascade');

            $table->foreign('tipo_usuario_id')
                ->references('id')->on('tipos_usuarios')
                ->onDelete('set null');

            $table->foreign('departamento_id')
                ->references('id')->on('departamentos')
                ->onDelete('set null');
        });

        DB::table('users')->insert([
            [
                'name'            => 'Admin Master',
                'email'           => 'admin@teste.com',
                'tipo_usuario_id' => 1,
                'departamento_id' => 1,
                'telefone'        => '11999999999',
                'ativo'           => true,
                'password'        => Hash::make('123456'),
                'created_at'      => now(),
                'updated_at'      => now()
            ],
            [
                'name'            => 'Common',
                'email'           => 'common@teste.com',
                'tipo_usuario_id' => 2,
                'departamento_id' => 2,
                'telefone'        => '11999999999',
                'ativo'           => true,
                'password'        => Hash::make('123456'),
                'created_at'      => now(),
                'updated_at'      => now()
            ]
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
