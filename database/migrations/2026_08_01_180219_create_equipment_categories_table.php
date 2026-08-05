<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_categories', function (Blueprint $table) {

            $table->id();

            $table->string('category_code', 30)->unique();

            $table->string('category_name', 150);

            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('category_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_categories');
    }
};