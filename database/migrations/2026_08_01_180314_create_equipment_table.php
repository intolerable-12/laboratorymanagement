<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {

            $table->id();

            // Identification
            $table->string('equipment_code',30)->unique();
            $table->string('barcode',100)->unique();

            $table->string('equipment_name');

            // Relationships
            $table->foreignId('category_id')
                ->constrained('equipment_categories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('laboratory_id')
                ->constrained('laboratories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('supplier_id')
                ->nullable()
                ->constrained('suppliers')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            // Information
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();

            $table->date('purchase_date')->nullable();

            $table->decimal('unit_cost',12,2)->nullable();

            // Inventory
            $table->unsignedInteger('quantity')->default(0);

            $table->unsignedInteger('available_quantity')->default(0);

            $table->unsignedInteger('minimum_stock')->default(1);

            // Status
            $table->enum('condition',[
                'Excellent',
                'Good',
                'Fair',
                'Damaged',
                'Under Repair',
                'Condemned'
            ])->default('Good');

            $table->enum('status',[
                'Available',
                'Borrowed',
                'Reserved',
                'Unavailable',
                'Maintenance'
            ])->default('Available');

            // Image
            $table->string('image')->nullable();

            // Location
            $table->string('storage_location')->nullable();

            // Notes
            $table->text('description')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('equipment_name');
            $table->index('status');
            $table->index('condition');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};