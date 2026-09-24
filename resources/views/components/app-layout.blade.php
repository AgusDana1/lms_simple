<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'LMS' }} - LMS Prima</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800 selection:bg-indigo-500 selection:text-white flex overflow-hidden">

    <!-- Flash Toast Notifications -->
    <div id="toast-container" class="fixed top-4 right-4 sm:top-5 sm:right-5 z-50 flex flex-col space-y-3 pointer-events-none max-w-[calc(100vw-2rem)] sm:max-w-md w-full">
        @if (session('success'))
            <div class="toast-alert pointer-events-auto flex items-start gap-3 p-4 bg-white/95 backdrop-blur-md rounded-2xl shadow-xl border border-emerald-200 text-emerald-900 transition transform">
                <div class="p-1.5 bg-emerald-100 text-emerald-600 rounded-xl">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                </div>
                <div class="flex-1 text-sm font-medium pt-0.5">
                    {{ session('success') }}
                </div>
                <button type="button" class="toast-close text-slate-400 hover:text-slate-600 p-1">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="toast-alert pointer-events-auto flex items-start gap-3 p-4 bg-white/95 backdrop-blur-md rounded-2xl shadow-xl border border-rose-200 text-rose-900 transition transform">
                <div class="p-1.5 bg-rose-100 text-rose-600 rounded-xl">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                </div>
                <div class="flex-1 text-sm font-medium pt-0.5">
                    {{ session('error') }}
                </div>
                <button type="button" class="toast-close text-slate-400 hover:text-slate-600 p-1">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        @endif

        @if (session('warning'))
            <div class="toast-alert pointer-events-auto flex items-start gap-3 p-4 bg-white/95 backdrop-blur-md rounded-2xl shadow-xl border border-amber-200 text-amber-900 transition transform">
                <div class="p-1.5 bg-amber-100 text-amber-600 rounded-xl">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                </div>
                <div class="flex-1 text-sm font-medium pt-0.5">
                    {{ session('warning') }}
                </div>
                <button type="button" class="toast-close text-slate-400 hover:text-slate-600 p-1">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        @endif

        @if (session('info'))
            <div class="toast-alert pointer-events-auto flex items-start gap-3 p-4 bg-white/95 backdrop-blur-md rounded-2xl shadow-xl border border-indigo-200 text-indigo-900 transition transform">
                <div class="p-1.5 bg-indigo-100 text-indigo-600 rounded-xl">
                    <i data-lucide="info" class="w-5 h-5"></i>
                </div>
                <div class="flex-1 text-sm font-medium pt-0.5">
                    {{ session('info') }}
                </div>
                <button type="button" class="toast-close text-slate-400 hover:text-slate-600 p-1">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        @endif
    </div>

    <!-- Mobile/Tablet Sidebar Backdrop Overlay -->
    <div id="sidebar-backdrop" class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-40 hidden lg:hidden opacity-0 transition-opacity duration-300"></div>

    <!-- Sidebar Navigation -->
    <aside id="app-sidebar" class="fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] bg-slate-900 text-slate-300 flex flex-col shrink-0 transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 lg:static lg:w-64 shadow-2xl lg:shadow-none">
        <!-- Brand Header -->
        <div class="h-16 lg:h-18 px-5 lg:px-6 flex items-center justify-between border-b border-slate-800/80">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                <div class="w-9 h-9 lg:w-10 lg:h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white font-bold shadow-lg shadow-indigo-500/25 group-hover:scale-105 transition">
                    <i data-lucide="graduation-cap" class="w-5 h-5 lg:w-6 lg:h-6"></i>
                </div>
                <div>
                    <span class="text-base lg:text-lg font-bold text-white tracking-tight">LMS Prima</span>
                    <span class="block text-[9px] lg:text-[10px] uppercase font-semibold tracking-wider text-indigo-400">Prima Akademik</span>
                </div>
            </a>
            <button type="button" id="sidebar-close" class="p-1.5 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg lg:hidden transition" aria-label="Tutup Menu">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
            <div class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                Menu Utama
            </div>

            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('courses.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('courses.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <i data-lucide="book-open" class="w-5 h-5"></i>
                <span>Mata Kuliah</span>
            </a>

            @if(auth()->user()->isMahasiswa())
                <a href="{{ route('grades.my-grades') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('grades.my-grades') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i data-lucide="award" class="w-5 h-5"></i>
                    <span>Nilai Saya</span>
                </a>
            @endif

            @if(auth()->user()->isAdmin())
                <div class="pt-5 px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                    Administrasi
                </div>

                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.users.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    <span>Kelola Pengguna</span>
                </a>

                <a href="{{ route('admin.courses.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.courses.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i data-lucide="shield-alert" class="w-5 h-5"></i>
                    <span>Pengawasan Course</span>
                </a>
            @endif

            <div class="pt-5 px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                Akun & Preferensi
            </div>

            <a href="{{ route('profile') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('profile') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <i data-lucide="user" class="w-5 h-5"></i>
                <span>Profil Pengguna</span>
            </a>
        </nav>

        <!-- User Card in Sidebar -->
        <div class="p-4 border-t border-slate-800/80 bg-slate-950/40">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 overflow-hidden flex items-center justify-center shrink-0">
                    @if(auth()->user()->profile_photo)
                        <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}" alt="Avatar" class="w-full h-full object-cover">
                    @else
                        <span class="text-sm font-bold text-indigo-400 uppercase">
                            {{ substr(auth()->user()->name, 0, 2) }}
                        </span>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium 
                        {{ auth()->user()->isAdmin() ? 'bg-rose-500/20 text-rose-300' : (auth()->user()->isDosen() ? 'bg-amber-500/20 text-amber-300' : 'bg-emerald-500/20 text-emerald-300') }}">
                        {{ auth()->user()->role?->name ?? 'User' }}
                    </span>
                </div>
                <button type="button" onclick="openLogoutModal()" title="Keluar" class="p-2 text-slate-400 hover:text-rose-400 hover:bg-slate-800 rounded-lg transition" aria-label="Keluar">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Top Navbar -->
        <header class="h-16 lg:h-18 bg-white/80 backdrop-blur-md border-b border-slate-200/80 px-4 sm:px-6 lg:px-8 flex items-center justify-between shrink-0 z-20">
            <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                <button type="button" id="sidebar-toggle" class="p-2 -ml-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl lg:hidden shrink-0 transition" aria-label="Buka Menu">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
                <h1 class="text-sm sm:text-base lg:text-lg font-bold text-slate-800 tracking-tight truncate">
                    {{ $pageHeader ?? 'Learning Management System' }}
                </h1>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                @if(auth()->user()->isDosen() || auth()->user()->isAdmin())
                    <a href="{{ route('courses.create') }}" class="inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-3.5 py-1.5 sm:py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm shadow-indigo-600/25 transition">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span class="hidden sm:inline">Buat Kelas</span>
                    </a>
                @endif

                <div class="h-6 sm:h-8 w-px bg-slate-200 mx-0.5 sm:mx-1"></div>

                <div class="text-right hidden sm:block">
                    <span class="block text-xs font-semibold text-slate-700 leading-tight">{{ auth()->user()->name }}</span>
                    <span class="block text-[11px] text-slate-400 leading-tight">{{ auth()->user()->email }}</span>
                </div>
            </div>
        </header>

        <!-- Scrollable Page Content -->
        <main class="flex-1 overflow-y-auto overflow-x-hidden p-4 sm:p-6 lg:p-8 bg-slate-50 relative">
            {{ $slot }}
        </main>
    </div>

    <!-- Modern Logout Confirmation Modal -->
    <div id="logout-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="modal-backdrop fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeLogoutModal()"></div>
        <div class="modal-panel relative bg-white rounded-3xl shadow-2xl max-w-sm sm:max-w-md w-full p-6 sm:p-7 z-10 border border-slate-100/80 text-center">
            <!-- Close Button -->
            <button type="button" onclick="closeLogoutModal()" class="absolute top-4 right-4 p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition" aria-label="Tutup">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>

            <!-- Icon Header -->
            <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 border border-rose-100/80 mx-auto flex items-center justify-center mb-4 shadow-sm shadow-rose-500/10">
                <i data-lucide="log-out" class="w-7 h-7 stroke-[2.2]"></i>
            </div>

            <!-- Title & Description -->
            <h3 class="text-lg font-bold text-slate-800">Konfirmasi Keluar</h3>
            <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                Apakah Anda yakin ingin keluar dari akun ini? Sesi pembelajaran Anda saat ini akan diakhiri.
            </p>

            <!-- Active User Preview Card -->
            <div class="my-5 p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center gap-3 text-left">
                <div class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 overflow-hidden flex items-center justify-center shrink-0">
                    @if(auth()->user()->profile_photo)
                        <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}" alt="Avatar" class="w-full h-full object-cover">
                    @else
                        <span class="text-xs font-bold text-indigo-400 uppercase">
                            {{ substr(auth()->user()->name, 0, 2) }}
                        </span>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold text-slate-800 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[11px] text-slate-400 truncate">{{ auth()->user()->email }}</p>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold shrink-0
                    {{ auth()->user()->isAdmin() ? 'bg-rose-100 text-rose-700' : (auth()->user()->isDosen() ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                    {{ auth()->user()->role?->name ?? 'User' }}
                </span>
            </div>

            <!-- Modal Action Buttons -->
            <div class="flex items-center gap-3">
                <button type="button" onclick="closeLogoutModal()" class="flex-1 py-2.5 px-4 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 transition">
                    Batal
                </button>
                <form method="POST" action="{{ route('logout') }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 shadow-md shadow-rose-600/25 transition transform active:scale-98">
                        <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                        <span>Ya, Keluar</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openLogoutModal() {
            if (typeof window.openModal === 'function') {
                window.openModal('logout-modal');
            } else {
                const modal = document.getElementById('logout-modal');
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }
            }
        }

        function closeLogoutModal() {
            if (typeof window.closeModal === 'function') {
                window.closeModal('logout-modal');
            } else {
                const modal = document.getElementById('logout-modal');
                if (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLogoutModal();
            }
        });
    </script>
</body>
</html>
