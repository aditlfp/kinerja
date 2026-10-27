<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('check_point_report_settings', function (Blueprint $table) {
            $table->id();
            // 1 = Senin ... 7 = Minggu (Carbon dayOfWeekIso).
            $table->unsignedTinyInteger('last_day')->default(6);
            $table->unsignedBigInteger('updated_by')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_point_report_settings');
    }
};
