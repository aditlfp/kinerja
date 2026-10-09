<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            if (!Schema::hasColumn('absensis', 'subuh')) {
                $table->string('subuh')->nullable()->after('point_id');
            }

            if (!Schema::hasColumn('absensis', 'fotoSubuh')) {
                $table->string('fotoSubuh')->nullable()->after('subuh');
            }

            if (!Schema::hasColumn('absensis', 'dzuhur')) {
                $table->string('dzuhur')->nullable()->after('fotoSubuh');
            }

            if (!Schema::hasColumn('absensis', 'fotoDzuhur')) {
                $table->string('fotoDzuhur')->nullable()->after('dzuhur');
            }

            if (!Schema::hasColumn('absensis', 'asar')) {
                $table->string('asar')->nullable()->after('fotoDzuhur');
            }

            if (!Schema::hasColumn('absensis', 'fotoAsar')) {
                $table->string('fotoAsar')->nullable()->after('asar');
            }

            if (!Schema::hasColumn('absensis', 'maghrib')) {
                $table->string('maghrib')->nullable()->after('fotoAsar');
            }

            if (!Schema::hasColumn('absensis', 'fotoMaghrib')) {
                $table->string('fotoMaghrib')->nullable()->after('maghrib');
            }

            if (!Schema::hasColumn('absensis', 'isya')) {
                $table->string('isya')->nullable()->after('fotoMaghrib');
            }

            if (!Schema::hasColumn('absensis', 'fotoIsya')) {
                $table->string('fotoIsya')->nullable()->after('isya');
            }

            if (!Schema::hasColumn('absensis', 'subuh_lat')) {
                $table->string('subuh_lat')->nullable()->after('subuh');
            }

            if (!Schema::hasColumn('absensis', 'subuh_long')) {
                $table->string('subuh_long')->nullable()->after('subuh_lat');
            }

            if (!Schema::hasColumn('absensis', 'dzuhur_lat')) {
                $table->string('dzuhur_lat')->nullable()->after('subuh_long');
            }

            if (!Schema::hasColumn('absensis', 'dzuhur_long')) {
                $table->string('dzuhur_long')->nullable()->after('dzuhur_lat');
            }

            if (!Schema::hasColumn('absensis', 'asar_lat')) {
                $table->string('asar_lat')->nullable()->after('dzuhur_long');
            }

            if (!Schema::hasColumn('absensis', 'asar_long')) {
                $table->string('asar_long')->nullable()->after('asar_lat');
            }

            if (!Schema::hasColumn('absensis', 'maghrib_lat')) {
                $table->string('maghrib_lat')->nullable()->after('asar_long');
            }

            if (!Schema::hasColumn('absensis', 'maghrib_long')) {
                $table->string('maghrib_long')->nullable()->after('maghrib_lat');
            }

            if (!Schema::hasColumn('absensis', 'isya_lat')) {
                $table->string('isya_lat')->nullable()->after('maghrib_long');
            }

            if (!Schema::hasColumn('absensis', 'isya_long')) {
                $table->string('isya_long')->nullable()->after('isya_lat');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            //
        });
    }
};
