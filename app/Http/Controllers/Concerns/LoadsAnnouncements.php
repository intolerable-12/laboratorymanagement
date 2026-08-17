<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Announcement;
use Illuminate\Support\Collection;

trait LoadsAnnouncements
{
    protected function publishedAnnouncements(?string $audience = null, int $limit = 6): Collection
    {
        $query = Announcement::with(['postedBy.role'])
            ->latest();

        if ($audience === null) {
            $query->published();
        } else {
            $query->visibleTo($audience);
        }

        return $query
            ->limit($limit)
            ->get()
            ->map(fn (Announcement $announcement) => $this->announcementData($announcement))
            ->values();
    }

    protected function latestAnnouncements(int $limit = 4): Collection
    {
        return Announcement::with(['postedBy.role'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (Announcement $announcement) => $this->announcementData($announcement))
            ->values();
    }

    protected function announcementData(Announcement $announcement): array
    {
        return [
            'id' => $announcement->getKey(),
            'title' => $announcement->title,
            'content' => $announcement->content,
            'excerpt' => $announcement->excerpt(150),
            'audiences' => $announcement->audienceLabels(),
            'audience_slugs' => collect($announcement->audiences ?? [])->filter()->values()->all(),
            'images' => $announcement->imageUrls(),
            'cover_image' => $announcement->coverImageUrl(),
            'start_date' => optional($announcement->start_date)->format('F d, Y'),
            'end_date' => optional($announcement->end_date)->format('F d, Y'),
            'posted_by' => trim(collect([
                $announcement->postedBy?->first_name,
                $announcement->postedBy?->middle_name,
                $announcement->postedBy?->last_name,
                $announcement->postedBy?->suffix,
            ])->filter()->implode(' ')) ?: 'System',
            'posted_by_role' => $announcement->postedBy?->role?->role_name ?? 'Coordinator',
            'is_published' => (bool) $announcement->is_published,
            'send_email' => (bool) $announcement->send_email,
            'updated_at' => $announcement->updated_at?->format('F d, Y h:i A'),
            'created_at' => $announcement->created_at?->format('F d, Y h:i A'),
        ];
    }
}
