<x-app-layout :pageHeader="'Hasil Kuis: ' . $quiz->title">
    @php
        $totalQ = $quiz->questions->count();
        $correctCount = $attempt->answers->where('is_correct', true)->count();
        $incorrectCount = $attempt->answers->where('is_correct', false)->count();
        $unansweredCount = max(0, $totalQ - ($correctCount + $incorrectCount));
    @endphp

    <div class="max-w-4xl mx-auto space-y-6 motion-fade-in">
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-xs text-slate-400">
            <a href="{{ route('courses.show', $course) }}" class="hover:text-indigo-600 transition">{{ $course->code }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            <a href="{{ route('quizzes.show', $quiz) }}" class="hover:text-indigo-600 transition">{{ $quiz->title }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            <span class="text-slate-700 font-semibold">Hasil Kuis</span>
        </div>

        <!-- Score Summary Card -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 text-center space-y-4">
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

            <!-- Stats breakdown -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 max-w-xl mx-auto pt-2">
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 text-center">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Soal</span>
                    <span class="text-base font-extrabold text-slate-800">{{ $totalQ }} Butir</span>
                </div>
                <div class="p-3 rounded-2xl bg-emerald-50/60 border border-emerald-100 text-center">
                    <span class="text-[10px] uppercase font-bold text-emerald-600 block">Jawaban Benar</span>
                    <span class="text-base font-extrabold text-emerald-700">{{ $correctCount }} Soal</span>
                </div>
                <div class="p-3 rounded-2xl bg-rose-50/60 border border-rose-100 text-center">
                    <span class="text-[10px] uppercase font-bold text-rose-600 block">Jawaban Salah</span>
                    <span class="text-base font-extrabold text-rose-700">{{ $incorrectCount }} Soal</span>
                </div>
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 text-center">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Akurasi</span>
                    <span class="text-base font-extrabold text-indigo-600">
                        {{ $totalQ > 0 ? round(($correctCount / $totalQ) * 100) : 0 }}%
                    </span>
                </div>
            </div>

            <p class="text-xs text-slate-400 max-w-md mx-auto pt-2">
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
                <span>Tinjauan Jawaban Butir Soal ({{ $totalQ }} Butir)</span>
            </h2>

            @foreach($quiz->questions as $index => $q)
                @php
                    $ans = $attempt->answers->firstWhere('question_id', $q->id);
                    $isCorrect = $ans?->is_correct;
                @endphp
                <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-3">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <span class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-xs shrink-0 font-mono">
                                {{ $index + 1 }}
                            </span>
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                                        Soal {{ $index + 1 }} dari {{ $totalQ }} • Bobot: {{ $q->points }} Poin
                                    </span>
                                </div>
                                <p class="text-sm font-bold text-slate-800">{{ $q->text }}</p>
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
                        <div class="pl-0 sm:pl-11 space-y-2 text-xs">
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
                        <div class="pl-0 sm:pl-11 text-xs space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <p class="font-bold text-slate-700">{{ $isLecturer ? 'Jawaban Mahasiswa:' : 'Jawaban Anda:' }}</p>
                                
                                @if($isLecturer && $ans && filled($ans->answer_text))
                                    <button type="button" id="btn-ai-essay-{{ $ans->id }}" onclick="summarizeEssayAi({{ $ans->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gradient-to-r from-purple-50 to-indigo-50 hover:from-purple-100 hover:to-indigo-100 text-purple-700 border border-purple-200 text-xs font-bold transition shadow-2xs">
                                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-purple-600"></i>
                                        <span>Ringkas & Analisis dengan AI</span>
                                    </button>
                                @endif
                            </div>

                            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-slate-700 whitespace-pre-line leading-relaxed">
                                {{ $ans?->answer_text ?? 'Tidak dijawab.' }}
                            </div>

                            @if($isLecturer && $ans)
                                <!-- AI Essay Analysis Box (Feature 3) -->
                                <div id="ai-essay-box-{{ $ans->id }}" class="hidden p-4 rounded-2xl bg-gradient-to-br from-purple-50/70 to-indigo-50/60 border border-purple-200 space-y-3">
                                    <div class="flex items-center gap-2">
                                        <span class="p-1.5 rounded-lg bg-purple-600 text-white shadow-xs">
                                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                                        </span>
                                        <div>
                                            <h4 class="text-xs font-bold text-slate-800">Ringkasan & Referensi Jawaban AI</h4>
                                            <p class="text-[10px] text-slate-500">Meringkas esai panjang agar dosen dapat menilai dengan cepat</p>
                                        </div>
                                    </div>

                                    <div id="ai-essay-loading-{{ $ans->id }}" class="py-3 text-center space-y-2">
                                        <div class="inline-block animate-spin rounded-full h-5 w-5 border-2 border-purple-600 border-t-transparent"></div>
                                        <p class="text-xs font-semibold text-purple-700 animate-pulse">Gemini AI sedang membaca dan meringkas esai mahasiswa...</p>
                                    </div>

                                    <div id="ai-essay-error-{{ $ans->id }}" class="hidden p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700"></div>

                                    <div id="ai-essay-content-{{ $ans->id }}" class="hidden space-y-2 text-xs">
                                        <div class="p-3 rounded-xl bg-white border border-purple-100 shadow-2xs">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-purple-700 block mb-1">
                                                📝 Intisari Jawaban Mahasiswa
                                            </span>
                                            <p id="ai-essay-summary-text-{{ $ans->id }}" class="text-slate-700 leading-relaxed whitespace-pre-line"></p>
                                        </div>

                                        <div class="p-3 rounded-xl bg-white border border-purple-100 shadow-2xs">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-700 block mb-1">
                                                🎯 Evaluasi Kesesuaian dengan Soal
                                            </span>
                                            <p id="ai-essay-eval-text-{{ $ans->id }}" class="text-slate-700 leading-relaxed whitespace-pre-line"></p>
                                        </div>

                                        <div class="p-3 rounded-xl bg-emerald-50/80 border border-emerald-200 flex items-center justify-between">
                                            <div>
                                                <span class="text-[10px] uppercase font-bold text-emerald-700 block">Saran Skor AI</span>
                                                <span id="ai-essay-score-text-{{ $ans->id }}" class="text-base font-black text-emerald-800">0</span>
                                                <span class="text-[10px] text-emerald-600 font-semibold">/ {{ $q->points }} Poin</span>
                                            </div>
                                            <button type="button" onclick="applyEssayScore({{ $ans->id }})" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold transition shadow-xs">
                                                Gunakan Nilai Ini
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Lecturer Essay Grading Form -->
                                <form method="POST" action="{{ route('quiz-answers.grade-essay', $ans) }}" class="p-3.5 rounded-2xl bg-white border border-slate-200/90 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    @csrf
                                    <div class="flex items-center gap-2">
                                        <label class="text-xs font-semibold text-slate-700 whitespace-nowrap">Beri Skor Esai (Maks: {{ $q->points }} Poin):</label>
                                        <input type="number" step="0.1" min="0" max="{{ $q->points }}" name="score" id="essay-score-input-{{ $ans->id }}" value="{{ $ans->score !== null ? $ans->score : '' }}" required placeholder="0 - {{ $q->points }}"
                                            class="w-28 px-3 py-1.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500 outline-none">
                                    </div>
                                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm transition">
                                        Simpan Nilai Esai
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    @if($isLecturer)
        <script>
            const aiEssayScores = {};

            function summarizeEssayAi(answerId) {
                const box = document.getElementById('ai-essay-box-' + answerId);
                const btn = document.getElementById('btn-ai-essay-' + answerId);
                const loading = document.getElementById('ai-essay-loading-' + answerId);
                const errorBox = document.getElementById('ai-essay-error-' + answerId);
                const content = document.getElementById('ai-essay-content-' + answerId);

                if (!box) return;

                box.classList.remove('hidden');
                loading.classList.remove('hidden');
                errorBox.classList.add('hidden');
                content.classList.add('hidden');
                if (btn) btn.disabled = true;

                fetch('/quiz-answers/' + answerId + '/ai-evaluate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    loading.classList.add('hidden');
                    if (btn) btn.disabled = false;

                    if (data.success && data.data) {
                        const info = data.data;
                        aiEssayScores[answerId] = info.suggested_score;

                        document.getElementById('ai-essay-summary-text-' + answerId).textContent = info.summary || '-';
                        document.getElementById('ai-essay-eval-text-' + answerId).textContent = info.evaluation || '-';
                        document.getElementById('ai-essay-score-text-' + answerId).textContent = info.suggested_score ?? '0';

                        content.classList.remove('hidden');
                    } else {
                        errorBox.textContent = data.message || 'Gagal memproses dengan AI.';
                        errorBox.classList.remove('hidden');
                    }
                })
                .catch(err => {
                    loading.classList.add('hidden');
                    if (btn) btn.disabled = false;
                    errorBox.textContent = 'Gagal menghubungi server atau API Gemini: ' + err.message;
                    errorBox.classList.remove('hidden');
                });
            }

            function applyEssayScore(answerId) {
                const input = document.getElementById('essay-score-input-' + answerId);
                if (input && aiEssayScores[answerId] !== undefined) {
                    input.value = aiEssayScores[answerId];
                }
            }
        </script>
    @endif
</x-app-layout>
