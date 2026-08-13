<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackQuestionnaireAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'feedback_questionnaire_response_id',
        'feedback_questionnaire_question_id',
        'likert_value',
        'raw_answer',
    ];

    protected function casts(): array
    {
        return [
            'likert_value' => 'integer',
        ];
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(FeedbackQuestionnaireResponse::class, 'feedback_questionnaire_response_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(FeedbackQuestionnaireQuestion::class, 'feedback_questionnaire_question_id');
    }
}
