<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE borrow_items MODIFY condition_out ENUM('Excellent', 'Good', 'Fair', 'Damaged', 'Under Repair', 'Lost', 'Active', 'Inactive', 'Expired', 'For Disposal') NOT NULL");
    }

    public function down(): void
    {
        DB::table('borrow_items')
            ->whereIn('condition_out', ['Active', 'Inactive', 'Expired', 'For Disposal'])
            ->update(['condition_out' => 'Good']);

        DB::statement("ALTER TABLE borrow_items MODIFY condition_out ENUM('Excellent', 'Good', 'Fair', 'Damaged', 'Under Repair', 'Lost') NOT NULL");
    }
};
