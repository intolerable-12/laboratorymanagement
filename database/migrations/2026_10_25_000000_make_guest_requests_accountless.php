<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table): void {
            $table->dropForeign(['user_no']);
        });

        Schema::table('borrow_transactions', function (Blueprint $table): void {
            $table->dropForeign(['borrower_id']);
        });

        Schema::table('reservations', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_no')->nullable()->change();
            $table->string('guest_key', 64)->nullable()->after('user_no');
            $table->string('guest_first_name', 100)->nullable()->after('guest_key');
            $table->string('guest_middle_name', 100)->nullable()->after('guest_first_name');
            $table->string('guest_last_name', 100)->nullable()->after('guest_middle_name');
            $table->string('guest_suffix', 20)->nullable()->after('guest_last_name');
            $table->string('guest_student_id', 30)->nullable()->after('guest_suffix');
            $table->string('guest_email')->nullable()->after('guest_student_id');
            $table->string('guest_contact_number', 20)->nullable()->after('guest_email');
            $table->unsignedBigInteger('guest_department_id')->nullable()->after('guest_contact_number');
            $table->index('guest_key');
            $table->index('guest_email');
            $table->foreign('user_no')
                ->references('userNo')
                ->on('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreign('guest_department_id')
                ->references('id')
                ->on('departments')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        Schema::table('borrow_transactions', function (Blueprint $table): void {
            $table->unsignedBigInteger('borrower_id')->nullable()->change();
            $table->string('guest_key', 64)->nullable()->after('borrower_id');
            $table->string('guest_first_name', 100)->nullable()->after('guest_key');
            $table->string('guest_middle_name', 100)->nullable()->after('guest_first_name');
            $table->string('guest_last_name', 100)->nullable()->after('guest_middle_name');
            $table->string('guest_suffix', 20)->nullable()->after('guest_last_name');
            $table->string('guest_student_id', 30)->nullable()->after('guest_suffix');
            $table->string('guest_email')->nullable()->after('guest_student_id');
            $table->string('guest_contact_number', 20)->nullable()->after('guest_email');
            $table->unsignedBigInteger('guest_department_id')->nullable()->after('guest_contact_number');
            $table->index('guest_key');
            $table->index('guest_email');
            $table->foreign('borrower_id')
                ->references('userNo')
                ->on('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreign('guest_department_id')
                ->references('id')
                ->on('departments')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        $guestUsers = DB::table('users')
            ->where('is_guest', true)
            ->get([
                'userNo',
                'first_name',
                'middle_name',
                'last_name',
                'suffix',
                'userID',
                'email',
                'contact_number',
                'department_id',
            ]);

        foreach ($guestUsers as $guestUser) {
            $email = strtolower(trim((string) $guestUser->email));
            $studentId = strtoupper(trim((string) $guestUser->userID));
            $snapshot = [
                'guest_key' => hash('sha256', $email . '|' . $studentId),
                'guest_first_name' => $guestUser->first_name,
                'guest_middle_name' => $guestUser->middle_name,
                'guest_last_name' => $guestUser->last_name,
                'guest_suffix' => $guestUser->suffix,
                'guest_student_id' => $studentId,
                'guest_email' => $email,
                'guest_contact_number' => $guestUser->contact_number,
                'guest_department_id' => $guestUser->department_id,
            ];

            DB::table('reservations')
                ->where('user_no', $guestUser->userNo)
                ->update($snapshot + ['user_no' => null]);

            DB::table('borrow_transactions')
                ->where('borrower_id', $guestUser->userNo)
                ->update($snapshot + ['borrower_id' => null]);
        }

        // Keep historical request snapshots, but prevent legacy pseudo-users
        // from remaining active or appearing in normal account management.
        DB::table('users')
            ->where('is_guest', true)
            ->update([
                'status' => 'Archived',
                'deleted_at' => now(),
            ]);
    }

    public function down(): void
    {
        Schema::table('borrow_transactions', function (Blueprint $table): void {
            $table->dropForeign(['guest_department_id']);
            $table->dropForeign(['borrower_id']);
            $table->dropIndex(['guest_key']);
            $table->dropIndex(['guest_email']);
            $table->dropColumn([
                'guest_key',
                'guest_first_name',
                'guest_middle_name',
                'guest_last_name',
                'guest_suffix',
                'guest_student_id',
                'guest_email',
                'guest_contact_number',
                'guest_department_id',
            ]);
            $table->unsignedBigInteger('borrower_id')->nullable(false)->change();
            $table->foreign('borrower_id')
                ->references('userNo')
                ->on('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::table('reservations', function (Blueprint $table): void {
            $table->dropForeign(['guest_department_id']);
            $table->dropForeign(['user_no']);
            $table->dropIndex(['guest_key']);
            $table->dropIndex(['guest_email']);
            $table->dropColumn([
                'guest_key',
                'guest_first_name',
                'guest_middle_name',
                'guest_last_name',
                'guest_suffix',
                'guest_student_id',
                'guest_email',
                'guest_contact_number',
                'guest_department_id',
            ]);
            $table->unsignedBigInteger('user_no')->nullable(false)->change();
            $table->foreign('user_no')
                ->references('userNo')
                ->on('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }
};
