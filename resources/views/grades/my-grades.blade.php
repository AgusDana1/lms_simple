<x-app-layout pageHeader="Transkrip Nilai Saya">
    <div class="space-y-6 motion-fade-in max-w-4xl mx-auto">
        <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-sm p-5 sm:p-8">
            <h2 class="text-xl font-bold text-slate-900 tracking-tight mb-1">Rekapitulasi Nilai Akademik</h2>
            <p class="text-xs text-slate-400">Daftar seluruh nilai tugas, kuis, dan ujian Anda pada setiap mata kuliah</p>
        </div>

        @if($enrolledCourses->isEmpty())
            <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 p-8 sm:p-12 text-center">
                <i data-lucide="award" class="w-10 h-10 text-slate-400 mx-auto mb-3"></i>
                <h3 class="text-sm font-bold text-slate-800">Belum ada mata kuliah yang diikuti</h3>
                <p class="text-xs text-slate-400 mt-1 mb-4">Ikuti kelas terlebih dahulu untuk melihat catatan penilaian Anda.</p>
                <a href="{{ route('courses.index') }}" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700">
                    Jelajahi Kelas
                </a>
            </div>
        @else
            <div class="space-y-6">
                @foreach($enrolledCourses as $course)
                    @php
                        $courseGrades = $gradesByCourse->get($course->id, collect());
                        $avgScore = $courseGrades->count() > 0 ? round($courseGrades->avg('score'), 2) : 0;
                    @endphp
                    <div class="motion-card bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                        <div class="p-4 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
                            <div>
                                <span class="px-2.5 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 font-mono text-[11px] font-bold">
                                    {{ $course->code }}
                                </span>
                                <h3 class="text-base font-bold text-slate-800 mt-1">
                                    <a href="{{ route('courses.show', $course) }}" class="hover:text-indigo-600 transition">{{ $course->name }}</a>
                                </h3>
                                <p class="text-xs text-slate-400">Dosen Pengampu: {{ $course->creator?->name }}</p>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="text-right">
                                    <span class="block text-[10px] uppercase font-bold text-slate-400">Rata-Rata Kelas</span>
                                    <span class="text-2xl font-black {{ ($avgScore >= 70) ? 'text-emerald-600' : 'text-slate-800' }}">
                                        {{ $courseGrades->count() > 0 ? $avgScore : '-' }}
                                    </span>
                                </div>
                                <a href="{{ route('courses.show', $course) }}" class="p-2 text-slate-400 hover:text-indigo-600 rounded-xl hover:bg-slate-100 transition">
                                    <i data-lucide="external-link" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </div>

                        @if($courseGrades->isEmpty())
                            <div class="p-6 text-center text-xs text-slate-400">
                                Belum ada komponen nilai yang diinputkan untuk mata kuliah ini.
                            </div>
                        @else
                            <div class="divide-y divide-slate-100 text-xs">
                                @foreach($courseGrades as $g)
                                    <div class="p-4 sm:px-6 flex items-center justify-between gap-4 hover:bg-slate-50/60 transition">
                                        <div>
                                            <p class="font-bold text-slate-800 text-sm">{{ $g->component }}</p>
                                            @if($g->notes)
                                                <p class="text-slate-500 text-[11px] italic mt-0.5">"{{ $g->notes }}"</p>
                                            @endif
                                            <span class="text-[10px] text-slate-400 mt-1 block">
                                                Dinilai oleh: {{ $g->grader?->name ?? 'Dosen' }} • {{ $g->created_at->diffForHumans() }}
                                            </span>
                                        </div>

                                        <div class="text-right shrink-0">
                                            <span class="text-lg font-black {{ ($g->score >= 70) ? 'text-emerald-600' : 'text-amber-600' }}">
                                                {{ $g->score }}
                                            </span>
                                            <span class="text-slate-400 text-xs">/ 100</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>

