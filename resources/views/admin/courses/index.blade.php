<x-app-layout pageHeader="Pengawasan Mata Kuliah">
    <div class="space-y-6 motion-fade-in">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-extrabold text-slate-800 tracking-tight">Pengawasan Seluruh Mata Kuliah</h2>
                <p class="text-xs text-slate-400 mt-0.5">Tinjau seluruh kelas yang dibuat oleh para dosen pengampu</p>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white p-3.5 sm:p-4 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 sm:gap-4">
            <form method="GET" action="{{ route('admin.courses.index') }}" class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-2.5 w-full">
                <select name="lecturer_id" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-xs font-semibold text-slate-700 outline-none">
                    <option value="">Semua Dosen</option>
                    @foreach($lecturers as $lec)
                        <option value="{{ $lec->id }}" {{ $lecturerId == $lec->id ? 'selected' : '' }}>{{ $lec->name }}</option>
                    @endforeach
                </select>

                <select name="published" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-xs font-semibold text-slate-700 outline-none">
                    <option value="">Semua Status</option>
                    <option value="1" {{ $published === '1' ? 'selected' : '' }}>Dipublikasikan</option>
                    <option value="0" {{ $published === '0' ? 'selected' : '' }}>Draft</option>
                </select>

                <div class="relative flex-1 min-w-[180px]">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari kode atau nama kelas..."
                        class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-xs focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none" />
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 border-b border-slate-200/80 text-[11px] uppercase font-bold text-slate-400">
                        <tr>
                            <th class="px-6 py-4">Kode & Kelas</th>
                            <th class="px-6 py-4">Dosen Pengampu</th>
                            <th class="px-6 py-4">Mahasiswa</th>
                            <th class="px-6 py-4">Konten (M/T/K)</th>
                            <th class="px-6 py-4">Status Publikasi</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($courses as $c)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 rounded font-mono font-bold bg-indigo-50 text-indigo-700 text-[10px] block w-max mb-1">
                                        {{ $c->code }}
                                    </span>
                                    <a href="{{ route('courses.show', $c) }}" class="font-bold text-slate-800 hover:text-indigo-600 transition">
                                        {{ $c->name }}
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-slate-700">{{ $c->creator?->name }}</p>
                                    <span class="text-[11px] text-slate-400">{{ $c->creator?->email }}</span>
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-800">
                                    {{ $c->student_count }} Mahasiswa
                                </td>
                                <td class="px-6 py-4 text-slate-400 font-mono">
                                    {{ $c->materials_count }} M / {{ $c->assignments_count }} T / {{ $c->quizzes_count }} K
                                </td>
                                <td class="px-6 py-4">
                                    <form method="POST" action="{{ route('admin.courses.toggle-publish', $c) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-bold transition {{ $c->is_published ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                                            {{ $c->is_published ? 'Dipublikasikan' : 'Draft' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('courses.show', $c) }}" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-700 text-xs font-semibold transition">
                                        Buka Kelas
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $courses->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

