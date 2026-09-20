<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'phone',
        'profile_photo',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role?->slug === 'admin';
    }

    public function isDosen(): bool
    {
        return $this->role?->slug === 'dosen';
    }

    public function isMahasiswa(): bool
    {
        return $this->role?->slug === 'mahasiswa';
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role?->slug, $roles, true);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function lecturerProfile(): HasOne
    {
        return $this->hasOne(LecturerProfile::class);
    }

    public function createdCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'created_by');
    }

    public function courseMembership(): HasMany
    {
        return $this->hasMany(CourseMember::class);
    }

    public function enrolledCourses()
    {
        return $this->belongsToMany(Course::class, 'course_members')
            ->withPivot('member_role', 'joined_at')
            ->withTimestamps();
    }

    public function createdAssignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'created_by');
    }

    public function assignmentSubmissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class, 'student_id');
    }

    public function createdQuizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'created_by');
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class, 'student_id');
    }

    public function discussions(): HasMany
    {
        return $this->hasMany(Discussion::class);
    }

    public function discussionReplies(): HasMany
    {
        return $this->hasMany(DiscussionReply::class);
    }
}
