<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use App\Support\Campus;

class Laboratory extends Model
{
    use HasFactory, SoftDeletes;

    public const CAMPUS_MAIN = Campus::MAIN;
    public const CAMPUS_IBED = Campus::IBED;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'laboratory_code',
        'laboratory_name',
        'campus',
        'image',
        'building',
        'room_number',
        'capacity',
        'description',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
        ];
    }

    public function scopeForCampus(Builder $query, ?string $campus): Builder
    {
        return filled($campus) ? $query->where($query->qualifyColumn('campus'), $campus) : $query;
    }

    public function images(): HasMany
    {
        return $this->hasMany(LaboratoryImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }

    public function chemicals(): HasMany
    {
        return $this->hasMany(Chemical::class);
    }

    public function primaryImagePath(): ?string
    {
        $galleryImage = $this->relationLoaded('images')
            ? $this->images->first()
            : $this->images()->first();

        return $galleryImage?->image_path ?: $this->image;
    }

    public static function campusOptions(): array
    {
        return Campus::values();
    }

    /**
     * Resolve the short lab code used in sequential reservation and borrow numbers.
     */
    public function sequenceCode(): string
    {
        $storedCode = strtoupper(trim((string) $this->laboratory_code));

        if ($storedCode !== '' && ! preg_match('/^LAB-\d+$/', $storedCode)) {
            return preg_replace('/[^A-Z0-9]/', '', $storedCode) ?: $this->derivedSequenceCode();
        }

        return $this->derivedSequenceCode();
    }

    private function derivedSequenceCode(): string
    {
        $name = strtoupper(trim((string) $this->laboratory_name));
        $name = preg_replace('/\b(LABORATORY|LAB|ROOM|DEPARTMENT)\b/i', ' ', $name) ?? $name;
        $name = trim(preg_replace('/[^A-Z0-9 ]+/', ' ', $name) ?? $name);

        $words = array_values(array_filter(preg_split('/\s+/', $name) ?: []));

        if ($words === []) {
            return 'LAB';
        }

        $candidate = preg_replace('/[^A-Z0-9]/', '', $words[0]) ?: 'LAB';

        if (strlen($candidate) >= 3) {
            return Str::upper(Str::substr($candidate, 0, 4));
        }

        foreach (array_slice($words, 1) as $word) {
            $candidate .= preg_replace('/[^A-Z0-9]/', '', $word) ?: '';

            if (strlen($candidate) >= 3) {
                break;
            }
        }

        return Str::upper(Str::substr($candidate, 0, 4)) ?: 'LAB';
    }

    public function scopeMainCampus(Builder $query): Builder
    {
        return $query->where($query->qualifyColumn('campus'), self::CAMPUS_MAIN);
    }

    public function scopeForViewerCampus(Builder $query, ?string $campus): Builder
    {
        return $campus === null
            ? $query->mainCampus()
            : $query->where($query->qualifyColumn('campus'), $campus);
    }
}
