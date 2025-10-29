<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requisicoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servico_id')
                  ->constrained('servicos')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->text('corpo');
            $table->timestamps();
        });

            DB::table('requisicoes')->insert([
                'id' => 1,
                'servico_id' => 1,
                'corpo' => '<serviceRequest serviceName="%serviceName%">
    <requestBody>
        <NOMUSU>%NOMUSU%</NOMUSU>
        <INTERNO>%INTERNO%</INTERNO>
    </requestBody>
</serviceRequest>',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    

    public function down(): void
    {
        Schema::dropIfExists('requisicoes');
    }
};
