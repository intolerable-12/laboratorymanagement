<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->timestamp('low_stock_alert_sent_at')->nullable()->after('supplier_alert_sent_at');
        });

        Schema::table('chemicals', function (Blueprint $table) {
            $table->timestamp('low_stock_supplier_alert_sent_at')->nullable()->after('supplier_alert_sent_at');
            $table->timestamp('low_stock_alert_sent_at')->nullable()->after('low_stock_supplier_alert_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn('low_stock_alert_sent_at');
        });

        Schema::table('chemicals', function (Blueprint $table) {
            $table->dropColumn(['low_stock_supplier_alert_sent_at', 'low_stock_alert_sent_at']);
        });
    }
};
