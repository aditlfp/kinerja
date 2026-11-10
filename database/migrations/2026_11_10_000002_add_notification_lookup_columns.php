<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `notifications` ADD `notification_type` VARCHAR(255) GENERATED ALWAYS AS (IF(JSON_VALID(`data`), JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.type')), NULL)) STORED AFTER `type`");
        DB::statement("ALTER TABLE `notifications` ADD `kerjasama_id` BIGINT UNSIGNED GENERATED ALWAYS AS (IF(JSON_VALID(`data`) AND JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.kerjasama_id')) <> '', CAST(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.kerjasama_id')) AS UNSIGNED), NULL)) STORED AFTER `notification_type`");
        DB::statement("ALTER TABLE `notifications` ADD INDEX `notifications_unread_lookup_idx` (`notifiable_type`, `notifiable_id`, `read_at`, `notification_type`, `kerjasama_id`)");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `notifications` DROP INDEX `notifications_unread_lookup_idx`, DROP COLUMN `notification_type`, DROP COLUMN `kerjasama_id`");
    }
};
