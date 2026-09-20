<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LecturerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lecturer_number',
        'academic_title',
        'study_program',
        'bio',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
