<x-app-layout :pageHeader="'Kelola Soal: ' . $quiz->title">
    <div class="max-w-4xl mx-auto space-y-8 motion-fade-in">
        <!-- Breadcrumb -->
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('courses.show', $course) }}" class="hover:text-indigo-600 transition">{{ $course->code }}</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                <a href="{{ route('quizzes.show', $quiz) }}" class="hover:text-indigo-600 transition">{{ $quiz->title }}</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                <span class="text-slate-700 font-semibold">Kelola Soal</span>
            </div>

            <a href="{{ route('quizzes.show', $quiz) }}" class="px-3.5 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                Selesai & Kembali
            </a>
        </div>

        <!-- Add Question Form Card -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
            <h2 class="text-base font-bold text-slate-800 mb-1 flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-5 h-5 text-indigo-600"></i>
                <span>Tambah Butir Soal Baru</span>
            </h2>
            <p class="text-xs text-slate-400 mb-6">Pilih format soal pilihan ganda, benar/salah, atau esai</p>

            <form method="POST" action="{{ route('quizzes.questions.store', $quiz) }}" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe Soal</label>
                        <select name="type" id="q-type-select" onchange="toggleQuestionFields(this.value)" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm">
                            <option value="multiple_choice">Pilihan Ganda (Multiple Choice)</option>
                            <option value="true_false">Benar / Salah (True/False)</option>
                            <option value="essay">Esai / Jawaban Uraian</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Bobot Nilai (Poin)</label>
                        <input type="number" step="0.5" name="points" value="25" min="0.5" max="100" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Teks Pertanyaan / Soal</label>
                    <textarea name="text" rows="3" required placeholder="Tuliskan pertanyaan di sini..." class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"></textarea>
                </div>

                <!-- Multiple Choice Options -->
                <div id="mc-options-container" class="space-y-3 pt-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Opsi Pilihan Jawaban (Pilih Radio untuk Jawaban Benar)</label>
                    
                    @for($i = 0; $i < 4; $i++)
                        <div class="flex items-center gap-3 p-2 rounded-2xl bg-slate-50 border border-slate-200">
                            <input type="radio" name="correct_option" value="{{ $i }}" {{ $i === 0 ? 'checked' : '' }}
                                class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 ml-2" title="Tandai sebagai jawaban benar">
                            <input type="text" name="options[]" placeholder="Pilihan {{ chr(65 + $i) }}"
                                class="flex-1 px-3 py-1.5 rounded-xl border border-slate-300 bg-white text-xs outline-none focus:ring-2 focus:ring-indigo-500" />
                        </div>
                    @endfor
                </div>

                <!-- True/False Selection -->
                <div id="tf-options-container" class="space-y-2 pt-2 hidden">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Kunci Jawaban</label>
                    <div class="flex items-center gap-6 p-3 rounded-2xl bg-slate-50 border border-slate-200 text-xs">
                        <label class="flex items-center gap-2 cursor-pointer font-semibold text-slate-800">
                            <input type="radio" name="tf_correct" value="true" checked class="w-4 h-4 text-indigo-600">
                            <span>Benar (True)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer font-semibold text-slate-800">
                            <input type="radio" name="tf_correct" value="false" class="w-4 h-4 text-indigo-600">
                            <span>Salah (False)</span>
                        </label>
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/25 transition">
                        Tambahkan Soal Ini
                    </button>
                </div>
            </form>
        </div>

        <!-- Existing Questions List -->
        <div class="space-y-4">
            <h2 class="text-base font-bold text-slate-800 flex items-center justify-between">
                <span>Daftar Soal Tersimpan ({{ $quiz->questions->count() }})</span>
                <span class="text-xs font-normal text-slate-400">Total Poin: {{ $quiz->questions->sum('points') }}</span>
            </h2>

            @if($quiz->questions->isEmpty())
                <div class="bg-white rounded-3xl border border-slate-200/80 p-8 text-center text-xs text-slate-400">
                    Belum ada butir soal. Buat soal pertama di atas.
                </div>
            @else
                <div class="space-y-4">
                    @foreach($quiz->questions as $index => $q)
                        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-3">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-start gap-3">
                                    <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-700 font-bold flex items-center justify-center text-xs shrink-0 font-mono">
                                        {{ $index + 1 }}
                                    </span>
                                    <div>
                                        <p class="text-sm font-bold text-slate-800">{{ $q->text }}</p>
                                        <div class="mt-1 flex items-center gap-2 text-[11px] text-slate-400">
                                            <span class="capitalize font-medium">{{ str_replace('_', ' ', $q->type) }}</span>
                                            <span>•</span>
                                            <span>{{ $q->points }} Poin</span>
                                        </div>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('questions.destroy', $q) }}" onsubmit="return confirm('Hapus butir soal ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 transition" title="Hapus Soal">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>

                            @if($q->options->isNotEmpty())
                                <div class="pl-10 grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                    @foreach($q->options as $opt)
                                        <div class="p-2.5 rounded-xl border flex items-center gap-2 {{ $opt->is_correct ? 'bg-emerald-50/70 border-emerald-300 text-emerald-900 font-semibold' : 'bg-slate-50 border-slate-200 text-slate-600' }}">
                                            <i data-lucide="{{ $opt->is_correct ? 'check-circle-2' : 'circle' }}" class="w-4 h-4 {{ $opt->is_correct ? 'text-emerald-600' : 'text-slate-300' }} shrink-0"></i>
                                            <span class="truncate">{{ $opt->option_text }}</span>
                                            @if($opt->is_correct)
                                                <span class="ml-auto text-[10px] uppercase font-bold text-emerald-600">Kunci</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <script>
        function toggleQuestionFields(type) {
            const mcContainer = document.getElementById('mc-options-container');
            const tfContainer = document.getElementById('tf-options-container');

            if (type === 'multiple_choice') {
                mcContainer.classList.remove('hidden');
                tfContainer.classList.add('hidden');
            } else if (type === 'true_false') {
                mcContainer.classList.add('hidden');
                tfContainer.classList.remove('hidden');
            } else {
                mcContainer.classList.add('hidden');
                tfContainer.classList.add('hidden');
            }
        }
    </script>
</x-app-layout>

