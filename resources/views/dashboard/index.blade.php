<x-app-layout pageHeader="Dashboard">
    <div class="space-y-6 sm:space-y-8">
        <!-- Hero Greeting Banner with Motion -->
        <div class="relative overflow-hidden rounded-2xl sm:rounded-3xl bg-gradient-to-r from-indigo-700 via-indigo-600 to-violet-600 text-white p-5 sm:p-8 shadow-xl shadow-indigo-600/20 motion-fade-in">
            <div class="relative z-10 max-w-2xl">
                <div class="inline-flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-1 rounded-full bg-white/15 backdrop-blur-md text-[11px] sm:text-xs font-semibold text-indigo-100 mb-2.5 sm:mb-3 border border-white/20">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-300"></i>
                    <span>Portal Pembelajaran Modern</span>
                </div>
                <h1 class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight">
                    Halo, {{ $user->name }}! 
                </h1>
                <p class="mt-2 text-indigo-100 text-xs sm:text-sm leading-relaxed">
                    @if($user->isAdmin())
                        Anda memiliki hak akses Administrator penuh untuk mengelola pengguna, memantau mata kuliah, dan meninjau kesehatan platform LMS.
                    @elseif($user->isDosen())
                        Kelola silabus, unggah materi kuliah, tinjau pengumpulan tugas mahasiswa, dan buat kuis interaktif dengan mudah.
                    @else
                        Selamat datang di ruang belajar Anda. Pantau materi baru, selesaikan tugas sebelum batas waktu, dan kerjakan kuis tepat waktu.
                    @endif
                </p>
            </div>

            <!-- Background decorative shape -->
            <div class="absolute -right-10 -bottom-10 w-60 h-60 sm:w-72 sm:h-72 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="absolute right-12 top-6 opacity-20 hidden lg:block pointer-events-none">
                <i data-lucide="graduation-cap" class="w-40 h-40"></i>
            </div>
        </div>

        <!-- 4 Stats Cards Grid (Staggered Motion) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-5">
            @if($user->isAdmin())
                <div class="motion-stat bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center gap-4">
                    <div class="p-3.5 bg-indigo-50 text-indigo-600 rounded-2xl">
                        <i data-lucide="users" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Pengguna</p>
                        <h3 class="text-2xl font-black text-slate-800">{{ $totalUsers }}</h3>
                    </div>
                </div>

                <div class="motion-stat bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center gap-4">
                    <div class="p-3.5 bg-amber-50 text-amber-600 rounded-2xl">
                        <i data-lucide="user-check" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Dosen</p>
                        <h3 class="text-2xl font-black text-slate-800">{{ $totalLecturers }}</h3>
                    </div>
                </div>

                <div class="motion-stat bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center gap-4">
                    <div class="p-3.5 bg-emerald-50 text-emerald-600 rounded-2xl">
                        <i data-lucide="graduation-cap" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Mahasiswa</p>
                        <h3 class="text-2xl font-black text-slate-800">{{ $totalStudents }}</h3>
                    </div>
                </div>

                <div class="motion-stat bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center gap-4">
                    <div class="p-3.5 bg-sky-50 text-sky-600 rounded-2xl">
                        <i data-lucide="book-open" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Mata Kuliah</p>
                        <h3 class="text-2xl font-black text-slate-800">{{ $publishedCourses }}/{{ $totalCourses }}</h3>
                    </div>
                </div>
            @elseif($user->isDosen())
                <div class="motion-stat bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center gap-4">
                    <div class="p-3.5 bg-indigo-50 text-indigo-600 rounded-2xl">
                        <i data-lucide="book-open" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Mata Kuliah Diampu</p>
                        <h3 class="text-2xl font-black text-slate-800">{{ $totalCourses }}</h3>
                    </div>
                </div>

                <div class="motion-stat bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center gap-4">
                    <div class="p-3.5 bg-emerald-50 text-emerald-600 rounded-2xl">
                        <i data-lucide="users" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Mahasiswa Terdaftar</p>
                        <h3 class="text-2xl font-black text-slate-800">{{ $totalStudents }}</h3>
                    </div>
                </div>

                <div class="motion-stat bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center gap-4">
                    <div class="p-3.5 bg-rose-50 text-rose-600 rounded-2xl">
                        <i data-lucide="file-check" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tugas Perlu Dinilai</p>
                        <h3 class="text-2xl font-black text-slate-800">{{ $pendingSubmissions->count() }}</h3>
                    </div>
                </div>

                <div class="motion-stat bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center gap-4">
                    <div class="p-3.5 bg-amber-50 text-amber-600 rounded-2xl">
                        <i data-lucide="timer" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Kuis Aktif</p>
                        <h3 class="text-2xl font-black text-slate-800">{{ $activeQuizzes->count() }}</h3>
                    </div>
                </div>
            @else
                <div class="motion-stat bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center gap-4">
                    <div class="p-3.5 bg-indigo-50 text-indigo-600 rounded-2xl">
                        <i data-lucide="book-open" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Mata Kuliah Diikuti</p>
                        <h3 class="text-2xl font-black text-slate-800">{{ $totalEnrolledCourses }}</h3>
                    </div>
                </div>

                <div class="motion-stat bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center gap-4">
                    <div class="p-3.5 bg-rose-50 text-rose-600 rounded-2xl">
                        <i data-lucide="clock" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tenggat Mendatang</p>
                        <h3 class="text-2xl font-black text-slate-800">{{ $upcomingAssignments->count() }}</h3>
                    </div>
                </div>

                <div class="motion-stat bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center gap-4">
                    <div class="p-3.5 bg-amber-50 text-amber-600 rounded-2xl">
                        <i data-lucide="help-circle" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Kuis Tersedia</p>
                        <h3 class="text-2xl font-black text-slate-800">{{ $availableQuizzes->count() }}</h3>
                    </div>
                </div>

                <div class="motion-stat bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center gap-4">
                    <div class="p-3.5 bg-emerald-50 text-emerald-600 rounded-2xl">
                        <i data-lucide="bell" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Pengumuman Baru</p>
                        <h3 class="text-2xl font-black text-slate-800">{{ $recentAnnouncements->count() }}</h3>
                    </div>
                </div>
            @endif
        </div>

        @if($user->isAdmin())
            <!-- Grafik Perkembangan Dosen & Mahasiswa -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-5 sm:p-7 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-slate-100">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl">
                                <i data-lucide="trending-up" class="w-5 h-5"></i>
                            </span>
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-slate-800">Tren Pertumbuhan & Aktivitas Pengguna</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Pertumbuhan data Dosen dan Mahasiswa yang terdaftar & aktif di LMS</p>
                            </div>
                        </div>
                    </div>

                    <!-- Legend & Active Badges -->
                    <div class="flex flex-wrap items-center gap-3 text-xs font-semibold">
                        <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-indigo-50/80 text-indigo-700 border border-indigo-100/60">
                            <span class="w-3 h-3 rounded-full bg-indigo-600"></span>
                            <span>Mahasiswa: <strong class="text-slate-800">{{ $totalStudents }}</strong></span>
                            <span class="ml-1 text-[11px] font-bold text-indigo-500">({{ $activeStudentsCount ?? 0 }} Aktif)</span>
                        </div>
                        <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-50/80 text-amber-700 border border-amber-100/60">
                            <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                            <span>Dosen: <strong class="text-slate-800">{{ $totalLecturers }}</strong></span>
                            <span class="ml-1 text-[11px] font-bold text-amber-500">({{ $activeLecturersCount ?? 0 }} Aktif)</span>
                        </div>
                    </div>
                </div>

                @php
                    $maxDataVal = max(
                        max($chartStudents ?? [1]),
                        max($chartLecturers ?? [1]),
                        5
                    );
                    $chartPointsCount = count($chartLabels ?? [1]);
                    $svgWidth = 700;
                    $svgHeight = 220;
                    $padX = 45;
                    $padYTop = 30;
                    $padYBottom = 35;
                    $chartW = $svgWidth - ($padX * 2);
                    $chartH = $svgHeight - $padYTop - $padYBottom;

                    $stepX = $chartPointsCount > 1 ? $chartW / ($chartPointsCount - 1) : $chartW;

                    $studentPoints = [];
                    $lecturerPoints = [];

                    foreach (($chartStudents ?? []) as $idx => $val) {
                        $x = round($padX + ($idx * $stepX), 1);
                        $y = round($padYTop + $chartH - (($val / $maxDataVal) * $chartH), 1);
                        $studentPoints[] = ['x' => $x, 'y' => $y, 'val' => $val, 'label' => $chartLabels[$idx] ?? ''];
                    }

                    foreach (($chartLecturers ?? []) as $idx => $val) {
                        $x = round($padX + ($idx * $stepX), 1);
                        $y = round($padYTop + $chartH - (($val / $maxDataVal) * $chartH), 1);
                        $lecturerPoints[] = ['x' => $x, 'y' => $y, 'val' => $val, 'label' => $chartLabels[$idx] ?? ''];
                    }

                    $studentPath = '';
                    $studentArea = '';
                    if (!empty($studentPoints)) {
                        $studentPath = 'M ' . $studentPoints[0]['x'] . ' ' . $studentPoints[0]['y'];
                        $studentArea = 'M ' . $studentPoints[0]['x'] . ' ' . ($padYTop + $chartH);
                        $studentArea .= ' L ' . $studentPoints[0]['x'] . ' ' . $studentPoints[0]['y'];
                        for ($i = 1; $i < count($studentPoints); $i++) {
                            $studentPath .= ' L ' . $studentPoints[$i]['x'] . ' ' . $studentPoints[$i]['y'];
                            $studentArea .= ' L ' . $studentPoints[$i]['x'] . ' ' . $studentPoints[$i]['y'];
                        }
                        $studentArea .= ' L ' . end($studentPoints)['x'] . ' ' . ($padYTop + $chartH) . ' Z';
                    }

                    $lecturerPath = '';
                    $lecturerArea = '';
                    if (!empty($lecturerPoints)) {
                        $lecturerPath = 'M ' . $lecturerPoints[0]['x'] . ' ' . $lecturerPoints[0]['y'];
                        $lecturerArea = 'M ' . $lecturerPoints[0]['x'] . ' ' . ($padYTop + $chartH);
                        $lecturerArea .= ' L ' . $lecturerPoints[0]['x'] . ' ' . $lecturerPoints[0]['y'];
                        for ($i = 1; $i < count($lecturerPoints); $i++) {
                            $lecturerPath .= ' L ' . $lecturerPoints[$i]['x'] . ' ' . $lecturerPoints[$i]['y'];
                            $lecturerArea .= ' L ' . $lecturerPoints[$i]['x'] . ' ' . $lecturerPoints[$i]['y'];
                        }
                        $lecturerArea .= ' L ' . end($lecturerPoints)['x'] . ' ' . ($padYTop + $chartH) . ' Z';
                    }
                @endphp

                <!-- Chart Canvas Container -->
                <div class="mt-6 w-full overflow-x-auto">
                    <div class="min-w-[550px]">
                        <svg viewBox="0 0 {{ $svgWidth }} {{ $svgHeight }}" class="w-full h-auto overflow-visible select-none" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <linearGradient id="studentGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#4f46e5" stop-opacity="0.22" />
                                    <stop offset="100%" stop-color="#4f46e5" stop-opacity="0.0" />
                                </linearGradient>
                                <linearGradient id="lecturerGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#f59e0b" stop-opacity="0.22" />
                                    <stop offset="100%" stop-color="#f59e0b" stop-opacity="0.0" />
                                </linearGradient>
                            </defs>

                            <!-- Horizontal Grid Lines -->
                            @for($g = 0; $g <= 4; $g++)
                                @php
                                    $gridY = round($padYTop + ($g * ($chartH / 4)), 1);
                                    $gridVal = round($maxDataVal - ($g * ($maxDataVal / 4)));
                                @endphp
                                <line x1="{{ $padX }}" y1="{{ $gridY }}" x2="{{ $svgWidth - $padX }}" y2="{{ $gridY }}" stroke="#f1f5f9" stroke-width="1.5" stroke-dasharray="{{ $g == 4 ? '0' : '4 4' }}" />
                                <text x="{{ $padX - 10 }}" y="{{ $gridY + 4 }}" font-size="10" fill="#94a3b8" text-anchor="end" font-family="sans-serif">{{ $gridVal }}</text>
                            @endfor

                            <!-- Area Fills -->
                            @if($studentArea)
                                <path d="{{ $studentArea }}" fill="url(#studentGrad)" />
                            @endif
                            @if($lecturerArea)
                                <path d="{{ $lecturerArea }}" fill="url(#lecturerGrad)" />
                            @endif

                            <!-- Student Line -->
                            @if($studentPath)
                                <path d="{{ $studentPath }}" fill="none" stroke="#4f46e5" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                            @endif

                            <!-- Lecturer Line -->
                            @if($lecturerPath)
                                <path d="{{ $lecturerPath }}" fill="none" stroke="#f59e0b" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                            @endif

                            <!-- Points & Labels -->
                            @foreach($studentPoints as $p)
                                <g class="group">
                                    <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="4.5" fill="#ffffff" stroke="#4f46e5" stroke-width="2.5" class="transition-all hover:r-6 cursor-pointer">
                                        <title>Mahasiswa: {{ $p['val'] }} ({{ $p['label'] }})</title>
                                    </circle>
                                    <text x="{{ $p['x'] }}" y="{{ $padYTop + $chartH + 20 }}" font-size="11" fill="#64748b" text-anchor="middle" font-weight="500" font-family="sans-serif">
                                        {{ $p['label'] }}
                                    </text>
                                </g>
                            @endforeach

                            @foreach($lecturerPoints as $p)
                                <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="4.5" fill="#ffffff" stroke="#f59e0b" stroke-width="2.5" class="transition-all hover:r-6 cursor-pointer">
                                    <title>Dosen: {{ $p['val'] }} ({{ $p['label'] }})</title>
                                </circle>
                            @endforeach
                        </svg>
                    </div>
                </div>
            </div>
        @endif

        <!-- Role-Specific Main Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left 2 Cols: Courses / Main Feed -->
            <div class="lg:col-span-2 space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="book-marked" class="w-5 h-5 text-indigo-600"></i>
                        <span>{{ $user->isDosen() ? 'Mata Kuliah yang Anda Ampu' : ($user->isAdmin() ? 'Mata Kuliah Terdaftar Sistem' : 'Mata Kuliah Anda') }}</span>
                    </h2>
                    <a href="{{ route('courses.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                        Lihat Semua <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

                @php
                    $coursesToDisplay = $user->isDosen() ? $myCourses : ($user->isAdmin() ? $recentCourses : $enrolledCourses);
                @endphp

                @if($coursesToDisplay->isEmpty())
                    <div class="bg-white rounded-3xl border border-slate-200/80 p-8 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 mx-auto flex items-center justify-center mb-3">
                            <i data-lucide="inbox" class="w-6 h-6"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-700">Belum ada mata kuliah</h3>
                        <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                            @if($user->isDosen())
                                Mulai buat mata kuliah pertama Anda untuk menambahkan materi dan tugas.
                            @else
                                Jelajahi katalog mata kuliah dan ikuti kelas yang diminati.
                            @endif
                        </p>
                        <div class="mt-4">
                            @if($user->isDosen() || $user->isAdmin())
                                <a href="{{ route('courses.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold bg-indigo-600 text-white shadow hover:bg-indigo-700">
                                    <i data-lucide="plus" class="w-4 h-4"></i> Buat Mata Kuliah
                                </a>
                            @else
                                <a href="{{ route('courses.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold bg-indigo-600 text-white shadow hover:bg-indigo-700">
                                    <i data-lucide="compass" class="w-4 h-4"></i> Jelajahi Katalog
                                </a>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($coursesToDisplay as $c)
                            <div class="motion-card bg-white rounded-3xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md hover:border-indigo-200 transition group flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-3">
                                        <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-[11px] font-bold font-mono">
                                            {{ $c->code }}
                                        </span>
                                        @if(!$c->is_published)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-500">Draft</span>
                                        @endif
                                    </div>
                                    <h3 class="text-base font-bold text-slate-800 group-hover:text-indigo-600 transition line-clamp-2 mb-1.5">
                                        <a href="{{ route('courses.show', $c) }}">{{ $c->name }}</a>
                                    </h3>
                                    <p class="text-xs text-slate-500 line-clamp-2 mb-4 leading-relaxed">
                                        {{ $c->description ?? 'Tidak ada deskripsi singkat untuk mata kuliah ini.' }}
                                    </p>
                                </div>

                                <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-3 text-slate-400">
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                            {{ $c->materials_count ?? $c->materials()->count() }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="check-square" class="w-3.5 h-3.5"></i>
                                            {{ $c->assignments_count ?? $c->assignments()->count() }}
                                        </span>
                                    </div>
                                    <a href="{{ route('courses.show', $c) }}" class="font-semibold text-indigo-600 group-hover:translate-x-0.5 transition inline-flex items-center gap-1">
                                        Buka Kelas <i data-lucide="chevron-right" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Right 1 Col: Dynamic Sidebar Feed -->
            <div class="space-y-6">
                @if($user->isDosen())
                    <!-- Submissions to Grade -->
                    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                <i data-lucide="check-circle" class="w-4 h-4 text-rose-500"></i>
                                <span>Tugas Perlu Dinilai</span>
                            </h3>
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-rose-50 text-rose-600">
                                {{ $pendingSubmissions->count() }} Baru
                            </span>
                        </div>

                        @if($pendingSubmissions->isEmpty())
                            <p class="text-xs text-slate-400 text-center py-6">Semua pengumpulan tugas telah diperiksa!</p>
                        @else
                            <div class="space-y-3">
                                @foreach($pendingSubmissions as $sub)
                                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold text-slate-800 truncate">{{ $sub->student->name }}</p>
                                            <p class="text-[11px] text-slate-500 truncate">{{ $sub->assignment->title }}</p>
                                            <span class="text-[10px] text-slate-400">{{ $sub->submitted_at?->diffForHumans() }}</span>
                                        </div>
                                        <a href="{{ route('assignments.show', $sub->assignment_id) }}" class="px-3 py-1.5 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition shrink-0">
                                            Nilai
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @elseif($user->isAdmin())
                    <!-- Recent Users -->
                    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                <i data-lucide="user-plus" class="w-4 h-4 text-indigo-600"></i>
                                <span>Pengguna Terdaftar Baru</span>
                            </h3>
                            <a href="{{ route('admin.users.index') }}" class="text-[11px] font-semibold text-indigo-600 hover:text-indigo-700">Kelola</a>
                        </div>

                        <div class="space-y-3">
                            @foreach($recentUsers as $ru)
                                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold shrink-0">
                                            {{ substr($ru->name, 0, 1) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold text-slate-800 truncate">{{ $ru->name }}</p>
                                            <p class="text-[10px] text-slate-400 truncate">{{ $ru->email }}</p>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold 
                                        {{ $ru->isAdmin() ? 'bg-rose-100 text-rose-700' : ($ru->isDosen() ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                        {{ $ru->role?->name }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <!-- Student: Upcoming Assignments Radar -->
                    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm">
                        <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2 mb-4">
                            <i data-lucide="alarm-clock" class="w-4 h-4 text-rose-500"></i>
                            <span>Tenggat Tugas Mendatang</span>
                        </h3>

                        @if($upcomingAssignments->isEmpty())
                            <p class="text-xs text-slate-400 text-center py-6">Tidak ada tenggat tugas dalam waktu dekat.</p>
                        @else
                            <div class="space-y-3">
                                @foreach($upcomingAssignments as $ass)
                                    @php
                                        $hasSubmitted = $ass->submissions->isNotEmpty();
                                    @endphp
                                    <div class="p-3.5 rounded-2xl {{ $hasSubmitted ? 'bg-emerald-50/50 border border-emerald-100' : 'bg-rose-50/50 border border-rose-100' }} flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold text-slate-800 truncate">{{ $ass->title }}</p>
                                            <p class="text-[11px] text-slate-500 truncate">{{ $ass->course->name }}</p>
                                            <span class="text-[10px] font-semibold {{ $hasSubmitted ? 'text-emerald-600' : 'text-rose-600' }}">
                                                {{ $hasSubmitted ? 'Sudah Dikumpulkan' : 'Batas: ' . $ass->due_at->isoFormat('D MMM, HH:mm') }}
                                            </span>
                                        </div>
                                        <a href="{{ route('assignments.show', $ass) }}" class="px-3 py-1.5 rounded-xl bg-white text-slate-700 border border-slate-200 text-xs font-semibold hover:bg-slate-50 shadow-sm shrink-0">
                                            Buka
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Student: Recent Announcements Feed -->
                    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm">
                        <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2 mb-4">
                            <i data-lucide="megaphone" class="w-4 h-4 text-indigo-600"></i>
                            <span>Pengumuman Kuliah</span>
                        </h3>

                        @if($recentAnnouncements->isEmpty())
                            <p class="text-xs text-slate-400 text-center py-6">Belum ada pengumuman terbaru.</p>
                        @else
                            <div class="space-y-3">
                                @foreach($recentAnnouncements as $ann)
                                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                                        <div class="flex items-center justify-between gap-2 mb-1">
                                            <span class="text-[10px] font-bold font-mono px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700">{{ $ann->course->code }}</span>
                                            <span class="text-[10px] text-slate-400">{{ $ann->published_at?->diffForHumans() }}</span>
                                        </div>
                                        <h4 class="text-xs font-bold text-slate-800 mb-1">{{ $ann->title }}</h4>
                                        <p class="text-[11px] text-slate-500 line-clamp-2 leading-relaxed">{{ $ann->content }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

