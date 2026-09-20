<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Grade;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssignmentController extends Controller
{
    public function store(Request $request, Course $course): RedirectResponse
    {
        $user = Auth::user();
        if ($course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Hanya dosen pengampu yang dapat membuat penugasan.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'max_score' => ['required', 'numeric', 'min:1', 'max:1000'],
            'available_at' => ['nullable', 'date'],
            'due_at' => ['required', 'date'],
            'allow_late_submission' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        Assignment::create([
            'course_id' => $course->id,
            'created_by' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'max_score' => $validated['max_score'],
            'available_at' => $validated['available_at'] ?? now(),
            'due_at' => $validated['due_at'],
            'allow_late_submission' => $request->boolean('allow_late_submission'),
            'is_published' => $request->boolean('is_published', true),
        ]);

        return redirect()->route('courses.show', ['course' => $course, 'tab' => 'assignments'])
            ->with('success', 'Tugas "'.$validated['title'].'" berhasil dibuat!');
    }

    public function show(Assignment $assignment): View
    {
        $user = Auth::user();
        $course = $assignment->course;

        $isMember = $course->members()->where('user_id', $user->id)->exists();
        $isLecturer = $course->created_by === $user->id || $user->isAdmin();

        if (! $isMember && ! $isLecturer) {
            abort(403, 'Akses ditolak.');
        }

        $studentSubmission = null;
        $allSubmissions = collect();

        if ($user->isMahasiswa()) {
            $studentSubmission = $assignment->submissions()
                ->where('student_id', $user->id)
                ->with('grader')
                ->first();
        }

        if ($isLecturer) {
            $allSubmissions = $assignment->submissions()
                ->with(['student.studentProfile', 'grader'])
                ->latest('submitted_at')
                ->get();
        }

        return view('assignments.show', compact(
            'assignment',
            'course',
            'isLecturer',
            'studentSubmission',
            'allSubmissions'
        ));
    }

    public function update(Request $request, Assignment $assignment): RedirectResponse
    {
        $user = Auth::user();
        $course = $assignment->course;

        if ($course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Anda tidak memiliki izin mengubah penugasan.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'max_score' => ['required', 'numeric', 'min:1', 'max:1000'],
            'available_at' => ['nullable', 'date'],
            'due_at' => ['required', 'date'],
            'allow_late_submission' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $assignment->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'max_score' => $validated['max_score'],
            'available_at' => $validated['available_at'] ?? $assignment->available_at,
            'due_at' => $validated['due_at'],
            'allow_late_submission' => $request->boolean('allow_late_submission'),
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()->route('assignments.show', $assignment)
            ->with('success', 'Penugasan berhasil diperbarui!');
    }

    public function destroy(Assignment $assignment): RedirectResponse
    {
        $user = Auth::user();
        $course = $assignment->course;

        if ($course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Anda tidak memiliki izin menghapus penugasan.');
        }

        $title = $assignment->title;
        $assignment->delete();

        return redirect()->route('courses.show', ['course' => $course, 'tab' => 'assignments'])
            ->with('success', 'Tugas "'.$title.'" berhasil dihapus.');
    }

    public function submit(Request $request, Assignment $assignment): RedirectResponse
    {
        $user = Auth::user();

        // Check if available
        if ($assignment->available_at && now()->lt($assignment->available_at)) {
            return back()->with('error', 'Penugasan ini belum dibuka.');
        }

        $isLate = false;
        if ($assignment->due_at && now()->gt($assignment->due_at)) {
            if (! $assignment->allow_late_submission) {
                return back()->with('error', 'Batas waktu pengumpulan telah berakhir dan keterlambatan tidak diizinkan.');
            }
            $isLate = true;
        }

        $validated = $request->validate([
            'content' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:20480'], // max 20MB
        ]);

        if (empty($validated['content']) && ! $request->hasFile('file')) {
            return back()->with('error', 'Harap isi teks jawaban atau unggah berkas tugas Anda.');
        }

        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $user->id)
            ->first();

        $filePath = $submission?->file_path;
        $fileName = $submission?->file_name;

        if ($request->hasFile('file')) {
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            $uploaded = $request->file('file');
            $fileName = $uploaded->getClientOriginalName();
            $filePath = $uploaded->store('courses/assignments/submissions', 'public');
        }

        AssignmentSubmission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'student_id' => $user->id],
            [
                'content' => $validated['content'] ?? null,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'submitted_at' => now(),
                'is_late' => $isLate,
            ]
        );

        return back()->with('success', 'Tugas Anda berhasil dikumpulkan!'.($isLate ? ' (Status: Terlambat)' : ''));
    }

    public function grade(Request $request, AssignmentSubmission $submission): RedirectResponse
    {
        $user = Auth::user();
        $assignment = $submission->assignment;
        $course = $assignment->course;

        if ($course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Hanya dosen pengampu yang dapat memberikan penilaian.');
        }

        $validated = $request->validate([
            'score' => ['required', 'numeric', 'min:0', 'max:'.$assignment->max_score],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ]);

        $submission->update([
            'score' => $validated['score'],
            'feedback' => $validated['feedback'] ?? null,
            'graded_by' => $user->id,
            'graded_at' => now(),
        ]);

        // Also record in Grade table for unified course gradebook
        Grade::updateOrCreate(
            [
                'course_id' => $course->id,
                'student_id' => $submission->student_id,
                'component' => 'Tugas: '.$assignment->title,
            ],
            [
                'score' => $validated['score'],
                'notes' => $validated['feedback'] ?? null,
                'graded_by' => $user->id,
            ]
        );

        return back()->with('success', 'Nilai dan umpan balik berhasil disimpan untuk mahasiswa.');
    }

    public function downloadSubmission(AssignmentSubmission $submission): StreamedResponse
    {
        $user = Auth::user();
        $assignment = $submission->assignment;
        $course = $assignment->course;

        $isLecturer = $course->created_by === $user->id || $user->isAdmin();
        $isOwner = $submission->student_id === $user->id;

        if (! $isLecturer && ! $isOwner) {
            abort(403, 'Akses ditolak.');
        }

        if (! $submission->file_path || ! Storage::disk('public')->exists($submission->file_path)) {
            abort(404, 'Berkas submission tidak ditemukan.');
        }

        return Storage::disk('public')->download($submission->file_path, $submission->file_name ?? 'submission');
    }
}
