<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chemicals', function (Blueprint $table) {

            $table->id();

            // Identification
            $table->string('chemical_code',30)->unique();
            $table->string('barcode',100)->unique();

            $table->string('chemical_name');

            // CAS Registry Number
            $table->string('cas_number')->nullable();

            // Relationships
            $table->foreignId('category_id')
                ->constrained('chemical_categories')
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

            // Inventory
            $table->decimal('quantity',12,2)->default(0);

            $table->string('unit',20); // g, kg, mL, L, bottle, etc.

            $table->decimal('minimum_stock',12,2)->default(1);

            // Dates
            $table->date('manufactured_date')->nullable();

            $table->date('expiration_date')->nullable();

            $table->date('received_date')->nullable();

            $table->decimal('unit_cost',12,2)->nullable();

            // Classification
            $table->enum('hazard_classification',[
                'Non-Hazardous',
                'Flammable',
                'Corrosive',
                'Oxidizer',
                'Toxic',
                'Explosive',
                'Compressed Gas',
                'Irritant',
                'Environmental Hazard'
            ])->default('Non-Hazardous');

            // Storage
            $table->string('storage_location')->nullable();

            // Availability
            $table->enum('status',[
                'Available',
                'Low Stock',
                'Expired',
                'Disposed',
                'Unavailable'
            ])->default('Available');

            $table->string('image')->nullable();

            $table->text('description')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->softDeletes();

            // Indexes
            $table->index('chemical_name');
            $table->index('expiration_date');
            $table->index('status');
            $table->index('hazard_classification');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chemicals');
    }
};