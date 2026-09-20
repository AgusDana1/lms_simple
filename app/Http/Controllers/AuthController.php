<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()->withErrors(['email' => 'Email atau password yang Anda masukkan salah.'])->onlyInput('email');
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();

            return back()->withErrors(['email' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi administrator.']);
        }

        $request->session()->regenerate();

        $user->update([
            'last_login_at' => now(),
        ]);

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Selamat datang kembali, '.$user->name.'!');
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'student_number' => ['required', 'string', 'max:30', 'unique:student_profiles,student_number'],
            'study_program' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $studentRole = Role::firstOrCreate(['slug' => 'mahasiswa'], [
            'name' => 'Mahasiswa',
            'description' => 'Mengikuti course, materi, tugas, dan kuis.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $studentRole->id,
            'phone' => $validated['phone'] ?? null,
            'is_active' => true,
            'last_login_at' => now(),
        ]);

        StudentProfile::create([
            'user_id' => $user->id,
            'student_number' => $validated['student_number'],
            'study_program' => $validated['study_program'],
            'entry_year' => (int) date('Y'),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'Pendaftaran berhasil! Selamat datang di LMS, '.$user->name.'.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Anda telah berhasil keluar.');
    }

    public function profile(): View
    {
        $user = Auth::user()->load(['role', 'studentProfile', 'lecturerProfile']);

        return view('profile.edit', compact('user'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            // Profile specific
            'study_program' => ['nullable', 'string', 'max:100'],
            'academic_title' => ['nullable', 'string', 'max:50'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'address' => ['nullable', 'string', 'max:500'],
            'gender' => ['nullable', 'in:male,female'],
            'birth_date' => ['nullable', 'date'],
        ]);

        $userData = [
            'name' => $validated['name'],
            'phone' => $validated['phone'],
        ];

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $userData['profile_photo'] = $path;
        }

        $user->update($userData);

        if ($user->isMahasiswa()) {
            $user->studentProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'study_program' => $validated['study_program'] ?? null,
                    'gender' => $validated['gender'] ?? null,
                    'birth_date' => $validated['birth_date'] ?? null,
                    'address' => $validated['address'] ?? null,
                ]
            );
        } elseif ($user->isDosen()) {
            $user->lecturerProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'academic_title' => $validated['academic_title'] ?? null,
                    'study_program' => $validated['study_program'] ?? null,
                    'bio' => $validated['bio'] ?? null,
                ]
            );
        }

        return back()->with('success', 'Profil Anda berhasil diperbarui!');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Kata sandi berhasil diubah!');
    }
}
