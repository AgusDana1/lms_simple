<x-app-layout pageHeader="Edit Mata Kuliah">
    <div class="max-w-3xl mx-auto motion-fade-in">
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
            <div class="mb-6 pb-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Edit Kelas: {{ $course->name }}</h2>
                    <p class="text-xs text-slate-400 mt-1">Perbarui kode, nama, deskripsi, atau status publikasi</p>
                </div>
                <a href="{{ route('courses.show', $course) }}" class="px-3.5 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                    Kembali
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

            <form method="POST" action="{{ route('courses.update', $course) }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Kode Mata Kuliah</label>
                        <input type="text" name="code" value="{{ old('code', $course->code) }}" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono uppercase focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" />
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Mata Kuliah</label>
                        <input type="text" name="name" value="{{ old('name', $course->name) }}" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Deskripsi / Silabus</label>
                    <textarea name="description" rows="4"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">{{ old('description', $course->description) }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Ganti Gambar Sampul (Opsional)</label>
                    @if($course->thumbnail)
                        <div class="mb-3 w-40 h-24 rounded-xl overflow-hidden border border-slate-200">
                            <img src="{{ asset('storage/' . $course->thumbnail) }}" alt="Thumbnail" class="w-full h-full object-cover">
                        </div>
                    @endif
                    <input type="file" name="thumbnail" accept="image/*"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 cursor-pointer" />
                </div>

                <div class="pt-2">
                    <label class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 cursor-pointer">
                        <input type="checkbox" name="is_published" value="1" {{ old('is_published', $course->is_published) ? 'checked' : '' }}
                            class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500">
                        <div>
                            <span class="block text-xs font-bold text-slate-800">Status Dipublikasikan</span>
                            <span class="block text-[11px] text-slate-500">Jika dinonaktifkan, kelas akan disimpan sebagai draft tersembunyi.</span>
                        </div>
                    </label>
                </div>

                <div class="pt-4 flex items-center justify-between">
                    <button type="button" onclick="if(confirm('Yakin ingin menghapus mata kuliah ini secara permanen?')) document.getElementById('delete-course-form').submit();"
                        class="px-4 py-2.5 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50 transition flex items-center gap-1.5">
                        <i data-lucide="trash-2" class="w-4 h-4"></i> Hapus Kelas
                    </button>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('courses.show', $course) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                            Batal
                        </a>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/25 transition">
                            Perbarui Kelas
                        </button>
                    </div>
                </div>
            </form>

            <form id="delete-course-form" method="POST" action="{{ route('courses.destroy', $course) }}" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</x-app-layout>

