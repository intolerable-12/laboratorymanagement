<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE approval_logs MODIFY role ENUM('Instructor', 'Facilitator', 'Laboratory In-charge', 'Coordinator') NOT NULL");

        DB::table('approval_logs')
            ->where('role', 'Facilitator')
            ->update(['role' => 'Laboratory In-charge']);

        DB::statement("ALTER TABLE approval_logs MODIFY role ENUM('Instructor', 'Laboratory In-charge', 'Coordinator') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE approval_logs MODIFY role ENUM('Instructor', 'Facilitator', 'Laboratory In-charge', 'Coordinator') NOT NULL");

        DB::table('approval_logs')
            ->where('role', 'Laboratory In-charge')
            ->update(['role' => 'Facilitator']);

        DB::statement("ALTER TABLE approval_logs MODIFY role ENUM('Instructor', 'Facilitator', 'Coordinator') NOT NULL");
    }
};
