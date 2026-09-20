<x-app-layout :pageHeader="$quiz->title">
    <div class="max-w-4xl mx-auto space-y-6 motion-fade-in">
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-xs text-slate-400">
            <a href="{{ route('courses.show', $course) }}" class="hover:text-indigo-600 transition">{{ $course->code }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            <a href="{{ route('courses.show', ['course' => $course, 'tab' => 'quizzes']) }}" class="hover:text-indigo-600 transition">Kuis</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            <span class="text-slate-700 font-semibold truncate">{{ $quiz->title }}</span>
        </div>

        <!-- Quiz Information Banner -->
        <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-sm p-5 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                        {{ $quiz->title }}
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 leading-relaxed">
                        {{ $quiz->description ?? 'Pastikan koneksi internet Anda stabil sebelum menekan tombol mulai kuis.' }}
                    </p>
                </div>

                @if($isLecturer)
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('quizzes.questions', $quiz) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-100 text-xs font-semibold transition">
                            <i data-lucide="list-ordered" class="w-4 h-4"></i>
                            <span>Kelola Butir Soal</span>
                        </a>
                        <form method="POST" action="{{ route('quizzes.destroy', $quiz) }}" onsubmit="return confirm('Hapus kuis ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-slate-50 transition">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <!-- Quiz Param Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3 pt-4 border-t border-slate-100">
                <div class="p-3 sm:p-3.5 rounded-2xl bg-slate-50 text-center">
                    <span class="block text-[10px] sm:text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Durasi</span>
                    <span class="text-sm sm:text-base font-extrabold text-slate-800 flex items-center justify-center gap-1 mt-0.5">
                        <i data-lucide="timer" class="w-4 h-4 text-indigo-600"></i>
                        {{ $quiz->duration_minutes }} Menit
                    </span>
                </div>
                <div class="p-3 sm:p-3.5 rounded-2xl bg-slate-50 text-center">
                    <span class="block text-[10px] sm:text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Batas Attempt</span>
                    <span class="text-sm sm:text-base font-extrabold text-slate-800 flex items-center justify-center gap-1 mt-0.5">
                        <i data-lucide="rotate-cw" class="w-4 h-4 text-amber-500"></i>
                        {{ $quiz->max_attempts }} Kali
                    </span>
                </div>
                <div class="p-3 sm:p-3.5 rounded-2xl bg-slate-50 text-center">
                    <span class="block text-[10px] sm:text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Nilai Kelulusan</span>
                    <span class="text-sm sm:text-base font-extrabold text-slate-800 flex items-center justify-center gap-1 mt-0.5">
                        <i data-lucide="award" class="w-4 h-4 text-emerald-600"></i>
                        {{ $quiz->passing_score ?? 60 }} Poin
                    </span>
                </div>
                <div class="p-3 sm:p-3.5 rounded-2xl bg-slate-50 text-center">
                    <span class="block text-[10px] sm:text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Jumlah Soal</span>
                    <span class="text-sm sm:text-base font-extrabold text-slate-800 flex items-center justify-center gap-1 mt-0.5">
                        <i data-lucide="file-question" class="w-4 h-4 text-sky-600"></i>
                        {{ $quiz->questions->count() }} Butir
                    </span>
                </div>
            </div>

            <!-- Start Quiz Action Button for Students -->
            @if(auth()->user()->isMahasiswa())
                <div class="mt-6 pt-6 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                    <div>
                        <span class="text-xs text-slate-500">
                            Percobaan terpakai: <strong>{{ $myAttempts->count() }}</strong> dari <strong>{{ $quiz->max_attempts }}</strong> kali
                        </span>
                    </div>

                    <div>
                        @if($canAttempt)
                            <form method="POST" action="{{ route('quizzes.start', $quiz) }}">
                                @csrf
                                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/30 transition transform hover:scale-102">
                                    <i data-lucide="play" class="w-4 h-4"></i>
                                    <span>Mulai Pengerjaan Kuis</span>
                                </button>
                            </form>
                        @else
                            <button disabled class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-200 text-slate-400 text-xs font-bold cursor-not-allowed text-center">
                                Batas Percobaan Habis
                            </button>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Student Attempt History -->
        @if(auth()->user()->isMahasiswa() && $myAttempts->isNotEmpty())
            <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-sm p-5 sm:p-8">
                <h2 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <i data-lucide="history" class="w-5 h-5 text-indigo-600"></i>
                    <span>Riwayat Percobaan Anda</span>
                </h2>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 border-b border-slate-200/80 text-[11px] uppercase font-bold text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Percobaan #</th>
                                <th class="px-4 py-3">Waktu Mulai</th>
                                <th class="px-4 py-3">Waktu Selesai</th>
                                <th class="px-4 py-3">Nilai</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($myAttempts as $att)
                                <tr>
                                    <td class="px-4 py-3 font-bold font-mono text-slate-800">#{{ $att->attempt_number }}</td>
                                    <td class="px-4 py-3">{{ $att->started_at->isoFormat('D MMM Y, HH:mm') }}</td>
                                    <td class="px-4 py-3">{{ $att->submitted_at ? $att->submitted_at->isoFormat('D MMM Y, HH:mm') : '-' }}</td>
                                    <td class="px-4 py-3 font-extrabold text-sm {{ ($att->score >= $quiz->passing_score) ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $att->score !== null ? $att->score : 'Dalam Proses' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold 
                                            {{ $att->status === 'graded' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                            {{ $att->status === 'graded' ? 'Selesai' : 'Sedang Berjalan' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        @if($att->status === 'in_progress')
                                            <a href="{{ route('quizzes.take', $att) }}" class="px-3 py-1 rounded-xl bg-amber-500 text-white text-xs font-semibold">
                                                Lanjutkan
                                            </a>
                                        @else
                                            <a href="{{ route('quizzes.result', $att) }}" class="px-3 py-1 rounded-xl bg-indigo-50 text-indigo-600 font-semibold hover:bg-indigo-100 transition">
                                                Review Hasil
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Lecturer Attempts Monitoring Table -->
        @if($isLecturer && $allAttempts->isNotEmpty())
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="users" class="w-5 h-5 text-indigo-600"></i>
                        <span>Hasil Percobaan Mahasiswa</span>
                    </h2>
                    <span class="text-xs font-semibold text-slate-500">{{ $allAttempts->count() }} Percobaan Terdata</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 border-b border-slate-200/80 text-[11px] uppercase font-bold text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Mahasiswa</th>
                                <th class="px-4 py-3">Attempt #</th>
                                <th class="px-4 py-3">Waktu Selesai</th>
                                <th class="px-4 py-3">Skor</th>
                                <th class="px-4 py-3">Kelulusan</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($allAttempts as $att)
                                <tr>
                                    <td class="px-4 py-3 font-bold text-slate-800">{{ $att->student->name }}</td>
                                    <td class="px-4 py-3 font-mono">Attempt {{ $att->attempt_number }}</td>
                                    <td class="px-4 py-3">{{ $att->submitted_at?->isoFormat('D MMM, HH:mm') ?? '-' }}</td>
                                    <td class="px-4 py-3 font-bold text-sm">{{ $att->score ?? '0' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold 
                                            {{ ($att->score >= $quiz->passing_score) ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                            {{ ($att->score >= $quiz->passing_score) ? 'Lulus' : 'Tidak Lulus' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('quizzes.result', $att) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">
                                            Rincian Jawaban
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>

