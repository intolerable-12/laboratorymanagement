<?php

use App\Support\Campus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table): void {
            $table->string('campus', 20)->default(Campus::MAIN)->after('department_name');
            $table->index('campus');
        });

        Schema::table('laboratories', function (Blueprint $table): void {
            $table->string('campus', 20)->default(Campus::MAIN)->after('laboratory_name');
            $table->index('campus');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->string('campus', 20)->nullable()->after('department_id');
            $table->index('campus');
        });

        DB::table('departments')->whereIn('department_code', ['JHS'])->update(['campus' => Campus::IBED]);
        DB::table('departments')->whereIn('department_code', ['COL', 'SHS'])->update(['campus' => Campus::MAIN]);

        // Keep the existing seeded science laboratories usable after the migration.
        DB::table('laboratories')->where('laboratory_code', 'LAB-003')->update(['campus' => Campus::IBED]);

        DB::table('users')
            ->whereNull('campus')
            ->whereIn('role_id', DB::table('roles')->where('role_name', 'Laboratory In-charge')->pluck('id'))
            ->update(['campus' => Campus::MAIN]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['campus']);
            $table->dropColumn('campus');
        });

        Schema::table('laboratories', function (Blueprint $table): void {
            $table->dropIndex(['campus']);
            $table->dropColumn('campus');
        });

        Schema::table('departments', function (Blueprint $table): void {
            $table->dropIndex(['campus']);
            $table->dropColumn('campus');
        });
    }
};
