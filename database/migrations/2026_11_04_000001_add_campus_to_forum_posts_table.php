<?php

use App\Support\Campus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forum_posts', function (Blueprint $table): void {
            $table->string('campus', 20)->default(Campus::MAIN)->after('user_no');
            $table->index('campus');
        });

        DB::table('forum_posts')
            ->leftJoin('users', 'users.userNo', '=', 'forum_posts.user_no')
            ->leftJoin('roles', 'roles.id', '=', 'users.role_id')
            ->leftJoin('departments', 'departments.id', '=', 'users.department_id')
            ->select([
                'forum_posts.id',
                'roles.role_name',
                'users.campus as user_campus',
                'departments.campus as department_campus',
            ])
            ->orderBy('forum_posts.id')
            ->chunkById(500, function ($posts): void {
                $postsByCampus = [];

                foreach ($posts as $post) {
                    $campus = in_array($post->role_name, ['Student', 'Instructor'], true)
                        ? ($post->department_campus ?? $post->user_campus)
                        : ($post->user_campus ?? $post->department_campus);
                    $campus = Campus::normalize($campus);
                    $campus = in_array($campus, Campus::values(), true) ? $campus : Campus::MAIN;
                    $postsByCampus[$campus][] = $post->id;
                }

                foreach ($postsByCampus as $campus => $postIds) {
                    DB::table('forum_posts')
                        ->whereIn('id', $postIds)
                        ->update(['campus' => $campus]);
                }
            }, 'forum_posts.id', 'id');
    }

    public function down(): void
    {
        Schema::table('forum_posts', function (Blueprint $table): void {
            $table->dropIndex(['campus']);
            $table->dropColumn('campus');
        });
    }
};
