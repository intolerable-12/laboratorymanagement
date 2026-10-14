<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table): void {
            $table->string('campus', 20)->nullable()->after('laboratory_id');
            $table->index('campus');
        });

        Schema::table('borrow_transactions', function (Blueprint $table): void {
            $table->string('campus', 20)->nullable()->after('laboratory_id');
            $table->index('campus');
        });
    }

    public function down(): void
    {
        Schema::table('borrow_transactions', function (Blueprint $table): void {
            $table->dropIndex(['campus']);
            $table->dropColumn('campus');
        });

        Schema::table('reservations', function (Blueprint $table): void {
            $table->dropIndex(['campus']);
            $table->dropColumn('campus');
        });
    }
};
