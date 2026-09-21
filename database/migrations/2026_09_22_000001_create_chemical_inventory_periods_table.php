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
        Schema::create('chemical_inventory_periods', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('chemical_id')->constrained('chemicals')->cascadeOnDelete();
            $table->foreignId('school_year_id')->constrained('school_years')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->decimal('beginning_quantity', 12, 2)->nullable();
            $table->decimal('ending_quantity', 12, 2)->nullable();
            $table->decimal('used_quantity', 12, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['chemical_id', 'school_year_id', 'semester_id'], 'chemical_inventory_period_unique');
            $table->index(['school_year_id', 'semester_id']);
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
        $chemicals = DB::table('chemicals')
            ->whereNull('deleted_at')
            ->get();

        if ($schoolYears->isEmpty() || $semesters->isEmpty() || $chemicals->isEmpty()) {
            return;
        }

        $rows = [];
        $semesterCount = $semesters->count();

        foreach ($schoolYears as $schoolYear) {
            $start = $schoolYear->start_date
                ? Carbon::parse($schoolYear->start_date)->startOfDay()
                : Carbon::now()->startOfYear();
            $end = $schoolYear->end_date
                ? Carbon::parse($schoolYear->end_date)->endOfDay()
                : $start->copy()->endOfYear();
            $totalDays = max(1, $start->diffInDays($end) + 1);

            foreach ($semesters as $semesterIndex => $semester) {
                $periodStart = $start->copy()->addDays((int) floor($totalDays * $semesterIndex / $semesterCount));
                $periodEnd = $start->copy()
                    ->addDays((int) floor($totalDays * ($semesterIndex + 1) / $semesterCount) - 1)
                    ->endOfDay();
                $periodEnd = $periodEnd->lt($periodStart) ? $periodStart->copy() : $periodEnd;

                foreach ($chemicals as $item) {
                    $acquiredAfterPeriod = $item->received_date
                        && Carbon::parse($item->received_date)->startOfDay()->greaterThan($periodEnd);
                    $quantity = $acquiredAfterPeriod ? null : (float) $item->quantity;

                    $rows[] = [
                        'chemical_id' => $item->id,
                        'school_year_id' => $schoolYear->id,
                        'semester_id' => $semester->id,
                        'beginning_quantity' => $quantity,
                        'ending_quantity' => $quantity,
                        'used_quantity' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('chemical_inventory_periods')->insert($chunk);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chemical_inventory_periods');
    }
};
