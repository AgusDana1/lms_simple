<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LecturerProfile;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $roleSlug = $request->input('role');
        $status = $request->input('status');

        $query = User::with(['role', 'studentProfile', 'lecturerProfile']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($roleSlug) {
            $query->whereHas('role', fn ($q) => $q->where('slug', $roleSlug));
        }

        if ($status !== null && $status !== '') {
            $query->where('is_active', (bool) $status);
        }

        $users = $query->latest()->paginate(10)->withQueryString();
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles', 'search', 'roleSlug', 'status'));
    }

    public function create(): View
    {
        $roles = Role::all();

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => ['required', 'exists:roles,id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
            'identifier_number' => ['nullable', 'string', 'max:30'],
            'study_program' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $role = Role::find($validated['role_id']);
        if ($role->slug === 'mahasiswa' && filled($validated['identifier_number'])) {
            StudentProfile::create([
                'user_id' => $user->id,
                'student_number' => $validated['identifier_number'],
                'study_program' => $validated['study_program'] ?? null,
                'entry_year' => (int) date('Y'),
            ]);
        } elseif ($role->slug === 'dosen' && filled($validated['identifier_number'])) {
            LecturerProfile::create([
                'user_id' => $user->id,
                'lecturer_number' => $validated['identifier_number'],
                'study_program' => $validated['study_program'] ?? null,
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna '.$user->name.' berhasil didaftarkan!');
    }

    public function edit(User $user): View
    {
        $user->load(['role', 'studentProfile', 'lecturerProfile']);
        $roles = Role::all();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role_id' => ['required', 'exists:roles,id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
            'identifier_number' => ['nullable', 'string', 'max:30'],
            'study_program' => ['nullable', 'string', 'max:100'],
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ];

        if (filled($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);

        $role = Role::find($validated['role_id']);
        if ($role->slug === 'mahasiswa') {
            $user->studentProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'student_number' => $validated['identifier_number'] ?? ($user->studentProfile?->student_number ?? 'NIM-'.$user->id),
                    'study_program' => $validated['study_program'] ?? null,
                ]
            );
        } elseif ($role->slug === 'dosen') {
            $user->lecturerProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'lecturer_number' => $validated['identifier_number'] ?? ($user->lecturerProfile?->lecturer_number ?? 'NIDN-'.$user->id),
                    'study_program' => $validated['study_program'] ?? null,
                ]
            );
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Data pengguna berhasil diperbarui!');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna "'.$name.'" berhasil dihapus.');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', 'Akun '.$user->name.' berhasil '.$statusText.'.');
    }
}
