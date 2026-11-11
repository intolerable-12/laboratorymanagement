<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_archives', function (Blueprint $table): void {
            $table->id();
            $table->string('campus', 20);
            $table->string('report_type', 20);
            $table->string('source', 20);
            $table->string('period_key', 20)->nullable();
            $table->string('period_label')->nullable();
            $table->string('file_name');
            $table->string('path');
            $table->foreignId('generated_by')->nullable()->constrained('users', 'userNo')->nullOnDelete();
            $table->timestamps();

            $table->unique(['campus', 'report_type', 'period_key']);
            $table->index(['campus', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_archives');
    }
};
