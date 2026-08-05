<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratory_schedules', function (Blueprint $table) {

            $table->id();

            $table->foreignId('laboratory_id')
                ->constrained('laboratories')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('school_year_id')
                ->constrained('school_years')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('semester_id')
                ->constrained('semesters')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->enum('day_of_week',[
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday',
                'Sunday'
            ]);

            $table->time('start_time');

            $table->time('end_time');

            $table->boolean('is_available')->default(true);

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->softDeletes();

            $table->index([
                'laboratory_id',
                'day_of_week'
            ]);

            $table->index([
                'school_year_id',
                'semester_id'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratory_schedules');
    }
};