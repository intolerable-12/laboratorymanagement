<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereIn('status', ['Inactive', 'Suspended'])
            ->update(['status' => 'Active']);

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE users MODIFY status ENUM('Active', 'Archived') NOT NULL DEFAULT 'Active'");
        }

        DB::table('users')
            ->whereNotNull('deleted_at')
            ->update(['status' => 'Archived']);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('status', 'Archived')
            ->update(['status' => 'Active']);

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE users MODIFY status ENUM('Active', 'Inactive', 'Suspended') NOT NULL DEFAULT 'Active'");
        }
    }
};
