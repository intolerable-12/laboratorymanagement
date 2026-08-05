<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_logs', function (Blueprint $table) {

            $table->id();

            // Equipment or Chemical
            $table->enum('item_type', [
                'Equipment',
                'Chemical'
            ]);

            // equipment.id or chemicals.id
            $table->unsignedBigInteger('item_id');

            // User who performed the action
            $table->unsignedBigInteger('performed_by');

            $table->enum('action', [
                'Borrow',
                'Return',
                'Purchase',
                'Adjustment',
                'Damage',
                'Lost',
                'Maintenance',
                'Stock In',
                'Stock Out'
            ]);

            $table->decimal('quantity_before', 12, 2);

            $table->decimal('quantity_changed', 12, 2);

            $table->decimal('quantity_after', 12, 2);

            $table->text('remarks')->nullable();

            $table->timestamp('performed_at');

            $table->timestamps();

            $table->foreign('performed_by')
                ->references('userNo')
                ->on('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->index(['item_type', 'item_id']);
            $table->index('performed_at');
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
    }
};