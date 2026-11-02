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
        Schema::table('academic_periods', function (Blueprint $table): void {
            $table->string('campus', 20)->default(Campus::MAIN)->after('semester_id');
        });

        Schema::table('academic_periods', function (Blueprint $table): void {
            $table->dropUnique(['school_year_id', 'semester_id']);
        });

        $mainPeriods = DB::table('academic_periods')
            ->where('campus', Campus::MAIN)
            ->get();

        foreach ($mainPeriods as $period) {
            DB::table('academic_periods')->insert([
                'school_year_id' => $period->school_year_id,
                'semester_id' => $period->semester_id,
                'campus' => Campus::IBED,
                'start_date' => $period->start_date,
                'end_date' => $period->end_date,
                'created_at' => $period->created_at,
                'updated_at' => $period->updated_at,
            ]);
        }

        Schema::table('academic_periods', function (Blueprint $table): void {
            $table->unique(['school_year_id', 'semester_id', 'campus']);
            $table->index('campus');
        });
    }

    public function down(): void
    {
        DB::table('academic_periods')->where('campus', Campus::IBED)->delete();

        Schema::table('academic_periods', function (Blueprint $table): void {
            $table->dropUnique(['school_year_id', 'semester_id', 'campus']);
            $table->dropIndex(['campus']);
            $table->unique(['school_year_id', 'semester_id']);
            $table->dropColumn('campus');
        });
    }
};
