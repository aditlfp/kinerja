<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_final_scores', function (Blueprint $table): void {
            $table->id();
            // User IDs are stored without FK because this deployment keeps the
            // user table on a separate/legacy schema connection.
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->decimal('score', 5, 2);
            $table->unsignedBigInteger('created_by')->index();
            $table->timestamps();

            $table->unique(['user_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_final_scores');
    }
};
