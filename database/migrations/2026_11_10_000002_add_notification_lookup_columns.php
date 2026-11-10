<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! $this->columnExists('notification_type')) {
            DB::statement("ALTER TABLE `notifications` ADD `notification_type` VARCHAR(255) GENERATED ALWAYS (IF(JSON_VALID(`data`), JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.type')), NULL)) STORED AFTER `type`");
        }

        if (! $this->columnExists('kerjasama_id')) {
            DB::statement("ALTER TABLE `notifications` ADD `kerjasama_id` BIGINT UNSIGNED GENERATED ALWAYS (IF(JSON_VALID(`data`), NULLIF(CAST(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.kerjasama_id')) AS UNSIGNED), 0), NULL)) STORED AFTER `notification_type`");
        }

        if (! $this->indexExists('notifications_unread_lookup_idx')) {
            DB::statement("ALTER TABLE `notifications` ADD INDEX `notifications_unread_lookup_idx` (`notifiable_type`, `notifiable_id`, `read_at`, `notification_type`, `kerjasama_id`)");
        }
    }

    public function down(): void
    {
        if ($this->indexExists('notifications_unread_lookup_idx')) {
            DB::statement("ALTER TABLE `notifications` DROP INDEX `notifications_unread_lookup_idx`");
        }

        if ($this->columnExists('notification_type')) {
            DB::statement("ALTER TABLE `notifications` DROP COLUMN `notification_type`");
        }

        if ($this->columnExists('kerjasama_id')) {
            DB::statement("ALTER TABLE `notifications` DROP COLUMN `kerjasama_id`");
        }
    }

    private function columnExists(string $column): bool
    {
        return DB::selectOne(
            'SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? LIMIT 1',
            ['notifications', $column],
        ) !== null;
    }

    private function indexExists(string $index): bool
    {
        return DB::selectOne(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1',
            ['notifications', $index],
        ) !== null;
    }
};
