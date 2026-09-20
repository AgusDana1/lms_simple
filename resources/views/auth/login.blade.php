<x-guest-layout title="Masuk ke LMS">
    <div class="sm:mx-auto sm:w-full sm:max-w-md motion-fade-in">
        <div class="text-center">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-500 items-center justify-center text-white shadow-xl shadow-indigo-500/30 mb-4">
                <i data-lucide="graduation-cap" class="w-8 h-8"></i>
            </div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900">
                Masuk ke Portal LMS
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Akses materi kuliah, kuis interaktif, dan tugas Anda
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

            <form id="login-form" method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Alamat Email
                    </label>
                    <div class="relative">
                        <input id="email" name="email" type="email" autocomplete="email" required
                            value="{{ old('email') }}"
                            placeholder="nama@lms.test"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition bg-white/80" />
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-semibold text-slate-700">
                            Kata Sandi
                        </label>
                    </div>
                    <div class="relative">
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                            placeholder="••••••••"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition bg-white/80" />
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500">
                        <span class="text-slate-600">Ingat Saya</span>
                    </label>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-600/30 transition transform active:scale-98">
                        Masuk
                    </button>
                </div>
            </form>

            <!-- Quick 1-Click Demo Login Bar -->
            <div class="mt-6 pt-6 border-t border-slate-200/80">
                <p class="text-[11px] font-semibold text-slate-400 text-center uppercase tracking-wider mb-3">
                    Akses Cepat Pengujian (Demo)
                </p>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" onclick="fillAndSubmit('admin@lms.test', 'password')"
                        class="p-2 text-center rounded-xl bg-slate-100 hover:bg-rose-50 hover:border-rose-200 border border-slate-200 text-xs font-medium text-slate-700 hover:text-rose-700 transition">
                        <span class="block font-semibold">Admin</span>
                        <span class="text-[10px] text-slate-400">Full Akses</span>
                    </button>
                    <button type="button" onclick="fillAndSubmit('dosen@lms.test', 'password')"
                        class="p-2 text-center rounded-xl bg-slate-100 hover:bg-amber-50 hover:border-amber-200 border border-slate-200 text-xs font-medium text-slate-700 hover:text-amber-700 transition">
                        <span class="block font-semibold">Dosen</span>
                        <span class="text-[10px] text-slate-400">Kelola Kuliah</span>
                    </button>
                    <button type="button" onclick="fillAndSubmit('mahasiswa@lms.test', 'password')"
                        class="p-2 text-center rounded-xl bg-slate-100 hover:bg-emerald-50 hover:border-emerald-200 border border-slate-200 text-xs font-medium text-slate-700 hover:text-emerald-700 transition">
                        <span class="block font-semibold">Mahasiswa</span>
                        <span class="text-[10px] text-slate-400">Tugas & Kuis</span>
                    </button>
                </div>
            </div>

            <div class="mt-6 text-center text-xs text-slate-500">
                Belum memiliki akun mahasiswa?
                <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:text-indigo-700 ml-1">
                    Daftar di sini
                </a>
            </div>
        </div>
    </div>

    <script>
        function fillAndSubmit(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
            document.getElementById('login-form').submit();
        }
    </script>
</x-guest-layout>

