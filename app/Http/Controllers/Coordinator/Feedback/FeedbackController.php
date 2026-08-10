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
        $visibility = trim((string) $request->query('visibility', ''));

        $feedbacks = Feedback::with(['user', 'laboratory', 'reservation'])
            ->when($type !== '', fn ($query) => $query->where('feedback_type', $type))
            ->when($visibility !== '', fn ($query) => $query->where('visibility', $visibility))
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
            'public' => Feedback::where('visibility', 'Public')->count(),
            'private' => Feedback::where('visibility', 'Private')->count(),
            'lab_service' => Feedback::where('feedback_type', 'Lab Service')->count(),
            'system' => Feedback::where('feedback_type', 'System')->count(),
        ];

        $types = ['Lab Service', 'System'];
        $visibilities = ['Private', 'Public'];

        return view('users.coordinator.feedback.index', compact('feedbacks', 'search', 'type', 'visibility', 'stats', 'types', 'visibilities'));
    }

    public function show(Request $request, Feedback $feedback)
    {
        $this->ensureCoordinator($request);

        $feedback->load(['user', 'laboratory', 'reservation']);

        return view('users.coordinator.feedback.show', compact('feedback'));
    }

    public function toggleVisibility(Request $request, Feedback $feedback)
    {
        $this->ensureCoordinator($request);

        $feedback->update([
            'visibility' => $feedback->visibility === 'Public' ? 'Private' : 'Public',
        ]);

        return back()->with('status', 'Feedback visibility updated successfully.');
    }

    private function ensureCoordinator(Request $request): void
    {
        abort_unless(optional($request->user()->role)->role_name === 'Coordinator', 403);
    }
}