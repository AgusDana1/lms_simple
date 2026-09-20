<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Discussion;
use App\Models\DiscussionReply;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DiscussionController extends Controller
{
    public function store(Request $request, Course $course): RedirectResponse
    {
        $user = Auth::user();

        $isMember = $course->members()->where('user_id', $user->id)->exists();
        if (! $isMember && $course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Anda harus bergabung dengan mata kuliah untuk memulai diskusi.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        $discussion = Discussion::create([
            'course_id' => $course->id,
            'user_id' => $user->id,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'is_locked' => false,
        ]);

        return redirect()->route('discussions.show', $discussion)
            ->with('success', 'Topik diskusi berhasil dibuat!');
    }

    public function show(Discussion $discussion): View
    {
        $user = Auth::user();
        $course = $discussion->course;

        $isMember = $course->members()->where('user_id', $user->id)->exists();
        $isLecturer = $course->created_by === $user->id || $user->isAdmin();

        if (! $isMember && ! $isLecturer) {
            abort(403, 'Akses ditolak.');
        }

        $discussion->load([
            'user.role',
            'user.studentProfile',
            'user.lecturerProfile',
            'replies' => fn ($q) => $q->whereNull('parent_id')->with(['user.role', 'replies.user.role'])->oldest(),
        ]);

        return view('discussions.show', compact('discussion', 'course', 'isLecturer'));
    }

    public function reply(Request $request, Discussion $discussion): RedirectResponse
    {
        $user = Auth::user();
        $course = $discussion->course;

        if ($discussion->is_locked) {
            return back()->with('error', 'Topik diskusi ini telah dikunci.');
        }

        $isMember = $course->members()->where('user_id', $user->id)->exists();
        if (! $isMember && $course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:3000'],
            'parent_id' => ['nullable', 'exists:discussion_replies,id'],
        ]);

        DiscussionReply::create([
            'discussion_id' => $discussion->id,
            'user_id' => $user->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'Balasan berhasil dikirim!');
    }

    public function toggleLock(Discussion $discussion): RedirectResponse
    {
        $user = Auth::user();
        $course = $discussion->course;

        if ($course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $discussion->update([
            'is_locked' => ! $discussion->is_locked,
        ]);

        $status = $discussion->is_locked ? 'dikunci' : 'dibuka kembali';

        return back()->with('success', 'Topik diskusi berhasil '.$status.'.');
    }

    public function destroy(Discussion $discussion): RedirectResponse
    {
        $user = Auth::user();
        $course = $discussion->course;

        $isOwner = $discussion->user_id === $user->id;
        $isLecturer = $course->created_by === $user->id || $user->isAdmin();

        if (! $isOwner && ! $isLecturer) {
            abort(403, 'Akses ditolak.');
        }

        $discussion->delete();

        return redirect()->route('courses.show', ['course' => $course, 'tab' => 'discussions'])
            ->with('success', 'Topik diskusi berhasil dihapus.');
    }
}
