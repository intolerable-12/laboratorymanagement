<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrow_transactions', function (Blueprint $table) {
            $table->dateTime('checked_out_at')->nullable()->after('borrowed_at');
        });

        Schema::table('borrow_items', function (Blueprint $table) {
            $table->decimal('quantity_checked_out', 12, 2)->default(0)->after('quantity_borrowed');
        });

        DB::statement("ALTER TABLE borrow_transactions MODIFY status ENUM('Pending', 'Instructor Approved', 'Facilitator Approved', 'Coordinator Approved', 'Partially Borrowed', 'Rejected', 'Cancelled', 'Borrowed', 'Partially Returned', 'Returned', 'Overdue') NOT NULL DEFAULT 'Pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE borrow_transactions MODIFY status ENUM('Pending', 'Instructor Approved', 'Facilitator Approved', 'Coordinator Approved', 'Rejected', 'Cancelled', 'Borrowed', 'Partially Returned', 'Returned', 'Overdue') NOT NULL DEFAULT 'Pending'");

        Schema::table('borrow_items', function (Blueprint $table) {
            $table->dropColumn('quantity_checked_out');
        });

        Schema::table('borrow_transactions', function (Blueprint $table) {
            $table->dropColumn('checked_out_at');
        });
    }
};
