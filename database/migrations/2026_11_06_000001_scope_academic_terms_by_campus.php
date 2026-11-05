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
        Schema::table('school_years', function (Blueprint $table): void {
            $table->string('campus', 20)->default(Campus::MAIN)->after('id');
        });
        Schema::table('semesters', function (Blueprint $table): void {
            $table->string('campus', 20)->default(Campus::MAIN)->after('id');
        });

        Schema::table('school_years', function (Blueprint $table): void {
            $table->dropUnique(['school_year']);
        });
        Schema::table('semesters', function (Blueprint $table): void {
            $table->dropUnique(['semester_name']);
        });

        $schoolYearIds = [];
        foreach (DB::table('school_years')->orderBy('id')->get() as $schoolYear) {
            $schoolYearIds[$schoolYear->id] = DB::table('school_years')->insertGetId([
                'campus' => Campus::IBED,
                'school_year' => $schoolYear->school_year,
                'is_current' => $schoolYear->is_current,
                'start_date' => $schoolYear->start_date,
                'end_date' => $schoolYear->end_date,
                'created_at' => $schoolYear->created_at,
                'updated_at' => $schoolYear->updated_at,
                'deleted_at' => $schoolYear->deleted_at,
            ]);
        }

        $semesterIds = [];
        foreach (DB::table('semesters')->orderBy('id')->get() as $semester) {
            $semesterIds[$semester->id] = DB::table('semesters')->insertGetId([
                'campus' => Campus::IBED,
                'semester_name' => $semester->semester_name,
                'display_order' => $semester->display_order,
                'is_current' => $semester->is_current,
                'created_at' => $semester->created_at,
                'updated_at' => $semester->updated_at,
                'deleted_at' => $semester->deleted_at,
            ]);
        }

        foreach (DB::table('academic_periods')->where('campus', Campus::IBED)->get() as $period) {
            DB::table('academic_periods')->where('id', $period->id)->update([
                'school_year_id' => $schoolYearIds[$period->school_year_id],
                'semester_id' => $semesterIds[$period->semester_id],
            ]);
        }

        $ibedSetting = DB::table('campus_academic_settings')->where('campus', Campus::IBED)->first();
        if ($ibedSetting) {
            DB::table('campus_academic_settings')->where('id', $ibedSetting->id)->update([
                'school_year_id' => $ibedSetting->school_year_id === null ? null : $schoolYearIds[$ibedSetting->school_year_id],
                'semester_id' => $ibedSetting->semester_id === null ? null : $semesterIds[$ibedSetting->semester_id],
            ]);
        }

        $this->remapInventoryPeriods('equipment_inventory_periods', 'equipment', $schoolYearIds, $semesterIds);
        $this->remapInventoryPeriods('chemical_inventory_periods', 'chemicals', $schoolYearIds, $semesterIds);
        $this->remapLaboratorySchedules($schoolYearIds, $semesterIds);
        $this->remapReservations($schoolYearIds, $semesterIds);

        Schema::table('school_years', function (Blueprint $table): void {
            $table->unique(['school_year', 'campus']);
        });
        Schema::table('semesters', function (Blueprint $table): void {
            $table->unique(['semester_name', 'campus']);
        });
    }

    public function down(): void
    {
        $schoolYearMap = [];
        foreach (DB::table('school_years')->where('campus', Campus::IBED)->orderBy('id')->get() as $schoolYear) {
            $mainSchoolYear = DB::table('school_years')
                ->where('campus', Campus::MAIN)
                ->where('school_year', $schoolYear->school_year)
                ->first();
            $schoolYearMap[$schoolYear->id] = $mainSchoolYear?->id ?? DB::table('school_years')->insertGetId([
                'campus' => Campus::MAIN,
                'school_year' => $schoolYear->school_year,
                'is_current' => $schoolYear->is_current,
                'start_date' => $schoolYear->start_date,
                'end_date' => $schoolYear->end_date,
                'created_at' => $schoolYear->created_at,
                'updated_at' => $schoolYear->updated_at,
                'deleted_at' => $schoolYear->deleted_at,
            ]);
        }
        $semesterMap = [];
        foreach (DB::table('semesters')->where('campus', Campus::IBED)->orderBy('id')->get() as $semester) {
            $mainSemester = DB::table('semesters')
                ->where('campus', Campus::MAIN)
                ->where('semester_name', $semester->semester_name)
                ->first();
            $semesterMap[$semester->id] = $mainSemester?->id ?? DB::table('semesters')->insertGetId([
                'campus' => Campus::MAIN,
                'semester_name' => $semester->semester_name,
                'display_order' => $semester->display_order,
                'is_current' => $semester->is_current,
                'created_at' => $semester->created_at,
                'updated_at' => $semester->updated_at,
                'deleted_at' => $semester->deleted_at,
            ]);
        }

        foreach (DB::table('academic_periods')->where('campus', Campus::IBED)->get() as $period) {
            DB::table('academic_periods')->where('id', $period->id)->update([
                'school_year_id' => $schoolYearMap[$period->school_year_id],
                'semester_id' => $semesterMap[$period->semester_id],
            ]);
        }

        $ibedSetting = DB::table('campus_academic_settings')->where('campus', Campus::IBED)->first();
        if ($ibedSetting) {
            DB::table('campus_academic_settings')->where('id', $ibedSetting->id)->update([
                'school_year_id' => $ibedSetting->school_year_id === null ? null : $schoolYearMap[$ibedSetting->school_year_id],
                'semester_id' => $ibedSetting->semester_id === null ? null : $semesterMap[$ibedSetting->semester_id],
            ]);
        }

        $this->remapInventoryPeriods('equipment_inventory_periods', 'equipment', $schoolYearMap, $semesterMap);
        $this->remapInventoryPeriods('chemical_inventory_periods', 'chemicals', $schoolYearMap, $semesterMap);
        $this->remapLaboratorySchedules($schoolYearMap, $semesterMap);
        $this->remapReservations($schoolYearMap, $semesterMap);

        Schema::table('school_years', function (Blueprint $table): void {
            $table->dropUnique(['school_year', 'campus']);
        });
        Schema::table('semesters', function (Blueprint $table): void {
            $table->dropUnique(['semester_name', 'campus']);
        });

        DB::table('school_years')->where('campus', Campus::IBED)->delete();
        DB::table('semesters')->where('campus', Campus::IBED)->delete();

        Schema::table('school_years', function (Blueprint $table): void {
            $table->unique('school_year');
            $table->dropColumn('campus');
        });
        Schema::table('semesters', function (Blueprint $table): void {
            $table->unique('semester_name');
            $table->dropColumn('campus');
        });
    }

    private function remapInventoryPeriods(string $periodTable, string $inventoryTable, array $schoolYearIds, array $semesterIds): void
    {
        $foreignKey = $inventoryTable === 'equipment' ? 'equipment_id' : 'chemical_id';

        DB::table($periodTable)
            ->join($inventoryTable, $inventoryTable.'.id', '=', $periodTable.'.'.$foreignKey)
            ->join('laboratories', 'laboratories.id', '=', $inventoryTable.'.laboratory_id')
            ->where('laboratories.campus', Campus::IBED)
            ->select($periodTable.'.id', $periodTable.'.school_year_id', $periodTable.'.semester_id')
            ->orderBy($periodTable.'.id')
            ->get()
            ->each(function (object $period) use ($periodTable, $schoolYearIds, $semesterIds): void {
                DB::table($periodTable)->where('id', $period->id)->update([
                    'school_year_id' => $schoolYearIds[$period->school_year_id],
                    'semester_id' => $semesterIds[$period->semester_id],
                ]);
            });
    }

    private function remapLaboratorySchedules(array $schoolYearIds, array $semesterIds): void
    {
        DB::table('laboratory_schedules')
            ->join('laboratories', 'laboratories.id', '=', 'laboratory_schedules.laboratory_id')
            ->where('laboratories.campus', Campus::IBED)
            ->select('laboratory_schedules.id', 'laboratory_schedules.school_year_id', 'laboratory_schedules.semester_id')
            ->orderBy('laboratory_schedules.id')
            ->get()
            ->each(function (object $schedule) use ($schoolYearIds, $semesterIds): void {
                DB::table('laboratory_schedules')->where('id', $schedule->id)->update([
                    'school_year_id' => $schoolYearIds[$schedule->school_year_id],
                    'semester_id' => $semesterIds[$schedule->semester_id],
                ]);
            });
    }

    private function remapReservations(array $schoolYearIds, array $semesterIds): void
    {
        DB::table('reservations')
            ->join('users', 'users.userNo', '=', 'reservations.user_no')
            ->where('users.campus', Campus::IBED)
            ->select('reservations.id', 'reservations.school_year_id', 'reservations.semester_id')
            ->orderBy('reservations.id')
            ->get()
            ->each(function (object $reservation) use ($schoolYearIds, $semesterIds): void {
                DB::table('reservations')->where('id', $reservation->id)->update([
                    'school_year_id' => $schoolYearIds[$reservation->school_year_id],
                    'semester_id' => $semesterIds[$reservation->semester_id],
                ]);
            });
    }
};
