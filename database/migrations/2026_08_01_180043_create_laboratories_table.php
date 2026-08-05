<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratories', function (Blueprint $table) {

            $table->id();

            $table->string('laboratory_code', 20)->unique();

            $table->string('laboratory_name');

            $table->string('building', 100)->nullable();

            $table->string('room_number', 50);

            $table->unsignedInteger('capacity')->default(0);

            $table->text('description')->nullable();

            $table->enum('status', [
                'Available',
                'Unavailable',
                'Under Maintenance'
            ])->default('Available');

            $table->timestamps();

            $table->softDeletes();

            $table->index('laboratory_name');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratories');
    }
};