<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table): void {
            $table->string('lifespan_mode', 20)->nullable()->after('manufacturing_date');
            $table->unsignedSmallInteger('lifespan_months')->nullable()->after('lifespan_mode');
            $table->unsignedSmallInteger('lifespan_years')->nullable()->after('lifespan_months');
            $table->dateTime('lifespan_expires_at')->nullable()->after('lifespan_years')->index();
            $table->timestamp('lifespan_alert_notified_at')->nullable()->after('lifespan_expires_at');
            $table->timestamp('lifespan_supplier_alert_sent_at')->nullable()->after('lifespan_alert_notified_at');
        });
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table): void {
            $table->dropIndex(['lifespan_expires_at']);
            $table->dropColumn([
                'lifespan_mode',
                'lifespan_months',
                'lifespan_years',
                'lifespan_expires_at',
                'lifespan_alert_notified_at',
                'lifespan_supplier_alert_sent_at',
            ]);
        });
    }
};
