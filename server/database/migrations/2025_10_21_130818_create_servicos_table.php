<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // necessário para usar DB::table()
use Carbon\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->timestamps();
        });

        DB::table('servicos')->insert([
            [
                'nome' => 'MobileLoginSP.login',
                'descricao' => 'Login',
                'created_at' => Carbon::parse('2021-08-25 21:35:22'),
                'updated_at' => Carbon::parse('2021-08-25 21:35:22'),
            ],
            [
                'nome' => 'CRUDServiceProvider.loadView',
                'descricao' => 'Recupera os dados da view passada como parâmetro no corpo da requisição',
                'created_at' => Carbon::parse('2021-08-26 12:18:00'),
                'updated_at' => Carbon::parse('2021-08-26 12:18:00'),
            ],
            [
                'nome' => 'crud.save',
                'descricao' => 'Operação de CRUD',
                'created_at' => Carbon::parse('2021-12-26 11:49:57'),
                'updated_at' => Carbon::parse('2021-12-26 11:49:57'),
            ],
            [
                'nome' => 'CRUDServiceProvider.loadRecords',
                'descricao' => 'Consultar tabela',
                'created_at' => Carbon::parse('2022-09-01 18:35:43'),
                'updated_at' => Carbon::parse('2022-09-01 18:35:43'),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('servicos');
    }
};
