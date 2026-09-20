<x-app-layout :pageHeader="$course->code . ' - ' . $course->name">
    <div class="space-y-6">
        <!-- Course Header Banner -->
        <div class="relative overflow-hidden rounded-2xl sm:rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white p-5 sm:p-8 shadow-xl border border-slate-800 motion-fade-in">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-5 sm:gap-6 relative z-10">
                <div class="space-y-2 max-w-2xl">
                    <div class="flex items-center gap-2.5">
                        <span class="px-3 py-1 rounded-xl bg-indigo-500/20 text-indigo-300 border border-indigo-400/30 text-xs font-mono font-bold">
                            {{ $course->code }}
                        </span>
                        @if(!$course->is_published)
                            <span class="px-2.5 py-0.5 rounded-lg bg-amber-500/20 text-amber-300 border border-amber-400/30 text-[11px] font-semibold">
                                Draft (Belum Dipublikasikan)
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-lg bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 text-[11px] font-semibold">
                                Aktif
                            </span>
                        @endif
                    </div>

                    <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold tracking-tight text-white">
                        {{ $course->name }}
                    </h1>

                    <p class="text-slate-300 text-xs sm:text-sm leading-relaxed">
                        {{ $course->description ?? 'Selamat datang di mata kuliah ini. Akses seluruh materi, tugas, dan kuis melalui menu tab di bawah.' }}
                    </p>

                    <div class="pt-2 flex flex-wrap items-center gap-4 text-xs text-slate-400">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="user-check" class="w-4 h-4 text-indigo-400"></i>
                            Dosen: <strong class="text-white">{{ $course->creator?->name }}</strong>
                        </span>
                        <span>•</span>
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="users" class="w-4 h-4 text-indigo-400"></i>
                            <strong class="text-white">{{ $course->members->where('member_role', 'student')->count() }}</strong> Mahasiswa
                        </span>
                    </div>
                </div>

                <!-- Action Button in Banner -->
                <div class="flex flex-wrap items-center gap-2.5 sm:gap-3 shrink-0">
                    @if($isCreator || auth()->user()->isAdmin())
                        <a href="{{ route('courses.edit', $course) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs font-semibold backdrop-blur-md transition">
                            <i data-lucide="settings" class="w-4 h-4"></i>
                            <span>Pengaturan Kelas</span>
                        </a>
                        <a href="{{ route('grades.index', $course) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/30 transition">
                            <i data-lucide="award" class="w-4 h-4"></i>
                            <span>Buku Nilai</span>
                        </a>
                    @elseif($isMember)
                        <form method="POST" action="{{ route('courses.unenroll', $course) }}" onsubmit="return confirm('Apakah Anda yakin ingin keluar dari kelas ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-2xl bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-400/30 text-xs font-semibold transition">
                                <i data-lucide="user-minus" class="w-4 h-4"></i>
                                <span>Keluar Kelas</span>
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('courses.enroll', $course) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 transition transform hover:scale-102">
                                <i data-lucide="user-plus" class="w-4 h-4"></i>
                                <span>Ikuti Kelas Ini</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-1 sm:p-1.5 shadow-sm overflow-x-auto no-scrollbar flex items-center gap-1">
            <a href="{{ route('courses.show', ['course' => $course, 'tab' => 'materials']) }}"
                class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition flex items-center gap-2 {{ $activeTab === 'materials' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                <i data-lucide="book-open" class="w-4 h-4"></i>
                <span>Materi ({{ $course->materials->count() }})</span>
            </a>

            <a href="{{ route('courses.show', ['course' => $course, 'tab' => 'assignments']) }}"
                class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition flex items-center gap-2 {{ $activeTab === 'assignments' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                <span>Tugas ({{ $course->assignments->count() }})</span>
            </a>

            <a href="{{ route('courses.show', ['course' => $course, 'tab' => 'quizzes']) }}"
                class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition flex items-center gap-2 {{ $activeTab === 'quizzes' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                <i data-lucide="help-circle" class="w-4 h-4"></i>
                <span>Kuis ({{ $course->quizzes->count() }})</span>
            </a>

            <a href="{{ route('courses.show', ['course' => $course, 'tab' => 'discussions']) }}"
                class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition flex items-center gap-2 {{ $activeTab === 'discussions' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                <i data-lucide="message-square" class="w-4 h-4"></i>
                <span>Diskusi ({{ $course->discussions->count() }})</span>
            </a>

            <a href="{{ route('courses.show', ['course' => $course, 'tab' => 'announcements']) }}"
                class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition flex items-center gap-2 {{ $activeTab === 'announcements' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                <i data-lucide="megaphone" class="w-4 h-4"></i>
                <span>Pengumuman ({{ $course->announcements->count() }})</span>
            </a>

            <a href="{{ route('courses.show', ['course' => $course, 'tab' => 'members']) }}"
                class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition flex items-center gap-2 {{ $activeTab === 'members' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                <i data-lucide="users" class="w-4 h-4"></i>
                <span>Anggota ({{ $course->members->count() }})</span>
            </a>
        </div>

        <!-- Tab Content Area -->
        <div class="space-y-6">
            <!-- 1. TAB: MATERIALS -->
            @if($activeTab === 'materials')
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-slate-800">Modul & Materi Pembelajaran</h2>
                            <p class="text-xs text-slate-400">Pelajari bahan ajar yang telah diunggah pengampu</p>
                        </div>
                        @if($isCreator || auth()->user()->isAdmin())
                            <button type="button" onclick="openModal('add-material-modal')"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow transition">
                                <i data-lucide="plus" class="w-4 h-4"></i> Tambah Materi
                            </button>
                        @endif
                    </div>

                    @if($course->materials->isEmpty())
                        <div class="bg-white rounded-3xl border border-slate-200/80 p-8 text-center">
                            <i data-lucide="book-open" class="w-8 h-8 text-slate-400 mx-auto mb-2"></i>
                            <p class="text-xs text-slate-500">Belum ada materi pembelajaran yang ditambahkan.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 gap-3">
                            @foreach($course->materials as $mat)
                                <div class="motion-card bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 flex items-start justify-between gap-4 hover:border-indigo-200 hover:shadow-sm transition">
                                    <div class="flex items-start gap-3.5 min-w-0">
                                        <div class="p-2.5 rounded-xl shrink-0 
                                            {{ $mat->type === 'video' ? 'bg-rose-50 text-rose-600' : ($mat->type === 'file' ? 'bg-amber-50 text-amber-600' : ($mat->type === 'link' ? 'bg-sky-50 text-sky-600' : 'bg-indigo-50 text-indigo-600')) }}">
                                            @if($mat->type === 'video')
                                                <i data-lucide="video" class="w-5 h-5"></i>
                                            @elseif($mat->type === 'file')
                                                <i data-lucide="file-text" class="w-5 h-5"></i>
                                            @elseif($mat->type === 'link')
                                                <i data-lucide="external-link" class="w-5 h-5"></i>
                                            @else
                                                <i data-lucide="align-left" class="w-5 h-5"></i>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="text-[10px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-slate-100 text-slate-600">
                                                    {{ $mat->type }}
                                                </span>
                                                <span class="text-[11px] text-slate-400 font-mono">#{{ $mat->sort_order }}</span>
                                            </div>
                                            <h3 class="text-sm font-bold text-slate-800 hover:text-indigo-600 transition">
                                                <a href="{{ route('materials.show', $mat) }}">{{ $mat->title }}</a>
                                            </h3>
                                            @if($mat->description)
                                                <p class="text-xs text-slate-500 mt-1 line-clamp-1">{{ $mat->description }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 shrink-0">
                                        <a href="{{ route('materials.show', $mat) }}" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-700 text-xs font-semibold transition">
                                            Buka Materi
                                        </a>
                                        @if($mat->type === 'file' && $mat->file_path)
                                            <a href="{{ route('materials.download', $mat) }}" class="p-1.5 rounded-xl text-slate-400 hover:text-indigo-600 hover:bg-slate-100 transition" title="Unduh Berkas">
                                                <i data-lucide="download" class="w-4 h-4"></i>
                                            </a>
                                        @endif
                                        @if($isCreator || auth()->user()->isAdmin())
                                            <form method="POST" action="{{ route('materials.destroy', $mat) }}" onsubmit="return confirm('Hapus materi ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-slate-100 transition" title="Hapus">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            <!-- 2. TAB: ASSIGNMENTS -->
            @elseif($activeTab === 'assignments')
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-slate-800">Tugas & Penugasan Kuliah</h2>
                            <p class="text-xs text-slate-400">Kerjakan dan kumpulkan tugas sebelum batas waktu berakhir</p>
                        </div>
                        @if($isCreator || auth()->user()->isAdmin())
                            <button type="button" onclick="openModal('add-assignment-modal')"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow transition">
                                <i data-lucide="plus" class="w-4 h-4"></i> Buat Tugas Baru
                            </button>
                        @endif
                    </div>

                    @if($course->assignments->isEmpty())
                        <div class="bg-white rounded-3xl border border-slate-200/80 p-8 text-center">
                            <i data-lucide="clipboard-list" class="w-8 h-8 text-slate-400 mx-auto mb-2"></i>
                            <p class="text-xs text-slate-500">Belum ada penugasan untuk kelas ini.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 gap-3">
                            @foreach($course->assignments as $ass)
                                @php
                                    $studentSub = $ass->submissions->first();
                                @endphp
                                <div class="motion-card bg-white rounded-2xl border border-slate-200/80 p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-indigo-200 hover:shadow-sm transition">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 mb-1.5">
                                            <span class="px-2 py-0.5 rounded-lg text-[10px] font-semibold {{ $ass->due_at->isPast() ? 'bg-slate-100 text-slate-500' : 'bg-rose-50 text-rose-600' }}">
                                                Tenggat: {{ $ass->due_at->isoFormat('D MMM Y, HH:mm') }}
                                            </span>
                                            <span class="text-[11px] text-slate-400">Maksimal: {{ $ass->max_score }} Poin</span>
                                        </div>
                                        <h3 class="text-sm font-bold text-slate-800 hover:text-indigo-600 transition">
                                            <a href="{{ route('assignments.show', $ass) }}">{{ $ass->title }}</a>
                                        </h3>
                                        <p class="text-xs text-slate-500 mt-1 line-clamp-1">{{ $ass->description }}</p>
                                    </div>

                                    <div class="flex items-center gap-3 shrink-0">
                                        @if(auth()->user()->isMahasiswa())
                                            @if($studentSub)
                                                <div class="text-right">
                                                    <span class="px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-700 text-xs font-bold flex items-center gap-1">
                                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                                        {{ $studentSub->score !== null ? 'Nilai: ' . $studentSub->score : 'Terkumpul' }}
                                                    </span>
                                                </div>
                                            @else
                                                <span class="px-2.5 py-1 rounded-xl bg-amber-50 text-amber-700 text-xs font-semibold">
                                                    Belum Kumpul
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-xs text-slate-400 font-medium">
                                                {{ $ass->submissions()->count() }} Terkumpul
                                            </span>
                                        @endif

                                        <a href="{{ route('assignments.show', $ass) }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold transition">
                                            Buka Tugas
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            <!-- 3. TAB: QUIZZES -->
            @elseif($activeTab === 'quizzes')
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-slate-800">Kuis & Ujian Daring</h2>
                            <p class="text-xs text-slate-400">Uji pemahaman materi Anda dengan kuis berwaktu</p>
                        </div>
                        @if($isCreator || auth()->user()->isAdmin())
                            <button type="button" onclick="openModal('add-quiz-modal')"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow transition">
                                <i data-lucide="plus" class="w-4 h-4"></i> Buat Kuis Baru
                            </button>
                        @endif
                    </div>

                    @if($course->quizzes->isEmpty())
                        <div class="bg-white rounded-3xl border border-slate-200/80 p-8 text-center">
                            <i data-lucide="help-circle" class="w-8 h-8 text-slate-400 mx-auto mb-2"></i>
                            <p class="text-xs text-slate-500">Belum ada kuis yang tersedia untuk mata kuliah ini.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 gap-3">
                            @foreach($course->quizzes as $quiz)
                                @php
                                    $attemptCount = $quiz->attempts->count();
                                    $best = $quiz->attempts->where('status', 'graded')->max('score');
                                @endphp
                                <div class="motion-card bg-white rounded-2xl border border-slate-200/80 p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-indigo-200 hover:shadow-sm transition">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 mb-1.5">
                                            <span class="px-2 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 text-[10px] font-semibold flex items-center gap-1">
                                                <i data-lucide="timer" class="w-3 h-3"></i> {{ $quiz->duration_minutes }} Menit
                                            </span>
                                            <span class="text-[11px] text-slate-400">Passing: {{ $quiz->passing_score ?? 60 }} Poin</span>
                                            <span class="text-[11px] text-slate-400">• {{ $quiz->questions()->count() }} Soal</span>
                                        </div>
                                        <h3 class="text-sm font-bold text-slate-800 hover:text-indigo-600 transition">
                                            <a href="{{ route('quizzes.show', $quiz) }}">{{ $quiz->title }}</a>
                                        </h3>
                                        <p class="text-xs text-slate-500 mt-1 line-clamp-1">{{ $quiz->description }}</p>
                                    </div>

                                    <div class="flex items-center gap-3 shrink-0">
                                        @if(auth()->user()->isMahasiswa())
                                            @if($best !== null)
                                                <span class="px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-700 text-xs font-bold">
                                                    Skor Terbaik: {{ $best }}
                                                </span>
                                            @endif
                                        @endif

                                        @if($isCreator || auth()->user()->isAdmin())
                                            <a href="{{ route('quizzes.questions', $quiz) }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                                                Kelola Soal
                                            </a>
                                        @endif

                                        <a href="{{ route('quizzes.show', $quiz) }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold transition">
                                            Detail Kuis
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            <!-- 4. TAB: DISCUSSIONS -->
            @elseif($activeTab === 'discussions')
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-slate-800">Forum Diskusi Kelas</h2>
                            <p class="text-xs text-slate-400">Ajukan pertanyaan dan berdiskusi dengan rekan kelas & dosen</p>
                        </div>
                        <button type="button" onclick="openModal('add-discussion-modal')"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow transition">
                            <i data-lucide="plus" class="w-4 h-4"></i> Buat Topik Baru
                        </button>
                    </div>

                    @if($course->discussions->isEmpty())
                        <div class="bg-white rounded-3xl border border-slate-200/80 p-8 text-center">
                            <i data-lucide="message-square" class="w-8 h-8 text-slate-400 mx-auto mb-2"></i>
                            <p class="text-xs text-slate-500">Belum ada diskusi yang dimulai. Jadilah yang pertama bertanya!</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($course->discussions as $disc)
                                <div class="motion-card bg-white rounded-2xl border border-slate-200/80 p-5 flex items-start justify-between gap-4 hover:border-indigo-200 hover:shadow-sm transition">
                                    <div class="min-w-0 flex items-start gap-3">
                                        <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-xs shrink-0 mt-0.5">
                                            {{ substr($disc->user?->name, 0, 2) }}
                                        </div>
                                        <div class="min-w-0">
                                            <h3 class="text-sm font-bold text-slate-800 hover:text-indigo-600 transition flex items-center gap-2">
                                                <a href="{{ route('discussions.show', $disc) }}">{{ $disc->title }}</a>
                                                @if($disc->is_locked)
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-slate-100 text-slate-500 font-medium">Terkunci</span>
                                                @endif
                                            </h3>
                                            <p class="text-xs text-slate-500 mt-1 line-clamp-1">{{ $disc->content }}</p>
                                            <span class="text-[11px] text-slate-400 mt-2 block">
                                                Oleh <strong class="text-slate-600">{{ $disc->user?->name }}</strong> • {{ $disc->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 shrink-0">
                                        <span class="flex items-center gap-1.5 text-xs font-semibold text-slate-500">
                                            <i data-lucide="message-circle" class="w-4 h-4"></i>
                                            {{ $disc->replies->count() }} Balasan
                                        </span>
                                        <a href="{{ route('discussions.show', $disc) }}" class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                                            Buka
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            <!-- 5. TAB: ANNOUNCEMENTS -->
            @elseif($activeTab === 'announcements')
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-slate-800">Papan Pengumuman</h2>
                            <p class="text-xs text-slate-400">Informasi penting terkait perkuliahan dari dosen pengampu</p>
                        </div>
                        @if($isCreator || auth()->user()->isAdmin())
                            <button type="button" onclick="openModal('add-announcement-modal')"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow transition">
                                <i data-lucide="plus" class="w-4 h-4"></i> Buat Pengumuman
                            </button>
                        @endif
                    </div>

                    @if($course->announcements->isEmpty())
                        <div class="bg-white rounded-3xl border border-slate-200/80 p-8 text-center">
                            <i data-lucide="megaphone" class="w-8 h-8 text-slate-400 mx-auto mb-2"></i>
                            <p class="text-xs text-slate-500">Belum ada pengumuman untuk mata kuliah ini.</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($course->announcements as $ann)
                                <div class="motion-card bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm">
                                    <div class="flex items-start justify-between gap-4 mb-3">
                                        <div class="flex items-center gap-3">
                                            <div class="p-2 bg-indigo-50 text-indigo-600 rounded-xl">
                                                <i data-lucide="megaphone" class="w-4 h-4"></i>
                                            </div>
                                            <div>
                                                <h3 class="text-sm font-bold text-slate-800">{{ $ann->title }}</h3>
                                                <span class="text-[11px] text-slate-400">{{ $ann->published_at?->diffForHumans() }}</span>
                                            </div>
                                        </div>

                                        @if($isCreator || auth()->user()->isAdmin())
                                            <form method="POST" action="{{ route('announcements.destroy', $ann) }}" onsubmit="return confirm('Hapus pengumuman ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 transition">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                    <div class="text-xs text-slate-600 leading-relaxed whitespace-pre-line pl-11">
                                        {{ $ann->content }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            <!-- 6. TAB: MEMBERS -->
            @elseif($activeTab === 'members')
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-slate-800">Daftar Anggota Kelas</h2>
                            <p class="text-xs text-slate-400">Dosen pengampu dan rekan mahasiswa yang terdaftar</p>
                        </div>
                        @if($isCreator || auth()->user()->isAdmin())
                            <button type="button" onclick="openModal('add-member-modal')"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow transition">
                                <i data-lucide="user-plus" class="w-4 h-4"></i> Tambah Anggota
                            </button>
                        @endif
                    </div>

                    <div class="bg-white rounded-3xl border border-slate-200/80 overflow-hidden shadow-sm">
                        <table class="w-full text-left text-xs text-slate-600">
                            <thead class="bg-slate-50 border-b border-slate-200/80 text-[11px] uppercase font-bold text-slate-400">
                                <tr>
                                    <th class="px-6 py-4">Nama</th>
                                    <th class="px-6 py-4">Email</th>
                                    <th class="px-6 py-4">Peran Kelas</th>
                                    <th class="px-6 py-4">Bergabung</th>
                                    @if($isCreator || auth()->user()->isAdmin())
                                        <th class="px-6 py-4 text-right">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($course->members as $member)
                                    <tr class="hover:bg-slate-50/60 transition">
                                        <td class="px-6 py-4 font-bold text-slate-800 flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-xs shrink-0">
                                                {{ substr($member->user->name, 0, 2) }}
                                            </div>
                                            <span>{{ $member->user->name }}</span>
                                        </td>
                                        <td class="px-6 py-4">{{ $member->user->email }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $member->member_role === 'lecturer' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
                                                {{ $member->member_role === 'lecturer' ? 'Dosen Pengampu' : 'Mahasiswa' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-slate-400">{{ $member->joined_at?->diffForHumans() }}</td>
                                        @if($isCreator || auth()->user()->isAdmin())
                                            <td class="px-6 py-4 text-right">
                                                @if($member->user_id !== $course->created_by)
                                                    <form method="POST" action="{{ route('courses.members.remove', ['course' => $course, 'member' => $member->user_id]) }}" onsubmit="return confirm('Keluarkan anggota ini?');" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-rose-500 hover:text-rose-700 font-semibold text-xs">
                                                            Keluarkan
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- MODAL 1: ADD MATERIAL -->
    <div id="add-material-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-4">
        <div class="modal-backdrop fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('add-material-modal')"></div>
        <div class="modal-panel relative bg-white rounded-2xl sm:rounded-3xl shadow-2xl max-w-lg w-full p-5 sm:p-8 z-10 max-h-[90vh] overflow-y-auto">
            <h3 class="text-lg font-bold text-slate-800 mb-4">Tambah Materi Pembelajaran</h3>
            <form method="POST" action="{{ route('materials.store', $course) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Materi</label>
                    <input type="text" name="title" required placeholder="Contoh: Pengenalan Routing" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe Materi</label>
                    <select name="type" id="mat-type-select" onchange="toggleMaterialInputs(this.value)" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm">
                        <option value="text">Artikel / Teks (Markdown)</option>
                        <option value="file">Berkas Dokumen (PDF, DOCX, ZIP)</option>
                        <option value="video">Tautan Video (YouTube)</option>
                        <option value="link">Tautan Referensi Eksternal</option>
                    </select>
                </div>
                <div id="mat-content-input">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Isi Artikel / Bacaan</label>
                    <textarea name="content" rows="5" placeholder="Tuliskan materi pembelajaran di sini..." class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm"></textarea>
                </div>
                <div id="mat-file-input" class="hidden">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Berkas</label>
                    <input type="file" name="file" class="w-full text-xs" />
                </div>
                <div id="mat-url-input" class="hidden">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">URL / Link</label>
                    <input type="url" name="external_url" placeholder="https://..." class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm" />
                </div>
                <div class="pt-4 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeModal('add-material-modal')" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold">Simpan Materi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: ADD ASSIGNMENT -->
    <div id="add-assignment-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-4">
        <div class="modal-backdrop fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('add-assignment-modal')"></div>
        <div class="modal-panel relative bg-white rounded-2xl sm:rounded-3xl shadow-2xl max-w-lg w-full p-5 sm:p-8 z-10 max-h-[90vh] overflow-y-auto">
            <h3 class="text-lg font-bold text-slate-800 mb-4">Buat Tugas Baru</h3>
            <form method="POST" action="{{ route('assignments.store', $course) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Tugas</label>
                    <input type="text" name="title" required placeholder="Contoh: Tugas 1: Desain Basis Data" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi & Instruksi</label>
                    <textarea name="description" rows="4" placeholder="Jelaskan petunjuk pengerjaan dan kriteria penilaian..." class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm"></textarea>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nilai Maksimal</label>
                        <input type="number" name="max_score" value="100" min="1" max="1000" class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tenggat Waktu</label>
                        <input type="datetime-local" name="due_at" required class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm" />
                    </div>
                </div>
                <div>
                    <label class="flex items-center gap-2 text-xs text-slate-700">
                        <input type="checkbox" name="allow_late_submission" value="1" class="rounded text-indigo-600">
                        <span>Izinkan pengumpulan terlambat (diberi tanda terlambat)</span>
                    </label>
                </div>
                <div class="pt-4 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeModal('add-assignment-modal')" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold">Buat Tugas</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: ADD QUIZ -->
    <div id="add-quiz-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-4">
        <div class="modal-backdrop fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('add-quiz-modal')"></div>
        <div class="modal-panel relative bg-white rounded-2xl sm:rounded-3xl shadow-2xl max-w-lg w-full p-5 sm:p-8 z-10 max-h-[90vh] overflow-y-auto">
            <h3 class="text-lg font-bold text-slate-800 mb-4">Buat Kuis Baru</h3>
            <form method="POST" action="{{ route('quizzes.store', $course) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Kuis</label>
                    <input type="text" name="title" required placeholder="Contoh: Kuis 1: Dasar Laravel" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Petunjuk Kuis</label>
                    <textarea name="description" rows="3" placeholder="Informasikan ketentuan pengerjaan..." class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm"></textarea>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Durasi (Menit)</label>
                        <input type="number" name="duration_minutes" value="30" min="1" max="300" required class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Batas Percobaan</label>
                        <input type="number" name="max_attempts" value="2" min="1" max="10" required class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nilai Lulus</label>
                        <input type="number" name="passing_score" value="70" min="0" max="100" class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm" />
                    </div>
                </div>
                <div>
                    <label class="flex items-center gap-2 text-xs text-slate-700">
                        <input type="checkbox" name="shuffle_questions" value="1" checked class="rounded text-indigo-600">
                        <span>Acak urutan butir soal untuk setiap mahasiswa</span>
                    </label>
                </div>
                <div class="pt-4 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeModal('add-quiz-modal')" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold">Lanjut ke Butir Soal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 4: ADD ANNOUNCEMENT -->
    <div id="add-announcement-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-4">
        <div class="modal-backdrop fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('add-announcement-modal')"></div>
        <div class="modal-panel relative bg-white rounded-2xl sm:rounded-3xl shadow-2xl max-w-lg w-full p-5 sm:p-8 z-10 max-h-[90vh] overflow-y-auto">
            <h3 class="text-lg font-bold text-slate-800 mb-4">Buat Pengumuman Kuliah</h3>
            <form method="POST" action="{{ route('announcements.store', $course) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Pengumuman</label>
                    <input type="text" name="title" required placeholder="Contoh: Perubahan Jadwal Kuliah Daring" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Isi Pesan</label>
                    <textarea name="content" rows="4" required placeholder="Tulis pengumuman untuk seluruh mahasiswa..." class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm"></textarea>
                </div>
                <div class="pt-4 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeModal('add-announcement-modal')" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold">Publikasikan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 5: ADD DISCUSSION TOPIC -->
    <div id="add-discussion-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-4">
        <div class="modal-backdrop fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('add-discussion-modal')"></div>
        <div class="modal-panel relative bg-white rounded-2xl sm:rounded-3xl shadow-2xl max-w-lg w-full p-5 sm:p-8 z-10 max-h-[90vh] overflow-y-auto">
            <h3 class="text-lg font-bold text-slate-800 mb-4">Mulai Topik Diskusi Baru</h3>
            <form method="POST" action="{{ route('discussions.store', $course) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Topik / Pertanyaan</label>
                    <input type="text" name="title" required placeholder="Contoh: Pertanyaan seputar Tugas 1" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Uraian Diskusi</label>
                    <textarea name="content" rows="4" required placeholder="Jelaskan pertanyaan atau topik diskusi secara lengkap..." class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm"></textarea>
                </div>
                <div class="pt-4 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeModal('add-discussion-modal')" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold">Kirim Topik</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 6: ADD MEMBER -->
    <div id="add-member-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-4">
        <div class="modal-backdrop fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('add-member-modal')"></div>
        <div class="modal-panel relative bg-white rounded-2xl sm:rounded-3xl shadow-2xl max-w-md w-full p-5 sm:p-6 z-10 max-h-[90vh] overflow-y-auto">
            <h3 class="text-lg font-bold text-slate-800 mb-4">Tambahkan Anggota Kelas</h3>
            <form method="POST" action="{{ route('courses.members.add', $course) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Email Pengguna Terdaftar</label>
                    <input type="email" name="email" required placeholder="nama@lms.test" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Peran dalam Kelas</label>
                    <select name="member_role" class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm">
                        <option value="student">Mahasiswa</option>
                        <option value="lecturer">Dosen Tambahan</option>
                    </select>
                </div>
                <div class="pt-4 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeModal('add-member-modal')" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold">Tambah</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleMaterialInputs(type) {
            document.getElementById('mat-content-input').classList.add('hidden');
            document.getElementById('mat-file-input').classList.add('hidden');
            document.getElementById('mat-url-input').classList.add('hidden');

            if (type === 'text') {
                document.getElementById('mat-content-input').classList.remove('hidden');
            } else if (type === 'file') {
                document.getElementById('mat-file-input').classList.remove('hidden');
            } else if (type === 'video' || type === 'link') {
                document.getElementById('mat-url-input').classList.remove('hidden');
            }
        }
    </script>
</x-app-layout>

