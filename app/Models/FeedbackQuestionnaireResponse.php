<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeedbackQuestionnaireResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'feedback_questionnaire_id',
        'user_no',
    ];

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(FeedbackQuestionnaire::class, 'feedback_questionnaire_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_no', 'userNo');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(FeedbackQuestionnaireAnswer::class, 'feedback_questionnaire_response_id');
    }
}
