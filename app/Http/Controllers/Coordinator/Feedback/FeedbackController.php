<?php

namespace App\Http\Controllers\Coordinator\Feedback;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureCoordinator($request);

        $search = trim((string) $request->query('search', ''));
        $type = trim((string) $request->query('type', ''));

        $feedbacks = Feedback::with(['user', 'laboratory', 'reservation'])
            ->when($type !== '', fn ($query) => $query->where('feedback_type', $type))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery->where('comments', 'like', '%' . $search . '%')
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('userID', 'like', '%' . $search . '%')
                                ->orWhere('first_name', 'like', '%' . $search . '%')
                                ->orWhere('last_name', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('laboratory', function ($laboratoryQuery) use ($search) {
                            $laboratoryQuery->where('laboratory_name', 'like', '%' . $search . '%')
                                ->orWhere('laboratory_code', 'like', '%' . $search . '%');
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => Feedback::count(),
            'lab_service' => Feedback::where('feedback_type', 'Lab Service')->count(),
            'system' => Feedback::where('feedback_type', 'System')->count(),
        ];

        $types = ['Lab Service', 'System'];

        return view('users.coordinator.feedback.index', compact('feedbacks', 'search', 'type', 'stats', 'types'));
    }

    public function show(Request $request, Feedback $feedback)
    {
        $this->ensureCoordinator($request);

        $feedback->load(['user', 'laboratory', 'reservation']);

        return view('users.coordinator.feedback.show', compact('feedback'));
    }

    private function ensureCoordinator(Request $request): void
    {
        abort_unless(optional($request->user()->role)->role_name === 'Coordinator', 403);
    }
}
