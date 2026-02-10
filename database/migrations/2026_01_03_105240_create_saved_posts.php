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
        Schema::create('saved_posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('title')->nullable();
            $table->text('excerpt')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $table->text('body')->nullable();
            $table->integer('read_time')->nullable()->default(null);
            $table->string('image_path')->nullable();
            $table->boolean('is_published')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            //SEO
            $table->string('meta_title', 80)->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->string('focus_keyword', 100)->nullable();
            $table->string('image_alt', 255)->nullable();
            $table->string('og_title', 80)->nullable();
            $table->string('og_description', 160)->nullable();
            $table->string('og_image', 2048)->nullable();
            $table->string('twitter_title', 80)->nullable();
            $table->string('twitter_description', 160)->nullable();
            $table->string('twitter_image', 2048)->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_posts');
    }
};
