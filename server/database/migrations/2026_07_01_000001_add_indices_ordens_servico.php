<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->index('dhprevista', 'idx_os_dhprevista');
            $table->index(['cliente_id', 'dhprevista'], 'idx_os_cliente_dhprevista');
            $table->index(['tecnico_id', 'dhprevista'], 'idx_os_tecnico_dhprevista');
            $table->index('hrfin', 'idx_os_hrfin');
            $table->index(['cliente_id', 'hrfin'], 'idx_os_cliente_hrfin');
        });
    }

    public function down(): void
    {
        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->dropIndex('idx_os_dhprevista');
            $table->dropIndex('idx_os_cliente_dhprevista');
            $table->dropIndex('idx_os_tecnico_dhprevista');
            $table->dropIndex('idx_os_hrfin');
            $table->dropIndex('idx_os_cliente_hrfin');
        });
    }
};
