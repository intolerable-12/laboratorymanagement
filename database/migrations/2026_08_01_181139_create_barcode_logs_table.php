<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barcode_logs', function (Blueprint $table) {

            $table->id();

            // User who scanned
            $table->unsignedBigInteger('user_no');

            $table->enum('item_type', [
                'Equipment',
                'Chemical'
            ]);

            // equipment.id or chemicals.id
            $table->unsignedBigInteger('item_id');

            $table->string('barcode', 100);

            $table->enum('action', [
                'Borrow',
                'Return',
                'Inventory Scan',
                'Verification'
            ]);

            $table->dateTime('scanned_at');

            $table->string('device_name')->nullable();

            $table->string('ip_address', 45)->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->foreign('user_no')
                ->references('userNo')
                ->on('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->index('barcode');
            $table->index('scanned_at');
            $table->index('action');
            $table->index(['item_type', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barcode_logs');
    }
};