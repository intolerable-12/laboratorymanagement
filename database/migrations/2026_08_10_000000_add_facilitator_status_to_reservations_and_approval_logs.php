<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE reservations MODIFY status ENUM('Pending', 'Instructor Approved', 'Facilitator Approved', 'Coordinator Approved', 'Rejected', 'Cancelled', 'Completed') NOT NULL DEFAULT 'Pending'");
        DB::statement("ALTER TABLE approval_logs MODIFY role ENUM('Instructor', 'Facilitator', 'Coordinator') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE reservations MODIFY status ENUM('Pending', 'Instructor Approved', 'Coordinator Approved', 'Rejected', 'Cancelled', 'Completed') NOT NULL DEFAULT 'Pending'");
        DB::statement("ALTER TABLE approval_logs MODIFY role ENUM('Instructor', 'Coordinator') NOT NULL");
    }
};