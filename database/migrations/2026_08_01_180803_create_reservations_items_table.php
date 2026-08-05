<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_items', function (Blueprint $table) {

            $table->id();

            $table->foreignId('reservation_id')
                ->constrained('reservations')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Equipment or Chemical
            $table->enum('item_type',[
                'Equipment',
                'Chemical'
            ]);

            // References either equipment.id or chemicals.id
            $table->unsignedBigInteger('item_id');

            // Requested quantity
            $table->decimal('quantity',12,2)->default(1);

            $table->string('unit',20)->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index([
                'item_type',
                'item_id'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_items');
    }
};