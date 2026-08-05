<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback', function (Blueprint $table) {

            $table->id();

            // Student
            $table->unsignedBigInteger('user_no');

            // Reservation being evaluated
            $table->foreignId('reservation_id')
                ->constrained('reservations')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Overall Rating
            $table->unsignedTinyInteger('rating');

            // Comments
            $table->text('comments')->nullable();

            $table->enum('visibility',[
                'Private',
                'Public'
            ])->default('Private');

            $table->boolean('is_anonymous')->default(false);

            $table->timestamps();

            $table->softDeletes();

            $table->foreign('user_no')
                ->references('userNo')
                ->on('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->unique([
                'user_no',
                'reservation_id'
            ]);

            $table->index('rating');
            $table->index('visibility');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};