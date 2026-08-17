<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Announcement extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'posted_by',
        'title',
        'content',
        'audiences',
        'images',
        'start_date',
        'end_date',
        'send_email',
        'is_published',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'audiences' => 'array',
            'images' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
            'send_email' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    /**
     * Get the user who posted the announcement.
     */
    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by', 'userNo');
    }

    /**
     * Scope announcements that are published and visible to a role.
     */
    public function scopeVisibleTo(Builder $query, string $audience): Builder
    {
        return $query
            ->published()
            ->where(function (Builder $innerQuery) use ($audience) {
                $innerQuery
                    ->whereNull('audiences')
                    ->orWhereJsonLength('audiences', 0)
                    ->orWhereJsonContains('audiences', $audience);
            });
    }

    /**
     * Scope only published announcements.
     */
    public function scopePublished(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query
            ->where('is_published', true)
            ->where(function (Builder $innerQuery) use ($today) {
                $innerQuery
                    ->whereNull('start_date')
                    ->orWhereDate('start_date', '<=', $today);
            })
            ->where(function (Builder $innerQuery) use ($today) {
                $innerQuery
                    ->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $today);
            });
    }

    /**
     * Convert the stored audience slugs into readable labels.
     *
     * @return array<int, string>
     */
    public function audienceLabels(): array
    {
        $audiences = collect($this->audiences ?? [])
            ->filter()
            ->values();

        if ($audiences->isEmpty()) {
            return ['All users'];
        }

        return $audiences->map(static fn (string $audience) => self::audienceLabel($audience))->all();
    }

    /**
     * Get the first announcement image if available.
     */
    public function coverImageUrl(): ?string
    {
        $firstImage = collect($this->images ?? [])->first();

        if (! $firstImage) {
            return null;
        }

        return asset('storage/' . ltrim($firstImage, '/'));
    }

    /**
     * Get all announcement image URLs.
     *
     * @return array<int, string>
     */
    public function imageUrls(): array
    {
        return collect($this->images ?? [])
            ->filter()
            ->map(static fn (string $path) => asset('storage/' . ltrim($path, '/')))
            ->values()
            ->all();
    }

    /**
     * Get a plain-text summary for cards and previews.
     */
    public function excerpt(int $limit = 180): string
    {
        return Str::limit(trim(strip_tags((string) $this->content)), $limit);
    }

    /**
     * Get the configured audience options.
     *
     * @return array<string, string>
     */
    public static function audienceOptions(): array
    {
        return [
            'student' => 'Student',
            'instructor' => 'Instructor',
            'facilitator' => 'Laboratory In-charge',
        ];
    }

    /**
     * Convert an audience slug into a display label.
     */
    public static function audienceLabel(string $audience): string
    {
        return self::audienceOptions()[$audience] ?? Str::headline($audience);
    }
}
