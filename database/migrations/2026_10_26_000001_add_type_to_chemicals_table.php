<?php

use App\Support\ChemicalType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chemicals', function (Blueprint $table): void {
            $table->enum('type', ChemicalType::values())
                ->nullable()
                ->after('chemical_name')
                ->index();
        });

        DB::table('chemicals')->update(['type' => ChemicalType::INORGANIC]);

        DB::table('chemicals')
            ->whereIn('chemical_name', ChemicalType::organicNames())
            ->update(['type' => ChemicalType::ORGANIC]);

        Schema::table('chemicals', function (Blueprint $table): void {
            $table->enum('type', ChemicalType::values())->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('chemicals', function (Blueprint $table): void {
            $table->dropIndex(['type']);
            $table->dropColumn('type');
        });
    }
};
