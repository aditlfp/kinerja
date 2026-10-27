<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('check_point_messages', function (Blueprint $table): void {
            // "Tarik pesan". Retraction is a tombstone, not a delete: the row
            // keeps the slot (and its sender, so the thread still alternates
            // sides) while the body and every attachment are wiped. That way
            // the conversation keeps its shape without keeping the words.
            $table->timestamp('retracted_at')->nullable()->after('body');
            // One voice note per message, stored under storage/app/public/voices.
            $table->string('audio_path')->nullable()->after('retracted_at');
            // Length in seconds. The browser measures it while recording —
            // a MediaRecorder blob carries no reliable duration header — and
            // the server only checks the value against the cap.
            $table->unsignedInteger('audio_duration')->nullable()->after('audio_path');
        });
    }

    public function down(): void
    {
        Schema::table('check_point_messages', function (Blueprint $table): void {
            $table->dropColumn(['retracted_at', 'audio_path', 'audio_duration']);
        });
    }
};
