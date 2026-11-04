<x-mitra-layout title="Data Karyawan Mitra">
    <div class="p-6 rounded-3xl mitra-panel mitra-mobile-card">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.25em] mitra-accent">Data Personel Mitra</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight mitra-text-strong">{{ $totalKaryawan }} Karyawan Terdaftar</h1>
                <p class="mt-1 text-sm mitra-text-soft">Kelola daftar karyawan aktif di area kerja Anda.</p>
            </div>
            <form id="mitraUserSearchForm" method="GET" action="{{ route('mitra_user') }}" class="flex w-full flex-wrap items-center gap-2 md:w-auto">
                <input
                    id="mitraUserSearchInput"
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari nama, username, email..."
                    class="w-full md:w-72 text-sm input input-sm input-bordered mitra-input"
                />
                @if($search)
                    <a href="{{ route('mitra_user') }}" class="btn btn-sm btn-ghost mitra-text-soft hover:text-white">Reset</a>
                @endif
            </form>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl mitra-panel-soft">
        <div class="p-4 border-b border-slate-600/50">
            <h2 class="text-xs font-black tracking-[0.3em] uppercase mitra-section-title">Daftar Karyawan</h2>
        </div>
        <div class="p-4">
            @if($users->isEmpty())
                <p class="text-sm italic mitra-empty-state">Data karyawan tidak ditemukan.</p>
            @else
                <div class="space-y-3 md:hidden" data-viewport-content="mobile">
                    @foreach($users as $user)
                        <article class="p-4 mitra-mobile-list-card mitra-user-card-search">
                            <div class="flex items-start gap-3">
                                @if($user->image && $user->image !== 'no-image.jpg' && \Illuminate\Support\Facades\Storage::disk('public')->exists('images/' . $user->image))
                                    <img
                                        class="object-cover w-12 h-12 rounded-full"
                                        loading="lazy"
                                        decoding="async"
                                        fetchpriority="low"
                                        src="{{ asset('storage/images/' . $user->image) }}"
                                        alt="Foto {{ $user->nama_lengkap }}"
                                    >
                                @else
                                    <div class="flex items-center justify-center w-12 h-12 text-xs font-bold rounded-full mitra-avatar-fallback">
                                        {{ strtoupper(substr($user->nama_lengkap ?? 'K', 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="font-semibold mitra-text-strong">{{ ucwords(strtolower($user->nama_lengkap)) }}</p>
                                    <p class="text-sm break-all mitra-text-soft">{{ $user->email ?? '-' }}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3 mt-4 text-sm">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase mitra-meta-label">Username</p>
                                    <p class="mt-1 mitra-text-strong">{{ $user->name }}</p>
                                </div>
                                <div>
                                    <p class="text-[11px] font-semibold uppercase mitra-meta-label">Jabatan</p>
                                    <p class="mt-1 mitra-text-strong">{{ $user->divisi?->jabatan?->code_jabatan ?? '-' }}</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-[11px] font-semibold uppercase mitra-meta-label">Penempatan</p>
                                    <p class="mt-1 mitra-text-strong">{{ $user->kerjasama?->client?->panggilan ?: ($user->kerjasama?->client?->name ?? '-') }}</p>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="hidden -mx-4 px-4 md:block md:mx-0 md:px-0 mitra-table-wrap" data-viewport-content="desktop">
                    <table class="w-full text-sm text-left mitra-table mitra-mobile-table-wide">
                        <thead class="text-xs uppercase bg-slate-800/50">
                            <tr>
                                <th class="px-4 py-3">#</th>
                                <th class="px-4 py-3">Profil</th>
                                <th class="px-4 py-3">Username</th>
                                <th class="px-4 py-3">Nama Lengkap</th>
                                <th class="px-4 py-3">Jabatan</th>
                                <th class="px-4 py-3">Email</th>
                                <th class="px-4 py-3">Penempatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr class="border-b mitra-table-row hover:bg-slate-800/30">
                                    <td class="px-4 py-3">{{ $users->firstItem() + $loop->index }}</td>
                                    <td class="px-4 py-3">
                                        @if($user->image && $user->image !== 'no-image.jpg' && \Illuminate\Support\Facades\Storage::disk('public')->exists('images/' . $user->image))
                                            <img
                                                class="object-cover w-10 h-10 rounded-full"
                                                loading="lazy"
                                                decoding="async"
                                                fetchpriority="low"
                                                src="{{ asset('storage/images/' . $user->image) }}"
                                                alt="Foto {{ $user->nama_lengkap }}"
                                            >
                                        @else
                                            <div class="flex items-center justify-center w-10 h-10 text-xs font-bold rounded-full mitra-avatar-fallback">
                                                {{ strtoupper(substr($user->nama_lengkap ?? 'K', 0, 1)) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">{{ $user->name }}</td>
                                    <td class="px-4 py-3 font-medium mitra-text-strong">{{ ucwords(strtolower($user->nama_lengkap)) }}</td>
                                    <td class="px-4 py-3">{{ $user->divisi?->jabatan?->code_jabatan ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $user->email ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $user->kerjasama?->client?->panggilan ?: ($user->kerjasama?->client?->name ?? '-') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-5">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('mitraUserSearchForm');
            const input = document.getElementById('mitraUserSearchInput');
            const cards = document.querySelectorAll('.mitra-user-card-search');
            if (!form || !input) return;

            let debounceTimer = null;
            input.addEventListener('input', function () {
                const keyword = this.value.toLowerCase().trim();
                cards.forEach(function (card) {
                    const content = card.textContent.toLowerCase();
                    card.style.display = content.includes(keyword) ? '' : 'none';
                });

                if (window.innerWidth < 768) {
                    return;
                }

                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function () {
                    form.submit();
                }, 300);
            });
        });
    </script>
</x-mitra-layout>
