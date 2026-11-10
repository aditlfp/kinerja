<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE person_outs MODIFY out_date DATE NULL DEFAULT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE person_outs MODIFY out_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    }
};
