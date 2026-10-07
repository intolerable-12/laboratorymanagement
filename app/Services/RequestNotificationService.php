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
        User::query()
            ->where('status', 'Active')
            ->whereHas('role', fn ($query) => $query->where('role_name', $roleName))
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
        User::query()
            ->where('status', 'Active')
            ->whereHas('role', fn ($query) => $query->where('role_name', $roleName))
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
        $postAuthor = $forumPost?->user;

        if ($postAuthor
            && $postAuthor->userNo !== $actor->userNo
            && in_array($postAuthor->role?->role_name, ['Student', 'Instructor', 'Laboratory In-charge', 'Coordinator'], true)) {
            $actorName = $this->displayName($actor);
            $title = 'New comment on your forum post';
            $message = $actorName . ' commented on your forum post: "' . $forumPost->title . '".';

            $this->notifyUser($postAuthor, 'System', $title, $message, $forumPost);
            $this->emailUser(
                $postAuthor,
                'Forum comment',
                'Post #' . $forumPost->id,
                $title,
                $message . ' Open LabCentral to read the comment and reply.',
                $this->forumPostUrl($forumPost, $postAuthor),
                'View forum post',
                [
                    ['label' => 'Post', 'value' => $forumPost->title],
                    ['label' => 'Commenter', 'value' => $actorName],
                ],
            );
        }

        $parentAuthor = $comment->parent?->user;

        if ($parentAuthor
            && $parentAuthor->userNo !== $actor->userNo
            && $parentAuthor->userNo !== $postAuthor?->userNo) {
            $message = $this->displayName($actor) . ' replied to your comment on "' . $forumPost->title . '".';

            $this->notifyUser($parentAuthor, 'System', 'Someone replied to your comment', $message, $forumPost);
        }
    }

    public function notifyQuestionnaireCreated(FeedbackQuestionnaire $questionnaire, User $creator): void
    {
        $title = 'New feedback questionnaire';
        $message = 'Coordinator ' . $this->displayName($creator) . ' created a new questionnaire: "' . $questionnaire->topic . '".';
        $actionUrl = route('coordinator.feedback.questionnaires.show', $questionnaire);

        $this->notifyRoleUsers('Coordinator', 'System', $title, $message, $questionnaire);
        $this->emailRoleUsers(
            'Coordinator',
            'Questionnaire',
            'Questionnaire #' . $questionnaire->id,
            $title,
            $message . ' Review it in LabCentral.',
            $actionUrl,
            'Review questionnaire',
            [
                ['label' => 'Topic', 'value' => $questionnaire->topic],
                ['label' => 'Status', 'value' => $questionnaire->is_active ? 'Active' : 'Inactive'],
            ],
        );
    }

    public function notifyQuestionnaireResponseSubmitted(FeedbackQuestionnaireResponse $response): void
    {
        $response->loadMissing(['questionnaire', 'user.role']);

        $questionnaire = $response->questionnaire;
        $respondent = $response->user;
        $respondentName = $respondent ? $this->displayName($respondent) : 'A user';
        $respondentRole = $respondent?->role?->role_name ?? 'User';
        $title = 'New questionnaire response';
        $message = $respondentRole . ' ' . $respondentName . ' submitted a response to "' . $questionnaire->topic . '".';
        $actionUrl = route('coordinator.feedback.questionnaires.responses.show', [$questionnaire, $response]);

        $this->notifyRoleUsers('Coordinator', 'System', $title, $message, $response);
        $this->emailRoleUsers(
            'Coordinator',
            'Questionnaire response',
            'Response #' . $response->id,
            $title,
            $message . ' Review the submitted answers in LabCentral.',
            $actionUrl,
            'Review response',
            [
                ['label' => 'Questionnaire', 'value' => $questionnaire->topic],
                ['label' => 'Respondent', 'value' => $respondentName],
                ['label' => 'Submitted', 'value' => $response->created_at?->format('M d, Y h:i A') ?? 'Just now'],
            ],
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
            return $user->role?->role_name === 'Coordinator'
                ? route('coordinator.feedback.show', $reference)
                : route('notifications.index');
        }

        if ($reference instanceof ForumPost) {
            return $this->forumPostUrl($reference, $user);
        }

        if ($reference instanceof FeedbackQuestionnaire) {
            return $user->role?->role_name === 'Coordinator'
                ? route('coordinator.feedback.questionnaires.show', $reference)
                : route('notifications.index');
        }

        if ($reference instanceof FeedbackQuestionnaireResponse) {
            $reference->loadMissing('questionnaire');

            return $user->role?->role_name === 'Coordinator'
                ? route('coordinator.feedback.questionnaires.responses.show', [$reference->questionnaire, $reference])
                : route('notifications.index');
        }

        if ($reference instanceof Reservation) {
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
            $routeGroup = in_array($roleName, ['Coordinator', 'Laboratory In-charge'], true)
                ? match ($notification->title) {
                    'Checkout is due now' => 'checkout',
                    'Check-in is due now' => 'checkin',
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
