<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\QuestionOption;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\AttemptAnswer;

class Question extends Model
{
    use HasFactory;

    public const DIFFICULTIES = [
        'easy',
        'medium',
        'hard',
    ];

    protected $fillable = [
        'subject_id',
        'topic_id',
        'statement',
        'difficulty',
        'explanation',
        'source',
    ];

    protected $casts = [
        'difficulty' => 'string',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(AttemptAnswer::class);
    }
}
