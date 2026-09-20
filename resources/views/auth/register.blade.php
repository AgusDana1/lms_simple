<x-guest-layout title="Pendaftaran Mahasiswa">
    <div class="sm:mx-auto sm:w-full sm:max-w-md motion-fade-in">
        <div class="text-center">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-500 items-center justify-center text-white shadow-xl shadow-indigo-500/30 mb-4">
                <i data-lucide="user-plus" class="w-8 h-8"></i>
            </div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900">
                Pendaftaran Mahasiswa
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Lengkapi data diri Anda untuk memulai pembelajaran
            </p>
        </div>

        <div class="mt-8 bg-white/90 backdrop-blur-xl py-8 px-6 shadow-xl shadow-slate-200/50 rounded-3xl border border-slate-200/70 sm:px-10">
            @if ($errors->any())
                <div class="mb-5 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-start gap-3">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 mt-0.5"></i>
                    <div>
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Nama Lengkap
                    </label>
                    <input id="name" name="name" type="text" required
                        value="{{ old('name') }}"
                        placeholder="Budi Santoso"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition bg-white/80" />
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Alamat Email
                    </label>
                    <input id="email" name="email" type="email" required
                        value="{{ old('email') }}"
                        placeholder="budi@mhs.ac.id"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition bg-white/80" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="student_number" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            NIM / No. Mahasiswa
                        </label>
                        <input id="student_number" name="student_number" type="text" required
                            value="{{ old('student_number') }}"
                            placeholder="NIM-2026001"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition bg-white/80" />
                    </div>
                    <div>
                        <label for="study_program" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Program Studi
                        </label>
                        <input id="study_program" name="study_program" type="text" required
                            value="{{ old('study_program', 'Teknik Informatika') }}"
                            placeholder="Teknik Informatika"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition bg-white/80" />
                    </div>
                </div>

                <div>
                    <label for="phone" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Nomor Telepon (Opsional)
                    </label>
                    <input id="phone" name="phone" type="text"
                        value="{{ old('phone') }}"
                        placeholder="081234567890"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition bg-white/80" />
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Kata Sandi
                    </label>
                    <input id="password" name="password" type="password" required
                        placeholder="Minimal 8 karakter"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition bg-white/80" />
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Konfirmasi Kata Sandi
                    </label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                        placeholder="Ulangi kata sandi"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition bg-white/80" />
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-600/30 transition transform active:scale-98">
                        Daftar Sekarang
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center text-xs text-slate-500">
                Sudah memiliki akun?
                <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-700 ml-1">
                    Masuk ke akun Anda
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>

