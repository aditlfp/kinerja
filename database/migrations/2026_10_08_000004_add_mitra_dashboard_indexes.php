<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $indexes = [
        'absensis' => [
            'absensis_kerjasama_created_idx' => '(`kerjasama_id`, `created_at`)',
        ],
        'izins' => [
            'izins_kerjasama_updated_idx' => '(`kerjasama_id`, `updated_at`)',
        ],
        'lemburs' => [
            'lemburs_kerjasama_created_idx' => '(`kerjasama_id`, `created_at`)',
        ],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $table => $definitions) {
            foreach ($definitions as $name => $columns) {
                if (! $this->exists($table, $name)) {
                    DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$name}` {$columns}");
                }
            }
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as $table => $definitions) {
            foreach (array_keys($definitions) as $name) {
                if ($this->exists($table, $name)) {
                    DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$name}`");
                }
            }
        }
    }

    private function exists(string $table, string $name): bool
    {
        $db = Schema::getConnection()->getDatabaseName();
        return DB::selectOne(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
            [$db, $table, $name]
        ) !== null;
    }
};
