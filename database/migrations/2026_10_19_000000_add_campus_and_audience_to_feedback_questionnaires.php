<?php

use App\Support\Campus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feedback_questionnaires', function (Blueprint $table): void {
            $table->string('campus', 20)->default(Campus::MAIN)->after('description');
            $table->string('audience', 20)->default('Both')->after('campus');
            $table->index(['campus', 'audience'], 'fq_campus_audience_idx');
        });
    }

    public function down(): void
    {
        Schema::table('feedback_questionnaires', function (Blueprint $table): void {
            $table->dropIndex('fq_campus_audience_idx');
            $table->dropColumn(['campus', 'audience']);
        });
    }
};
