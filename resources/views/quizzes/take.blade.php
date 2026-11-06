<x-app-layout :pageHeader="'Pengerjaan Kuis: ' . $quiz->title">
    <div class="max-w-6xl mx-auto space-y-6 motion-fade-in relative">
        <!-- Floating Sticky Countdown Timer Header -->
        <div id="quiz-timer-container" class="sticky top-2 z-30 p-3 sm:p-4 rounded-2xl bg-indigo-50/95 backdrop-blur-md border border-indigo-200 text-indigo-700 shadow-md flex items-center justify-between transition-colors duration-300">
            <div class="flex items-center gap-2.5 sm:gap-3">
                <i data-lucide="timer" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                <div>
                    <span class="text-[9px] sm:text-[10px] uppercase font-bold tracking-wider opacity-75">Sisa Waktu</span>
                    <h3 id="quiz-timer-display" class="text-lg sm:text-2xl font-black font-mono tracking-tight leading-tight">00:00</h3>
                </div>
            </div>
            <div class="text-right">
                <span class="text-xs font-semibold block">Percobaan ke-{{ $attempt->attempt_number }}</span>
                <span class="text-[10px] sm:text-[11px] opacity-75">
                    Terjawab: <strong id="progress-answered-label">0</strong> / {{ $questions->count() }} Butir Soal
                </span>
            </div>
        </div>

        <!-- Quiz Attempt Form -->
        <form id="quiz-attempt-form" method="POST" action="{{ route('quizzes.submit', $attempt) }}">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <!-- Left: Question Cards List (8 cols) -->
                <div class="lg:col-span-8 space-y-5">
                    @foreach($questions as $index => $q)
                        @php
                            $savedAns = $savedAnswers->get($q->id);
                        @endphp
                        <div id="question-card-{{ $index + 1 }}" class="motion-card bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-sm p-5 sm:p-7 space-y-4 scroll-mt-24">
                            <div class="flex items-start gap-3">
                                <span class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 font-extrabold flex items-center justify-center text-xs shrink-0 font-mono">
                                    {{ $index + 1 }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-[10px] sm:text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                                            Soal {{ $index + 1 }} dari {{ $questions->count() }} • {{ str_replace('_', ' ', $q->type) }}
                                        </span>
                                        <span class="text-[10px] sm:text-[11px] font-bold text-indigo-600 bg-indigo-50 px-2.5 py-0.5 rounded-lg border border-indigo-100">
                                            {{ $q->points }} Poin
                                        </span>
                                    </div>
                                    <p class="text-sm sm:text-base font-bold text-slate-800 leading-snug">
                                        {{ $q->text }}
                                    </p>
                                </div>
                            </div>

                            <!-- Answers choices -->
                            <div class="pl-0 sm:pl-11 pt-1 sm:pt-2 space-y-2.5">
                                @if($q->type === 'multiple_choice' || $q->type === 'true_false')
                                    @foreach($q->options as $opt)
                                        <label class="flex items-center gap-3 p-3 sm:p-3.5 rounded-xl sm:rounded-2xl border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50/30 transition cursor-pointer group">
                                            <input type="radio" name="answers[{{ $q->id }}]" value="{{ $opt->id }}"
                                                data-question-idx="{{ $index + 1 }}"
                                                onchange="onAnswerChanged({{ $index + 1 }})"
                                                {{ ($savedAns && $savedAns->question_option_id === $opt->id) ? 'checked' : '' }}
                                                class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 shrink-0">
                                            <span class="text-xs sm:text-sm font-medium text-slate-700 group-hover:text-slate-900">
                                                {{ $opt->option_text }}
                                            </span>
                                        </label>
                                    @endforeach
                                @else
                                    <!-- Essay -->
                                    <textarea name="answers[{{ $q->id }}]" rows="4" placeholder="Tuliskan jawaban uraian Anda di sini..."
                                        data-question-idx="{{ $index + 1 }}"
                                        oninput="onAnswerChanged({{ $index + 1 }})"
                                        class="w-full px-4 py-3 rounded-xl sm:rounded-2xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 outline-none">{{ $savedAns?->answer_text }}</textarea>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <!-- Submit Bottom Bar -->
                    <div class="p-4 sm:p-6 bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                        <span class="text-xs text-slate-500">Periksa seluruh jawaban Anda sebelum mengumpulkan kuis ini.</span>
                        <button type="submit" class="w-full sm:w-auto px-8 py-3 rounded-xl sm:rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-bold shadow-lg shadow-indigo-600/30 transition transform hover:scale-102">
                            Kumpulkan Kuis ({{ $questions->count() }} Soal)
                        </button>
                    </div>
                </div>

                <!-- Right: Navigation Grid Sidebar (4 cols) -->
                <div class="lg:col-span-4 lg:sticky lg:top-24 space-y-4">
                    <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-sm p-5 sm:p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                                <i data-lucide="layout-grid" class="w-4 h-4 text-indigo-600"></i>
                                <span>Navigasi Nomor Soal</span>
                            </h3>
                            <span class="text-[11px] font-semibold text-slate-400">Total: {{ $questions->count() }} Soal</span>
                        </div>

                        <!-- Progress Bar -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-[11px] text-slate-500 font-medium">
                                <span>Progres Terjawab</span>
                                <span id="progress-percent-label">0%</span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                                <div id="progress-bar-fill" class="h-full bg-indigo-600 rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                        </div>

                        <!-- Grid of Question Numbers -->
                        <div class="max-h-[360px] overflow-y-auto pr-1 grid grid-cols-5 gap-1.5 pt-2">
                            @foreach($questions as $index => $q)
                                <button type="button"
                                    id="nav-btn-{{ $index + 1 }}"
                                    onclick="scrollToQuestion({{ $index + 1 }})"
                                    class="h-9 rounded-xl font-bold font-mono text-xs flex items-center justify-center transition border bg-slate-50 text-slate-600 border-slate-200 hover:border-indigo-400">
                                    {{ $index + 1 }}
                                </button>
                            @endforeach
                        </div>

                        <!-- Legend -->
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-md bg-indigo-600"></span>
                                <span>Sudah Dijawab</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-md bg-slate-100 border border-slate-200"></span>
                                <span>Belum Dijawab</span>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition">
                                Kumpulkan Kuis
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Initialize Timer & Nav Script -->
    <script>
        const totalQuestions = {{ $questions->count() }};

        function scrollToQuestion(idx) {
            const card = document.getElementById('question-card-' + idx);
            if (card) {
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        function isQuestionAnswered(idx) {
            const card = document.getElementById('question-card-' + idx);
            if (!card) return false;

            const radios = card.querySelectorAll('input[type="radio"]');
            if (radios.length > 0) {
                for (let r of radios) {
                    if (r.checked) return true;
                }
                return false;
            }

            const textarea = card.querySelector('textarea');
            if (textarea) {
                return textarea.value.trim().length > 0;
            }

            return false;
        }

        function onAnswerChanged(idx) {
            updateNavPill(idx);
            updateOverallProgress();
        }

        function updateNavPill(idx) {
            const btn = document.getElementById('nav-btn-' + idx);
            if (!btn) return;

            if (isQuestionAnswered(idx)) {
                btn.className = 'h-9 rounded-xl font-bold font-mono text-xs flex items-center justify-center transition border bg-indigo-600 text-white border-indigo-600 shadow-sm';
            } else {
                btn.className = 'h-9 rounded-xl font-bold font-mono text-xs flex items-center justify-center transition border bg-slate-50 text-slate-600 border-slate-200 hover:border-indigo-400';
            }
        }

        function updateOverallProgress() {
            let answered = 0;
            for (let i = 1; i <= totalQuestions; i++) {
                if (isQuestionAnswered(i)) {
                    answered++;
                }
            }

            const pct = totalQuestions > 0 ? Math.round((answered / totalQuestions) * 100) : 0;
            const label = document.getElementById('progress-answered-label');
            const pctLabel = document.getElementById('progress-percent-label');
            const fill = document.getElementById('progress-bar-fill');

            if (label) label.textContent = answered;
            if (pctLabel) pctLabel.textContent = pct + '%';
            if (fill) fill.style.width = pct + '%';
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (window.initQuizTimer) {
                window.initQuizTimer({{ $remainingSeconds }}, 'quiz-attempt-form');
            }

            // Initial status update for all question pills
            for (let i = 1; i <= totalQuestions; i++) {
                updateNavPill(i);
            }
            updateOverallProgress();

            const form = document.getElementById('quiz-attempt-form');
            if (form) {
                form.addEventListener('submit', (e) => {
                    let answered = 0;
                    for (let i = 1; i <= totalQuestions; i++) {
                        if (isQuestionAnswered(i)) answered++;
                    }

                    if (answered < totalQuestions) {
                        const confirmMsg = `Perhatian: Anda baru menjawab ${answered} dari ${totalQuestions} butir soal.\nMasih ada ${totalQuestions - answered} soal yang belum dijawab.\n\nApakah Anda yakin ingin tetap mengumpulkan kuis sekarang?`;
                        if (!confirm(confirmMsg)) {
                            e.preventDefault();
                            return false;
                        }
                    } else {
                        const confirmMsg = `Seluruh ${totalQuestions} butir soal telah dijawab.\nApakah Anda yakin ingin mengumpulkan kuis sekarang?`;
                        if (!confirm(confirmMsg)) {
                            e.preventDefault();
                            return false;
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>
