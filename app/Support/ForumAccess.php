<?php

namespace App\Support;

use App\Models\ForumPost;
use App\Models\User;

final class ForumAccess
{
    public static function campusFor(User $user): ?string
    {
        $campus = Campus::normalize($user->campusName());

        if ($campus !== null) {
            return $campus;
        }

        return $user->role?->role_name === 'Coordinator' ? Campus::MAIN : null;
    }

    public static function canParticipate(User $user): bool
    {
        $campus = self::campusFor($user);
        $roleName = $user->role?->role_name;

        return match ($campus) {
            Campus::MAIN => in_array($roleName, ['Coordinator', 'Instructor', 'Laboratory In-charge', 'Student'], true),
            Campus::IBED => in_array($roleName, ['Instructor', 'Laboratory In-charge'], true),
            default => false,
        };
    }

    public static function canAccessPost(User $user, ForumPost $forumPost): bool
    {
        return self::canParticipate($user)
            && $forumPost->campus === self::campusFor($user);
    }

    public static function ensureCanAccessPost(User $user, ForumPost $forumPost): void
    {
        abort_unless(self::canParticipate($user), 403);
        abort_unless(self::canAccessPost($user, $forumPost), 404);
    }
}
