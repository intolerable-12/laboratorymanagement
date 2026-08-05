<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrow_items', function (Blueprint $table) {

            $table->id();

            $table->foreignId('borrow_transaction_id')
                ->constrained('borrow_transactions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Equipment or Chemical
            $table->enum('item_type',[
                'Equipment',
                'Chemical'
            ]);

            // equipment.id or chemicals.id
            $table->unsignedBigInteger('item_id');

            // Requested/Released Quantity
            $table->decimal('quantity_borrowed',12,2);

            // Returned Quantity
            $table->decimal('quantity_returned',12,2)
                ->default(0);

            // Lost Quantity
            $table->decimal('quantity_lost',12,2)
                ->default(0);

            // Damaged Quantity
            $table->decimal('quantity_damaged',12,2)
                ->default(0);

            // Condition when released
            $table->enum('condition_out',[
                'Excellent',
                'Good',
                'Fair'
            ]);

            // Condition upon return
            $table->enum('condition_in',[
                'Excellent',
                'Good',
                'Fair',
                'Damaged',
                'Lost'
            ])->nullable();

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
        Schema::dropIfExists('borrow_items');
    }
};