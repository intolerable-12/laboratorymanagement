<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('barcode_logs', 'is_voided')) {
            Schema::table('barcode_logs', function (Blueprint $table) {
                $table->boolean('is_voided')->default(false)->after('quantity');
            });
        }

        if (! Schema::hasColumn('barcode_logs', 'voided_by')) {
            Schema::table('barcode_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('voided_by')->nullable()->after('is_voided');
            });
        }

        if (! Schema::hasColumn('barcode_logs', 'voided_at')) {
            Schema::table('barcode_logs', function (Blueprint $table) {
                $table->dateTime('voided_at')->nullable()->after('voided_by');
            });
        }

        Schema::table('barcode_logs', function (Blueprint $table) {
            $table->foreign('voided_by', 'barcode_logs_voided_by_foreign')
                ->references('userNo')
                ->on('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('barcode_logs', function (Blueprint $table) {
            $table->dropForeign('barcode_logs_voided_by_foreign');
            $table->dropColumn(['is_voided', 'voided_at']);
            $table->dropColumn('voided_by');
        });
    }
};
