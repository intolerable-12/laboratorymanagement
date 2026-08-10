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
            $table->foreignId('laboratory_id')
                ->nullable()
                ->after('borrow_no')
                ->constrained('laboratories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        DB::statement("ALTER TABLE borrow_transactions MODIFY borrowed_at DATETIME NULL");
        DB::statement("ALTER TABLE borrow_transactions MODIFY due_at DATETIME NULL");
        DB::statement("ALTER TABLE borrow_transactions MODIFY released_by BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE borrow_transactions MODIFY status ENUM('Pending', 'Instructor Approved', 'Facilitator Approved', 'Coordinator Approved', 'Rejected', 'Cancelled', 'Borrowed', 'Partially Returned', 'Returned', 'Overdue') NOT NULL DEFAULT 'Pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE borrow_transactions MODIFY status ENUM('Borrowed', 'Partially Returned', 'Returned', 'Overdue', 'Cancelled') NOT NULL DEFAULT 'Borrowed'");
        DB::statement("ALTER TABLE borrow_transactions MODIFY released_by BIGINT UNSIGNED NOT NULL");
        DB::statement("ALTER TABLE borrow_transactions MODIFY due_at DATETIME NOT NULL");
        DB::statement("ALTER TABLE borrow_transactions MODIFY borrowed_at DATETIME NOT NULL");

        Schema::table('borrow_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('laboratory_id');
        });
    }
};