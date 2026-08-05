<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {

            $table->id();

            // Coordinator/Admin
            $table->unsignedBigInteger('posted_by');

            $table->string('title');

            $table->longText('content');

            $table->date('start_date')->nullable();

            $table->date('end_date')->nullable();

            $table->boolean('send_email')->default(true);

            $table->boolean('is_published')->default(true);

            $table->timestamps();

            $table->softDeletes();

            $table->foreign('posted_by')
                ->references('userNo')
                ->on('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->index('posted_by');
            $table->index('is_published');
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};