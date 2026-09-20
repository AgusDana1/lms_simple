<x-app-layout :pageHeader="'Hasil Kuis: ' . $quiz->title">
    <div class="max-w-3xl mx-auto space-y-6 motion-fade-in">
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-xs text-slate-400">
            <a href="{{ route('courses.show', $course) }}" class="hover:text-indigo-600 transition">{{ $course->code }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            <a href="{{ route('quizzes.show', $quiz) }}" class="hover:text-indigo-600 transition">{{ $quiz->title }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            <span class="text-slate-700 font-semibold">Hasil Kuis</span>
        </div>

        <!-- Score Summary Card -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-8 text-center space-y-4">
            <div class="inline-flex p-4 rounded-3xl {{ $isPassed ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} mb-2">
                <i data-lucide="{{ $isPassed ? 'award' : 'alert-circle' }}" class="w-12 h-12"></i>
            </div>

            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Skor Akhir Percobaan</span>
                <div class="flex items-baseline justify-center gap-1.5 mt-1">
                    <span class="text-5xl font-black {{ $isPassed ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $attempt->score ?? '0' }}
                    </span>
                    <span class="text-lg font-bold text-slate-400">/ 100</span>
                </div>
            </div>

            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold 
                    {{ $isPassed ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                    {{ $isPassed ? 'LULUS (MEMENUHI SYARAT)' : 'BELUM MEMENUHI PASSING GRADE' }}
                </span>
            </div>

            <p class="text-xs text-slate-400 max-w-sm mx-auto">
                Passing grade minimal: <strong>{{ $quiz->passing_score ?? 60 }}</strong> poin.
                Diselesaikan pada {{ $attempt->submitted_at?->isoFormat('D MMMM Y, HH:mm') }}.
            </p>

            <div class="pt-4 flex items-center justify-center gap-3">
                <a href="{{ route('quizzes.show', $quiz) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Kembali ke Ringkasan Kuis
                </a>
                <a href="{{ route('courses.show', $course) }}" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow transition">
                    Buka Kelas
                </a>
            </div>
        </div>

        <!-- Question-by-Question Detailed Review -->
        <div class="space-y-4">
            <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <i data-lucide="check-square" class="w-5 h-5 text-indigo-600"></i>
                <span>Tinjauan Jawaban Butir Soal</span>
            </h2>

            @foreach($quiz->questions as $index => $q)
                @php
                    $ans = $attempt->answers->firstWhere('question_id', $q->id);
                    $isCorrect = $ans?->is_correct;
                @endphp
                <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-3">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <span class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-xs shrink-0 font-mono">
                                {{ $index + 1 }}
                            </span>
                            <div>
                                <p class="text-sm font-bold text-slate-800">{{ $q->text }}</p>
                                <span class="text-[11px] text-slate-400">Bobot: {{ $q->points }} Poin</span>
                            </div>
                        </div>

                        @if($isCorrect !== null)
                            <span class="px-2.5 py-1 rounded-xl text-xs font-bold flex items-center gap-1 shrink-0 
                                {{ $isCorrect ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                <i data-lucide="{{ $isCorrect ? 'check' : 'x' }}" class="w-3.5 h-3.5"></i>
                                {{ $isCorrect ? '+' . $q->points . ' Poin' : '0 Poin' }}
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[10px] bg-amber-50 text-amber-700 font-semibold shrink-0">
                                Esai (Menunggu Koreksi)
                            </span>
                        @endif
                    </div>

                    <!-- Choices review -->
                    @if($q->options->isNotEmpty())
                        <div class="pl-10 space-y-2 text-xs">
                            @foreach($q->options as $opt)
                                @php
                                    $wasChosen = $ans && $ans->question_option_id === $opt->id;
                                    $isCorrectOpt = $opt->is_correct;
                                @endphp
                                <div class="p-3 rounded-xl border flex items-center justify-between gap-2 
                                    {{ $isCorrectOpt ? 'bg-emerald-50/80 border-emerald-300 text-emerald-900 font-medium' : ($wasChosen ? 'bg-rose-50 border-rose-300 text-rose-900' : 'bg-slate-50 border-slate-200 text-slate-600') }}">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="{{ $isCorrectOpt ? 'check-circle-2' : ($wasChosen ? 'x-circle' : 'circle') }}" class="w-4 h-4 shrink-0 {{ $isCorrectOpt ? 'text-emerald-600' : ($wasChosen ? 'text-rose-500' : 'text-slate-300') }}"></i>
                                        <span>{{ $opt->option_text }}</span>
                                    </div>
                                    <div>
                                        @if($wasChosen)
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-white shadow-xs">Pilihan Anda</span>
                                        @endif
                                        @if($isCorrectOpt)
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 ml-1">Kunci Benar</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <!-- Essay review -->
                        <div class="pl-10 text-xs">
                            <p class="font-bold text-slate-700 mb-1">Jawaban Anda:</p>
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 whitespace-pre-line">
                                {{ $ans?->answer_text ?? 'Tidak dijawab.' }}
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>

