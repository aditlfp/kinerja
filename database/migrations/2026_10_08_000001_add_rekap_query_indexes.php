<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Missing indexes on the FK / date columns that the rekap, dashboard and
 * delete flows filter on. `foreignIdFor()` does NOT create an index unless
 * `->constrained()` / `->index()` is chained, so these tables were doing
 * full scans on every `where('user_id', ...)` + date-range query.
 *
 * The rekap screens filter on the domain date column (date_cut, date_in,
 * date_finish_train, out_date, date_overtime) — index those, not created_at.
 *
 * MyISAM + utf8mb3: varchar columns in a composite key need a short prefix
 * (see 2026_06_19_000001_add_dashboard_query_indexes.php). `type_check` /
 * status are short enum-like values, 20 bytes is plenty.
 *
 * Idempotent: guarded by index name, safe to re-run.
 */
return new class extends Migration
{
    /**
     * table => [indexName => raw "(cols)" fragment]
     */
    private array $indexes = [
        'absensis' => [
            // tanggal_absen is varchar(255) holding 'Y-m-d' (always 10 chars);
            // prefix index gives an exact key at a fraction of the width.
            'absensis_user_tanggal_idx' => '(`user_id`, `tanggal_absen`(10))',
            'absensis_tanggal_idx' => '(`tanggal_absen`(10))',
        ],
        'check_points' => [
            'check_points_user_created_idx' => '(`user_id`, `created_at`)',
            'check_points_user_type_created_idx' => '(`user_id`, `type_check`(20), `created_at`)',
        ],
        'izins' => [
            'izins_user_updated_idx' => '(`user_id`, `updated_at`)',
            'izins_user_created_idx' => '(`user_id`, `created_at`)',
            'izins_kerjasama_created_idx' => '(`kerjasama_id`, `created_at`)',
        ],
        'lemburs' => [
            'lemburs_user_created_idx' => '(`user_id`, `created_at`)',
        ],
        'laporans' => [
            'laporans_user_created_idx' => '(`user_id`, `created_at`)',
        ],
        'overtimes' => [
            'overtimes_user_overtime_idx' => '(`user_id`, `date_overtime`)',
        ],
        'performance_cuts' => [
            'performance_cuts_user_cut_idx' => '(`user_id`, `date_cut`)',
        ],
        'finished_trainings' => [
            'finished_trainings_user_finish_idx' => '(`user_id`, `date_finish_train`)',
        ],
        'person_outs' => [
            'person_outs_out_date_idx' => '(`out_date`)',
        ],
        'person_ins' => [
            'person_ins_client_in_idx' => '(`client_id`, `date_in`)',
        ],
        'users' => [
            'users_kerjasama_id_idx' => '(`kerjasama_id`)',
        ],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $table => $definitions) {
            foreach ($definitions as $name => $columns) {
                $this->addIndexIfMissing($table, $name, "ADD INDEX `{$name}` {$columns}");
            }
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as $table => $definitions) {
            foreach (array_keys($definitions) as $name) {
                $this->dropIndexIfExists($table, $name);
            }
        }
    }

    private function addIndexIfMissing(string $table, string $name, string $addSql): void
    {
        if ($this->indexExists($table, $name)) {
            return;
        }

        DB::statement("ALTER TABLE `{$table}` {$addSql}");
    }

    private function dropIndexIfExists(string $table, string $name): void
    {
        if (! $this->indexExists($table, $name)) {
            return;
        }

        DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$name}`");
    }

    private function indexExists(string $table, string $name): bool
    {
        $db = Schema::getConnection()->getDatabaseName();
        $row = DB::selectOne(
            'SELECT 1 AS ok FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
            [$db, $table, $name]
        );

        return $row !== null;
    }
};
