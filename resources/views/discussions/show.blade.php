<x-app-layout :pageHeader="'Forum: ' . $discussion->title">
    <div class="max-w-4xl mx-auto space-y-6 motion-fade-in">
        <!-- Breadcrumb & Actions -->
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('courses.show', $course) }}" class="hover:text-indigo-600 transition">{{ $course->code }}</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                <a href="{{ route('courses.show', ['course' => $course, 'tab' => 'discussions']) }}" class="hover:text-indigo-600 transition">Diskusi</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                <span class="text-slate-700 font-semibold truncate">{{ $discussion->title }}</span>
            </div>

            <div class="flex items-center gap-2">
                @if($isLecturer)
                    <form method="POST" action="{{ route('discussions.lock', $discussion) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="px-3.5 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition flex items-center gap-1.5">
                            <i data-lucide="{{ $discussion->is_locked ? 'unlock' : 'lock' }}" class="w-3.5 h-3.5"></i>
                            <span>{{ $discussion->is_locked ? 'Buka Kunci' : 'Kunci Topik' }}</span>
                        </button>
                    </form>
                @endif
                @if($discussion->user_id === auth()->id() || $isLecturer)
                    <form method="POST" action="{{ route('discussions.destroy', $discussion) }}" onsubmit="return confirm('Hapus topik diskusi ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 transition" title="Hapus Topik">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Main Discussion Topic Card -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-4">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 font-bold flex items-center justify-center text-sm shrink-0">
                        {{ substr($discussion->user?->name, 0, 2) }}
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">{{ $discussion->user?->name }}</h3>
                        <div class="flex items-center gap-2 text-[11px] text-slate-400 mt-0.5">
                            <span class="px-1.5 py-0.2 rounded font-semibold {{ $discussion->user?->isDosen() ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ $discussion->user?->role?->name }}
                            </span>
                            <span>•</span>
                            <span>{{ $discussion->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>

                @if($discussion->is_locked)
                    <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold flex items-center gap-1">
                        <i data-lucide="lock" class="w-3.5 h-3.5"></i> Terkunci
                    </span>
                @endif
            </div>

            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight pt-2">
                {{ $discussion->title }}
            </h1>

            <div class="text-sm text-slate-700 leading-relaxed whitespace-pre-line border-t border-slate-100 pt-4">
                {{ $discussion->content }}
            </div>
        </div>

        <!-- Threaded Replies Section -->
        <div class="space-y-4">
            <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <i data-lucide="message-circle" class="w-5 h-5 text-indigo-600"></i>
                <span>Tanggapan & Balasan ({{ $discussion->replies->count() }})</span>
            </h2>

            @if($discussion->replies->isEmpty())
                <div class="bg-white rounded-3xl border border-slate-200/80 p-8 text-center text-xs text-slate-400">
                    Belum ada balasan pada topik ini. Jadilah yang pertama menanggapi.
                </div>
            @else
                <div class="space-y-3">
                    @foreach($discussion->replies as $reply)
                        <div class="motion-card bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-3">
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-xs shrink-0">
                                        {{ substr($reply->user?->name ?? 'User', 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-slate-800">{{ $reply->user?->name ?? 'Pengguna' }}</span>
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold {{ $reply->user?->isDosen() ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
                                                {{ $reply->user?->role?->name ?? 'Member' }}
                                            </span>
                                        </div>
                                        <span class="text-[10px] text-slate-400">{{ $reply->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="text-xs sm:text-sm text-slate-700 leading-relaxed whitespace-pre-line pl-11">
                                {{ $reply->content }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Reply Form (if not locked) -->
            @if(!$discussion->is_locked)
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
                    <h3 class="text-sm font-bold text-slate-800 mb-2">Tulis Tanggapan Anda</h3>
                    <form method="POST" action="{{ route('discussions.reply', $discussion) }}" class="space-y-3">
                        @csrf
                        <textarea name="content" rows="3" required placeholder="Tuliskan jawaban atau tanggapan Anda dengan santun dan jelas..."
                            class="w-full px-4 py-2.5 rounded-2xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 outline-none"></textarea>
                        
                        <div class="flex items-center justify-end">
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/25 transition">
                                Kirim Tanggapan
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="p-4 rounded-2xl bg-slate-100 text-center text-xs text-slate-500 font-medium">
                    Topik diskusi ini telah dikunci oleh dosen pengampu. Balasan baru dinonaktifkan.
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

