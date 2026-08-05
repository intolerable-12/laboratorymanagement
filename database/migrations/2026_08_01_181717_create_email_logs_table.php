<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {

            $table->id();

            // Recipient (nullable in case the account is deleted)
            $table->unsignedBigInteger('user_no')->nullable();

            $table->string('recipient_email');

            $table->string('subject');

            $table->longText('body')->nullable();

            $table->enum('type',[
                'Reservation',
                'Borrow',
                'Return',
                'Announcement',
                'Low Stock',
                'Chemical Expiration',
                'Maintenance',
                'System'
            ]);

            $table->enum('status',[
                'Pending',
                'Sent',
                'Failed'
            ])->default('Pending');

            $table->unsignedTinyInteger('retry_count')->default(0);

            $table->text('error_message')->nullable();

            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            $table->foreign('user_no')
                ->references('userNo')
                ->on('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->index('recipient_email');
            $table->index('status');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};