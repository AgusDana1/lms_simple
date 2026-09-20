<x-app-layout :pageHeader="$assignment->title">
    <div class="max-w-4xl mx-auto space-y-6 motion-fade-in">
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-xs text-slate-400">
            <a href="{{ route('courses.show', $course) }}" class="hover:text-indigo-600 transition">{{ $course->code }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            <a href="{{ route('courses.show', ['course' => $course, 'tab' => 'assignments']) }}" class="hover:text-indigo-600 transition">Tugas</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            <span class="text-slate-700 font-semibold truncate">{{ $assignment->title }}</span>
        </div>

        <!-- Assignment Information Card -->
        <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-sm p-5 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-4">
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="px-2.5 py-1 rounded-xl text-xs font-bold font-mono 
                            {{ $assignment->due_at->isPast() ? 'bg-slate-100 text-slate-600' : 'bg-rose-50 text-rose-700 border border-rose-200/60' }}">
                            Tenggat: {{ $assignment->due_at->isoFormat('dddd, D MMMM Y • HH:mm') }}
                        </span>
                        <span class="px-2.5 py-1 rounded-xl bg-indigo-50 text-indigo-700 text-xs font-bold">
                            Maks. {{ $assignment->max_score }} Poin
                        </span>
                        @if($assignment->allow_late_submission)
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700">
                                Keterlambatan Diizinkan
                            </span>
                        @endif
                    </div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                        {{ $assignment->title }}
                    </h1>
                </div>

                @if($isLecturer)
                    <div class="flex items-center gap-2 shrink-0">
                        <form method="POST" action="{{ route('assignments.destroy', $assignment) }}" onsubmit="return confirm('Hapus tugas ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3.5 py-1.5 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-semibold transition">
                                Hapus Tugas
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <div class="pt-4 border-t border-slate-100 text-xs sm:text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                {{ $assignment->description ?? 'Tidak ada petunjuk tertulis untuk tugas ini.' }}
            </div>
        </div>

        <!-- Student Submission Section -->
        @if(auth()->user()->isMahasiswa())
            <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-sm p-5 sm:p-8">
                <h2 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <i data-lucide="upload-cloud" class="w-5 h-5 text-indigo-600"></i>
                    <span>Status Pengumpulan Tugas Anda</span>
                </h2>

                @if($studentSubmission)
                    <div class="space-y-6">
                        <!-- Submission Status Summary -->
                        <div class="p-5 rounded-2xl {{ $studentSubmission->score !== null ? 'bg-emerald-50/70 border border-emerald-200' : 'bg-slate-50 border border-slate-200' }}">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 flex items-center gap-1">
                                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                            Sudah Dikumpulkan
                                        </span>
                                        @if($studentSubmission->is_late)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700">
                                                Terlambat
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-500 mt-1">
                                        Waktu pengumpulan: <strong>{{ $studentSubmission->submitted_at?->isoFormat('D MMMM Y, HH:mm') }}</strong>
                                    </p>
                                </div>

                                @if($studentSubmission->score !== null)
                                    <div class="text-right sm:border-l sm:border-emerald-200 sm:pl-6">
                                        <span class="block text-[11px] font-semibold text-emerald-600 uppercase tracking-wider">Nilai Diperoleh</span>
                                        <span class="text-3xl font-black text-emerald-700">{{ $studentSubmission->score }}</span>
                                        <span class="text-xs text-emerald-600 font-semibold">/ {{ $assignment->max_score }}</span>
                                    </div>
                                @else
                                    <div class="text-right sm:border-l sm:border-slate-200 sm:pl-6">
                                        <span class="px-3 py-1 rounded-xl bg-amber-100 text-amber-800 text-xs font-semibold">
                                            Menunggu Penilaian Dosen
                                        </span>
                                    </div>
                                @endif
                            </div>

                            @if($studentSubmission->feedback)
                                <div class="mt-4 pt-4 border-t border-emerald-200/60">
                                    <p class="text-xs font-bold text-slate-700 mb-1 flex items-center gap-1.5">
                                        <i data-lucide="message-square" class="w-3.5 h-3.5 text-indigo-600"></i>
                                        <span>Catatan / Umpan Balik Dosen:</span>
                                    </p>
                                    <p class="text-xs text-slate-600 italic bg-white/70 p-3 rounded-xl border border-emerald-100">
                                        "{{ $studentSubmission->feedback }}"
                                    </p>
                                </div>
                            @endif
                        </div>

                        <!-- Submitted Content / Attachment -->
                        <div class="space-y-3">
                            <h3 class="text-xs font-bold uppercase text-slate-400 tracking-wider">Rincian Jawaban</h3>
                            @if($studentSubmission->content)
                                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 whitespace-pre-line leading-relaxed">
                                    {{ $studentSubmission->content }}
                                </div>
                            @endif

                            @if($studentSubmission->file_path)
                                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <i data-lucide="file-check" class="w-5 h-5 text-indigo-600 shrink-0"></i>
                                        <span class="text-xs font-bold text-slate-800 truncate">{{ $studentSubmission->file_name ?? 'Berkas Tugas' }}</span>
                                    </div>
                                    <a href="{{ route('submissions.download', $studentSubmission) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition">
                                        <i data-lucide="download" class="w-3.5 h-3.5"></i> Unduh
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <!-- Submission Form -->
                    <form method="POST" action="{{ route('assignments.submit', $assignment) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Teks Jawaban / Catatan Pengumpulan</label>
                            <textarea name="content" rows="4" placeholder="Tuliskan ringkasan hasil pengerjaan, tautan repository, atau catatan untuk dosen..." class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Unggah Berkas Tugas (PDF, ZIP, DOCX - Maks 20MB)</label>
                            <input type="file" name="file" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 cursor-pointer" />
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/25 transition">
                                Kumpulkan Tugas Sekarang
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        @endif

        <!-- Lecturer Submissions Overview Section -->
        @if($isLecturer)            <!-- LECTURER: SUBMISSIONS REVIEW & GRADING -->
            <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-sm p-5 sm:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Daftar Pengumpulan Mahasiswa</h2>
                        <p class="text-xs text-slate-400">Tinjau berkas yang dikumpulkan dan berikan skor penilaian</p>
                    </div>
                    <span class="px-3 py-1 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold font-mono w-max">
                        {{ $allSubmissions->count() }} Terkumpul
                    </span>
                </div>

                @if($allSubmissions->isEmpty())
                    <p class="text-xs text-slate-400 text-center py-8">Belum ada mahasiswa yang mengumpulkan tugas ini.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-600">
                            <thead class="bg-slate-50 border-b border-slate-200/80 text-[11px] uppercase font-bold text-slate-400">
                                <tr>
                                    <th class="px-4 py-3">Mahasiswa</th>
                                    <th class="px-4 py-3">Waktu Kumpul</th>
                                    <th class="px-4 py-3">Berkas</th>
                                    <th class="px-4 py-3">Nilai</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($allSubmissions as $sub)
                                    <tr class="hover:bg-slate-50/60 transition">
                                        <td class="px-4 py-3">
                                            <p class="font-bold text-slate-800">{{ $sub->student->name }}</p>
                                            <span class="text-[11px] text-slate-400 font-mono">{{ $sub->student->studentProfile?->student_number }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span>{{ $sub->submitted_at?->isoFormat('D MMM, HH:mm') }}</span>
                                            @if($sub->is_late)
                                                <span class="ml-1 text-[10px] font-bold text-rose-500">Terlambat</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($sub->file_path)
                                                <a href="{{ route('submissions.download', $sub) }}" class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-800 font-medium">
                                                    <i data-lucide="download" class="w-3.5 h-3.5"></i> Berkas
                                                </a>
                                            @else
                                                <span class="text-slate-400">Hanya Teks</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($sub->score !== null)
                                                <span class="font-bold text-emerald-600">{{ $sub->score }}</span>
                                            @else
                                                <span class="text-amber-600 font-medium">Belum Dinilai</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <button type="button" onclick="openGradeModal({{ $sub->id }}, '{{ addslashes($sub->student->name) }}', '{{ $sub->score }}', '{{ addslashes($sub->feedback ?? '') }}', '{{ addslashes($sub->content ?? '') }}')"
                                                class="px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-100 text-xs font-semibold transition">
                                                {{ $sub->score !== null ? 'Edit Nilai' : 'Beri Nilai' }}
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- GRADING MODAL FOR LECTURER -->
            <div id="grade-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-4">
                <div class="modal-backdrop fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('grade-modal')"></div>
                <div class="modal-panel relative bg-white rounded-2xl sm:rounded-3xl shadow-2xl max-w-lg w-full p-5 sm:p-8 z-10 max-h-[90vh] overflow-y-auto">
                    <h3 class="text-lg font-bold text-slate-800 mb-1">Penilaian Tugas</h3>
                    <p class="text-xs text-slate-500 mb-4" id="modal-student-name">Mahasiswa: -</p>

                    <div id="modal-student-text" class="mb-4 p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 whitespace-pre-line hidden"></div>

                    <form id="grading-form" method="POST" action="" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Skor Nilai (Maksimal: {{ $assignment->max_score }})</label>
                            <input type="number" step="0.5" name="score" id="modal-score-input" required min="0" max="{{ $assignment->max_score }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-bold" />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Komentar & Umpan Balik (Opsional)</label>
                            <textarea name="feedback" id="modal-feedback-input" rows="3" placeholder="Berikan catatan evaluasi untuk mahasiswa..." class="w-full px-4 py-2 rounded-xl border border-slate-300 text-xs"></textarea>
                        </div>

                        <div class="pt-4 flex items-center justify-end gap-3">
                            <button type="button" onclick="closeModal('grade-modal')" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600">Batal</button>
                            <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold">Simpan Penilaian</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                function openGradeModal(submissionId, studentName, currentScore, currentFeedback, studentText) {
                    document.getElementById('modal-student-name').textContent = 'Mahasiswa: ' + studentName;
                    document.getElementById('grading-form').action = '/submissions/' + submissionId + '/grade';
                    document.getElementById('modal-score-input').value = currentScore;
                    document.getElementById('modal-feedback-input').value = currentFeedback;

                    const textField = document.getElementById('modal-student-text');
                    if (studentText && studentText.trim().length > 0) {
                        textField.textContent = 'Jawaban Teks Mahasiswa:\n' + studentText;
                        textField.classList.remove('hidden');
                    } else {
                        textField.classList.add('hidden');
                    }

                    openModal('grade-modal');
                }
            </script>
        @endif
    </div>
</x-app-layout>

