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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('title')->unique();
            $table->string('meta_title', 80)->nullable();
            $table->text('excerpt')->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->text('body');
            $table->string('slug')->unique();
            $table->string('image_path');
            $table->string('image_alt', 255)->nullable();
            $table->boolean('is_published');
            $table->boolean('can_comment')->default(false);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->integer('read_time')->nullable()->default(null);
            $table->integer('view_count')->default(0);
            // Open Graph tags
            $table->string('og_title', 80)->nullable();
            $table->string('og_description', 160)->nullable();
            $table->string('focus_keyword', 100)->nullable();
            $table->string('og_image', 2048)->nullable();
            // Twitter Card tags
            $table->string('twitter_title', 80)->nullable();
            $table->string('twitter_description', 160)->nullable();
            $table->string('twitter_image', 2048)->nullable();

            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $table->unsignedBigInteger('change_user_id');
            $table->text('changelog')->nullable()->default(null);
            $table->foreign('change_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->tinyInteger('additional_info')->default(0);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();

            $table->index(['is_published', 'scheduled_at', 'expires_at'], 'idx_posts_visibility');
            $table->index(['category_id', 'is_published', 'view_count'], 'idx_posts_category_popular');
            $table->index(['is_published', 'created_at', 'view_count'], 'idx_posts_trending');
            $table->index('view_count');
            $table->index('category_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
