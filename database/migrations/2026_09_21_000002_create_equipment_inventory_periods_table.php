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
        Schema::create('equipment_inventory_periods', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->foreignId('school_year_id')->constrained('school_years')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->unsignedInteger('beginning_quantity')->nullable();
            $table->unsignedInteger('ending_quantity')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['equipment_id', 'school_year_id', 'semester_id'], 'equipment_inventory_period_unique');
            $table->index(['school_year_id', 'semester_id']);
        });

        // Existing inventory receives a baseline so old equipment is immediately
        // available in the report without pretending that past changes are known.
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
        $equipment = DB::table('equipment')
            ->whereNull('deleted_at')
            ->get();

        if ($schoolYears->isEmpty() || $semesters->isEmpty() || $equipment->isEmpty()) {
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

                foreach ($equipment as $item) {
                    $acquiredAfterPeriod = $item->purchase_date
                        && Carbon::parse($item->purchase_date)->startOfDay()->greaterThan($periodEnd);
                    $quantity = $acquiredAfterPeriod ? null : (int) $item->quantity;

                    $rows[] = [
                        'equipment_id' => $item->id,
                        'school_year_id' => $schoolYear->id,
                        'semester_id' => $semester->id,
                        'beginning_quantity' => $quantity,
                        'ending_quantity' => $quantity,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('equipment_inventory_periods')->insert($chunk);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_inventory_periods');
    }
};
