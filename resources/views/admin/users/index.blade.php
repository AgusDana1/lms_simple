<x-app-layout pageHeader="Manajemen Pengguna">
    <div class="space-y-6 motion-fade-in">
        <!-- Header & Action Row -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-extrabold text-slate-800 tracking-tight">Kelola Pengguna Sistem</h2>
                <p class="text-xs text-slate-400 mt-0.5">Daftar seluruh akun Administrator, Dosen, dan Mahasiswa</p>
            </div>

            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/25 transition">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>Tambah Pengguna Baru</span>
            </a>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white p-3.5 sm:p-4 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 sm:gap-4">
            <!-- Filter Options -->
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-2.5 w-full">
                <select name="role" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-xs font-semibold text-slate-700 outline-none">
                    <option value="">Semua Peran</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->slug }}" {{ $roleSlug === $r->slug ? 'selected' : '' }}>{{ $r->name }}</option>
                    @endforeach
                </select>

                <select name="status" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-xs font-semibold text-slate-700 outline-none">
                    <option value="">Semua Status</option>
                    <option value="1" {{ $status === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ $status === '0' ? 'selected' : '' }}>Non-Aktif</option>
                </select>

                <div class="relative flex-1 min-w-[180px]">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, email, no. telp..."
                        class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-xs focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none" />
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 border-b border-slate-200/80 text-[11px] uppercase font-bold text-slate-400">
                        <tr>
                            <th class="px-6 py-4">Pengguna</th>
                            <th class="px-6 py-4">Peran</th>
                            <th class="px-6 py-4">Nomor Identitas</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Terakhir Login</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($users as $u)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-xs shrink-0">
                                            {{ substr($u->name, 0, 2) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800">{{ $u->name }}</p>
                                            <span class="text-[11px] text-slate-400">{{ $u->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold 
                                        {{ $u->isAdmin() ? 'bg-rose-100 text-rose-700' : ($u->isDosen() ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                        {{ $u->role?->name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-mono text-slate-500">
                                    @if($u->isMahasiswa())
                                        {{ $u->studentProfile?->student_number ?? '-' }}
                                    @elseif($u->isDosen())
                                        {{ $u->lecturerProfile?->lecturer_number ?? '-' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <form method="POST" action="{{ route('admin.users.toggle-status', $u) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-bold transition {{ $u->is_active ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                                            {{ $u->is_active ? 'Aktif' : 'Non-Aktif' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-6 py-4 text-slate-400">
                                    {{ $u->last_login_at ? $u->last_login_at->diffForHumans() : 'Belum pernah' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.users.edit', $u) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-slate-100 transition" title="Edit">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </a>

                                        @if($u->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('Hapus pengguna ini secara permanen?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-slate-100 transition" title="Hapus">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

