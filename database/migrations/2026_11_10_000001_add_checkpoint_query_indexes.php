<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEXES = [
        'check_point_items' => [
            'cp_items_checkpoint_tanggal_idx' => ['check_point_id', 'tanggal'],
        ],
        'check_point_messages' => [
            'cp_messages_item_pending_idx' => [
                'check_point_item_id',
                'sender_role',
                'requires_confirmation',
                'retracted_at',
            ],
        ],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as $table => $indexes) {
            foreach ($indexes as $name => $columns) {
                if (! Schema::hasIndex($table, $name)) {
                    Schema::table($table, function (Blueprint $table) use ($name, $columns): void {
                        $table->index($columns, $name);
                    });
                }
            }
        }
    }

    public function down(): void
    {
        foreach (self::INDEXES as $table => $indexes) {
            foreach (array_keys($indexes) as $name) {
                if (Schema::hasIndex($table, $name)) {
                    Schema::table($table, function (Blueprint $table) use ($name): void {
                        $table->dropIndex($name);
                    });
                }
            }
        }
    }
};
