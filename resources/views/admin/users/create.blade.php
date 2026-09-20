<x-app-layout pageHeader="Tambah Pengguna Baru">
    <div class="max-w-2xl mx-auto motion-fade-in">
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
            <div class="mb-6 pb-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Registrasi Pengguna Sistem</h2>
                    <p class="text-xs text-slate-400 mt-1">Daftarkan akun Admin, Dosen, atau Mahasiswa secara manual</p>
                </div>
                <a href="{{ route('admin.users.index') }}" class="px-3.5 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                    Kembali
                </a>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Nama Lengkap & Gelar (jika ada)"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="email@lms.test"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Peran / Role</label>
                        <select name="role_id" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm">
                            @foreach($roles as $r)
                                <option value="{{ $r->id }}" {{ old('role_id') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">No. Identitas (NIM/NIDN)</label>
                        <input type="text" name="identifier_number" value="{{ old('identifier_number') }}" placeholder="NIM/NIDN..."
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Program Studi</label>
                        <input type="text" name="study_program" value="{{ old('study_program', 'Teknik Informatika') }}" placeholder="Program Studi"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">No. Telepon (Opsional)</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="0812345..."
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi Awal</label>
                    <input type="password" name="password" required placeholder="Minimal 8 karakter"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                </div>

                <div class="pt-2">
                    <label class="flex items-center gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-200 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded text-indigo-600">
                        <span class="text-xs font-bold text-slate-700">Status Akun Aktif (Dapat langsung login)</span>
                    </label>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3">
                    <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/25 transition">
                        Simpan Pengguna
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

