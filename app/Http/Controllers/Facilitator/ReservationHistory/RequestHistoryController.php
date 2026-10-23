<?php

namespace App\Http\Controllers\Facilitator\ReservationHistory;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RequestHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $campus = $this->campusForUser($request);
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

        $requesterCount = (clone $requestersQuery)->count();
        $requesters = $requestersQuery
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(15)
            ->withQueryString();

        return view('users.coordinator.requesthistory.index', [
            'search' => $search,
            'requesters' => $requesters,
            'requesterCount' => $requesterCount,
            'campus' => $campus,
        ]);
    }

    public function show(Request $request, User $user): View
    {
        $campus = $this->campusForUser($request);
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

    private function campusForUser(Request $request): string
    {
        $campus = $request->user()?->campusName();

        abort_unless(filled($campus), 403);

        return $campus;
    }
}
