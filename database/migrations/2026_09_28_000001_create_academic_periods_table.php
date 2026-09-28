<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_periods', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_year_id')->constrained('school_years')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();

            $table->unique(['school_year_id', 'semester_id']);
            $table->index(['school_year_id', 'start_date', 'end_date']);
        });

        $schoolYears = DB::table('school_years')
            ->whereNull('deleted_at')
            ->orderBy('start_date')
            ->orderBy('id')
            ->get();
        $semesters = DB::table('semesters')
            ->whereNull('deleted_at')
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();

        if ($schoolYears->isEmpty() || $semesters->isEmpty()) {
            return;
        }

        $rows = [];
        $semesterCount = $semesters->count();

        foreach ($schoolYears as $schoolYear) {
            $start = Carbon::parse($schoolYear->start_date)->startOfDay();
            $end = Carbon::parse($schoolYear->end_date)->endOfDay();
            $totalDays = max(1, $start->diffInDays($end) + 1);

            foreach ($semesters as $semesterIndex => $semester) {
                $periodStart = $start->copy()->addDays((int) floor($totalDays * $semesterIndex / $semesterCount));
                $periodEnd = $start->copy()
                    ->addDays((int) floor($totalDays * ($semesterIndex + 1) / $semesterCount) - 1)
                    ->endOfDay();

                $rows[] = [
                    'school_year_id' => $schoolYear->id,
                    'semester_id' => $semester->id,
                    'start_date' => $periodStart->toDateString(),
                    'end_date' => ($periodEnd->lt($periodStart) ? $periodStart : $periodEnd)->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('academic_periods')->insert($chunk);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_periods');
    }
};
