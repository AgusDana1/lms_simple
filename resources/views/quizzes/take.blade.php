<x-app-layout :pageHeader="'Pengerjaan Kuis: ' . $quiz->title">
    <div class="max-w-3xl mx-auto space-y-6 motion-fade-in relative">
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
                <span class="text-[10px] sm:text-[11px] opacity-75">{{ $questions->count() }} Butir Soal</span>
            </div>
        </div>

        <!-- Quiz Form -->
        <form id="quiz-attempt-form" method="POST" action="{{ route('quizzes.submit', $attempt) }}" onsubmit="return confirm('Apakah Anda yakin ingin mengumpulkan kuis sekarang? Pastikan seluruh soal telah dijawab.');" class="space-y-4 sm:space-y-6">
            @csrf

            @foreach($questions as $index => $q)
                @php
                    $savedAns = $savedAnswers->get($q->id);
                @endphp
                <div class="motion-card bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-sm p-4 sm:p-6 lg:p-8 space-y-4">
                    <div class="flex items-start gap-3">
                        <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-indigo-100 text-indigo-700 font-extrabold flex items-center justify-center text-xs shrink-0 font-mono">
                            {{ $index + 1 }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[10px] sm:text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                                    {{ str_replace('_', ' ', $q->type) }}
                                </span>
                                <span class="text-[10px] sm:text-[11px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-lg">
                                    {{ $q->points }} Poin
                                </span>
                            </div>
                            <p class="text-sm sm:text-base font-bold text-slate-800 leading-snug">
                                {{ $q->text }}
                            </p>
                        </div>
                    </div>

                    <!-- Answers choices -->
                    <div class="pl-0 sm:pl-11 pt-1 sm:pt-2 space-y-2 sm:space-y-2.5">
                        @if($q->type === 'multiple_choice' || $q->type === 'true_false')
                            @foreach($q->options as $opt)
                                <label class="flex items-center gap-3 p-3 sm:p-3.5 rounded-xl sm:rounded-2xl border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50/30 transition cursor-pointer group">
                                    <input type="radio" name="answers[{{ $q->id }}]" value="{{ $opt->id }}"
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
                                class="w-full px-4 py-3 rounded-xl sm:rounded-2xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 outline-none">{{ $savedAns?->answer_text }}</textarea>
                        @endif
                    </div>
                </div>
            @endforeach

            <!-- Submit Bottom Bar -->
            <div class="p-4 sm:p-6 bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                <span class="text-xs text-slate-500">Periksa kembali jawaban Anda sebelum mengumpulkan</span>
                <button type="submit" class="w-full sm:w-auto px-8 py-3 rounded-xl sm:rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-bold shadow-lg shadow-indigo-600/30 transition transform hover:scale-102">
                    Kumpulkan Kuis
                </button>
            </div>
        </form>
    </div>

    <!-- Initialize Timer Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.initQuizTimer) {
                window.initQuizTimer({{ $remainingSeconds }}, 'quiz-attempt-form');
            }
        });
    </script>
</x-app-layout>

