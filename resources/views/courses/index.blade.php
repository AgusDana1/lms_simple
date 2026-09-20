<x-app-layout pageHeader="Katalog Mata Kuliah">
    <div class="space-y-6">
        <!-- Header & Action Row -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-extrabold text-slate-800 tracking-tight">Daftar Mata Kuliah</h2>
                <p class="text-xs text-slate-400 mt-0.5">Jelajahi dan ikuti kelas akademik yang tersedia</p>
            </div>

            @if(auth()->user()->isDosen() || auth()->user()->isAdmin())
                <a href="{{ route('courses.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/25 transition">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Buat Mata Kuliah Baru</span>
                </a>
            @endif
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white p-3 sm:p-4 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 sm:gap-4">
            <!-- Tabs -->
            <div class="flex items-center gap-1 p-1 bg-slate-100/80 rounded-xl sm:rounded-2xl w-full sm:w-auto">
                <a href="{{ route('courses.index', ['filter' => 'all', 'search' => $search]) }}"
                    class="flex-1 sm:flex-initial px-4 py-2 rounded-lg sm:rounded-xl text-xs font-semibold transition text-center {{ $filter === 'all' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                    Semua Kelas
                </a>
                <a href="{{ route('courses.index', ['filter' => 'my', 'search' => $search]) }}"
                    class="flex-1 sm:flex-initial px-4 py-2 rounded-lg sm:rounded-xl text-xs font-semibold transition text-center {{ $filter === 'my' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                    Kelas Saya
                </a>
            </div>

            <!-- Search Form -->
            <form method="GET" action="{{ route('courses.index') }}" class="w-full sm:w-72 relative">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3"></i>
                <input type="text" name="search" value="{{ $search }}"
                    placeholder="Cari kode atau nama kelas..."
                    class="w-full pl-10 pr-4 py-2 rounded-xl sm:rounded-2xl border border-slate-200 bg-slate-50 text-xs focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" />
            </form>
        </div>

        <!-- Courses Grid -->
        @if($courses->isEmpty())
            <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 p-8 sm:p-12 text-center">
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 mx-auto flex items-center justify-center mb-3">
                    <i data-lucide="search-x" class="w-7 h-7"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800">Tidak ada mata kuliah yang ditemukan</h3>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                    Cobalah gunakan kata kunci lain atau ubah filter pencarian Anda.
                </p>
                <div class="mt-4">
                    <a href="{{ route('courses.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-slate-200 transition">
                        Reset Filter
                    </a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-6">
                @foreach($courses as $course)
                    @php
                        $isEnrolled = in_array($course->id, $enrolledCourseIds);
                        $isCreator = $course->created_by === auth()->id();
                    @endphp
                    <div class="motion-card bg-white rounded-3xl border border-slate-200/80 overflow-hidden shadow-sm hover:shadow-lg hover:border-indigo-200 transition flex flex-col justify-between group">
                        <!-- Card Banner / Thumbnail -->
                        <div class="h-36 relative overflow-hidden bg-gradient-to-tr from-indigo-700 via-indigo-600 to-violet-500 p-5 flex flex-col justify-between text-white">
                            @if($course->thumbnail)
                                <img src="{{ asset('storage/' . $course->thumbnail) }}" alt="Thumbnail" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/30 to-transparent"></div>
                            @endif

                            <div class="relative z-10 flex items-center justify-between">
                                <span class="px-2.5 py-1 rounded-xl bg-white/20 backdrop-blur-md text-[11px] font-bold font-mono text-white border border-white/25">
                                    {{ $course->code }}
                                </span>
                                @if(!$course->is_published)
                                    <span class="px-2 py-0.5 rounded-lg bg-amber-500/80 backdrop-blur-md text-[10px] font-semibold text-white">
                                        Draft
                                    </span>
                                @endif
                            </div>

                            <div class="relative z-10">
                                <span class="text-[11px] text-indigo-100 flex items-center gap-1.5 font-medium">
                                    <i data-lucide="user" class="w-3.5 h-3.5"></i>
                                    {{ $course->creator?->name }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="text-base font-bold text-slate-900 group-hover:text-indigo-600 transition line-clamp-2 mb-2 leading-snug">
                                    <a href="{{ route('courses.show', $course) }}">{{ $course->name }}</a>
                                </h3>
                                <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed mb-4">
                                    {{ $course->description ?? 'Mata kuliah ini menyediakan materi pembelajaran mendalam dan latihan praktikal.' }}
                                </p>
                            </div>

                            <!-- Course Metrics & Action Footer -->
                            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                                <div class="flex items-center gap-3 text-xs text-slate-400 font-medium">
                                    <span class="flex items-center gap-1" title="Jumlah Materi">
                                        <i data-lucide="file-text" class="w-3.5 h-3.5 text-slate-400"></i>
                                        {{ $course->materials_count }}
                                    </span>
                                    <span class="flex items-center gap-1" title="Jumlah Tugas">
                                        <i data-lucide="clipboard-list" class="w-3.5 h-3.5 text-slate-400"></i>
                                        {{ $course->assignments_count }}
                                    </span>
                                    <span class="flex items-center gap-1" title="Jumlah Mahasiswa">
                                        <i data-lucide="users" class="w-3.5 h-3.5 text-slate-400"></i>
                                        {{ $course->student_count }}
                                    </span>
                                </div>

                                <div>
                                    @if($isEnrolled || $isCreator || auth()->user()->isAdmin())
                                        <a href="{{ route('courses.show', $course) }}" class="inline-flex items-center gap-1 px-3.5 py-1.5 rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-100 font-semibold text-xs transition">
                                            <span>Buka Kelas</span>
                                            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                        </a>
                                    @else
                                        <form method="POST" action="{{ route('courses.enroll', $course) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1 px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-sm shadow-indigo-600/25 transition">
                                                <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                                                <span>Ikuti</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="pt-4">
                {{ $courses->links() }}
            </div>
        @endif
    </div>
</x-app-layout>

