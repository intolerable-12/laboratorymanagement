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
        Schema::create('campus_academic_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('campus', 20)->unique();
            $table->foreignId('school_year_id')->nullable()->constrained('school_years')->nullOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->nullOnDelete();
            $table->timestamps();
        });

        $currentSchoolYearId = DB::table('school_years')->where('is_current', true)->value('id');
        $currentSemesterId = DB::table('semesters')->where('is_current', true)->value('id');

        foreach (Campus::values() as $campus) {
            DB::table('campus_academic_settings')->insert([
                'campus' => $campus,
                'school_year_id' => $currentSchoolYearId,
                'semester_id' => $currentSemesterId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_academic_settings');
    }
};
