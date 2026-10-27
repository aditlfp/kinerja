<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('mysql2');

        if (! $schema->hasTable('p_g_j__kontraks')) {
            return;
        }

        $schema->table('p_g_j__kontraks', function (Blueprint $table): void {
            $table->index(['nama_pk_kda', 'created_at'], 'kontraks_name_created_idx');
        });
    }

    public function down(): void
    {
        $schema = Schema::connection('mysql2');

        if ($schema->hasTable('p_g_j__kontraks')) {
            $schema->table('p_g_j__kontraks', function (Blueprint $table): void {
                $table->dropIndex('kontraks_name_created_idx');
            });
        }
    }
};
