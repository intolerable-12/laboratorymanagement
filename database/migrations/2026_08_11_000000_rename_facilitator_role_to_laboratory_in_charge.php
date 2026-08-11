<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')
            ->where('role_name', 'Facilitator')
            ->update(['role_name' => 'Laboratory In-charge']);
    }

    public function down(): void
    {
        DB::table('roles')
            ->where('role_name', 'Laboratory In-charge')
            ->update(['role_name' => 'Facilitator']);
    }
};
