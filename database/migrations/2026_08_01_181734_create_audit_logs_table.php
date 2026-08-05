<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('user_no')->nullable();

            // Module
            $table->string('module');
            /*
                Equipment
                Chemicals
                Reservations
                Borrowing
                Users
                Laboratory
                etc.
            */

            $table->enum('action',[
                'Create',
                'Update',
                'Delete',
                'Restore',
                'Login',
                'Logout',
                'Approve',
                'Reject',
                'Borrow',
                'Return'
            ]);

            // Record affected
            $table->unsignedBigInteger('record_id')->nullable();

            // Before update
            $table->json('old_values')->nullable();

            // After update
            $table->json('new_values')->nullable();

            $table->ipAddress('ip_address')->nullable();

            $table->text('user_agent')->nullable();

            $table->timestamp('performed_at');

            $table->timestamps();

            $table->foreign('user_no')
                ->references('userNo')
                ->on('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->index('module');
            $table->index('action');
            $table->index('performed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};