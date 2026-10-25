<?php

namespace App\Http\Controllers\Coordinator\ReservationHistory;

use App\Http\Controllers\Controller;
use App\Models\BorrowTransaction;
use App\Models\Reservation;
use App\Models\User;
use App\Support\Campus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class RequestHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureCoordinator($request);

        $campus = Campus::MAIN;
        $search = trim((string) $request->query('search', ''));
        $searchTerms = $this->searchTerms($search);

        $requestersQuery = User::query()
            ->forRequestHistory($campus)
            ->where(function (Builder $query) use ($campus): void {
                $query
                    ->whereHas('reservations', fn (Builder $reservationQuery) => $reservationQuery->forCampus($campus))
                    ->orWhereHas('borrowTransactions', fn (Builder $borrowQuery) => $borrowQuery->forCampus($campus));
            })
            ->with(['role', 'department'])
            ->withCount([
                'reservations as reservations_count' => fn (Builder $query) => $query->forCampus($campus),
                'borrowTransactions as borrow_transactions_count' => fn (Builder $query) => $query->forCampus($campus),
            ]);

        $this->applyUserSearch($requestersQuery, $searchTerms);

        $requesters = $requestersQuery->get()
            ->concat($this->guestRequesters($campus, $searchTerms))
            ->sortBy(fn ($requester): string => mb_strtolower(trim(($requester->last_name ?? '') . ' ' . ($requester->first_name ?? '') . ' ' . ($requester->guest_key ?? ''))))
            ->values();

        $requesterCount = $requesters->count();
        $page = max(1, (int) $request->query('page', 1));
        $requesters = new LengthAwarePaginator(
            $requesters->forPage($page, 15)->values(),
            $requesterCount,
            15,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('users.coordinator.requesthistory.index', [
            'search' => $search,
            'requesters' => $requesters,
            'requesterCount' => $requesterCount,
            'campus' => $campus,
        ]);
    }

    public function show(Request $request, User $user): View
    {
        $this->ensureCoordinator($request);

        $campus = Campus::MAIN;
        $user->load(['role', 'department']);

        abort_unless($this->canViewRequester($user, $campus), 404);

        $activeTab = in_array($request->query('tab'), ['reservations', 'borrowing'], true)
            ? $request->query('tab')
            : 'reservations';
        $search = trim((string) $request->query('search', ''));

        $reservations = $user->reservations()
            ->forCampus($campus)
            ->with(['laboratory', 'items.item', 'schoolYear', 'semester'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'reservation_page')
            ->withQueryString();
        $borrowings = $user->borrowTransactions()
            ->forCampus($campus)
            ->with(['laboratory', 'items.item'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'borrowing_page')
            ->withQueryString();

        return view('users.coordinator.requesthistory.show', [
            'user' => $user,
            'search' => $search,
            'activeTab' => $activeTab,
            'reservations' => $reservations,
            'borrowings' => $borrowings,
            'campus' => $campus,
        ]);
    }

    public function showGuest(Request $request, string $guestKey): View
    {
        $this->ensureCoordinator($request);

        $campus = Campus::MAIN;
        $guest = $this->guestRequester($campus, $guestKey);

        abort_unless($guest, 404);

        $activeTab = in_array($request->query('tab'), ['reservations', 'borrowing'], true)
            ? $request->query('tab')
            : 'reservations';
        $search = trim((string) $request->query('search', ''));

        $reservations = Reservation::query()
            ->where('guest_key', $guestKey)
            ->forCampus($campus)
            ->with(['laboratory', 'items.item', 'schoolYear', 'semester'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'reservation_page')
            ->withQueryString();
        $borrowings = BorrowTransaction::query()
            ->where('guest_key', $guestKey)
            ->forCampus($campus)
            ->with(['laboratory', 'items.item'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'borrowing_page')
            ->withQueryString();

        return view('users.coordinator.requesthistory.show', [
            'user' => $guest,
            'search' => $search,
            'activeTab' => $activeTab,
            'reservations' => $reservations,
            'borrowings' => $borrowings,
            'campus' => $campus,
        ]);
    }

    private function guestRequesters(string $campus, array $searchTerms)
    {
        $reservations = Reservation::query()
            ->forCampus($campus)
            ->whereNotNull('guest_key')
            ->get([
                'guest_key',
                'guest_first_name',
                'guest_middle_name',
                'guest_last_name',
                'guest_suffix',
                'guest_student_id',
                'guest_email',
            ]);
        $borrowings = BorrowTransaction::query()
            ->forCampus($campus)
            ->whereNotNull('guest_key')
            ->get([
                'guest_key',
                'guest_first_name',
                'guest_middle_name',
                'guest_last_name',
                'guest_suffix',
                'guest_student_id',
                'guest_email',
            ]);

        return $reservations->pluck('guest_key')
            ->concat($borrowings->pluck('guest_key'))
            ->filter()
            ->unique()
            ->values()
            ->map(function (string $guestKey) use ($reservations, $borrowings, $searchTerms): ?object {
                $requesterReservations = $reservations->where('guest_key', $guestKey);
                $requesterBorrowings = $borrowings->where('guest_key', $guestKey);
                $snapshot = $requesterReservations->first() ?: $requesterBorrowings->first();

                $searchable = mb_strtolower(implode(' ', array_filter([
                    $snapshot?->guest_first_name,
                    $snapshot?->guest_middle_name,
                    $snapshot?->guest_last_name,
                    $snapshot?->guest_suffix,
                    $snapshot?->guest_student_id,
                    $snapshot?->guest_email,
                ])));

                foreach ($searchTerms as $term) {
                    if (! str_contains($searchable, mb_strtolower($term))) {
                        return null;
                    }
                }

                return (object) [
                    'is_guest' => true,
                    'guest_key' => $guestKey,
                    'first_name' => $snapshot?->guest_first_name,
                    'middle_name' => $snapshot?->guest_middle_name,
                    'last_name' => $snapshot?->guest_last_name,
                    'suffix' => $snapshot?->guest_suffix,
                    'userID' => $snapshot?->guest_student_id,
                    'email' => $snapshot?->guest_email,
                    'reservations_count' => $requesterReservations->count(),
                    'borrow_transactions_count' => $requesterBorrowings->count(),
                    'role' => null,
                ];
            })
            ->filter()
            ->values();
    }

    private function guestRequester(string $campus, string $guestKey): ?object
    {
        $reservation = Reservation::query()
            ->forCampus($campus)
            ->where('guest_key', $guestKey)
            ->first([
                'guest_key',
                'guest_first_name',
                'guest_middle_name',
                'guest_last_name',
                'guest_suffix',
                'guest_student_id',
                'guest_email',
            ]);
        $borrow = BorrowTransaction::query()
            ->forCampus($campus)
            ->where('guest_key', $guestKey)
            ->first([
                'guest_key',
                'guest_first_name',
                'guest_middle_name',
                'guest_last_name',
                'guest_suffix',
                'guest_student_id',
                'guest_email',
            ]);
        $snapshot = $reservation ?: $borrow;

        if (! $snapshot) {
            return null;
        }

        return (object) [
            'is_guest' => true,
            'guest_key' => $guestKey,
            'first_name' => $snapshot->guest_first_name,
            'middle_name' => $snapshot->guest_middle_name,
            'last_name' => $snapshot->guest_last_name,
            'suffix' => $snapshot->guest_suffix,
            'userID' => $snapshot->guest_student_id,
            'email' => $snapshot->guest_email,
            'role' => null,
        ];
    }

    private function canViewRequester(User $user, string $campus): bool
    {
        return User::query()
            ->whereKey($user->getKey())
            ->forRequestHistory($campus)
            ->where(function (Builder $query) use ($campus): void {
                $query
                    ->whereHas('reservations', fn (Builder $reservationQuery) => $reservationQuery->forCampus($campus))
                    ->orWhereHas('borrowTransactions', fn (Builder $borrowQuery) => $borrowQuery->forCampus($campus));
            })
            ->exists();
    }

    private function applyUserSearch(Builder $query, array $searchTerms): void
    {
        foreach ($searchTerms as $term) {
            $like = '%' . $term . '%';

            $query->where(function (Builder $termQuery) use ($like): void {
                $termQuery
                    ->where('first_name', 'like', $like)
                    ->orWhere('middle_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('suffix', 'like', $like)
                    ->orWhere('userID', 'like', $like)
                    ->orWhere('email', 'like', $like);
            });
        }
    }

    private function searchTerms(string $search): array
    {
        return array_values(array_filter(
            preg_split('/\s+/', $search) ?: [],
            static fn (string $term): bool => $term !== ''
        ));
    }

    private function ensureCoordinator(Request $request): void
    {
        abort_unless(optional($request->user()->role)->role_name === 'Coordinator', 403);
    }
}
