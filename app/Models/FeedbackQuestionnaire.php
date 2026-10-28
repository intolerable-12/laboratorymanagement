<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use App\Support\Campus;

class FeedbackQuestionnaire extends Model
{
    use HasFactory;

    public const AUDIENCE_STUDENT = 'Student';
    public const AUDIENCE_INSTRUCTOR = 'Instructor';
    public const AUDIENCE_BOTH = 'Both';

    protected $fillable = [
        'topic',
        'description',
        'campus',
        'audience',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function questions(): HasMany
    {
        return $this->hasMany(FeedbackQuestionnaireQuestion::class)->orderBy('sort_order');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(FeedbackQuestionnaireResponse::class);
    }

    public function scopeForCampus(Builder $query, string $campus): Builder
    {
        return $query->where('campus', $campus);
    }

    public function targetsAudience(string $audience): bool
    {
        return in_array($this->audience, [$audience, self::AUDIENCE_BOTH], true);
    }

    public static function audiences(): array
    {
        return [
            self::AUDIENCE_STUDENT,
            self::AUDIENCE_INSTRUCTOR,
            self::AUDIENCE_BOTH,
        ];
    }

    public static function audiencesForCampus(string $campus): array
    {
        return Campus::normalize($campus) === Campus::IBED
            ? [self::AUDIENCE_INSTRUCTOR]
            : self::audiences();
    }
}
