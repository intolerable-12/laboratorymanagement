<?php

use App\Exports\ChemicalInventoryReportExport;
use App\Exports\EquipmentInventoryReportExport;
use App\Models\AcademicPeriod;
use App\Models\Chemical;
use App\Models\Equipment;
use App\Models\Laboratory;
use App\Models\Role;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\User;
use App\Support\Campus;
use App\Support\AcademicPeriodSelection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('keeps academic periods and generated inventory reports scoped to each campus', function () {
    $coordinatorRole = Role::create(['role_name' => 'Coordinator']);
    $inChargeRole = Role::create(['role_name' => 'Laboratory In-charge']);
    $mainCoordinator = User::create([
        'userID' => 'MAIN-COORD',
        'first_name' => 'Main',
        'last_name' => 'Coordinator',
        'email' => 'main.coordinator@example.test',
        'password' => bcrypt('password123'),
        'role_id' => $coordinatorRole->id,
        'campus' => Campus::MAIN,
        'status' => 'Active',
    ]);
    $ibedInCharge = User::create([
        'userID' => 'IBED-INCHARGE',
        'first_name' => 'IBED',
        'last_name' => 'In-charge',
        'email' => 'ibed.incharge@example.test',
        'password' => bcrypt('password123'),
        'role_id' => $inChargeRole->id,
        'campus' => Campus::IBED,
        'status' => 'Active',
    ]);

    $schoolYear = SchoolYear::create([
        'school_year' => '2026-2027',
        'is_current' => true,
        'start_date' => '2026-08-01',
        'end_date' => '2027-05-31',
    ]);
    $ibedSchoolYear = SchoolYear::create([
        'campus' => Campus::IBED,
        'school_year' => '2026-2027',
        'is_current' => true,
        'start_date' => '2026-08-01',
        'end_date' => '2027-05-31',
    ]);
    $firstSemester = Semester::create([
        'semester_name' => 'First Semester',
        'display_order' => 1,
        'is_current' => true,
    ]);
    $secondSemester = Semester::create([
        'semester_name' => 'Second Semester',
        'display_order' => 2,
    ]);
    $ibedFirstSemester = Semester::create([
        'campus' => Campus::IBED,
        'semester_name' => 'First Semester',
        'display_order' => 1,
        'is_current' => true,
    ]);
    $ibedSecondSemester = Semester::create([
        'campus' => Campus::IBED,
        'semester_name' => 'Second Semester',
        'display_order' => 2,
    ]);

    $this->actingAs($mainCoordinator)
        ->get(route('coordinator.academic-periods.index'))
        ->assertOk()
        ->assertViewHas('campus', Campus::MAIN);

    $this->actingAs($ibedInCharge)
        ->get(route('facilitator.academic-periods.index'))
        ->assertOk()
        ->assertViewHas('campus', Campus::IBED);

    $ibedFirstPeriod = AcademicPeriod::query()
        ->where('school_year_id', $ibedSchoolYear->id)
        ->where('semester_id', $ibedFirstSemester->id)
        ->where('campus', Campus::IBED)
        ->firstOrFail();

    $this->actingAs($mainCoordinator)
        ->put(route('coordinator.academic-periods.semester-periods.update'), [
            'periods' => [
                $schoolYear->id => [
                    $firstSemester->id => [
                        'start_date' => '2026-08-01',
                        'end_date' => '2026-12-15',
                    ],
                    $secondSemester->id => [
                        'start_date' => '2026-12-16',
                        'end_date' => '2027-05-31',
                    ],
                ],
            ],
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('academic_periods', [
        'school_year_id' => $schoolYear->id,
        'semester_id' => $firstSemester->id,
        'campus' => Campus::MAIN,
        'start_date' => '2026-08-01',
        'end_date' => '2026-12-15',
    ]);
    $this->assertDatabaseHas('academic_periods', [
        'id' => $ibedFirstPeriod->id,
        'campus' => Campus::IBED,
        'start_date' => $ibedFirstPeriod->start_date->toDateString(),
        'end_date' => $ibedFirstPeriod->end_date->toDateString(),
    ]);

    $this->actingAs($ibedInCharge)
        ->put(route('facilitator.academic-periods.semester-periods.update'), [
            'periods' => [
                $ibedSchoolYear->id => [
                    $ibedFirstSemester->id => [
                        'start_date' => '2026-08-03',
                        'end_date' => '2027-01-15',
                    ],
                    $ibedSecondSemester->id => [
                        'start_date' => '2027-01-16',
                        'end_date' => '2027-05-30',
                    ],
                ],
            ],
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('academic_periods', [
        'school_year_id' => $ibedSchoolYear->id,
        'semester_id' => $ibedFirstSemester->id,
        'campus' => Campus::IBED,
        'start_date' => '2026-08-03',
        'end_date' => '2027-01-15',
    ]);
    $this->assertDatabaseHas('academic_periods', [
        'school_year_id' => $schoolYear->id,
        'semester_id' => $firstSemester->id,
        'campus' => Campus::MAIN,
        'start_date' => '2026-08-01',
        'end_date' => '2026-12-15',
    ]);

    $nextSchoolYear = SchoolYear::create([
        'school_year' => '2027-2028',
        'is_current' => false,
        'start_date' => '2027-08-01',
        'end_date' => '2028-05-31',
    ]);

    $this->actingAs($mainCoordinator)
        ->post(route('coordinator.academic-periods.school-years.current', $nextSchoolYear))
        ->assertRedirect();
    $this->actingAs($mainCoordinator)
        ->post(route('coordinator.academic-periods.semesters.current', $secondSemester))
        ->assertRedirect();

    $this->assertSame($nextSchoolYear->id, AcademicPeriodSelection::currentSchoolYearId(Campus::MAIN));
    $this->assertSame($ibedSchoolYear->id, AcademicPeriodSelection::currentSchoolYearId(Campus::IBED));
    $this->assertSame($secondSemester->id, AcademicPeriodSelection::currentSemesterId(Campus::MAIN));
    $this->assertSame($ibedFirstSemester->id, AcademicPeriodSelection::currentSemesterId(Campus::IBED));

    $mainLaboratory = Laboratory::factory()->create([
        'laboratory_name' => 'Main Science Laboratory',
        'campus' => Campus::MAIN,
    ]);
    $ibedLaboratory = Laboratory::factory()->create([
        'laboratory_name' => 'IBED Science Laboratory',
        'campus' => Campus::IBED,
    ]);
    $equipmentCategoryId = DB::table('equipment_categories')->insertGetId([
        'category_code' => 'EQUIP-CAMPUS-TEST',
        'category_name' => 'Campus test equipment',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $chemicalCategoryId = DB::table('chemical_categories')->insertGetId([
        'category_code' => 'CHEM-CAMPUS-TEST',
        'category_name' => 'Campus test chemicals',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    foreach ([$mainLaboratory, $ibedLaboratory] as $index => $laboratory) {
        $suffix = $index === 0 ? 'MAIN' : 'IBED';
        Equipment::create([
            'equipment_code' => 'EQUIP-'.$suffix,
            'barcode' => 'BARCODE-EQUIP-'.$suffix,
            'equipment_name' => $suffix.' equipment',
            'category_id' => $equipmentCategoryId,
            'laboratory_id' => $laboratory->id,
            'quantity' => 1,
            'available_quantity' => 1,
        ]);
        Chemical::create([
            'chemical_code' => 'CHEM-'.$suffix,
            'barcode' => 'BARCODE-CHEM-'.$suffix,
            'chemical_name' => $suffix.' chemical',
            'type' => 'Organic',
            'category_id' => $chemicalCategoryId,
            'laboratory_id' => $laboratory->id,
            'quantity' => 1,
            'unit' => 'ml',
        ]);
    }

    Equipment::query()->where('equipment_code', 'EQUIP-MAIN')->firstOrFail()->update(['quantity' => 2]);
    Chemical::query()->where('chemical_code', 'CHEM-IBED')->firstOrFail()->update(['quantity' => 2]);

    $this->assertDatabaseHas('equipment_inventory_periods', [
        'equipment_id' => Equipment::query()->where('equipment_code', 'EQUIP-MAIN')->value('id'),
        'school_year_id' => $nextSchoolYear->id,
        'semester_id' => $secondSemester->id,
        'beginning_quantity' => 1,
        'ending_quantity' => 2,
    ]);
    $this->assertDatabaseHas('chemical_inventory_periods', [
        'chemical_id' => Chemical::query()->where('chemical_code', 'CHEM-IBED')->value('id'),
        'school_year_id' => $ibedSchoolYear->id,
        'semester_id' => $ibedFirstSemester->id,
        'beginning_quantity' => 1,
        'ending_quantity' => 2,
    ]);

    $mainEquipmentReport = $this->actingAs($mainCoordinator)
        ->get(route('coordinator.reports.equipment.index'))
        ->assertOk();
    $this->assertSame(Campus::MAIN, $mainEquipmentReport->viewData('campus'));
    $this->assertSame(1, $mainEquipmentReport->viewData('equipmentCount'));

    $ibedChemicalReport = $this->actingAs($ibedInCharge)
        ->get(route('facilitator.reports.chemicals.index'))
        ->assertOk();
    $this->assertSame(Campus::IBED, $ibedChemicalReport->viewData('campus'));
    $this->assertSame(1, $ibedChemicalReport->viewData('chemicalCount'));

    $mainEquipmentSheets = (new EquipmentInventoryReportExport(
        collect([$schoolYear]),
        collect([$firstSemester, $secondSemester]),
        campus: Campus::MAIN,
    ))->sheets();
    $ibedChemicalSheets = (new ChemicalInventoryReportExport(
        collect([$ibedSchoolYear]),
        collect([$ibedFirstSemester, $ibedSecondSemester]),
        campus: Campus::IBED,
    ))->sheets();

    $this->assertSame(['Main Science Laboratory'], array_map(fn ($sheet) => $sheet->title(), $mainEquipmentSheets));
    $this->assertSame(['IBED Science Laboratory'], array_map(fn ($sheet) => $sheet->title(), $ibedChemicalSheets));
});

it('keeps school years and semesters isolated when one campus creates updates or deletes them', function () {
    $coordinatorRole = Role::create(['role_name' => 'Coordinator']);
    $inChargeRole = Role::create(['role_name' => 'Laboratory In-charge']);
    $mainCoordinator = User::create([
        'userID' => 'MAIN-TERMS-COORD',
        'first_name' => 'Main',
        'last_name' => 'Coordinator',
        'email' => 'main.terms@example.test',
        'password' => bcrypt('password123'),
        'role_id' => $coordinatorRole->id,
        'campus' => Campus::MAIN,
        'status' => 'Active',
    ]);
    $ibedInCharge = User::create([
        'userID' => 'IBED-TERMS-INCHARGE',
        'first_name' => 'IBED',
        'last_name' => 'In-charge',
        'email' => 'ibed.terms@example.test',
        'password' => bcrypt('password123'),
        'role_id' => $inChargeRole->id,
        'campus' => Campus::IBED,
        'status' => 'Active',
    ]);

    $mainSchoolYear = SchoolYear::create([
        'campus' => Campus::MAIN,
        'school_year' => '2028-2029',
        'start_date' => '2028-08-01',
        'end_date' => '2029-05-31',
    ]);
    $ibedSchoolYear = SchoolYear::create([
        'campus' => Campus::IBED,
        'school_year' => '2028-2029',
        'start_date' => '2028-08-01',
        'end_date' => '2029-05-31',
    ]);
    $mainSemester = Semester::create([
        'campus' => Campus::MAIN,
        'semester_name' => 'Campus Isolation Term',
        'display_order' => 1,
    ]);
    $ibedSemester = Semester::create([
        'campus' => Campus::IBED,
        'semester_name' => 'Campus Isolation Term',
        'display_order' => 1,
    ]);

    $this->actingAs($ibedInCharge)
        ->put(route('facilitator.academic-periods.school-years.update', $ibedSchoolYear), [
            'school_year' => '2028-2029 IBED',
            'start_date' => '2028-08-15',
            'end_date' => '2029-06-15',
        ])
        ->assertRedirect();
    $this->assertDatabaseHas('school_years', [
        'id' => $mainSchoolYear->id,
        'school_year' => '2028-2029',
        'start_date' => '2028-08-01',
        'end_date' => '2029-05-31',
    ]);

    $this->actingAs($ibedInCharge)
        ->put(route('facilitator.academic-periods.semesters.update', $ibedSemester), [
            'semester_name' => 'IBED Isolation Term',
            'display_order' => 4,
        ])
        ->assertRedirect();
    $this->assertDatabaseHas('semesters', [
        'id' => $mainSemester->id,
        'semester_name' => 'Campus Isolation Term',
        'display_order' => 1,
    ]);

    $this->actingAs($ibedInCharge)
        ->delete(route('facilitator.academic-periods.school-years.destroy', $ibedSchoolYear))
        ->assertRedirect();
    $this->actingAs($ibedInCharge)
        ->delete(route('facilitator.academic-periods.semesters.destroy', $ibedSemester))
        ->assertRedirect();
    $this->assertDatabaseHas('school_years', ['id' => $mainSchoolYear->id, 'deleted_at' => null]);
    $this->assertDatabaseHas('semesters', ['id' => $mainSemester->id, 'deleted_at' => null]);

    $this->actingAs($mainCoordinator)
        ->post(route('coordinator.academic-periods.school-years.store'), [
            'school_year' => '2030-2031 MAIN',
            'start_date' => '2030-08-01',
            'end_date' => '2031-05-31',
        ])
        ->assertRedirect();
    $this->actingAs($mainCoordinator)
        ->post(route('coordinator.academic-periods.semesters.store'), [
            'semester_name' => 'Main-Only Term',
            'display_order' => 5,
        ])
        ->assertRedirect();

    $ibedPage = $this->actingAs($ibedInCharge)
        ->get(route('facilitator.academic-periods.index'))
        ->assertOk();
    $this->assertFalse($ibedPage->viewData('schoolYears')->contains('school_year', '2030-2031 MAIN'));
    $this->assertFalse($ibedPage->viewData('semesters')->contains('semester_name', 'Main-Only Term'));
});
