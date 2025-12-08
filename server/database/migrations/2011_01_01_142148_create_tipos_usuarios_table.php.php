<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('descricao')->unique(); 
            $table->timestamps();
        });

        DB::table('tipos_usuarios')->insert([
            ['descricao' => 'ADMIN', 'created_at' => now(), 'updated_at' => now()],
            ['descricao' => 'COMMON', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_usuarios');
    }
};
