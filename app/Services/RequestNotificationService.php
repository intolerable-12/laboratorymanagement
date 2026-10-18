<?php

namespace App\Services;

use App\Models\BorrowTransaction;
use App\Models\Announcement;
use App\Models\Chemical;
use App\Models\Equipment;
use App\Models\Feedback;
use App\Models\ForumComment;
use App\Models\ForumPost;
use App\Models\FeedbackQuestionnaire;
use App\Models\FeedbackQuestionnaireResponse;
use App\Models\Notification as UserNotification;
use App\Mail\RequestReviewMail;
use App\Models\Reservation;
use App\Models\User;
use App\Support\Campus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class RequestNotificationService
{
    public function displayName(User $user): string
    {
        return trim(collect([
            $user->first_name,
            $user->middle_name,
            $user->last_name,
            $user->suffix,
        ])->filter()->implode(' '));
    }

    public function notifyUser(User $user, string $type, string $title, string $message, ?Model $reference = null): UserNotification
    {
        return UserNotification::create([
            'user_no' => $user->userNo,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'reference_id' => $reference?->getKey(),
            'reference_type' => $reference?->getMorphClass(),
            'is_read' => false,
            'read_at' => null,
            'sent_at' => now(),
        ]);
    }

    public function notifyRoleUsers(
        string $roleName,
        string $type,
        string $title,
        string $message,
        ?Model $reference = null,
        ?int $exceptUserNo = null
    ): void {
        $this->roleUsers($roleName)
            ->when($exceptUserNo !== null, fn ($query) => $query->where('userNo', '!=', $exceptUserNo))
            ->get()
            ->each(fn (User $user) => $this->notifyUser($user, $type, $title, $message, $reference));
    }

    public function notifyDepartmentRoleUsers(
        string $roleName,
        int $departmentId,
        string $type,
        string $title,
        string $message,
        ?Model $reference = null,
        ?int $exceptUserNo = null
    ): void {
        $this->roleUsers($roleName)
            ->where('department_id', $departmentId)
            ->when($exceptUserNo !== null, fn ($query) => $query->where('userNo', '!=', $exceptUserNo))
            ->get()
            ->each(fn (User $user) => $this->notifyUser($user, $type, $title, $message, $reference));
    }

    public function notifyCampusRoleUsers(
        string $roleName,
        ?string $campus,
        string $type,
        string $title,
        string $message,
        ?Model $reference = null,
        ?int $exceptUserNo = null
    ): void {
        $campus = Campus::normalize($campus);

        if ($campus === null) {
            return;
        }

        $this->roleUsers($roleName)
            ->where(function ($query) use ($campus): void {
                $query->where('users.campus', $campus)
                    ->orWhereHas('department', fn ($departmentQuery) => $departmentQuery->where('campus', $campus));
            })
            ->when($exceptUserNo !== null, fn ($query) => $query->where('userNo', '!=', $exceptUserNo))
            ->get()
            ->each(fn (User $user) => $this->notifyUser($user, $type, $title, $message, $reference));
    }

    public function emailRoleUsers(
        string $roleName,
        string $requestType,
        string $requestNumber,
        string $headline,
        string $bodyMessage,
        string $actionUrl,
        string $actionLabel,
        array $summaryRows = [],
        ?int $exceptUserNo = null
    ): void {
        $this->roleUsers($roleName)
            ->when($exceptUserNo !== null, fn ($query) => $query->where('userNo', '!=', $exceptUserNo))
            ->get()
            ->each(function (User $user) use ($requestType, $requestNumber, $headline, $bodyMessage, $actionUrl, $actionLabel, $summaryRows) {
                if (! $user->email) {
                    return;
                }

                Mail::to($user->email)->queue(new RequestReviewMail(
                    recipientName: $this->displayName($user),
                    requestType: $requestType,
                    requestNumber: $requestNumber,
                    headline: $headline,
                    bodyMessage: $bodyMessage,
                    actionUrl: $actionUrl,
                    actionLabel: $actionLabel,
                    summaryRows: $summaryRows,
                ));
            });
    }

    public function emailDepartmentRoleUsers(
        string $roleName,
        int $departmentId,
        string $requestType,
        string $requestNumber,
        string $headline,
        string $bodyMessage,
        string $actionUrl,
        string $actionLabel,
        array $summaryRows = [],
        ?int $exceptUserNo = null
    ): void {
        $this->emailUsers(
            $this->roleUsers($roleName)->where('department_id', $departmentId)
                ->when($exceptUserNo !== null, fn ($query) => $query->where('userNo', '!=', $exceptUserNo)),
            $requestType,
            $requestNumber,
            $headline,
            $bodyMessage,
            $actionUrl,
            $actionLabel,
            $summaryRows,
        );
    }

    public function emailCampusRoleUsers(
        string $roleName,
        ?string $campus,
        string $requestType,
        string $requestNumber,
        string $headline,
        string $bodyMessage,
        string $actionUrl,
        string $actionLabel,
        array $summaryRows = [],
        ?int $exceptUserNo = null
    ): void {
        $campus = Campus::normalize($campus);

        if ($campus === null) {
            return;
        }

        $this->emailUsers(
            $this->roleUsers($roleName)
                ->where(function ($query) use ($campus): void {
                    $query->where('users.campus', $campus)
                        ->orWhereHas('department', fn ($departmentQuery) => $departmentQuery->where('campus', $campus));
                })
                ->when($exceptUserNo !== null, fn ($query) => $query->where('userNo', '!=', $exceptUserNo)),
            $requestType,
            $requestNumber,
            $headline,
            $bodyMessage,
            $actionUrl,
            $actionLabel,
            $summaryRows,
        );
    }

    private function roleUsers(string $roleName)
    {
        return User::query()
            ->where('status', 'Active')
            ->whereHas('role', fn ($query) => $query->where('role_name', $roleName));
    }

    private function emailUsers($users, string $requestType, string $requestNumber, string $headline, string $bodyMessage, string $actionUrl, string $actionLabel, array $summaryRows): void
    {
        $users->get()->each(function (User $user) use ($requestType, $requestNumber, $headline, $bodyMessage, $actionUrl, $actionLabel, $summaryRows): void {
            if (! $user->email) {
                return;
            }

            Mail::to($user->email)->queue(new RequestReviewMail(
                recipientName: $this->displayName($user),
                requestType: $requestType,
                requestNumber: $requestNumber,
                headline: $headline,
                bodyMessage: $bodyMessage,
                actionUrl: $actionUrl,
                actionLabel: $actionLabel,
                summaryRows: $summaryRows,
            ));
        });
    }

    public function emailUser(
        User $user,
        string $requestType,
        string $requestNumber,
        string $headline,
        string $bodyMessage,
        string $actionUrl,
        string $actionLabel,
        array $summaryRows = [],
    ): void {
        if (! $user->email) {
            return;
        }

        Mail::to($user->email)->queue(new RequestReviewMail(
            recipientName: $this->displayName($user),
            requestType: $requestType,
            requestNumber: $requestNumber,
            headline: $headline,
            bodyMessage: $bodyMessage,
            actionUrl: $actionUrl,
            actionLabel: $actionLabel,
            summaryRows: $summaryRows,
        ));
    }

    /**
     * Send both the in-app and email versions of a notification to one user.
     */
    public function notifyAndEmailUser(
        User $user,
        string $type,
        string $title,
        string $message,
        Model $reference,
        string $requestType,
        string $requestNumber,
        string $actionUrl,
        string $actionLabel,
        array $summaryRows = [],
    ): void {
        $this->notifyUser($user, $type, $title, $message, $reference);
        $this->emailUser(
            $user,
            $requestType,
            $requestNumber,
            $title,
            $message,
            $actionUrl,
            $actionLabel,
            $summaryRows,
        );
    }

    public function notifyForumPostCreated(ForumPost $forumPost, User $author): void
    {
        $authorName = $this->displayName($author);
        $authorRole = $author->role?->role_name ?? 'User';
        $title = 'New forum post';
        $message = $authorRole . ' ' . $authorName . ' created a new forum post: "' . $forumPost->title . '".';

        foreach (['Student', 'Instructor'] as $roleName) {
            $this->notifyRoleUsers(
                $roleName,
                'System',
                $title,
                $message,
                $forumPost,
                $author->userNo,
            );
        }
    }

    public function notifyForumCommentCreated(ForumComment $comment, User $actor): void
    {
        $comment->loadMissing(['post.user.role', 'parent.user.role']);

        $forumPost = $comment->post;

        if (! $forumPost) {
            return;
        }

        $eligibleRoles = ['Student', 'Instructor', 'Laboratory In-charge', 'Coordinator'];
        $participants = collect([$forumPost->user])
            ->concat($forumPost->comments()
                ->with('user.role')
                ->get()
                ->pluck('user'))
            ->filter(fn (?User $user) => $user
                && $user->status === 'Active'
                && $user->userNo !== $actor->userNo
                && in_array($user->role?->role_name, $eligibleRoles, true))
            ->unique('userNo');

        $actorName = $this->displayName($actor);
        $title = 'New reply on forum post';
        $message = $actorName . ' posted a new reply on the forum post: "' . $forumPost->title . '".';

        // Notify every participant in-app; email is limited to the poster and direct parent author below.
        $participants->each(fn (User $user) => $this->notifyUser(
            $user,
            'System',
            $title,
            $message,
            $forumPost,
        ));

        $postAuthor = $forumPost->user;
        $parentAuthor = $comment->parent?->user;
        $emailNotifications = [];

        if ($postAuthor
            && $postAuthor->status === 'Active'
            && $postAuthor->userNo !== $actor->userNo
            && in_array($postAuthor->role?->role_name, $eligibleRoles, true)) {
            $emailNotifications[$postAuthor->userNo] = [
                'user' => $postAuthor,
                'requestType' => 'Forum comment',
                'headline' => 'New comment on your forum post',
                'message' => $actorName . ' commented on your forum post: "' . $forumPost->title . '".',
                'summaryRows' => [
                    ['label' => 'Post', 'value' => $forumPost->title],
                    ['label' => 'Commenter', 'value' => $actorName],
                ],
            ];
        }

        if ($parentAuthor
            && $parentAuthor->status === 'Active'
            && $parentAuthor->userNo !== $actor->userNo
            && in_array($parentAuthor->role?->role_name, $eligibleRoles, true)) {
            $emailNotifications[$parentAuthor->userNo] = [
                'user' => $parentAuthor,
                'requestType' => 'Forum reply',
                'headline' => 'Someone replied to your comment',
                'message' => $actorName . ' replied to your comment on "' . $forumPost->title . '".',
                'summaryRows' => [
                    ['label' => 'Post', 'value' => $forumPost->title],
                    ['label' => 'Replier', 'value' => $actorName],
                ],
            ];
        }

        foreach ($emailNotifications as $emailNotification) {
            $recipient = $emailNotification['user'];

            $this->emailUser(
                $recipient,
                $emailNotification['requestType'],
                'Post #' . $forumPost->id,
                $emailNotification['headline'],
                $emailNotification['message'] . ' Open LabCentral to read it and reply.',
                $this->forumPostUrl($forumPost, $recipient),
                'View forum post',
                $emailNotification['summaryRows'],
            );
        }
    }

    public function notifyQuestionnaireCreated(FeedbackQuestionnaire $questionnaire, User $creator): void
    {
        $title = 'New feedback questionnaire';
        $creatorRole = $creator->role?->role_name ?? 'User';
        $message = $creatorRole . ' ' . $this->displayName($creator) . ' created a new questionnaire: "' . $questionnaire->topic . '".';
        $requestNumber = 'Questionnaire #' . $questionnaire->id;
        $summaryRows = [
            ['label' => 'Topic', 'value' => $questionnaire->topic],
            ['label' => 'Campus', 'value' => $questionnaire->campus],
            ['label' => 'Audience', 'value' => $questionnaire->audience],
            ['label' => 'Status', 'value' => $questionnaire->is_active ? 'Active' : 'Inactive'],
        ];

        if ($questionnaire->campus === Campus::IBED) {
            $this->notifyCampusRoleUsers(
                'Laboratory In-charge',
                Campus::IBED,
                'System',
                $title,
                $message,
                $questionnaire,
            );
            $this->emailCampusRoleUsers(
                'Laboratory In-charge',
                Campus::IBED,
                'Questionnaire',
                $requestNumber,
                $title,
                $message . ' Review it in LabCentral.',
                route('facilitator.feedback.questionnaires.show', $questionnaire),
                'Review questionnaire',
                $summaryRows,
            );

            return;
        }

        if ($questionnaire->campus !== Campus::MAIN) {
            return;
        }

        $this->notifyRoleUsers('Coordinator', 'System', $title, $message, $questionnaire);
        $this->emailRoleUsers(
            'Coordinator',
            'Questionnaire',
            $requestNumber,
            $title,
            $message . ' Review it in LabCentral.',
            route('coordinator.feedback.questionnaires.show', $questionnaire),
            'Review questionnaire',
            $summaryRows,
        );
    }

    /**
     * Notify the feedback reviewer assigned to the submitter's campus.
     */
    public function notifyFeedbackSubmitted(Feedback $feedback, string $submitterRole): void
    {
        $feedback->loadMissing(['user.role', 'user.department', 'laboratory']);

        $submitterName = $feedback->is_anonymous
            ? 'Anonymous'
            : ($feedback->user ? $this->displayName($feedback->user) : $submitterRole);
        $target = $feedback->laboratory?->laboratory_name ?? 'System';
        $requestNumber = 'Feedback #' . $feedback->id;
        $title = 'New feedback submitted';
        $message = $submitterRole . ' ' . $submitterName . ' submitted ' . strtolower($feedback->feedback_type) . ' feedback for ' . $target . '.';
        $campus = $feedback->user?->campusName();

        if ($campus === Campus::IBED) {
            $this->notifyCampusRoleUsers(
                'Laboratory In-charge',
                Campus::IBED,
                'System',
                $title,
                $message,
                $feedback,
            );
            $this->emailCampusRoleUsers(
                'Laboratory In-charge',
                Campus::IBED,
                'Feedback',
                $requestNumber,
                $title,
                $message . ' Please review it in LabCentral.',
                route('facilitator.feedback.show', $feedback),
                'Review feedback',
                [
                    ['label' => 'Submitted by', 'value' => $submitterName],
                    ['label' => 'Feedback type', 'value' => $feedback->feedback_type],
                    ['label' => 'Target', 'value' => $target],
                    ['label' => 'Rating', 'value' => $feedback->rating . '/5'],
                ],
            );

            return;
        }

        if ($campus !== Campus::MAIN) {
            return;
        }

        $this->notifyRoleUsers('Coordinator', 'System', $title, $message, $feedback);
        $this->emailRoleUsers(
            'Coordinator',
            'Feedback',
            $requestNumber,
            $title,
            $message . ' Please review it in LabCentral.',
            route('coordinator.feedback.show', $feedback),
            'Review feedback',
            [
                ['label' => 'Submitted by', 'value' => $submitterName],
                ['label' => 'Feedback type', 'value' => $feedback->feedback_type],
                ['label' => 'Target', 'value' => $target],
                ['label' => 'Rating', 'value' => $feedback->rating . '/5'],
            ],
        );
    }

    public function notifyQuestionnaireResponseSubmitted(FeedbackQuestionnaireResponse $response): void
    {
        $response->loadMissing(['questionnaire', 'user.role', 'user.department']);

        $questionnaire = $response->questionnaire;
        if (! $questionnaire || ! in_array($questionnaire->campus, [Campus::MAIN, Campus::IBED], true)) {
            return;
        }

        $respondent = $response->user;
        $respondentName = $respondent ? $this->displayName($respondent) : 'A user';
        $respondentRole = $respondent?->role?->role_name ?? 'User';
        $title = 'New questionnaire response';
        $message = $respondentRole . ' ' . $respondentName . ' submitted a response to "' . $questionnaire->topic . '".';
        $requestNumber = 'Response #' . $response->id;
        $summaryRows = [
            ['label' => 'Questionnaire', 'value' => $questionnaire->topic],
            ['label' => 'Campus', 'value' => $questionnaire->campus],
            ['label' => 'Respondent', 'value' => $respondentName],
            ['label' => 'Submitted', 'value' => $response->created_at?->format('M d, Y h:i A') ?? 'Just now'],
        ];

        if ($questionnaire->campus === Campus::IBED) {
            $this->notifyCampusRoleUsers(
                'Laboratory In-charge',
                Campus::IBED,
                'System',
                $title,
                $message,
                $response,
            );
            $this->emailCampusRoleUsers(
                'Laboratory In-charge',
                Campus::IBED,
                'Questionnaire response',
                $requestNumber,
                $title,
                $message . ' Review the submitted answers in LabCentral.',
                route('facilitator.feedback.questionnaires.responses.show', [$questionnaire, $response]),
                'Review response',
                $summaryRows,
            );

            return;
        }

        $this->notifyRoleUsers('Coordinator', 'System', $title, $message, $response);
        $this->emailRoleUsers(
            'Coordinator',
            'Questionnaire response',
            $requestNumber,
            $title,
            $message . ' Review the submitted answers in LabCentral.',
            route('coordinator.feedback.questionnaires.responses.show', [$questionnaire, $response]),
            'Review response',
            $summaryRows,
        );
    }

    public function notifyRequester(Model $reference, string $type, string $title, string $message): void
    {
        $user = $this->requesterFor($reference);

        if ($user) {
            $this->notifyUser($user, $type, $title, $message, $reference);
        }
    }

    public function summaryFor(User $user, int $limit = 5): array
    {
        $notifications = UserNotification::query()
            ->where('user_no', $user->userNo)
            ->orderByDesc('sent_at')
            ->orderByDesc('id');

        return [
            'unreadCount' => (clone $notifications)->where('is_read', false)->count(),
            'items' => (clone $notifications)->limit($limit)->get(),
        ];
    }

    public function markAsRead(UserNotification $notification): void
    {
        if ($notification->is_read) {
            return;
        }

        $notification->forceFill([
            'is_read' => true,
            'read_at' => now(),
        ])->save();
    }

    public function markAllAsRead(User $user): int
    {
        return UserNotification::query()
            ->where('user_no', $user->userNo)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    public function routeFor(UserNotification $notification, User $user): string
    {
        $notification->loadMissing('reference');

        $reference = $notification->reference;

        if ($reference instanceof Equipment) {
            return $user->role?->role_name === 'Coordinator'
                ? route('coordinator.equipment.show', $reference)
                : route('notifications.index');
        }

        if ($reference instanceof Chemical) {
            return $user->role?->role_name === 'Coordinator'
                ? route('coordinator.chemicals.show', $reference)
                : route('notifications.index');
        }

        if ($reference instanceof Feedback) {
            return match ($user->role?->role_name) {
                'Coordinator' => route('coordinator.feedback.show', $reference),
                'Laboratory In-charge' => route('facilitator.feedback.show', $reference),
                default => route('notifications.index'),
            };
        }

        if ($reference instanceof ForumPost) {
            return $this->forumPostUrl($reference, $user);
        }

        if ($reference instanceof FeedbackQuestionnaire) {
            return match ($user->role?->role_name) {
                'Coordinator' => route('coordinator.feedback.questionnaires.show', $reference),
                'Laboratory In-charge' => route('facilitator.feedback.questionnaires.show', $reference),
                default => route('notifications.index'),
            };
        }

        if ($reference instanceof FeedbackQuestionnaireResponse) {
            $reference->loadMissing('questionnaire');

            return match ($user->role?->role_name) {
                'Coordinator' => route('coordinator.feedback.questionnaires.responses.show', [$reference->questionnaire, $reference]),
                'Laboratory In-charge' => route('facilitator.feedback.questionnaires.responses.show', [$reference->questionnaire, $reference]),
                default => route('notifications.index'),
            };
        }

        if ($reference instanceof Reservation) {
            if ($user->role?->role_name === 'Instructor' && (int) $reference->user_no === (int) $user->userNo) {
                return route('instructor.my-reservations.show', $reference);
            }

            return match ($user->role?->role_name) {
                'Coordinator' => route('coordinator.reservations.show', $reference),
                'Laboratory In-charge' => route('facilitator.reservations.show', $reference),
                'Instructor' => route('instructor.reservations.show', $reference),
                'Student' => route('student.reservations.show', $reference),
                default => route('notifications.index'),
            };
        }

        if ($reference instanceof BorrowTransaction) {
            $roleName = $user->role?->role_name;

            if ($roleName === 'Instructor' && (int) $reference->borrower_id === (int) $user->userNo) {
                return route('instructor.my-borrow-requests.show', $reference);
            }

            $routeGroup = in_array($roleName, ['Coordinator', 'Laboratory In-charge'], true)
                ? match ($notification->title) {
                    'Checkout is due now' => 'checkout',
                    'Check-in is due now', 'Check-in overdue' => 'checkin',
                    default => 'borrow',
                }
                : 'borrow';

            return match ($roleName) {
                'Coordinator' => route('coordinator.'.$routeGroup.'.show', $reference),
                'Laboratory In-charge' => route('facilitator.'.$routeGroup.'.show', $reference),
                'Instructor' => route('instructor.'.$routeGroup.'.show', $reference),
                'Student' => route('student.'.$routeGroup.'.show', $reference),
                default => route('notifications.index'),
            };
        }

        if ($reference instanceof Announcement) {
            return match ($user->role?->role_name) {
                'Student' => route('student.dashboard'),
                'Instructor' => route('instructor.dashboard'),
                'Laboratory In-charge' => route('facilitator.dashboard'),
                default => route('notifications.index'),
            };
        }

        return route('notifications.index');
    }

    private function requesterFor(Model $reference): ?User
    {
        if ($reference instanceof Reservation) {
            return $reference->user;
        }

        if ($reference instanceof BorrowTransaction) {
            return $reference->borrower;
        }

        return null;
    }

    private function forumPostUrl(ForumPost $forumPost, User $user): string
    {
        return match ($user->role?->role_name) {
            'Coordinator' => route('coordinator.forum.show', $forumPost),
            'Laboratory In-charge' => route('facilitator.forum.show', $forumPost),
            'Instructor' => route('instructor.forum.show', $forumPost),
            'Student' => route('student.forum.show', $forumPost),
            default => route('notifications.index'),
        };
    }
}
