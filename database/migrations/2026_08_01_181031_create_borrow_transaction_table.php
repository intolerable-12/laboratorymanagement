<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrow_transactions', function (Blueprint $table) {

            $table->id();

            $table->string('borrow_no',30)->unique();

            // Reservation (optional)
            $table->foreignId('reservation_id')
                ->nullable()
                ->constrained('reservations')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            // Student Borrower
            $table->unsignedBigInteger('borrower_id');

            // Facilitator who released the items
            $table->unsignedBigInteger('released_by');

            // Facilitator who accepted returned items
            $table->unsignedBigInteger('received_by')->nullable();

            $table->dateTime('borrowed_at');

            $table->dateTime('due_at');

            $table->dateTime('returned_at')->nullable();

            $table->enum('status',[
                'Borrowed',
                'Partially Returned',
                'Returned',
                'Overdue',
                'Cancelled'
            ])->default('Borrowed');

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->softDeletes();

            // Foreign Keys
            $table->foreign('borrower_id')
                ->references('userNo')
                ->on('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('released_by')
                ->references('userNo')
                ->on('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('received_by')
                ->references('userNo')
                ->on('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            // Indexes
            $table->index('borrowed_at');
            $table->index('due_at');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borrow_transactions');
    }
};