<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE borrow_items MODIFY condition_out ENUM('Excellent', 'Good', 'Fair', 'Damaged', 'Under Repair', 'Lost') NOT NULL");
        DB::statement("ALTER TABLE borrow_items MODIFY condition_in ENUM('Excellent', 'Good', 'Fair', 'Damaged', 'Under Repair', 'Lost') NULL");
        DB::statement("ALTER TABLE barcode_logs MODIFY condition_in ENUM('Excellent', 'Good', 'Fair', 'Damaged', 'Under Repair', 'Lost') NULL");
    }

    public function down(): void
    {
        DB::table('borrow_items')
            ->whereIn('condition_out', ['Damaged', 'Under Repair', 'Lost'])
            ->update(['condition_out' => 'Fair']);

        DB::table('borrow_items')
            ->where('condition_in', 'Under Repair')
            ->update(['condition_in' => 'Damaged']);

        DB::table('barcode_logs')
            ->where('condition_in', 'Under Repair')
            ->update(['condition_in' => 'Damaged']);

        DB::statement("ALTER TABLE borrow_items MODIFY condition_out ENUM('Excellent', 'Good', 'Fair') NOT NULL");
        DB::statement("ALTER TABLE borrow_items MODIFY condition_in ENUM('Excellent', 'Good', 'Fair', 'Damaged', 'Lost') NULL");
        DB::statement("ALTER TABLE barcode_logs MODIFY condition_in ENUM('Excellent', 'Good', 'Fair', 'Damaged', 'Lost') NULL");
    }
};
