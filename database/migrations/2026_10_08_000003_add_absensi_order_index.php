<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! $this->exists('absensis', 'absensis_tanggal_created_order_idx')) {
            DB::statement('ALTER TABLE `absensis` ADD INDEX `absensis_tanggal_created_order_idx` (`tanggal_absen`, `created_at`)');
        }
    }

    public function down(): void
    {
        if ($this->exists('absensis', 'absensis_tanggal_created_order_idx')) {
            DB::statement('ALTER TABLE `absensis` DROP INDEX `absensis_tanggal_created_order_idx`');
        }
    }

    private function exists(string $table, string $index): bool
    {
        return Schema::getConnection()->getSchemaBuilder()->hasIndex($table, $index);
    }
};
