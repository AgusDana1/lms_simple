<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminCourseController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $lecturerId = $request->input('lecturer_id');
        $published = $request->input('published');

        $query = Course::with(['creator.lecturerProfile'])
            ->withCount(['members as student_count' => fn ($q) => $q->where('member_role', 'student')])
            ->withCount('materials', 'assignments', 'quizzes');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($lecturerId) {
            $query->where('created_by', $lecturerId);
        }

        if ($published !== null && $published !== '') {
            $query->where('is_published', (bool) $published);
        }

        $courses = $query->latest()->paginate(10)->withQueryString();
        $lecturers = User::whereHas('role', fn ($q) => $q->where('slug', 'dosen'))->get();

        return view('admin.courses.index', compact('courses', 'lecturers', 'search', 'lecturerId', 'published'));
    }

    public function togglePublish(Course $course): RedirectResponse
    {
        $course->update(['is_published' => ! $course->is_published]);

        $statusText = $course->is_published ? 'dipublikasikan' : 'diarsipkan (draft)';

        return back()->with('success', 'Status mata kuliah "'.$course->name.'" berhasil diubah menjadi '.$statusText.'.');
    }
}
