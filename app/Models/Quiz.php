<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'created_by',
        'title',
        'description',
        'duration_minutes',
        'max_attempts',
        'passing_score',
        'available_at',
        'due_at',
        'shuffle_questions',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'passing_score' => 'decimal:2',
            'available_at' => 'datetime',
            'due_at' => 'datetime',
            'shuffle_questions' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
