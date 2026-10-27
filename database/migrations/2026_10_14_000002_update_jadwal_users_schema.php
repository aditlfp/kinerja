<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Rapikan dulu schema kolom agar bisa menampung jadwal tanpa shift/area.
        Schema::table('jadwal_users', function (Blueprint $table): void {
            if (! Schema::hasColumn('jadwal_users', 'area_id')) {
                $table->unsignedBigInteger('area_id')->nullable()->after('tanggal');
            }
            if (Schema::hasColumn('jadwal_users', 'area')) {
                $table->string('area')->nullable()->change();
            }
            if (Schema::hasColumn('jadwal_users', 'shift_id')) {
                $table->unsignedBigInteger('shift_id')->nullable()->change();
            }
            if (Schema::hasColumn('jadwal_users', 'area_id')) {
                $table->unsignedBigInteger('area_id')->nullable()->change();
            }
        });

        // 2. Hapus duplikat (user_id, tanggal) — simpan baris dengan id terbesar.
        $duplicateCount = DB::table('jadwal_users')
            ->select('user_id', 'tanggal', DB::raw('COUNT(*) as total'))
            ->groupBy('user_id', 'tanggal')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateCount as $row) {
            $keepId = DB::table('jadwal_users')
                ->where('user_id', $row->user_id)
                ->where('tanggal', $row->tanggal)
                ->max('id');

            DB::table('jadwal_users')
                ->where('user_id', $row->user_id)
                ->where('tanggal', $row->tanggal)
                ->where('id', '!=', $keepId)
                ->delete();
        }

        // 3. Baru amankan dengan unique index untuk upsert bulanan.
        Schema::table('jadwal_users', function (Blueprint $table): void {
            $table->unique(['user_id', 'tanggal'], 'jadwal_users_user_tanggal_unique');
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_users', function (Blueprint $table): void {
            $table->dropUnique('jadwal_users_user_tanggal_unique');
        });
    }
};
