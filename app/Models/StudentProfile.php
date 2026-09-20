<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_number',
        'study_program',
        'entry_year',
        'gender',
        'birth_date',
        'address',
    ];

    public function casts(): array
    {
        return [
            'birth_date' => 'date',
            'entry_yate' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
