<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE chemicals MODIFY status ENUM('Available', 'Low Stock', 'Expired', 'Disposed', 'Unavailable', 'Active', 'Inactive', 'For Disposal') NOT NULL DEFAULT 'Active'");
        }

        DB::table('chemicals')->whereIn('status', ['Available', 'Low Stock'])->update(['status' => 'Active']);
        DB::table('chemicals')->where('status', 'Unavailable')->update(['status' => 'Inactive']);
        DB::table('chemicals')->where('status', 'Disposed')->update(['status' => 'For Disposal']);
        DB::table('chemicals')
            ->where('status', 'Active')
            ->whereNotNull('expiration_date')
            ->whereDate('expiration_date', '<', today())
            ->update(['status' => 'Expired']);

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE chemicals MODIFY status ENUM('Active', 'Inactive', 'Expired', 'For Disposal') NOT NULL DEFAULT 'Active'");
        }
    }

    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE chemicals MODIFY status ENUM('Available', 'Low Stock', 'Expired', 'Disposed', 'Unavailable', 'Active', 'Inactive', 'For Disposal') NOT NULL DEFAULT 'Active'");
        }

        DB::table('chemicals')->where('status', 'Active')->update(['status' => 'Available']);
        DB::table('chemicals')->where('status', 'Inactive')->update(['status' => 'Unavailable']);
        DB::table('chemicals')->where('status', 'For Disposal')->update(['status' => 'Disposed']);

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE chemicals MODIFY status ENUM('Available', 'Low Stock', 'Expired', 'Disposed', 'Unavailable') NOT NULL DEFAULT 'Available'");
        }
    }
};
