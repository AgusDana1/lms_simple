<x-app-layout :pageHeader="$material->title">
    <div class="max-w-4xl mx-auto space-y-6 motion-fade-in">
        <!-- Navigation breadcrumb -->
        <div class="flex items-center gap-2 text-xs text-slate-400">
            <a href="{{ route('courses.show', $course) }}" class="hover:text-indigo-600 transition">{{ $course->code }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            <a href="{{ route('courses.show', ['course' => $course, 'tab' => 'materials']) }}" class="hover:text-indigo-600 transition">Materi</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            <span class="text-slate-700 font-semibold truncate">{{ $material->title }}</span>
        </div>

        <!-- Material Container Card -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <!-- Header -->
            <div class="p-6 sm:p-8 border-b border-slate-100 bg-slate-50/50">
                <div class="flex items-center justify-between gap-4 mb-2">
                    <span class="px-2.5 py-1 rounded-xl bg-indigo-50 text-indigo-700 text-xs font-bold uppercase tracking-wider font-mono">
                        Tipe: {{ $material->type }}
                    </span>
                    @if($course->created_by === auth()->id() || auth()->user()->isAdmin())
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('materials.destroy', $material) }}" onsubmit="return confirm('Hapus materi ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-semibold transition">
                                    Hapus Materi
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight mb-2">
                    {{ $material->title }}
                </h1>

                @if($material->description)
                    <p class="text-xs sm:text-sm text-slate-500 leading-relaxed max-w-2xl">
                        {{ $material->description }}
                    </p>
                @endif
            </div>

            <!-- Content Body based on type -->
            <div class="p-6 sm:p-8">
                @if($material->type === 'video')
                    @php
                        // Check if youtube link
                        $youtubeId = null;
                        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $material->external_url ?? '', $matches)) {
                            $youtubeId = $matches[1];
                        }
                    @endphp

                    @if($youtubeId)
                        <div class="aspect-video w-full rounded-2xl overflow-hidden shadow-lg border border-slate-200 mb-6">
                            <iframe src="https://www.youtube-nocookie.com/embed/{{ $youtubeId }}" class="w-full h-full" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                        </div>
                    @else
                        <div class="p-8 rounded-2xl bg-slate-50 border border-slate-200 text-center mb-6">
                            <i data-lucide="video" class="w-12 h-12 text-indigo-500 mx-auto mb-3"></i>
                            <h3 class="text-sm font-bold text-slate-800 mb-1">Tautan Video Pembelajaran</h3>
                            <p class="text-xs text-slate-500 mb-4">{{ $material->external_url }}</p>
                            <a href="{{ $material->external_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition">
                                <span>Buka Video di Tab Baru</span>
                                <i data-lucide="external-link" class="w-4 h-4"></i>
                            </a>
                        </div>
                    @endif

                    @if($material->content)
                        <div class="pt-4 border-t border-slate-100 text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                            {{ $material->content }}
                        </div>
                    @endif

                @elseif($material->type === 'file')
                    <div class="p-8 rounded-2xl bg-gradient-to-tr from-slate-50 to-indigo-50/40 border border-slate-200/80 text-center max-w-lg mx-auto">
                        <div class="w-14 h-14 rounded-2xl bg-indigo-100 text-indigo-600 mx-auto flex items-center justify-center mb-4 shadow-sm">
                            <i data-lucide="file-text" class="w-7 h-7"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-800 mb-1">{{ $material->file_name ?? 'Berkas Modul Kuliah' }}</h3>
                        <p class="text-xs text-slate-500 mb-6">Berkas lampiran materi untuk mendukung pembelajaran Anda</p>
                        
                        @if($material->file_path)
                            <a href="{{ route('materials.download', $material) }}" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/25 transition transform hover:scale-102">
                                <i data-lucide="download" class="w-4 h-4"></i>
                                <span>Unduh Berkas Sekarang</span>
                            </a>
                        @else
                            <span class="text-xs text-rose-500 font-semibold">Berkas belum diunggah.</span>
                        @endif
                    </div>

                    @if($material->content)
                        <div class="mt-8 pt-6 border-t border-slate-100 text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                            {{ $material->content }}
                        </div>
                    @endif

                @elseif($material->type === 'link')
                    <div class="p-8 rounded-2xl bg-slate-50 border border-slate-200 text-center max-w-lg mx-auto">
                        <div class="w-14 h-14 rounded-2xl bg-sky-100 text-sky-600 mx-auto flex items-center justify-center mb-4 shadow-sm">
                            <i data-lucide="globe" class="w-7 h-7"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-800 mb-1">Tautan Referensi Eksternal</h3>
                        <p class="text-xs text-slate-500 mb-6 break-all">{{ $material->external_url }}</p>
                        <a href="{{ $material->external_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/25 transition">
                            <span>Kunjungi Sumber Belajar</span>
                            <i data-lucide="external-link" class="w-4 h-4"></i>
                        </a>
                    </div>

                    @if($material->content)
                        <div class="mt-8 pt-6 border-t border-slate-100 text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                            {{ $material->content }}
                        </div>
                    @endif

                @else
                    <!-- Text / Article Reader -->
                    <div class="prose prose-slate max-w-none text-sm text-slate-700 leading-relaxed whitespace-pre-line font-sans">
                        {{ $material->content }}
                    </div>
                @endif
            </div>

            <!-- Footer Action -->
            <div class="p-6 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('courses.show', ['course' => $course, 'tab' => 'materials']) }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 flex items-center gap-1.5">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Daftar Materi
                </a>
            </div>
        </div>
    </div>
</x-app-layout>

