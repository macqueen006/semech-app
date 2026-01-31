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
        Schema::create('history_posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->text('body');
            $table->string('slug')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_published')->default(false); // Add default
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->integer('read_time')->nullable()->default(null);
            //SEO
            $table->string('meta_title', 60)->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->string('focus_keyword', 100)->nullable();
            $table->string('image_alt', 255)->nullable();
            $table->string('og_title', 60)->nullable();
            $table->string('og_description', 160)->nullable();
            $table->string('og_image', 500)->nullable();
            $table->string('twitter_title', 60)->nullable();
            $table->string('twitter_description', 160)->nullable();
            $table->string('twitter_image', 500)->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $table->unsignedBigInteger('change_user_id')->nullable(); // Make nullable
            $table->text('changelog')->nullable()->default(null);
            $table->foreign('change_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->tinyInteger('additional_info')->default(0);
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_posts');
    }
};
