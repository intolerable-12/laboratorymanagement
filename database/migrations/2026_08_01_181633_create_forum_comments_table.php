<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_comments', function (Blueprint $table) {

            $table->id();

            $table->foreignId('post_id')
                ->constrained('forum_posts')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->unsignedBigInteger('user_no');

            // Supports nested replies
            $table->foreignId('parent_comment_id')
                ->nullable()
                ->constrained('forum_comments')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->longText('comment');

            $table->boolean('is_hidden')->default(false);

            $table->timestamps();

            $table->softDeletes();

            $table->foreign('user_no')
                ->references('userNo')
                ->on('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->index('user_no');
            $table->index('is_hidden');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_comments');
    }
};