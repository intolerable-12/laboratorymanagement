<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Laboratory extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'laboratory_code',
        'laboratory_name',
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
}
