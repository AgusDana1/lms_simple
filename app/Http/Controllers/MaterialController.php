<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Material;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MaterialController extends Controller
{
    public function store(Request $request, Course $course): RedirectResponse
    {
        $user = Auth::user();
        if ($course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Anda tidak memiliki izin menambahkan materi.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:text,file,video,link'],
            'content' => ['nullable', 'string'],
            'external_url' => ['nullable', 'url', 'max:500'],
            'file' => ['nullable', 'file', 'max:20480'], // max 20MB
            'sort_order' => ['nullable', 'integer'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $filePath = null;
        $fileName = null;

        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');
            $fileName = $uploadedFile->getClientOriginalName();
            $filePath = $uploadedFile->store('courses/materials', 'public');
        }

        Material::create([
            'course_id' => $course->id,
            'created_by' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'content' => $validated['content'] ?? null,
            'external_url' => $validated['external_url'] ?? null,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'sort_order' => $validated['sort_order'] ?? ($course->materials()->max('sort_order') + 1),
            'is_published' => $request->boolean('is_published', true),
        ]);

        return redirect()->route('courses.show', ['course' => $course, 'tab' => 'materials'])
            ->with('success', 'Materi "'.$validated['title'].'" berhasil ditambahkan!');
    }

    public function show(Material $material): View
    {
        $user = Auth::user();
        $course = $material->course;

        $isMember = $course->members()->where('user_id', $user->id)->exists();
        if (! $isMember && $course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Anda harus bergabung dengan mata kuliah ini untuk melihat materi.');
        }

        return view('materials.show', compact('material', 'course'));
    }

    public function update(Request $request, Material $material): RedirectResponse
    {
        $user = Auth::user();
        $course = $material->course;

        if ($course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Anda tidak memiliki izin mengubah materi.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:text,file,video,link'],
            'content' => ['nullable', 'string'],
            'external_url' => ['nullable', 'url', 'max:500'],
            'file' => ['nullable', 'file', 'max:20480'],
            'sort_order' => ['nullable', 'integer'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $updateData = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'content' => $validated['content'] ?? null,
            'external_url' => $validated['external_url'] ?? null,
            'sort_order' => $validated['sort_order'] ?? $material->sort_order,
            'is_published' => $request->boolean('is_published'),
        ];

        if ($request->hasFile('file')) {
            if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
                Storage::disk('public')->delete($material->file_path);
            }

            $uploadedFile = $request->file('file');
            $updateData['file_name'] = $uploadedFile->getClientOriginalName();
            $updateData['file_path'] = $uploadedFile->store('courses/materials', 'public');
        }

        $material->update($updateData);

        return redirect()->route('courses.show', ['course' => $course, 'tab' => 'materials'])
            ->with('success', 'Materi berhasil diperbarui!');
    }

    public function destroy(Material $material): RedirectResponse
    {
        $user = Auth::user();
        $course = $material->course;

        if ($course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Anda tidak memiliki izin menghapus materi.');
        }

        if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
            Storage::disk('public')->delete($material->file_path);
        }

        $title = $material->title;
        $material->delete();

        return redirect()->route('courses.show', ['course' => $course, 'tab' => 'materials'])
            ->with('success', 'Materi "'.$title.'" berhasil dihapus.');
    }

    public function download(Material $material): StreamedResponse
    {
        $user = Auth::user();
        $course = $material->course;

        $isMember = $course->members()->where('user_id', $user->id)->exists();
        if (! $isMember && $course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        if (! $material->file_path || ! Storage::disk('public')->exists($material->file_path)) {
            abort(404, 'Berkas tidak ditemukan di server.');
        }

        return Storage::disk('public')->download($material->file_path, $material->file_name ?? 'materi');
    }
}
