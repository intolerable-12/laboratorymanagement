<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Concerns\LoadsAnnouncements;
use App\Http\Controllers\Controller;
use App\Models\BorrowTransaction;
use App\Models\Equipment;
use App\Models\Feedback;
use App\Models\ForumComment;
use App\Models\ForumPost;
use App\Models\Notification;
use App\Models\Reservation;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use LoadsAnnouncements;

    public function index(): View
    {
        $user = auth()->user();

        $transactions = BorrowTransaction::query()
            ->with(['items.item', 'laboratory'])
            ->where('borrower_id', $user?->userNo)
            ->latest('updated_at')
            ->latest('id')
            ->get();

        $reservations = Reservation::query()
            ->with('laboratory')
            ->where('user_no', $user?->userNo)
            ->latest('updated_at')
            ->latest('id')
            ->get();

        $feedbacks = Feedback::query()
            ->where('user_no', $user?->userNo)
            ->latest('created_at')
            ->limit(5)
            ->get();

        $forumPosts = ForumPost::query()
            ->where('user_no', $user?->userNo)
            ->latest('created_at')
            ->limit(5)
            ->get();

        $forumComments = ForumComment::query()
            ->with('post')
            ->where('user_no', $user?->userNo)
            ->latest('created_at')
            ->limit(5)
            ->get();

        $activeStatuses = ['Coordinator Approved', 'Partially Borrowed', 'Borrowed', 'Partially Returned', 'Overdue'];
        $pendingStatuses = ['Pending', 'Instructor Approved', 'Facilitator Approved'];

        $activeTransactions = $transactions->filter(fn (BorrowTransaction $transaction) => in_array($transaction->status, $activeStatuses, true));
        $pendingTransactions = $transactions->filter(fn (BorrowTransaction $transaction) => in_array($transaction->status, $pendingStatuses, true));
        $returnedTransactions = $transactions->filter(fn (BorrowTransaction $transaction) => $transaction->status === 'Returned');

        $activeEquipmentUnits = $this->equipmentQuantity($activeTransactions);
        $activeChemicalQuantity = $this->chemicalQuantitySummary($activeTransactions);
        $overdueRequestCount = (int) $activeTransactions->filter(fn (BorrowTransaction $transaction) => $transaction->due_at && $transaction->due_at->isPast())->count();
        $onTimeReturns = $returnedTransactions->filter(fn (BorrowTransaction $transaction) =>
            $transaction->returned_at
            && $transaction->due_at
            && $transaction->returned_at->lessThanOrEqualTo($transaction->due_at)
        )->count();
        $onTimeReturnRate = $returnedTransactions->count() > 0
            ? (int) round(($onTimeReturns / $returnedTransactions->count()) * 100)
            : 0;
        $activeReservationCount = $reservations
            ->filter(fn (Reservation $reservation): bool =>
                in_array($reservation->status, ['Instructor Approved', 'Coordinator Approved'], true)
                && $reservation->reservation_date?->greaterThanOrEqualTo(today())
            )
            ->count();
        $pendingReservationCount = $reservations
            ->whereIn('status', ['Pending', 'Instructor Approved'])
            ->count();
        $unreadNotificationCount = Notification::query()
            ->where('user_no', $user?->userNo)
            ->where('is_read', false)
            ->count();

        $recentBorrowedItems = $transactions
            ->filter(fn (BorrowTransaction $transaction) => in_array($transaction->status, $activeStatuses, true))
            ->flatMap(fn (BorrowTransaction $transaction) => $transaction->items->map(function ($item) use ($transaction) {
                $borrowedItem = $item->item;

                return [
                    'name' => $borrowedItem?->equipment_name ?? $borrowedItem?->chemical_name ?? 'Borrowed item',
                    'type' => $borrowedItem instanceof Equipment ? 'Equipment' : 'Chemical',
                    'laboratory' => $transaction->laboratory?->laboratory_name ?? $borrowedItem?->laboratory?->laboratory_name ?? 'Unassigned',
                    'return' => $transaction->due_at?->format('Y-m-d') ?? '—',
                    'status' => $transaction->status,
                ];
            }))
            ->take(3)
            ->values();

        $recentActivities = $this->recentActivities(
            $reservations,
            $transactions,
            $feedbacks,
            $forumPosts,
            $forumComments,
        );

        return view('users.student.dashboard', [
            'announcements' => $this->publishedAnnouncements('student', 6),
            'metrics' => [
                'active_requests' => $activeTransactions->count(),
                'equipment_units' => $activeEquipmentUnits,
                'chemical_quantity' => $activeChemicalQuantity !== '' ? $activeChemicalQuantity : '0',
                'overdue_returns' => $overdueRequestCount,
                'on_time_returns' => $onTimeReturnRate . '%',
                'active_reservations' => $activeReservationCount,
                'pending_reservations' => $pendingReservationCount,
                'unread_notifications' => $unreadNotificationCount,
                'feedback_submissions' => Feedback::query()->where('user_no', $user?->userNo)->count(),
                'forum_contributions' => ForumPost::query()->where('user_no', $user?->userNo)->count()
                    + ForumComment::query()->where('user_no', $user?->userNo)->count(),
            ],
            'recentBorrowedItems' => $recentBorrowedItems,
            'borrowSummary' => [
                'active' => $activeTransactions->count(),
                'pending' => $pendingTransactions->count(),
                'returned' => $returnedTransactions->count(),
                'overdue' => $overdueRequestCount,
            ],
            'recentActivities' => $recentActivities,
        ]);
    }

    private function recentActivities(
        Collection $reservations,
        Collection $transactions,
        Collection $feedbacks,
        Collection $forumPosts,
        Collection $forumComments,
    ): Collection {
        $activities = collect();

        foreach ($reservations as $reservation) {
            $activities->push([
                'occurred_at' => $reservation->updated_at ?? $reservation->created_at,
                'text' => 'Reservation ' . $reservation->reservation_no,
                'meta' => ($reservation->experiment_title ?: 'Laboratory reservation')
                    . ' · ' . ($reservation->laboratory?->laboratory_name ?? 'Laboratory')
                    . ' · ' . ($reservation->updated_at?->format('M d, Y') ?? 'Date unavailable'),
                'status' => $reservation->status,
                'url' => route('student.reservations.show', $reservation),
            ]);
        }

        foreach ($transactions as $transaction) {
            $activities->push([
                'occurred_at' => $transaction->updated_at ?? $transaction->created_at,
                'text' => 'Borrow request ' . $transaction->borrow_no,
                'meta' => $transaction->items->count() . ' item(s) · '
                    . ($transaction->laboratory?->laboratory_name ?? 'Laboratory')
                    . ' · ' . ($transaction->updated_at?->format('M d, Y') ?? 'Date unavailable'),
                'status' => $transaction->status,
                'url' => route('student.borrow.show', $transaction),
            ]);
        }

        foreach ($feedbacks as $feedback) {
            $activities->push([
                'occurred_at' => $feedback->created_at,
                'text' => 'Submitted ' . ($feedback->feedback_type ?: 'system') . ' feedback',
                'meta' => 'Coordinator review only · ' . ($feedback->created_at?->format('M d, Y') ?? 'Date unavailable'),
                'status' => 'Submitted',
                'url' => route('student.feedback.index'),
            ]);
        }

        foreach ($forumPosts as $post) {
            $activities->push([
                'occurred_at' => $post->created_at,
                'text' => 'Created forum post: ' . $post->title,
                'meta' => $post->category . ' · ' . ($post->created_at?->format('M d, Y') ?? 'Date unavailable'),
                'status' => 'Post',
                'url' => route('student.forum.show', $post),
            ]);
        }

        foreach ($forumComments as $comment) {
            $activities->push([
                'occurred_at' => $comment->created_at,
                'text' => 'Commented on: ' . ($comment->post?->title ?? 'Forum post'),
                'meta' => 'Forum discussion · ' . ($comment->created_at?->format('M d, Y') ?? 'Date unavailable'),
                'status' => 'Comment',
                'url' => $comment->post ? route('student.forum.show', $comment->post) : route('student.forum.index'),
            ]);
        }

        return $activities
            ->sortByDesc(fn (array $activity): int => $activity['occurred_at']?->timestamp ?? 0)
            ->take(8)
            ->values();
    }

    private function equipmentQuantity(Collection $transactions): int
    {
        return (int) $transactions
            ->flatMap(fn (BorrowTransaction $transaction) => $transaction->items)
            ->filter(fn ($item) => $item->item_type === 'Equipment')
            ->sum(fn ($item) => (float) ($item->quantity_borrowed ?? 0));
    }

    private function chemicalQuantitySummary(Collection $transactions): string
    {
        $quantities = [];
        $units = [];

        foreach ($transactions as $transaction) {
            foreach ($transaction->items as $item) {
                if ($item->item_type !== 'Chemical') {
                    continue;
                }

                $unit = trim((string) ($item->item?->unit ?? ''));
                $key = strtolower($unit !== '' ? $unit : 'unit');
                $quantities[$key] = ($quantities[$key] ?? 0) + (float) ($item->quantity_borrowed ?? 0);
                $units[$key] = $unit !== '' ? $unit : 'unit';
            }
        }

        return collect($quantities)
            ->sortKeys()
            ->map(fn (float $quantity, string $key) => $this->formatQuantity($quantity) . ' ' . $units[$key])
            ->implode(' + ');
    }

    private function formatQuantity(float $quantity): string
    {
        if (floor($quantity) === $quantity) {
            return number_format($quantity, 0);
        }

        return rtrim(rtrim(number_format($quantity, 2, '.', ''), '0'), '.');
    }
}
