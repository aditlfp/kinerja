<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('check_point_messages', function (Blueprint $table): void {
            $table->id();
            // The thread lives and dies with its job row, like check_point_images.
            $table->foreignId('check_point_item_id')->constrained('check_point_items')->cascadeOnDelete();
            // Sender. Nullable so a departed employee's message survives as an
            // audit trail instead of being deleted along with the user account.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            // 'management' | 'employee', stored explicitly so the thread keeps
            // rendering the right side even if the sender's jabatan changes.
            $table->string('sender_role');
            $table->text('body')->nullable();
            $table->timestamps();

            $table->index('check_point_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_point_messages');
    }
};
