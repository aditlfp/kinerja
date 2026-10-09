<?php

use App\Models\WorkOrder;
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
        Schema::table('check_points', function (Blueprint $table) {
            $table->foreignIdFor(WorkOrder::class)->nullable()->after('pekerjaan_cp_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('check_points', function (Blueprint $table) {
            $table->dropColumn('work_order_id');
        });
    }
};
