<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfMissing(
            'absensis',
            'absensis_tanggal_created_idx',
            'ADD INDEX `absensis_tanggal_created_idx` (`tanggal_absen`(10), `created_at`)'
        );
        $this->addIndexIfMissing(
            'absensis',
            'absensis_keterangan_idx',
            'ADD INDEX `absensis_keterangan_idx` (`keterangan`(20))'
        );
    }

    public function down(): void
    {
        $this->dropIndexIfExists('absensis', 'absensis_tanggal_created_idx');
        $this->dropIndexIfExists('absensis', 'absensis_keterangan_idx');
    }

    private function addIndexIfMissing(string $table, string $name, string $sql): void
    {
        if (! $this->indexExists($table, $name)) {
            DB::statement("ALTER TABLE `{$table}` {$sql}");
        }
    }

    private function dropIndexIfExists(string $table, string $name): void
    {
        if ($this->indexExists($table, $name)) {
            DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$name}`");
        }
    }

    private function indexExists(string $table, string $name): bool
    {
        $db = Schema::getConnection()->getDatabaseName();

        return DB::selectOne(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
            [$db, $table, $name]
        ) !== null;
    }
};
