<?php

namespace App\Http\Controllers\Coordinator\ReservationHistory;

use App\Http\Controllers\Controller;
use App\Models\BorrowTransaction;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RequestHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureCoordinator($request);

        $search = trim((string) $request->query('search', ''));
        $searchTerms = $this->searchTerms($search);

        $requestersQuery = User::query()
            ->whereHas('role', fn (Builder $query) => $query->where('role_name', 'Student'))
            ->where(function (Builder $query): void {
                $query
                    ->whereHas('reservations')
                    ->orWhereHas('borrowTransactions');
            })
            ->with('role')
            ->withCount(['reservations', 'borrowTransactions']);

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
        ]);
    }

    public function show(Request $request, User $user): View
    {
        $this->ensureCoordinator($request);

        abort_unless(
            $user->reservations()->exists() || $user->borrowTransactions()->exists(),
            404
        );

        $activeTab = in_array($request->query('tab'), ['reservations', 'borrowing'], true)
            ? $request->query('tab')
            : 'reservations';
        $search = trim((string) $request->query('search', ''));

        $user->load('role');
        $reservations = $user->reservations()
            ->with(['laboratory', 'items.item', 'schoolYear', 'semester'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'reservation_page')
            ->withQueryString();
        $borrowings = $user->borrowTransactions()
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
        ]);
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
