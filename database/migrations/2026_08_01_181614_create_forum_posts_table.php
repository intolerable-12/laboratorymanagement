<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_posts', function (Blueprint $table) {

            $table->id();

            // Author
            $table->unsignedBigInteger('user_no');

            $table->string('title');

            $table->longText('content');

            $table->unsignedInteger('views')->default(0);

            $table->boolean('is_pinned')->default(false);

            $table->boolean('is_locked')->default(false);

            $table->boolean('is_hidden')->default(false);

            $table->timestamps();

            $table->softDeletes();

            $table->foreign('user_no')
                ->references('userNo')
                ->on('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->index('user_no');
            $table->index('is_pinned');
            $table->index('is_hidden');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_posts');
    }
};