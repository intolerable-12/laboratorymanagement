<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {

            $table->id();

            // Human-readable reservation number
            $table->string('reservation_no', 30)->unique();

            // Student who created the reservation
            $table->unsignedBigInteger('user_no');

            // Laboratory reserved
            $table->foreignId('laboratory_id')
                ->constrained('laboratories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Reservation Details
            $table->string('experiment_title');

            $table->text('purpose');

            $table->date('reservation_date');

            $table->time('start_time');

            $table->time('end_time');

            $table->unsignedInteger('expected_participants')->default(1);

            // Current Status
            $table->enum('status', [
                'Pending',
                'Instructor Approved',
                'Coordinator Approved',
                'Rejected',
                'Cancelled',
                'Completed'
            ])->default('Pending');

            // Optional rejection remarks
            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->softDeletes();

            // Foreign Key
            $table->foreign('user_no')
                ->references('userNo')
                ->on('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('school_year_id')
                ->constrained('school_years')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('semester_id')
                ->constrained('semesters')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Indexes
            $table->index('reservation_date');
            $table->index('status');
            $table->index('user_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};