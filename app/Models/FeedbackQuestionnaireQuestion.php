<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeedbackQuestionnaireQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'feedback_questionnaire_id',
        'question_type',
        'question_text',
        'is_required',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(FeedbackQuestionnaire::class, 'feedback_questionnaire_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(FeedbackQuestionnaireAnswer::class, 'feedback_questionnaire_question_id');
    }
}
