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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('comments')->onDelete('cascade');
            $table->string('name', 100)->nullable();
            $table->text('body');
            $table->boolean('is_approved')->default(false);
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->timestamps();

            $table->index(['post_id', 'is_approved', 'created_at'], 'idx_comments_approved_sorted');
            $table->index(['parent_id', 'is_approved'], 'idx_comments_replies');
            $table->index(['is_approved', 'created_at'], 'idx_comments_recent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
