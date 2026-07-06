<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tecnicos', function (Blueprint $table) {
            $table->string('telefone', 20)->nullable()->after('nome');
            $table->string('crea', 60)->nullable()->after('telefone');
            $table->string('crbio', 60)->nullable()->after('crea');
            $table->longText('assinatura')->nullable()->after('crbio');
            $table->unsignedInteger('codvend_snk')->nullable()->after('assinatura');
        });
    }

    public function down(): void
    {
        Schema::table('tecnicos', function (Blueprint $table) {
            $table->dropColumn(['telefone', 'crea', 'crbio', 'assinatura', 'codvend_snk']);
        });
    }
};