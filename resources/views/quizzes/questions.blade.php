<x-app-layout :pageHeader="'Kelola Soal: ' . $quiz->title">
    @php
        $totalPoints = (float) $quiz->questions->sum('points');
        $questionCount = $quiz->questions->count();
        $isExact100 = abs($totalPoints - 100) < 0.01;
        $suggestedPoints = $questionCount > 0 ? round(100 / ($questionCount + 1), 2) : 100;
    @endphp

    <div class="max-w-4xl mx-auto space-y-8 motion-fade-in">
        <!-- Breadcrumb & Top Bar -->
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

        <!-- Point Distribution & Status Banner -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="calculator" class="w-5 h-5 text-indigo-600"></i>
                        <span>Distribusi Bobot Soal (Patokan Total: 100 Poin)</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Kuis dapat memuat berapa pun butir soal (misal: 10, 20, 50, atau 100 soal). Sistem selalu menjaga patokan total nilai kuis adalah 100 poin.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    @if($isExact100)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            Total Bobot Tepat 100 Poin
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 text-amber-700 text-xs font-bold border border-amber-200">
                            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                            Total: {{ $totalPoints }} Poin (Selisih {{ 100 - $totalPoints }})
                        </span>
                    @endif
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-2">
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Jumlah Soal Saat Ini</span>
                    <span class="text-lg font-black text-slate-800">{{ $questionCount }} Butir Soal</span>
                </div>
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Total Bobot Saat Ini</span>
                    <span class="text-lg font-black {{ $isExact100 ? 'text-emerald-600' : 'text-amber-600' }}">{{ $totalPoints }} / 100 Poin</span>
                </div>
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 col-span-2 sm:col-span-1">
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Rerata Bobot / Soal</span>
                    <span class="text-lg font-black text-indigo-600">
                        {{ $questionCount > 0 ? round($totalPoints / $questionCount, 2) : 0 }} Poin / Soal
                    </span>
                </div>
            </div>

            <!-- Action Buttons: Distribute Points & Bulk Add Questions -->
            <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                <div class="text-xs text-slate-500">
                    Alat bantu penyesuaian bobot & penambahan soal massal:
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @if($questionCount > 0)
                        <form method="POST" action="{{ route('quizzes.distribute-points', $quiz) }}" onsubmit="return confirm('Bagi rata bobot seluruh {{ $questionCount }} soal agar berjumlah tepat 100 poin (masing-masing ~{{ round(100 / $questionCount, 2) }} poin)?');">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold transition">
                                <i data-lucide="scale" class="w-4 h-4"></i>
                                <span>Bagi Rata Bobot (Total 100 Poin)</span>
                            </button>
                        </form>
                    @endif

                    <!-- AI Quiz Generator Button (Feature 2) -->
                    <button type="button" onclick="openAiGenerateModal()" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 hover:from-indigo-700 hover:to-pink-700 text-white text-xs font-bold shadow-md shadow-indigo-600/25 transition transform hover:scale-102">
                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                        <span>Buat Soal dengan AI</span>
                    </button>

                    <!-- Batch add template buttons -->
                    <div class="flex items-center gap-1.5">
                        <form method="POST" action="{{ route('quizzes.generate-template', $quiz) }}" onsubmit="return confirm('Tambahkan 5 butir soal template sekaligus? Total bobot akan otomatis dibagi rata menjadi 100 poin.');">
                            @csrf
                            <input type="hidden" name="count" value="5">
                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition" title="Tambah 5 soal sekaligus">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i> +5 Soal
                            </button>
                        </form>

                        <form method="POST" action="{{ route('quizzes.generate-template', $quiz) }}" onsubmit="return confirm('Tambahkan 10 butir soal template sekaligus? Total bobot akan otomatis dibagi rata menjadi 100 poin.');">
                            @csrf
                            <input type="hidden" name="count" value="10">
                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition" title="Tambah 10 soal sekaligus">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i> +10 Soal
                            </button>
                        </form>

                        <form method="POST" action="{{ route('quizzes.generate-template', $quiz) }}" onsubmit="return confirm('Tambahkan 20 butir soal template sekaligus? Total bobot akan otomatis dibagi rata menjadi 100 poin.');">
                            @csrf
                            <input type="hidden" name="count" value="20">
                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition" title="Tambah 20 soal sekaligus">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i> +20 Soal
                            </button>
                        </form>

                        <button type="button" onclick="openCustomBatchModal()" class="inline-flex items-center gap-1 px-3 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold transition">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5"></i> +N Soal Bebas
                        </button>
                    </div>
                </div>
            </div>
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
                        <input type="number" step="0.01" name="points" value="{{ $suggestedPoints }}" min="0.01" max="100" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm" />
                        <span class="text-[10px] text-slate-400 mt-1 block">Patokan kuis selalu 100 poin (misal 10 soal = @ 10 poin, 20 soal = @ 5 poin, 50 soal = @ 2 poin).</span>
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

                <div>
                    <label class="flex items-center gap-2 text-xs text-slate-600 cursor-pointer">
                        <input type="checkbox" name="auto_distribute" value="1" checked class="rounded text-indigo-600">
                        <span>Otomatis bagi rata bobot seluruh butir soal agar pas 100 poin setelah menambah soal ini (Disarankan)</span>
                    </label>
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
                <span>Daftar Soal Tersimpan ({{ $questionCount }} Butir)</span>
                <span class="text-xs font-semibold {{ $isExact100 ? 'text-emerald-600' : 'text-slate-500' }}">Total Bobot: {{ $totalPoints }} / 100 Poin</span>
            </h2>

            @if($quiz->questions->isEmpty())
                <div class="bg-white rounded-3xl border border-slate-200/80 p-8 text-center text-xs text-slate-400">
                    Belum ada butir soal. Buat soal pertama di atas atau gunakan tombol penambahan massal (+5, +10, +20, atau +N Soal) untuk membuat banyak soal sekaligus.
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
                                            <span class="font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-lg">{{ $q->points }} Poin</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1.5 shrink-0">
                                    <!-- Edit Button -->
                                    <button type="button" onclick="openEditQuestionModal({{ $q->id }})" class="p-1.5 text-slate-400 hover:text-indigo-600 transition" title="Edit Soal">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>

                                    <!-- Delete Button -->
                                    <form method="POST" action="{{ route('questions.destroy', $q) }}" onsubmit="return confirm('Hapus butir soal ini? Bobot soal lain akan otomatis disesuaikan agar total tetap 100 poin.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 transition" title="Hapus Soal">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
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

                        <!-- Edit Modal for Question {{ $q->id }} -->
                        <div id="edit-modal-{{ $q->id }}" class="fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-4">
                            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeEditQuestionModal({{ $q->id }})"></div>
                            <div class="relative bg-white rounded-2xl sm:rounded-3xl shadow-2xl max-w-lg w-full p-6 sm:p-8 z-10 max-h-[90vh] overflow-y-auto">
                                <h3 class="text-base font-bold text-slate-800 mb-4">Edit Butir Soal #{{ $index + 1 }}</h3>

                                <form method="POST" action="{{ route('questions.update', $q) }}" class="space-y-4">
                                    @csrf
                                    @method('PUT')

                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe Soal</label>
                                            <select name="type" id="edit-type-{{ $q->id }}" onchange="toggleEditType({{ $q->id }}, this.value)" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                                                <option value="multiple_choice" {{ $q->type === 'multiple_choice' ? 'selected' : '' }}>Pilihan Ganda</option>
                                                <option value="true_false" {{ $q->type === 'true_false' ? 'selected' : '' }}>Benar / Salah</option>
                                                <option value="essay" {{ $q->type === 'essay' ? 'selected' : '' }}>Esai</option>
                                            </select>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Bobot Nilai (Poin)</label>
                                            <input type="number" step="0.01" name="points" value="{{ $q->points }}" min="0.01" max="100" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs" />
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1">Teks Pertanyaan</label>
                                        <textarea name="text" rows="3" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500 outline-none">{{ $q->text }}</textarea>
                                    </div>

                                    <!-- Multiple choice edit -->
                                    <div id="edit-mc-{{ $q->id }}" class="space-y-2 {{ $q->type === 'multiple_choice' ? '' : 'hidden' }}">
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Opsi Jawaban & Kunci</label>
                                        @php
                                            $opts = $q->options;
                                        @endphp
                                        @for($optIdx = 0; $optIdx < 4; $optIdx++)
                                            @php
                                                $curOpt = $opts[$optIdx] ?? null;
                                                $isCorr = $curOpt ? (bool) $curOpt->is_correct : ($optIdx === 0);
                                            @endphp
                                            <div class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 border border-slate-200">
                                                <input type="radio" name="correct_option" value="{{ $optIdx }}" {{ $isCorr ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 ml-1">
                                                <input type="text" name="options[]" value="{{ $curOpt?->option_text ?? '' }}" placeholder="Pilihan {{ chr(65 + $optIdx) }}" class="flex-1 px-2.5 py-1 rounded-lg border border-slate-300 text-xs bg-white">
                                            </div>
                                        @endfor
                                    </div>

                                    <!-- True/False edit -->
                                    <div id="edit-tf-{{ $q->id }}" class="space-y-2 {{ $q->type === 'true_false' ? '' : 'hidden' }}">
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Kunci Jawaban</label>
                                        @php
                                            $trueCorrect = $q->options->firstWhere('option_text', 'Benar (True)')?->is_correct ?? true;
                                        @endphp
                                        <div class="flex items-center gap-6 p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                                            <label class="flex items-center gap-2 cursor-pointer font-semibold text-slate-800">
                                                <input type="radio" name="tf_correct" value="true" {{ $trueCorrect ? 'checked' : '' }} class="w-4 h-4 text-indigo-600">
                                                <span>Benar (True)</span>
                                            </label>
                                            <label class="flex items-center gap-2 cursor-pointer font-semibold text-slate-800">
                                                <input type="radio" name="tf_correct" value="false" {{ ! $trueCorrect ? 'checked' : '' }} class="w-4 h-4 text-indigo-600">
                                                <span>Salah (False)</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="pt-4 flex items-center justify-end gap-2">
                                        <button type="button" onclick="closeEditQuestionModal({{ $q->id }})" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600">Batal</button>
                                        <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Modal Custom Batch Add Questions -->
        <div id="custom-batch-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeCustomBatchModal()"></div>
            <div class="relative bg-white rounded-2xl sm:rounded-3xl shadow-2xl max-w-sm w-full p-6 z-10">
                <h3 class="text-base font-bold text-slate-800 mb-1 flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-5 h-5 text-indigo-600"></i>
                    <span>Tambah Soal Massal</span>
                </h3>
                <p class="text-xs text-slate-500 mb-4">Tambahkan sejumlah butir soal template sekaligus (misal 10, 20, atau 50 soal). Total bobot akan otomatis dibagi rata menjadi 100 poin.</p>

                <form method="POST" action="{{ route('quizzes.generate-template', $quiz) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jumlah Butir Soal Baru</label>
                        <input type="number" name="count" value="10" min="1" max="100" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-bold text-slate-800">
                        <span class="text-[11px] text-slate-400 mt-1 block">Bisa menambah hingga 100 butir soal sekali klik.</span>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" onclick="closeCustomBatchModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow">Tambahkan Soal</button>
                    </div>
                </form>
            </div>
        </div>
        <!-- Modal AI Quiz Question Generator (Feature 2) -->
        <div id="ai-generate-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeAiGenerateModal()"></div>
            <div class="relative bg-white rounded-2xl sm:rounded-3xl shadow-2xl max-w-lg w-full p-6 sm:p-8 z-10 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center gap-2.5 mb-2">
                    <span class="p-2 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-sm">
                        <i data-lucide="sparkles" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Generator Soal Kuis dengan AI</h3>
                        <p class="text-[11px] text-slate-400">Buat butir soal dan opsi jawaban yang kompleks secara otomatis</p>
                    </div>
                </div>

                <form id="ai-generate-form" method="POST" action="{{ route('quizzes.ai-generate', $quiz) }}" class="space-y-4 pt-2" onsubmit="onAiGenerateSubmit(this)">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Topik / Kata Kunci Soal Kuis</label>
                        <input type="text" name="topic" value="{{ $quiz->title }}" required placeholder="Contoh: Eloquent ORM, Relasi Database, dan MVC di Laravel"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-indigo-500 outline-none">
                        <span class="text-[10px] text-slate-400 mt-1 block">Tuliskan kata kunci materi yang ingin diujikan kepada mahasiswa.</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Jumlah Butir Soal</label>
                            <input type="number" name="count" value="5" min="1" max="50" required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-800">
                            <span class="text-[10px] text-slate-400 mt-1 block">Rekomendasi 5 - 20 butir.</span>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tingkat Kesulitan</label>
                            <select name="difficulty" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-700">
                                <option value="sedang" selected>Menengah (Standar Kuliah)</option>
                                <option value="dasar">Dasar (Pemula / Konsep Fundamental)</option>
                                <option value="lanjutan">Lanjutan / HOTS (Analisis Kasus Mendalam)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Instruksi Khusus Dosen (Opsional)</label>
                        <textarea name="custom_instruction" rows="2" placeholder="Contoh: Sertakan contoh potongan sintaks kode dan opsi pengecoh yang menantang..."
                            class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500 outline-none"></textarea>
                    </div>

                    <div id="ai-generate-status" class="hidden p-3 rounded-xl bg-indigo-50 border border-indigo-200 text-xs text-indigo-800 flex items-center gap-2">
                        <div class="inline-block animate-spin rounded-full h-4 w-4 border-2 border-indigo-600 border-t-transparent"></div>
                        <span class="font-medium animate-pulse">Gemini AI sedang merancang butir soal & opsi jawaban kompleks... Mohon tunggu.</span>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                        <button type="button" onclick="closeAiGenerateModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">Batal</button>
                        <button type="submit" id="btn-submit-ai-generate" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white text-xs font-bold shadow-md shadow-indigo-600/25 transition">
                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                            <span>Mulai Generate Soal</span>
                        </button>
                    </div>
                </form>
            </div>
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

        function openEditQuestionModal(id) {
            const modal = document.getElementById('edit-modal-' + id);
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }

        function closeEditQuestionModal(id) {
            const modal = document.getElementById('edit-modal-' + id);
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        function openCustomBatchModal() {
            const modal = document.getElementById('custom-batch-modal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }

        function closeCustomBatchModal() {
            const modal = document.getElementById('custom-batch-modal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        function openAiGenerateModal() {
            const modal = document.getElementById('ai-generate-modal');
            if (modal) {
                document.getElementById('ai-generate-status').classList.add('hidden');
                document.getElementById('btn-submit-ai-generate').disabled = false;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }

        function closeAiGenerateModal() {
            const modal = document.getElementById('ai-generate-modal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        function onAiGenerateSubmit(form) {
            const statusBox = document.getElementById('ai-generate-status');
            const submitBtn = document.getElementById('btn-submit-ai-generate');
            if (statusBox) statusBox.classList.remove('hidden');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="inline-block animate-spin rounded-full h-3.5 w-3.5 border-2 border-white border-t-transparent mr-1"></span> Sedang Membuat...';
            }
        }

        function toggleEditType(id, type) {
            const mc = document.getElementById('edit-mc-' + id);
            const tf = document.getElementById('edit-tf-' + id);
            if (type === 'multiple_choice') {
                mc.classList.remove('hidden');
                tf.classList.add('hidden');
            } else if (type === 'true_false') {
                mc.classList.add('hidden');
                tf.classList.remove('hidden');
            } else {
                mc.classList.add('hidden');
                tf.classList.add('hidden');
            }
        }
    </script>
</x-app-layout>
