<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('check_point_messages', function (Blueprint $table): void {
            // Whether this message asks the reader to answer with the
            // confirmation checklist. Only management replies are asked for it,
            // and only from the moment this column exists: messages written
            // before today default to false, so a finished thread from last
            // month does not suddenly lock its composer or wear a badge the
            // employee never earned.
            $table->boolean('requires_confirmation')->default(false)->after('sender_role');
        });

        Schema::create('check_point_message_acknowledgements', function (Blueprint $table): void {
            $table->id();
            // Every key and constraint is named by hand: the default names run
            // past MySQL's 64-character identifier limit (68 for the foreign
            // keys, 66 for the unique index) and the migration simply fails.
            //
            // One acknowledgement per management message: the unique index is
            // what makes a double-tapped "Kirim Konfirmasi" harmless.
            $table->unsignedBigInteger('check_point_message_id');
            $table->unique('check_point_message_id', 'cpma_message_unique');
            $table->foreign('check_point_message_id', 'cpma_message_foreign')
                ->references('id')->on('check_point_messages')->cascadeOnDelete();
            // The reply that carried the checklist back to management. It is a
            // normal thread message (so it notifies, counts and renders like
            // every other reply); this column is the link that lets the bubble
            // it answers show "Dikonfirmasi".
            $table->unsignedBigInteger('confirmation_message_id')->nullable();
            $table->foreign('confirmation_message_id', 'cpma_confirmation_foreign')
                ->references('id')->on('check_point_messages')->nullOnDelete();
            // The employee who read (and confirmed) the message. Deliberately no
            // foreign key: `users` is MyISAM, like the sender column next door.
            $table->unsignedBigInteger('user_id')->nullable();
            $table->index('user_id');
            // Opening the thread is what counts as reading, so this is stamped
            // before any checklist is sent.
            $table->timestamp('read_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            // Checklist answers: any of paham / siap / lainnya, one, two or all
            // three together.
            $table->json('choices')->nullable();
            // Free text behind the "Lainnya" answer.
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_point_message_acknowledgements');

        Schema::table('check_point_messages', function (Blueprint $table): void {
            $table->dropColumn('requires_confirmation');
        });
    }
};
