<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A previous run created the table before failing on the cross-database FK.
        // Keep that table and let this migration finish cleanly.
        if (Schema::hasTable('check_point_participants')) {
            return;
        }

        Schema::create('check_point_participants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('check_point_id')->constrained('check_points')->cascadeOnDelete();
            // users live on the application's auth DB; keep this cross-DB ID
            // un-constrained because MySQL cannot reference another schema.
            $table->unsignedBigInteger('user_id');
            $table->index('user_id');
            $table->timestamps();
            $table->unique(['check_point_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_point_participants');
    }
};
