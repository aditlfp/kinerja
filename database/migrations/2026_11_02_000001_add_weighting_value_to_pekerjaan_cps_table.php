<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pekerjaan_cps', function (Blueprint $table): void {
            $table->unsignedSmallInteger('weighting_value')->default(0)->after('type_check');
        });
    }

    public function down(): void
    {
        Schema::table('pekerjaan_cps', function (Blueprint $table): void {
            $table->dropColumn('weighting_value');
        });
    }
};
