<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->unsignedInteger('low_stock_threshold')->nullable()->after('available_quantity');
            $table->timestamp('supplier_alert_sent_at')->nullable()->after('low_stock_threshold');
        });

        Schema::table('chemicals', function (Blueprint $table) {
            $table->unsignedSmallInteger('expiration_alert_days')->nullable()->after('expiration_date');
            $table->timestamp('supplier_alert_sent_at')->nullable()->after('expiration_alert_days');
        });
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn(['low_stock_threshold', 'supplier_alert_sent_at']);
        });

        Schema::table('chemicals', function (Blueprint $table) {
            $table->dropColumn(['expiration_alert_days', 'supplier_alert_sent_at']);
        });
    }
};
