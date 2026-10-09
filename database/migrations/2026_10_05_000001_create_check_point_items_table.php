<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('check_point_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('check_point_id')->constrained('check_points')->cascadeOnDelete();
            // pekerjaan_cp_id is intentionally a string: it can hold a PekerjaanCp id
            // or a free-text "tambahan" label (legacy uploadBukti behaviour).
            $table->string('pekerjaan_cp_id')->nullable();
            $table->text('input_manual')->nullable();
            $table->text('deskripsi')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longtitude')->nullable();
            $table->string('approve_status')->default('proccess');
            $table->text('note')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();

            $table->index('check_point_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_point_items');
    }
};
