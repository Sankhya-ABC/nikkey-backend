<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ServicosSeeder extends Seeder
{
    public function run(): void
    {
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
            ]
        ]);
    }
}
