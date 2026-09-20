<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    public function store(Request $request, Course $course): RedirectResponse
    {
        $user = Auth::user();

        if ($course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Hanya dosen pengampu yang dapat membuat pengumuman.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        Announcement::create([
            'course_id' => $course->id,
            'created_by' => $user->id,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'published_at' => now(),
        ]);

        return redirect()->route('courses.show', ['course' => $course, 'tab' => 'announcements'])
            ->with('success', 'Pengumuman berhasil dipublikasikan!');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $user = Auth::user();
        $course = $announcement->course;

        if ($course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $announcement->delete();

        return redirect()->route('courses.show', ['course' => $course, 'tab' => 'announcements'])
            ->with('success', 'Pengumuman berhasil dihapus.');
    }
}
