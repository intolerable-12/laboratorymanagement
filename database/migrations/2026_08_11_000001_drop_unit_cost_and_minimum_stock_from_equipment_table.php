<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            if (Schema::hasColumn('equipment', 'unit_cost')) {
                $table->dropColumn('unit_cost');
            }

            if (Schema::hasColumn('equipment', 'minimum_stock')) {
                $table->dropColumn('minimum_stock');
            }
        });
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            if (! Schema::hasColumn('equipment', 'unit_cost')) {
                $table->decimal('unit_cost', 12, 2)->nullable()->after('purchase_date');
            }

            if (! Schema::hasColumn('equipment', 'minimum_stock')) {
                $table->unsignedInteger('minimum_stock')->default(1)->after('available_quantity');
            }
        });
    }
};
