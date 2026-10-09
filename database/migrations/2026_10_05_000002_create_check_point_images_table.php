<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('check_point_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('check_point_item_id')->constrained('check_point_items')->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();

            $table->index('check_point_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_point_images');
    }
};
