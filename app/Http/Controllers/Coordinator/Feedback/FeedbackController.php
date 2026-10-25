<?php

namespace App\Http\Controllers\Coordinator\Feedback;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Support\Campus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'average_rating' => round((float) ((clone $campusFeedback)->avg('rating') ?? 0), 1),
        ];

        $types = ['Lab Service', 'System'];

        return view('users.coordinator.feedback.index', compact('feedbacks', 'search', 'type', 'stats', 'types', 'campus'));
    }

    public function show(Request $request, Feedback $feedback)
    {
        $feedback->load(['user', 'laboratory', 'reservation']);
        $campus = $this->viewerCampus($request);
        abort_unless($this->feedbackBelongsToCampus($feedback, $campus), 404);

        return view('users.coordinator.feedback.show', compact('feedback', 'campus'));
    }

    public function image(Request $request, Feedback $feedback, int $image)
    {
        $feedback->loadMissing('user');
        $campus = $this->viewerCampus($request);
        abort_unless($this->feedbackBelongsToCampus($feedback, $campus), 404);

        $path = collect($feedback->images ?? [])->get($image);
        abort_unless(is_string($path) && $path !== '', 404);
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, null, [
            'Cache-Control' => 'private, max-age=300',
        ]);
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
