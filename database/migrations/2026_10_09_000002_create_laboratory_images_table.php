<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratory_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('laboratory_id')->constrained('laboratories')->cascadeOnDelete();
            $table->string('image_path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['laboratory_id', 'sort_order']);
        });

        if (! Schema::hasColumn('laboratories', 'image')) {
            return;
        }

        DB::table('laboratories')
            ->select(['id', 'image'])
            ->whereNotNull('image')
            ->where('image', '!=', '')
            ->orderBy('id')
            ->each(function (object $laboratory): void {
                DB::table('laboratory_images')->insert([
                    'laboratory_id' => $laboratory->id,
                    'image_path' => $laboratory->image,
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratory_images');
    }
};
