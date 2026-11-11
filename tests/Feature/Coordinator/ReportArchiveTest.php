<?php

use App\Models\Chemical;
use App\Models\Equipment;
use App\Models\Laboratory;
use App\Models\ReportArchive;
use App\Models\Role;
use App\Models\SchoolYear;
use App\Models\User;
use App\Services\ReportGenerationService;
use App\Support\Campus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

uses(RefreshDatabase::class);

it('archives generated reports and restricts report downloads to their campus', function () {
    Storage::fake('public');
    Excel::fake();

    $coordinatorRole = Role::create(['role_name' => 'Coordinator']);
    $mainCoordinator = User::create([
        'userID' => 'MAIN-REPORT-COORD',
        'first_name' => 'Main',
        'last_name' => 'Coordinator',
        'email' => 'main.report.coordinator@example.test',
        'password' => bcrypt('password123'),
        'role_id' => $coordinatorRole->id,
        'campus' => Campus::MAIN,
        'status' => 'Active',
    ]);
    $ibedCoordinator = User::create([
        'userID' => 'IBED-REPORT-COORD',
        'first_name' => 'IBED',
        'last_name' => 'Coordinator',
        'email' => 'ibed.report.coordinator@example.test',
        'password' => bcrypt('password123'),
        'role_id' => $coordinatorRole->id,
        'campus' => Campus::IBED,
        'status' => 'Active',
    ]);

    $report = app(ReportGenerationService::class)->generate(
        type: 'equipment',
        campus: Campus::MAIN,
        schoolYears: collect(),
        generatedBy: $mainCoordinator->getKey(),
    );

    Excel::assertStored($report->path, 'public');
    $this->assertDatabaseHas('report_archives', [
        'id' => $report->id,
        'campus' => Campus::MAIN,
        'report_type' => 'equipment',
        'source' => 'manual',
        'generated_by' => $mainCoordinator->getKey(),
    ]);

    Storage::disk('public')->put($report->path, 'fake-xlsx-content');

    $this->actingAs($mainCoordinator)
        ->get(route('coordinator.reports.index'))
        ->assertOk()
        ->assertSee('Equipment inventory')
        ->assertSee('Manual')
        ->assertSee('Main Coordinator');

    $this->actingAs($mainCoordinator)
        ->get(route('coordinator.reports.download', $report))
        ->assertDownload($report->file_name);

    $this->actingAs($ibedCoordinator)
        ->get(route('coordinator.reports.download', $report))
        ->assertNotFound();

    $this->actingAs($ibedCoordinator)
        ->get(route('coordinator.reports.index'))
        ->assertOk()
        ->assertDontSee('Equipment inventory')
        ->assertSee('No reports have been generated for this campus yet.');

    $this->actingAs($mainCoordinator)
        ->delete(route('coordinator.reports.destroy', $report))
        ->assertRedirect(route('coordinator.reports.index'))
        ->assertSessionHas('success');
    $this->assertDatabaseMissing('report_archives', ['id' => $report->id]);
    Storage::disk('public')->assertMissing($report->path);

    $bulkReports = collect(['bulk-one.xlsx', 'bulk-two.xlsx'])->map(function (string $fileName): ReportArchive {
        return ReportArchive::query()->create([
            'campus' => Campus::MAIN,
            'report_type' => 'equipment',
            'source' => 'manual',
            'file_name' => $fileName,
            'path' => 'reports/main/'.$fileName,
        ]);
    });
    $bulkReports->each(fn (ReportArchive $bulkReport) => Storage::disk('public')->put($bulkReport->path, 'fake-xlsx-content'));

    $this->actingAs($mainCoordinator)
        ->delete(route('coordinator.reports.destroy-many'), [
            'report_ids' => $bulkReports->pluck('id')->all(),
        ])
        ->assertRedirect(route('coordinator.reports.index'))
        ->assertSessionHas('success');

    foreach ($bulkReports as $bulkReport) {
        $this->assertDatabaseMissing('report_archives', ['id' => $bulkReport->id]);
        Storage::disk('public')->assertMissing($bulkReport->path);
    }
});

it('generates quarterly inventory reports once per campus and quarter', function () {
    Storage::fake('public');
    Excel::fake();

    $schoolYear = SchoolYear::create([
        'campus' => Campus::MAIN,
        'school_year' => '2026-2027',
        'is_current' => true,
        'start_date' => '2025-08-01',
        'end_date' => '2026-05-31',
    ]);
    $laboratory = Laboratory::factory()->create(['campus' => Campus::MAIN]);
    $equipmentCategoryId = DB::table('equipment_categories')->insertGetId([
        'category_code' => 'REPORT-EQUIP',
        'category_name' => 'Report equipment',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $chemicalCategoryId = DB::table('chemical_categories')->insertGetId([
        'category_code' => 'REPORT-CHEM',
        'category_name' => 'Report chemicals',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    Equipment::create([
        'equipment_code' => 'REPORT-EQUIP-001',
        'barcode' => 'REPORT-BARCODE-EQUIP',
        'equipment_name' => 'Quarterly report equipment',
        'category_id' => $equipmentCategoryId,
        'laboratory_id' => $laboratory->id,
        'quantity' => 1,
        'available_quantity' => 1,
    ]);
    Chemical::create([
        'chemical_code' => 'REPORT-CHEM-001',
        'barcode' => 'REPORT-BARCODE-CHEM',
        'chemical_name' => 'Quarterly report chemical',
        'type' => 'Organic',
        'category_id' => $chemicalCategoryId,
        'laboratory_id' => $laboratory->id,
        'quantity' => 1,
        'unit' => 'ml',
    ]);

    $coordinatorRole = Role::create(['role_name' => 'Coordinator']);
    $coordinator = User::create([
        'userID' => 'MAIN-QUARTERLY-COORD',
        'first_name' => 'Quarterly',
        'last_name' => 'Coordinator',
        'email' => 'quarterly.coordinator@example.test',
        'password' => bcrypt('password123'),
        'role_id' => $coordinatorRole->id,
        'campus' => Campus::MAIN,
        'status' => 'Active',
    ]);

    $generator = app(ReportGenerationService::class);

    $this->assertSame(2, $generator->generateQuarterlyReports());
    $this->actingAs($coordinator)
        ->post(route('coordinator.reports.generate-quarterly'))
        ->assertRedirect(route('coordinator.reports.index'))
        ->assertSessionHas('success');
    $this->assertDatabaseCount('report_archives', 4);
    $this->assertSame(0, $generator->generateQuarterlyReports());
    $this->assertDatabaseCount('report_archives', 4);
    $this->assertDatabaseHas('report_archives', [
        'campus' => Campus::MAIN,
        'report_type' => 'equipment',
        'source' => 'automatic',
    ]);
    $this->assertDatabaseHas('report_archives', [
        'campus' => Campus::MAIN,
        'report_type' => 'chemicals',
        'source' => 'automatic',
    ]);
    Excel::assertStored(
        DB::table('report_archives')->where('report_type', 'equipment')->value('path'),
        'public',
        fn ($export) => count($export->sheets()) === 1,
    );
    Excel::assertStored(
        DB::table('report_archives')->where('report_type', 'chemicals')->value('path'),
        'public',
        fn ($export) => count($export->sheets()) === 1,
    );
});
