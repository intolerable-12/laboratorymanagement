<?php

namespace App\Http\Controllers\Coordinator\Feedback;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Support\Campus;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        $campus = $this->viewerCampus($request);

        $search = trim((string) $request->query('search', ''));
        $type = trim((string) $request->query('type', ''));

        $campusFeedback = Feedback::query()
            ->whereHas('user.department', fn ($query) => $query->where('campus', $campus));

        $feedbacks = (clone $campusFeedback)
            ->with(['user', 'laboratory', 'reservation'])
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
            'total' => (clone $campusFeedback)->count(),
            'lab_service' => (clone $campusFeedback)->where('feedback_type', 'Lab Service')->count(),
            'system' => (clone $campusFeedback)->where('feedback_type', 'System')->count(),
        ];

        $types = ['Lab Service', 'System'];

        return view('users.coordinator.feedback.index', compact('feedbacks', 'search', 'type', 'stats', 'types'));
    }

    public function show(Request $request, Feedback $feedback)
    {
        $feedback->load(['user', 'laboratory', 'reservation']);
        abort_unless($this->feedbackBelongsToCampus($feedback, $this->viewerCampus($request)), 404);

        return view('users.coordinator.feedback.show', compact('feedback'));
    }

    private function viewerCampus(Request $request): string
    {
        $roleName = optional($request->user()->role)->role_name;

        if ($roleName === 'Coordinator') {
            return Campus::MAIN;
        }
    
        abort_unless($roleName === 'Laboratory In-charge', 403);

        // Facilitator: scope strictly to their own campus (MAIN or IBED).
        return $request->user()->campusName();
    }

    private function feedbackBelongsToCampus(Feedback $feedback, string $campus): bool
    {
        return $feedback->user?->campusName() === $campus;
    }
}
