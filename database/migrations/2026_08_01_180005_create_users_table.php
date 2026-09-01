<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {

            // Auto Increment Primary Key
            $table->id('userNo');

            // Lourdes College Student No. / Employee ID
            $table->string('userID', 30)->unique();

            // Personal Information
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->string('suffix', 20)->nullable();

            $table->date('birth_date')->nullable();
            $table->enum('gender', ['Male', 'Female'])->nullable();

            // Contact Information
            $table->string('email')->unique();
            $table->string('contact_number', 20)->nullable();

            // Authentication
            $table->string('password');
            $table->rememberToken();

            // Profile
            $table->string('profile_photo')->nullable();

            // Relationships
            $table->foreignId('role_id')
                ->constrained('roles')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            // Account Status
            $table->enum('status', [
                'Active',
                'Archived'
            ])->default('Active');

            $table->timestamp('email_verified_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('last_name');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
