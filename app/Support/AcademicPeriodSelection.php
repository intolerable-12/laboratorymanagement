<?php

namespace App\Support;

use App\Models\CampusAcademicSetting;
use App\Models\SchoolYear;
use App\Models\Semester;

final class AcademicPeriodSelection
{
    public static function currentSchoolYearId(string $campus): ?int
    {
        $setting = self::setting($campus);

        if ($setting->school_year_id === null) {
            $legacyCurrentId = SchoolYear::query()
                ->where('campus', Campus::normalize($campus) ?? Campus::MAIN)
                ->where('is_current', true)
                ->value('id');

            if ($legacyCurrentId !== null) {
                $setting->school_year_id = $legacyCurrentId;
                $setting->save();
            }
        }

        return $setting->school_year_id === null ? null : (int) $setting->school_year_id;
    }

    public static function currentSemesterId(string $campus): ?int
    {
        $setting = self::setting($campus);

        if ($setting->semester_id === null) {
            $legacyCurrentId = Semester::query()
                ->where('campus', Campus::normalize($campus) ?? Campus::MAIN)
                ->where('is_current', true)
                ->value('id');

            if ($legacyCurrentId !== null) {
                $setting->semester_id = $legacyCurrentId;
                $setting->save();
            }
        }

        return $setting->semester_id === null ? null : (int) $setting->semester_id;
    }

    public static function currentSchoolYear(string $campus): ?SchoolYear
    {
        $schoolYearId = self::currentSchoolYearId($campus);

        return $schoolYearId === null ? null : SchoolYear::query()->find($schoolYearId);
    }

    public static function currentSemester(string $campus): ?Semester
    {
        $semesterId = self::currentSemesterId($campus);

        return $semesterId === null ? null : Semester::query()->find($semesterId);
    }

    public static function setCurrentSchoolYear(string $campus, SchoolYear $schoolYear): void
    {
        $setting = self::setting($campus);
        $setting->school_year_id = $schoolYear->id;
        $setting->save();
    }

    public static function setCurrentSemester(string $campus, Semester $semester): void
    {
        $setting = self::setting($campus);
        $setting->semester_id = $semester->id;
        $setting->save();
    }

    public static function isSchoolYearCurrentAnywhere(SchoolYear $schoolYear): bool
    {
        foreach (Campus::values() as $campus) {
            self::currentSchoolYearId($campus);
        }

        return CampusAcademicSetting::query()->where('school_year_id', $schoolYear->id)->exists();
    }

    public static function isSemesterCurrentAnywhere(Semester $semester): bool
    {
        foreach (Campus::values() as $campus) {
            self::currentSemesterId($campus);
        }

        return CampusAcademicSetting::query()->where('semester_id', $semester->id)->exists();
    }

    private static function setting(string $campus): CampusAcademicSetting
    {
        $campus = Campus::normalize($campus) ?? Campus::MAIN;

        return CampusAcademicSetting::query()->firstOrCreate(['campus' => $campus]);
    }
}
