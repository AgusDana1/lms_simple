<x-app-layout pageHeader="Buat Mata Kuliah Baru">
    <div class="max-w-3xl mx-auto motion-fade-in">
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
            <div class="mb-6 pb-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Formulir Kelas Baru</h2>
                    <p class="text-xs text-slate-400 mt-1">Lengkapi informasi dasar mata kuliah yang akan Anda bina</p>
                </div>
                <a href="{{ route('courses.index') }}" class="px-3.5 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </a>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('courses.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Kode Mata Kuliah</label>
                        <input type="text" name="code" value="{{ old('code') }}" required
                            placeholder="Contoh: IF301"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono uppercase focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" />
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Mata Kuliah</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            placeholder="Contoh: Pemrograman Web Lanjut"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Deskripsi / Silabus Ringkas</label>
                    <textarea name="description" rows="4"
                        placeholder="Uraikan kompetensi mata kuliah, prasyarat, dan capaian pembelajaran..."
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Gambar Sampul / Thumbnail (Opsional)</label>
                    <input type="file" name="thumbnail" accept="image/*"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 cursor-pointer" />
                    <span class="text-[11px] text-slate-400 mt-1 block">Format: JPG, PNG, WEBP. Maksimal 2MB.</span>
                </div>

                <div class="pt-2">
                    <label class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 cursor-pointer">
                        <input type="checkbox" name="is_published" value="1" {{ old('is_published', 1) ? 'checked' : '' }}
                            class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500">
                        <div>
                            <span class="block text-xs font-bold text-slate-800">Publikasikan Langsung</span>
                            <span class="block text-[11px] text-slate-500">Mahasiswa dapat langsung melihat dan mendaftar ke mata kuliah ini.</span>
                        </div>
                    </label>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3">
                    <a href="{{ route('courses.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/25 transition">
                        Simpan & Buat Kelas
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

