<?php

namespace App\Http\Controllers\Coordinator\Announcement;

use App\Http\Controllers\Concerns\LoadsAnnouncements;
use App\Http\Controllers\Controller;
use App\Mail\AnnouncementPublishedMail;
use App\Models\Announcement;
use App\Models\User;
use App\Services\RequestNotificationService;
use App\Support\Campus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    use LoadsAnnouncements;

    public function __construct(private RequestNotificationService $notificationService)
    {
    }

    public function index(Request $request): View
    {
        $campus = $this->managedCampus($request);

        $audience = $request->query('audience', 'all');
        $status = $request->query('status', 'all');

        $campusAnnouncements = Announcement::query()->forCampus($campus);
        $query = (clone $campusAnnouncements)->with(['postedBy'])->latest();

        if ($audience !== 'all') {
            $query->where(function ($announcementQuery) use ($audience) {
                $announcementQuery
                    ->whereNull('audiences')
                    ->orWhereJsonLength('audiences', 0)
                    ->orWhereJsonContains('audiences', $audience);
            });
        }

        if ($status === 'published') {
            $query->published();
        } elseif ($status === 'draft') {
            $query->where('is_published', false);
        }

        $announcements = $query->paginate(8)->withQueryString();

        return view('users.coordinator.announcement.index', [
            'announcements' => $announcements,
            'audience' => $audience,
            'status' => $status,
            'audienceOptions' => Announcement::audienceOptions(),
            'statusOptions' => [
                'all' => 'All',
                'published' => 'Published',
                'draft' => 'Draft',
            ],
            'totals' => [
                'all' => (clone $campusAnnouncements)->count(),
                'published' => (clone $campusAnnouncements)->published()->count(),
                'draft' => (clone $campusAnnouncements)->where('is_published', false)->count(),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $this->managedCampus($request);

        return view('users.coordinator.announcement.create', [
            'announcement' => new Announcement([
                'audiences' => [],
                'images' => [],
                'is_published' => true,
                'send_email' => true,
            ]),
            'audienceOptions' => Announcement::audienceOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->managedCampus($request);

        $data = $this->validatedData($request);
        $data['posted_by'] = $request->user()->userNo;
        $data['audiences'] = $data['audiences'] ?? [];
        $data['images'] = $this->storeUploadedImages($request);

        $announcement = Announcement::create($data);
        $this->notifyAudienceUsers($announcement, sendEmail: $announcement->send_email);

        return redirect()
            ->route($this->routePrefix($request).'.announcements.index')
            ->with('status', 'Announcement created successfully.');
    }

    public function show(Request $request, Announcement $announcement): View
    {
        $campus = $this->managedCampus($request);
        $this->ensureAnnouncementBelongsToCampus($announcement, $campus);

        $announcement->load(['postedBy.role']);

        return view('users.coordinator.announcement.show', [
            'announcement' => $this->announcementData($announcement),
            'model' => $announcement,
        ]);
    }

    public function edit(Request $request, Announcement $announcement): View
    {
        $campus = $this->managedCampus($request);
        $this->ensureAnnouncementBelongsToCampus($announcement, $campus);

        return view('users.coordinator.announcement.edit', [
            'announcement' => $announcement,
            'audienceOptions' => Announcement::audienceOptions(),
        ]);
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $campus = $this->managedCampus($request);
        $this->ensureAnnouncementBelongsToCampus($announcement, $campus);

        $wasPublished = (bool) $announcement->is_published;
        $data = $this->validatedData($request);
        $data['audiences'] = $data['audiences'] ?? [];
        $existingImages = collect($announcement->images ?? [])->filter()->values()->all();
        $newImages = $this->storeUploadedImages($request);
        $data['images'] = array_values(array_merge($existingImages, $newImages));

        $announcement->update($data);

        if (! $wasPublished && $announcement->is_published) {
            $this->notifyAudienceUsers($announcement, sendEmail: $announcement->send_email);
        }

        return redirect()
            ->route($this->routePrefix($request).'.announcements.edit', $announcement)
            ->with('status', 'Announcement updated successfully.');
    }

    public function destroy(Request $request, Announcement $announcement): RedirectResponse
    {
        $campus = $this->managedCampus($request);
        $this->ensureAnnouncementBelongsToCampus($announcement, $campus);

        $this->deleteAnnouncementImages($announcement);
        $announcement->delete();

        return redirect()
            ->route($this->routePrefix($request).'.announcements.index')
            ->with('status', 'Announcement deleted successfully.');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'audiences' => ['required', 'array', 'min:1'],
            'audiences.*' => ['required', Rule::in(array_keys(Announcement::audienceOptions()))],
            'start_date' => ['nullable', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after_or_equal:today', 'after_or_equal:start_date'],
            'send_email' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'max:4096'],
        ]);

        $data['send_email'] = $request->boolean('send_email');
        $data['is_published'] = $request->boolean('is_published');
        $data['start_date'] = array_key_exists('start_date', $data) && $data['start_date'] !== '' ? $data['start_date'] : null;
        $data['end_date'] = array_key_exists('end_date', $data) && $data['end_date'] !== '' ? $data['end_date'] : null;

        return Arr::except($data, ['images']);
    }

    /**
     * Store uploaded announcement images and return their paths.
     *
     * @return array<int, string>
     */
    private function storeUploadedImages(Request $request): array
    {
        if (! $request->hasFile('images')) {
            return [];
        }

        return collect($request->file('images'))
            ->filter()
            ->map(fn ($image) => $image->store('announcements', 'public'))
            ->values()
            ->all();
    }

    private function deleteAnnouncementImages(Announcement $announcement): void
    {
        foreach (collect($announcement->images ?? [])->filter() as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    private function notifyAudienceUsers(Announcement $announcement, bool $sendEmail): void
    {
        if (! $announcement->is_published) {
            return;
        }

        $roleNames = collect($announcement->audiences ?? [])
            ->map(fn (string $audience) => Announcement::audienceOptions()[$audience] ?? null)
            ->filter()
            ->values();

        if ($roleNames->isEmpty()) {
            return;
        }

        $message = Str::limit(trim(strip_tags((string) $announcement->content)), 180);

        $announcement->loadMissing(['postedBy.role', 'postedBy.department']);
        $postedBy = $announcement->postedBy;
        $campus = match ($postedBy?->role?->role_name) {
            'Coordinator' => Campus::MAIN,
            'Laboratory In-charge' => $postedBy->campusName(),
            default => null,
        };

        if (! filled($campus)) {
            return;
        }

        User::query()
            ->where('status', 'Active')
            ->whereHas('role', fn ($query) => $query->whereIn('role_name', $roleNames->all()))
            ->forCampus($campus)
            ->with('role')
            ->get()
            ->each(function (User $user) use ($announcement, $message, $sendEmail): void {
                $this->notificationService->notifyUser(
                    user: $user,
                    type: 'Announcement',
                    title: 'New announcement: ' . $announcement->title,
                    message: $message ?: 'A new announcement is available on LabCentral.',
                    reference: $announcement,
                );

                if ($sendEmail && filled($user->email)) {
                    Mail::to($user->email)->queue(new AnnouncementPublishedMail(
                        announcement: $announcement,
                        recipientName: $this->notificationService->displayName($user),
                        actionUrl: $this->dashboardUrlFor($user),
                    ));
                }
            });
    }

    private function dashboardUrlFor(User $user): string
    {
        return match ($user->role?->role_name) {
            'Student' => route('student.dashboard'),
            'Instructor' => route('instructor.dashboard'),
            'Laboratory In-charge' => route('facilitator.dashboard'),
            default => route('notifications.index'),
        };
    }

    private function managedCampus(Request $request): string
    {
        $user = $request->user()->loadMissing(['role', 'department']);
        $roleName = $user->role?->role_name;

        if ($roleName === 'Coordinator') {
            return Campus::MAIN;
        }

        abort_unless($roleName === 'Laboratory In-charge', 403);

        $campus = $user->campusName();
        abort_unless(filled($campus), 403);

        return $campus;
    }

    private function ensureAnnouncementBelongsToCampus(Announcement $announcement, string $campus): void
    {
        abort_unless(
            Announcement::query()
                ->forCampus($campus)
                ->whereKey($announcement->getKey())
                ->exists(),
            404,
        );
    }

    private function routePrefix(Request $request): string
    {
        return $request->routeIs('facilitator.*') ? 'facilitator' : 'coordinator';
    }
}
