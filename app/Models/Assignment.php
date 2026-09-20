<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'created_by',
        'title',
        'description',
        'max_score',
        'available_at',
        'due_at',
        'allow_late_submission',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'max_score' => 'decimal:2',
            'available_at' => 'datetime',
            'due_at' => 'datetime',
            'allow_late_submission' => 'boolean',
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

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }
}
