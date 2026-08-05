<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_maintenance', function (Blueprint $table) {

            $table->id();

            $table->foreignId('equipment_id')
                ->constrained('equipment')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Who reported it
            $table->unsignedBigInteger('reported_by');

            // Assigned technician/facilitator
            $table->unsignedBigInteger('assigned_to')->nullable();

            $table->string('issue_title');

            $table->text('problem_description');

            $table->date('reported_date');

            $table->date('maintenance_date')->nullable();

            $table->date('completed_date')->nullable();

            $table->decimal('repair_cost',12,2)->nullable();

            $table->enum('priority',[
                'Low',
                'Medium',
                'High',
                'Critical'
            ])->default('Medium');

            $table->enum('status',[
                'Pending',
                'In Progress',
                'Completed',
                'Cancelled'
            ])->default('Pending');

            $table->text('maintenance_notes')->nullable();

            $table->timestamps();

            $table->softDeletes();

            // Foreign Keys
            $table->foreign('reported_by')
                ->references('userNo')
                ->on('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('assigned_to')
                ->references('userNo')
                ->on('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            // Indexes
            $table->index('reported_date');
            $table->index('status');
            $table->index('priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_maintenance');
    }
};