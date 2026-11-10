<?php

use App\Models\User;
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
        Schema::create('person_outs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->cascadeOnDelete();
            $table->string('total_mk');
            $table->date('out_date')->nullable()->default(null);
            $table->string('reason');
            $table->string('reason_manual')->nullable();
            $table->string('img');
            $table->string('status')->nullable()->default('pending');
            $table->timestamps();
            $table->unique(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('person_outs');
    }
};
