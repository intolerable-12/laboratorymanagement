<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('borrow_items', 'quantity_used')) {
            Schema::table('borrow_items', function (Blueprint $table) {
                $table->decimal('quantity_used', 12, 2)->default(0)->after('quantity_returned');
            });
        }

        if (! Schema::hasColumn('barcode_logs', 'condition_in')) {
            Schema::table('barcode_logs', function (Blueprint $table) {
                $table->enum('condition_in', ['Excellent', 'Good', 'Fair', 'Damaged', 'Lost'])
                    ->nullable()
                    ->after('is_voided');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('barcode_logs', 'condition_in')) {
            Schema::table('barcode_logs', function (Blueprint $table) {
                $table->dropColumn('condition_in');
            });
        }

        if (Schema::hasColumn('borrow_items', 'quantity_used')) {
            Schema::table('borrow_items', function (Blueprint $table) {
                $table->dropColumn('quantity_used');
            });
        }
    }
};
