<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feedback', function (Blueprint $table) {
            $table->enum('feedback_type', ['Lab Service', 'System'])
                ->default('System')
                ->after('user_no');

            $table->foreignId('laboratory_id')
                ->nullable()
                ->after('feedback_type')
                ->constrained('laboratories')
                ->nullOnDelete();
        });

        DB::statement('ALTER TABLE feedback MODIFY reservation_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        Schema::table('feedback', function (Blueprint $table) {
            $table->dropConstrainedForeignId('laboratory_id');
            $table->dropColumn('feedback_type');
        });

        DB::statement('ALTER TABLE feedback MODIFY reservation_id BIGINT UNSIGNED NOT NULL');
    }
};