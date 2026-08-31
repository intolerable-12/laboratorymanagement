<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barcode_logs', function (Blueprint $table) {
            $table->foreignId('borrow_transaction_id')
                ->nullable()
                ->after('user_no')
                ->constrained('borrow_transactions')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->decimal('quantity', 12, 2)->default(1)->after('barcode');
        });
    }

    public function down(): void
    {
        Schema::table('barcode_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('borrow_transaction_id');
            $table->dropColumn('quantity');
        });
    }
};
