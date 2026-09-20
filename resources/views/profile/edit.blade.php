<x-app-layout pageHeader="Pengaturan Profil">
    <div class="max-w-4xl mx-auto space-y-8 motion-fade-in">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 p-5 sm:p-6 bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-sm">
            <div class="w-16 h-16 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-2xl overflow-hidden shrink-0">
                @if($user->profile_photo)
                    <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="Avatar" class="w-full h-full object-cover">
                @else
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-xl font-bold text-slate-800 tracking-tight">{{ $user->name }}</h2>
                <p class="text-xs text-slate-400">{{ $user->email }}</p>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold 
                        {{ $user->isAdmin() ? 'bg-rose-100 text-rose-700' : ($user->isDosen() ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                        {{ $user->role?->name ?? 'User' }}
                    </span>
                    <span class="text-xs text-slate-400">• Terakhir login: {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Belum pernah' }}</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Edit Personal Info Form -->
            <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-sm p-5 sm:p-6">
                <h3 class="text-base font-bold text-slate-800 mb-1 flex items-center gap-2">
                    <i data-lucide="user-check" class="w-5 h-5 text-indigo-600"></i>
                    <span>Informasi Data Diri</span>
                </h3>
                <p class="text-xs text-slate-400 mb-6">Perbarui profil dan identitas akun Anda</p>

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Email</label>
                        <input type="email" value="{{ $user->email }}" disabled
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-400 text-sm cursor-not-allowed" />
                        <span class="text-[11px] text-slate-400">Email akun tidak dapat diubah sendiri.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">No. Telepon / WhatsApp</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Foto Profil (Max 2MB)</label>
                        <input type="file" name="profile_photo" accept="image/*"
                            class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 cursor-pointer" />
                    </div>

                    @if($user->isMahasiswa())
                        <div class="pt-3 border-t border-slate-100">
                            <h4 class="text-xs font-bold uppercase text-slate-400 tracking-wider mb-3">Detail Kemahasiswaan</h4>
                            
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Program Studi</label>
                                    <input type="text" name="study_program" value="{{ old('study_program', $user->studentProfile?->study_program) }}"
                                        class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Kelamin</label>
                                    <select name="gender" class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm">
                                        <option value="">Pilih Jenis Kelamin</option>
                                        <option value="male" {{ old('gender', $user->studentProfile?->gender) === 'male' ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="female" {{ old('gender', $user->studentProfile?->gender) === 'female' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Domisili</label>
                                    <textarea name="address" rows="2" class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm">{{ old('address', $user->studentProfile?->address) }}</textarea>
                                </div>
                            </div>
                        </div>
                    @elseif($user->isDosen())
                        <div class="pt-3 border-t border-slate-100">
                            <h4 class="text-xs font-bold uppercase text-slate-400 tracking-wider mb-3">Detail Dosen</h4>
                            
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Gelar Akademik</label>
                                    <input type="text" name="academic_title" value="{{ old('academic_title', $user->lecturerProfile?->academic_title) }}"
                                        placeholder="M.Kom, Ph.D."
                                        class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Program Studi</label>
                                    <input type="text" name="study_program" value="{{ old('study_program', $user->lecturerProfile?->study_program) }}"
                                        class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Biografi Singkat</label>
                                    <textarea name="bio" rows="2" class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm">{{ old('bio', $user->lecturerProfile?->bio) }}</textarea>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="pt-3">
                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-600/25 transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Change Password Card -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 flex flex-col justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-800 mb-1 flex items-center gap-2">
                        <i data-lucide="key" class="w-5 h-5 text-indigo-600"></i>
                        <span>Ubah Kata Sandi</span>
                    </h3>
                    <p class="text-xs text-slate-400 mb-6">Pastikan akun Anda menggunakan kata sandi yang aman dan kuat</p>

                    <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi Saat Ini</label>
                            <input type="password" name="current_password" required
                                placeholder="••••••••"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi Baru</label>
                            <input type="password" name="password" required
                                placeholder="Minimal 8 karakter"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Ulangi Kata Sandi Baru</label>
                            <input type="password" name="password_confirmation" required
                                placeholder="Ulangi kata sandi baru"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" />
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="w-full py-2.5 px-4 rounded-xl text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-300 transition">
                                Perbarui Kata Sandi
                            </button>
                        </div>
                    </form>
                </div>

                <div class="mt-8 p-4 rounded-2xl bg-indigo-50/60 border border-indigo-100 text-indigo-900 text-xs">
                    <p class="font-semibold flex items-center gap-1.5 mb-1">
                        <i data-lucide="shield-check" class="w-4 h-4 text-indigo-600"></i>
                        <span>Tips Keamanan Akun</span>
                    </p>
                    <p class="text-indigo-700/80">Jangan pernah membagikan kredensial login atau kata sandi kepada orang lain.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

