<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrow_transactions', function (Blueprint $table) {
            $table->dateTime('checkout_notified_at')->nullable()->after('checked_out_at');
            $table->dateTime('checkin_notified_at')->nullable()->after('due_at');
        });
    }

    public function down(): void
    {
        Schema::table('borrow_transactions', function (Blueprint $table) {
            $table->dropColumn(['checkout_notified_at', 'checkin_notified_at']);
        });
    }
};
