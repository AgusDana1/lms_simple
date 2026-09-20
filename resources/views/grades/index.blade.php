<x-app-layout :pageHeader="'Buku Nilai: ' . $course->name">
    <div class="space-y-6 motion-fade-in">
        <!-- Breadcrumb & Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
            <div class="flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('courses.show', $course) }}" class="hover:text-indigo-600 transition">{{ $course->code }}</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                <span class="text-slate-700 font-semibold">Buku Nilai</span>
            </div>

            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <button type="button" onclick="openModal('add-grade-modal')"
                    class="inline-flex items-center gap-1.5 px-3.5 sm:px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/25 transition">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Input Komponen Nilai Manual</span>
                </button>
                <a href="{{ route('courses.show', $course) }}" class="px-3.5 sm:px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                    Kembali
                </a>
            </div>
        </div>

        <!-- Gradebook Table Card -->
        <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-4 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold text-slate-800">Rekapitulasi Nilai Seluruh Mahasiswa</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Nilai otomatis dihimpun dari tugas, kuis, dan komponen manual</p>
                </div>
                <span class="text-xs font-bold font-mono px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 w-max">
                    {{ count($students) }} Mahasiswa Terdaftar
                </span>
            </div>

            @if(empty($students) || count($students) === 0)
                <div class="p-12 text-center text-xs text-slate-400">
                    Belum ada mahasiswa yang terdaftar dalam mata kuliah ini.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 border-b border-slate-200/80 text-[11px] uppercase font-bold text-slate-400">
                            <tr>
                                <th class="px-5 py-3.5 sticky left-0 bg-slate-50">Mahasiswa</th>
                                @foreach($components as $comp)
                                    <th class="px-4 py-3.5 whitespace-nowrap">{{ $comp }}</th>
                                @endforeach
                                <th class="px-5 py-3.5 text-right font-black text-indigo-700">Rata-Rata</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach($students as $st)
                                @php
                                    $stData = $studentGrades[$st->id] ?? null;
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="px-5 py-3.5 font-bold text-slate-800 sticky left-0 bg-white">
                                        <p class="truncate max-w-[180px]">{{ $st->name }}</p>
                                        <span class="text-[10px] text-slate-400 font-mono">{{ $st->studentProfile?->student_number }}</span>
                                    </td>

                                    @foreach($components as $comp)
                                        @php
                                            $val = $stData['scores'][$comp] ?? null;
                                        @endphp
                                        <td class="px-4 py-3.5">
                                            @if($val !== null)
                                                <span class="font-bold text-slate-800">{{ $val }}</span>
                                            @else
                                                <span class="text-slate-300">-</span>
                                            @endif
                                        </td>
                                    @endforeach

                                    <td class="px-5 py-3.5 text-right">
                                        <span class="px-2.5 py-1 rounded-xl text-xs font-black {{ ($stData['average'] >= 70) ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                            {{ $stData['average'] ?? '0' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- MODAL: ADD MANUAL GRADE COMPONENT -->
    <div id="add-grade-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-4">
        <div class="modal-backdrop fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('add-grade-modal')"></div>
        <div class="modal-panel relative bg-white rounded-2xl sm:rounded-3xl shadow-2xl max-w-md w-full p-5 sm:p-8 z-10 max-h-[90vh] overflow-y-auto">
            <h3 class="text-lg font-bold text-slate-800 mb-1">Input Komponen Nilai Manual</h3>
            <p class="text-xs text-slate-400 mb-4">Tambahkan komponen nilai (UTS, UAS, Keaktifan) untuk mahasiswa</p>

            <form method="POST" action="{{ route('grades.store', $course) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Mahasiswa</label>
                    <select name="student_id" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm">
                        <option value="">-- Pilih Mahasiswa --</option>
                        @foreach($students as $st)
                            <option value="{{ $st->id }}">{{ $st->name }} ({{ $st->studentProfile?->student_number ?? $st->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Komponen Penilaian</label>
                    <input type="text" name="component" required placeholder="Contoh: UTS, UAS, Keaktifan, Praktikum" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Skor Nilai (0 - 100)</label>
                    <input type="number" step="0.5" name="score" min="0" max="100" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-bold" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                    <textarea name="notes" rows="2" class="w-full px-4 py-2 rounded-xl border border-slate-300 text-xs"></textarea>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeModal('add-grade-modal')" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold">Simpan Nilai</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

