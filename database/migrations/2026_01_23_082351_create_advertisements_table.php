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
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_path');
            $table->string('link_url')->nullable();
            $table->enum('position', ['header', 'sidebar', 'footer', 'between-posts', 'popup'])->default('sidebar');
            $table->enum('size', ['small', 'medium', 'large', 'banner'])->default('medium');
            $table->boolean('is_active')->default(true);
            $table->boolean('open_new_tab')->default(true);
            $table->integer('display_order')->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('clicks')->default(0);
            $table->integer('impressions')->default(0);
            $table->timestamps();

            $table->index(['position', 'is_active', 'start_date', 'end_date'], 'idx_ads_active_by_position');
            $table->index(['is_active', 'display_order'], 'idx_ads_display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};
