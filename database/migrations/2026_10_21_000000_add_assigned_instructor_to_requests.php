<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table): void {
            $table->unsignedBigInteger('instructor_id')->nullable()->after('user_no');
            $table->foreign('instructor_id')
                ->references('userNo')
                ->on('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->index('instructor_id');
        });

        Schema::table('borrow_transactions', function (Blueprint $table): void {
            $table->unsignedBigInteger('instructor_id')->nullable()->after('borrower_id');
            $table->foreign('instructor_id')
                ->references('userNo')
                ->on('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->index('instructor_id');
        });
    }

    public function down(): void
    {
        Schema::table('borrow_transactions', function (Blueprint $table): void {
            $table->dropForeign(['instructor_id']);
            $table->dropIndex(['instructor_id']);
            $table->dropColumn('instructor_id');
        });

        Schema::table('reservations', function (Blueprint $table): void {
            $table->dropForeign(['instructor_id']);
            $table->dropIndex(['instructor_id']);
            $table->dropColumn('instructor_id');
        });
    }
};
