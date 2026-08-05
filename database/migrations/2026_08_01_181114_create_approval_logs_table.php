<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_logs', function (Blueprint $table) {

            $table->id();

            $table->foreignId('reservation_id')
                ->constrained('reservations')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Instructor/Coordinator who acted
            $table->unsignedBigInteger('approved_by');

            $table->enum('role', [
                'Instructor',
                'Coordinator'
            ]);

            $table->enum('action', [
                'Approved',
                'Rejected'
            ]);

            $table->text('remarks')->nullable();

            $table->timestamp('approved_at');

            $table->timestamps();

            $table->foreign('approved_by')
                ->references('userNo')
                ->on('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->index('approved_by');
            $table->index('approved_at');
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_logs');
    }
};