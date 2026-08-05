<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {

            $table->id();

            // Recipient
            $table->unsignedBigInteger('user_no');

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

            $table->string('title');

            $table->text('message');

            // Optional related record
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->string('reference_type')->nullable();
            /*
                reservation
                borrow_transaction
                equipment
                chemical
                announcement
            */

            $table->boolean('is_read')->default(false);

            $table->timestamp('read_at')->nullable();

            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            $table->foreign('user_no')
                ->references('userNo')
                ->on('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->index('user_no');
            $table->index('is_read');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};