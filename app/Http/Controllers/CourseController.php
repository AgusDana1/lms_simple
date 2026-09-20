<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseMember;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $search = $request->input('search');
        $filter = $request->input('filter', 'all'); // 'all', 'my'

        $query = Course::with('creator')
            ->withCount(['members as student_count' => fn ($q) => $q->where('member_role', 'student')])
            ->withCount('materials', 'assignments', 'quizzes');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Role filtering
        if ($user->isAdmin()) {
            // Admin sees all courses
            if ($filter === 'my') {
                $query->where('created_by', $user->id);
            }
        } elseif ($user->isDosen()) {
            if ($filter === 'my') {
                $query->where('created_by', $user->id);
            } else {
                $query->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                        ->orWhere('is_published', true);
                });
            }
        } else {
            // Mahasiswa
            if ($filter === 'my') {
                $query->whereHas('members', fn ($q) => $q->where('user_id', $user->id));
            } else {
                $query->where('is_published', true);
            }
        }

        $courses = $query->latest()->paginate(9)->withQueryString();

        $enrolledCourseIds = $user->courseMembership()->pluck('course_id')->toArray();

        return view('courses.index', compact('courses', 'search', 'filter', 'enrolledCourseIds'));
    }

    public function create(): View
    {
        $user = Auth::user();
        if (! $user->isDosen() && ! $user->isAdmin()) {
            abort(403, 'Hanya Dosen dan Admin yang dapat membuat mata kuliah baru.');
        }

        return view('courses.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (! $user->isDosen() && ! $user->isAdmin()) {
            abort(403, 'Hanya Dosen dan Admin yang dapat membuat mata kuliah baru.');
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:courses,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('courses/thumbnails', 'public');
        }

        $course = Course::create([
            'created_by' => $user->id,
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'thumbnail' => $thumbnailPath,
            'is_published' => $request->boolean('is_published'),
        ]);

        // Add creator as course member
        CourseMember::create([
            'course_id' => $course->id,
            'user_id' => $user->id,
            'member_role' => 'lecturer',
            'joined_at' => now(),
        ]);

        return redirect()->route('courses.show', $course)
            ->with('success', 'Mata kuliah "'.$course->name.'" berhasil dibuat!');
    }

    public function show(Course $course, Request $request): View
    {
        $user = Auth::user();

        // Check access: published or member or admin/creator
        $isMember = $course->members()->where('user_id', $user->id)->exists();
        $isCreator = $course->created_by === $user->id;

        if (! $course->is_published && ! $isMember && ! $isCreator && ! $user->isAdmin()) {
            abort(403, 'Mata kuliah ini belum dipublikasikan.');
        }

        $course->load([
            'creator.lecturerProfile',
            'materials' => fn ($q) => $q->orderBy('sort_order', 'asc'),
            'assignments' => fn ($q) => $q->with(['submissions' => fn ($sub) => $sub->where('student_id', $user->id)])->latest(),
            'quizzes' => fn ($q) => $q->with(['attempts' => fn ($att) => $att->where('student_id', $user->id)])->latest(),
            'announcements' => fn ($q) => $q->with('creator')->latest('published_at'),
            'discussions' => fn ($q) => $q->with(['user', 'replies.user'])->latest(),
            'members.user.role',
            'members.user.studentProfile',
            'members.user.lecturerProfile',
        ]);

        $activeTab = $request->input('tab', 'materials');

        // If student, calculate summary progress
        $studentSubmissionsCount = 0;
        $studentQuizzesPassedCount = 0;
        if ($user->isMahasiswa()) {
            $studentSubmissionsCount = $course->assignments()
                ->whereHas('submissions', fn ($q) => $q->where('student_id', $user->id))
                ->count();

            $studentQuizzesPassedCount = $course->quizzes()
                ->whereHas('attempts', fn ($q) => $q->where('student_id', $user->id)->where('status', 'graded'))
                ->count();
        }

        return view('courses.show', compact(
            'course',
            'isMember',
            'isCreator',
            'activeTab',
            'studentSubmissionsCount',
            'studentQuizzesPassedCount'
        ));
    }

    public function edit(Course $course): View
    {
        $user = Auth::user();
        if ($course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Anda tidak memiliki izin mengedit mata kuliah ini.');
        }

        return view('courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $user = Auth::user();
        if ($course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Anda tidak memiliki izin mengubah mata kuliah ini.');
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('courses')->ignore($course->id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $courseData = [
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_published' => $request->boolean('is_published'),
        ];

        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail && Storage::disk('public')->exists($course->thumbnail)) {
                Storage::disk('public')->delete($course->thumbnail);
            }
            $courseData['thumbnail'] = $request->file('thumbnail')->store('courses/thumbnails', 'public');
        }

        $course->update($courseData);

        return redirect()->route('courses.show', $course)
            ->with('success', 'Mata kuliah berhasil diperbarui!');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $user = Auth::user();
        if ($course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Anda tidak memiliki izin menghapus mata kuliah ini.');
        }

        if ($course->thumbnail && Storage::disk('public')->exists($course->thumbnail)) {
            Storage::disk('public')->delete($course->thumbnail);
        }

        $courseName = $course->name;
        $course->delete();

        return redirect()->route('courses.index')
            ->with('success', 'Mata kuliah "'.$courseName.'" berhasil dihapus.');
    }

    public function enroll(Course $course): RedirectResponse
    {
        $user = Auth::user();

        if (! $course->is_published && ! $user->isAdmin()) {
            return back()->with('error', 'Mata kuliah ini belum dapat diikuti.');
        }

        $memberRole = $user->isDosen() ? 'lecturer' : 'student';

        CourseMember::firstOrCreate(
            ['course_id' => $course->id, 'user_id' => $user->id],
            ['member_role' => $memberRole, 'joined_at' => now()]
        );

        return redirect()->route('courses.show', $course)
            ->with('success', 'Selamat! Anda berhasil bergabung dalam mata kuliah "'.$course->name.'".');
    }

    public function unenroll(Course $course): RedirectResponse
    {
        $user = Auth::user();

        // Cannot unenroll if you are the creator
        if ($course->created_by === $user->id) {
            return back()->with('error', 'Dosen pengampu utama tidak dapat keluar dari mata kuliah ini.');
        }

        CourseMember::where('course_id', $course->id)
            ->where('user_id', $user->id)
            ->delete();

        return redirect()->route('courses.index')
            ->with('info', 'Anda telah membatalkan keikutsertaan dari mata kuliah "'.$course->name.'".');
    }

    public function addMember(Request $request, Course $course): RedirectResponse
    {
        $user = Auth::user();
        if ($course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Anda tidak memiliki izin menambahkan anggota.');
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'member_role' => ['required', 'in:student,lecturer'],
        ]);

        $targetUser = User::where('email', $validated['email'])->firstOrFail();

        CourseMember::updateOrCreate(
            ['course_id' => $course->id, 'user_id' => $targetUser->id],
            ['member_role' => $validated['member_role'], 'joined_at' => now()]
        );

        return back()->with('success', 'Pengguna '.$targetUser->name.' berhasil ditambahkan ke mata kuliah.');
    }

    public function removeMember(Course $course, User $member): RedirectResponse
    {
        $user = Auth::user();
        if ($course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Anda tidak memiliki izin menghapus anggota.');
        }

        if ($member->id === $course->created_by) {
            return back()->with('error', 'Dosen pembuat mata kuliah tidak dapat dihapus.');
        }

        CourseMember::where('course_id', $course->id)
            ->where('user_id', $member->id)
            ->delete();

        return back()->with('success', 'Anggota berhasil dikeluarkan dari mata kuliah.');
    }
}
