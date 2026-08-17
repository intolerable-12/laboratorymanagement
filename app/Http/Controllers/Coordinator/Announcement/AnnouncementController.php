<?php

namespace App\Http\Controllers\Coordinator\Announcement;

use App\Http\Controllers\Concerns\LoadsAnnouncements;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    use LoadsAnnouncements;

    public function index(Request $request): View
    {
        $this->ensureCoordinator($request);

        $audience = $request->query('audience', 'all');
        $status = $request->query('status', 'all');

        $query = Announcement::with(['postedBy'])->latest();

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
                'all' => Announcement::count(),
                'published' => Announcement::published()->count(),
                'draft' => Announcement::where('is_published', false)->count(),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $this->ensureCoordinator($request);

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
        $this->ensureCoordinator($request);

        $data = $this->validatedData($request);
        $data['posted_by'] = $request->user()->userNo;
        $data['audiences'] = $data['audiences'] ?? [];
        $data['images'] = $this->storeUploadedImages($request);

        Announcement::create($data);

        return redirect()
            ->route('coordinator.announcements.index')
            ->with('status', 'Announcement created successfully.');
    }

    public function show(Request $request, Announcement $announcement): View
    {
        $this->ensureCoordinator($request);

        $announcement->load(['postedBy.role']);

        return view('users.coordinator.announcement.show', [
            'announcement' => $this->announcementData($announcement),
            'model' => $announcement,
        ]);
    }

    public function edit(Request $request, Announcement $announcement): View
    {
        $this->ensureCoordinator($request);

        return view('users.coordinator.announcement.edit', [
            'announcement' => $announcement,
            'audienceOptions' => Announcement::audienceOptions(),
        ]);
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->ensureCoordinator($request);

        $data = $this->validatedData($request);
        $data['audiences'] = $data['audiences'] ?? [];
        $existingImages = collect($announcement->images ?? [])->filter()->values()->all();
        $newImages = $this->storeUploadedImages($request);
        $data['images'] = array_values(array_merge($existingImages, $newImages));

        $announcement->update($data);

        return redirect()
            ->route('coordinator.announcements.edit', $announcement)
            ->with('status', 'Announcement updated successfully.');
    }

    public function destroy(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->ensureCoordinator($request);

        $this->deleteAnnouncementImages($announcement);
        $announcement->delete();

        return redirect()
            ->route('coordinator.announcements.index')
            ->with('status', 'Announcement deleted successfully.');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'audiences' => ['required', 'array', 'min:1'],
            'audiences.*' => ['required', Rule::in(array_keys(Announcement::audienceOptions()))],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
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

    private function ensureCoordinator(Request $request): void
    {
        abort_unless(optional($request->user()->role)->role_name === 'Coordinator', 403);
    }
}
