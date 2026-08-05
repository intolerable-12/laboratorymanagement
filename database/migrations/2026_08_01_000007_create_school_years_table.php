<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_years', function (Blueprint $table) {

            $table->id();

            $table->string('school_year',20)->unique();
            // Example: 2026-2027

            $table->boolean('is_current')->default(false);

            $table->date('start_date');

            $table->date('end_date');

            $table->timestamps();

            $table->softDeletes();

            $table->index('is_current');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_years');
    }
};