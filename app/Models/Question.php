<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'type',
        'text',
        'points',
        'sort_order',
    ];

    public function getQuestionAttribute(): ?string
    {
        return $this->text;
    }

    public function setQuestionAttribute(?string $value): void
    {
        $this->attributes['text'] = $value;
    }

    protected function casts(): array
    {
        return [
            'points' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }
}
