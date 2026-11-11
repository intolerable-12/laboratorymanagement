<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportArchive extends Model
{
    protected $fillable = [
        'campus',
        'report_type',
        'source',
        'period_key',
        'period_label',
        'file_name',
        'path',
        'generated_by',
    ];

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by', 'userNo');
    }
}
