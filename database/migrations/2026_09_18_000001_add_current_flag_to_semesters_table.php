<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->boolean('is_current')->default(false);
            $table->index('is_current');
        });

        $firstSemesterId = DB::table('semesters')->orderBy('display_order')->value('id');

        if ($firstSemesterId) {
            DB::table('semesters')->where('id', $firstSemesterId)->update(['is_current' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->dropIndex(['is_current']);
            $table->dropColumn('is_current');
        });
    }
};
