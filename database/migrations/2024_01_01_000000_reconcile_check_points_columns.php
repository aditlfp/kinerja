<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reconciles the `check_points` table with the columns the application has
 * always used but that were never added by a migration (schema drift).
 *
 * Without this, a fresh `php artisan migrate` fails as soon as the later
 * migrations reference `pekerjaan_cp_id`, and the CI/fresh-install database
 * is missing columns the models rely on.
 *
 * Idempotent: safe to run on databases that already have these columns.
 */
return new class extends Migration
{
    private array $columns = [
        'divisi_id' => 'unsignedBigInteger',
        'pekerjaan_cp_id' => 'string',
        'type_check' => 'string',
        'img' => 'string',
        'deskripsi' => 'text',
        'approve_status' => 'string',
        'note' => 'string',
        'latitude' => 'string',
        'longtitude' => 'string',
    ];

    public function up(): void
    {
        Schema::table('check_points', function (Blueprint $table): void {
            foreach ($this->columns as $name => $type) {
                if (Schema::hasColumn('check_points', $name)) {
                    continue;
                }

                $column = match ($type) {
                    'unsignedBigInteger' => $table->unsignedBigInteger($name),
                    'text' => $table->text($name),
                    default => $table->string($name),
                };

                $column->nullable();
            }
        });

        // The application creates checkpoints without a client, so the
        // original NOT NULL column has to become nullable.
        if (Schema::hasColumn('check_points', 'client_id')) {
            Schema::table('check_points', function (Blueprint $table): void {
                $table->unsignedBigInteger('client_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // Intentionally left alone: these columns pre-date the migration and
        // may hold production data.
    }
};
