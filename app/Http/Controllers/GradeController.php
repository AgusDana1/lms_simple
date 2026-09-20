<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Grade;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GradeController extends Controller
{
    public function index(Course $course): View
    {
        $user = Auth::user();

        if ($course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Hanya dosen pengampu yang dapat mengakses buku nilai.');
        }

        $students = $course->members()
            ->where('member_role', 'student')
            ->with(['user.studentProfile'])
            ->get()
            ->pluck('user');

        $grades = Grade::where('course_id', $course->id)
            ->with(['student', 'grader'])
            ->get();

        // Get distinct components
        $components = $grades->pluck('component')->unique()->values();

        // Map student -> component -> score
        $studentGrades = [];
        foreach ($students as $student) {
            $studentGrades[$student->id] = [
                'user' => $student,
                'scores' => [],
                'total' => 0,
                'count' => 0,
            ];

            foreach ($components as $component) {
                $g = $grades->where('student_id', $student->id)->where('component', $component)->first();
                $studentGrades[$student->id]['scores'][$component] = $g?->score;
                if ($g && ! is_null($g->score)) {
                    $studentGrades[$student->id]['total'] += $g->score;
                    $studentGrades[$student->id]['count']++;
                }
            }

            $count = $studentGrades[$student->id]['count'];
            $studentGrades[$student->id]['average'] = $count > 0 ? round($studentGrades[$student->id]['total'] / $count, 2) : 0;
        }

        return view('grades.index', compact('course', 'students', 'components', 'studentGrades'));
    }

    public function store(Request $request, Course $course): RedirectResponse
    {
        $user = Auth::user();

        if ($course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'student_id' => ['required', 'exists:users,id'],
            'component' => ['required', 'string', 'max:100'],
            'score' => ['required', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        Grade::updateOrCreate(
            [
                'course_id' => $course->id,
                'student_id' => $validated['student_id'],
                'component' => $validated['component'],
            ],
            [
                'score' => $validated['score'],
                'notes' => $validated['notes'] ?? null,
                'graded_by' => $user->id,
            ]
        );

        return back()->with('success', 'Komponen nilai berhasil disimpan.');
    }

    public function myGrades(): View
    {
        $user = Auth::user();

        $enrolledCourses = $user->enrolledCourses()->with('creator')->get();
        $courseIds = $enrolledCourses->pluck('id');

        $grades = Grade::where('student_id', $user->id)
            ->whereIn('course_id', $courseIds)
            ->with(['course.creator', 'grader'])
            ->get();

        $gradesByCourse = $grades->groupBy('course_id');

        return view('grades.my-grades', compact('enrolledCourses', 'gradesByCourse'));
    }
}
