<x-app-layout>
    <x-main-div>
        <div class="px-3 py-6 sm:px-8 lg:px-10">
            @php
                $jabatan = Auth::user()->divisi->jabatan->code_jabatan ?? Auth::user()->divisi->code_jabatan;
                $isMcs = (Auth::user()->jabatan->code_jabatan ?? null) === 'MCS';
                $penempatan = auth()->id() == 175 || $isMcs ? 'Semua Mitra' : (Auth::user()->kerjasama->client->name ?? '-');
            @endphp

            <div class="mx-auto w-full max-w-7xl space-y-5">
                <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs sm:p-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-blue-600">Data Karyawan</p>
                            <h1 class="mt-1 text-2xl font-extrabold text-slate-900">List Karyawan</h1>
                            <p class="mt-1 text-sm text-slate-500">{{ $penempatan }}</p>
                        </div>
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                            <a href="{{
                                $jabatan === 'CO-CS' ? route('leaderView') :
                                ($jabatan === 'CO-SCR' ? route('danruView') : route('dashboard.index'))
                            }}" class="btn btn-error btn-sm sm:btn-md">Kembali</a>
                            <div class="min-w-[240px]">
                                <x-search />
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs sm:p-5">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <h2 class="text-sm font-bold text-slate-800">Total User per Jabatan</h2>
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">{{ $jabatanCounts->sum() }} User</span>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        @forelse ($jabatanCounts as $jabatanName => $total)
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-3">
                                <p class="text-xs font-semibold text-slate-500">{{ $jabatanName }}</p>
                                <p class="mt-1 text-2xl font-extrabold text-slate-900">{{ $total }}</p>
                            </div>
                        @empty
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 text-center text-sm text-slate-500 sm:col-span-2 lg:col-span-4">~ Data Kosong ~</div>
                        @endforelse
                    </div>
                </section>

                {{-- Mobile Cards --}}
                <section id="userCards" class="space-y-3 md:hidden">
                    @php $mobileNo = 1; @endphp
                    @forelse ($user as $i)
                        @continue(in_array($i->nama_lengkap, ['admin', 'user', 'MITRA SAC']))
                        <article class="user-card rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
                            <div class="flex gap-3">
                                @if ($i->image !== 'no-image.jpg' && Storage::disk('public')->exists('images/' . $i->image))
                                    <img class="lazy lazy-image h-14 w-14 shrink-0 rounded-full object-cover" loading="lazy"
                                         src="{{ asset('storage/images/' . $i->image) }}"
                                         data-src="{{ asset('storage/images/' . $i->image) }}"
                                         alt="{{ $i->nama_lengkap }}">
                                @else
                                    <div class="h-14 w-14 shrink-0 overflow-hidden rounded-full bg-slate-100"><x-no-img /></div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <h3 class="wrap-break-word text-sm font-extrabold text-slate-900">{{ ucwords(strtolower($i->nama_lengkap)) }}</h3>
                                        <span class="shrink-0 rounded-full bg-slate-100 px-2 py-1 text-[10px] font-bold text-slate-700">#{{ $mobileNo++ }}</span>
                                    </div>
                                    <p class="mt-0.5 text-xs font-semibold text-slate-500">{{ $i->name }}</p>
                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                        <span class="rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-bold text-blue-700">{{ $i->divisi->jabatan->code_jabatan ?? 'Jabatan Kosong ?' }}</span>
                                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700">
                                            @php
                                                $name = $i->kerjasama->client->name ?? null;
                                                if ($name) {
                                                    preg_match('/\((.*?)\)/', $name, $match);
                                                    $suffix = isset($match[0]) ? ' ' . $match[0] : '';
                                                    $cleanName = preg_replace('/\s*\(.*?\)\s*/', ' ', $name);
                                                    echo collect(explode(' ', trim($cleanName)))->map(fn($word) => strtoupper(substr(str_replace("'", '', $word), 0, 1)))->implode('') . $suffix;
                                                } else {
                                                    echo 'kosong';
                                                }
                                            @endphp
                                        </span>
                                    </div>
                                    <p class="mt-2 break-all text-xs text-slate-500">{{ $i->email }}</p>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center text-sm text-slate-500">~ Data Kosong ~</div>
                    @endforelse
                </section>

                {{-- Desktop Table --}}
                <section class="hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs md:block">
                    <div class="overflow-x-auto">
                    <table id="searchTable" class="table min-w-[820px] text-xs font-semibold table-zebra bg-white sm:text-sm">
                        <thead>
                            <tr class="text-left text-slate-600">
                                <th class="bg-slate-50 px-4 py-3">#</th>
                                <th class="bg-slate-50 px-4 py-3">Profil</th>
                                <th class="bg-slate-50 px-4 py-3">Username</th>
                                <th class="bg-slate-50 px-4 py-3">Nama Lengkap</th>
                                <th class="bg-slate-50 px-4 py-3">Jabatan</th>
                                <th class="bg-slate-50 px-4 py-3">Email</th>
                                <th class="bg-slate-50 px-4 py-3">Penempatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $n = 1; @endphp
                            @forelse ($user as $i)
                                @continue(in_array($i->nama_lengkap, ['admin', 'user', 'MITRA SAC']))
                                <tr>
                                    <td class="px-4 py-3 text-slate-500">{{ $n++ }}</td>
                                    <td class="px-4 py-3">
                                        @if ($i->image !== 'no-image.jpg' && Storage::disk('public')->exists('images/' . $i->image))
                                            <img class="lazy lazy-image h-12 w-12 rounded-full object-cover" loading="lazy"
                                                 src="{{ asset('storage/images/' . $i->image) }}"
                                                 data-src="{{ asset('storage/images/' . $i->image) }}"
                                                 alt="{{ $i->nama_lengkap }}">
                                        @else
                                            <x-no-img />
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-slate-700">{{ $i->name }}</td>
                                    <td class="px-4 py-3 font-bold text-slate-800">{{ ucwords(strtolower($i->nama_lengkap)) }}</td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">{{ $i->divisi->jabatan->code_jabatan ?? 'Jabatan Kosong ?' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">{{ $i->email }}</td>
                                    <td class="px-4 py-3 text-slate-700">
                                        @php
                                            $name = $i->kerjasama->client->name ?? null;
                                            if ($name) {
                                                preg_match('/\((.*?)\)/', $name, $match);
                                                $suffix = isset($match[0]) ? ' ' . $match[0] : '';
                                                $cleanName = preg_replace('/\s*\(.*?\)\s*/', ' ', $name);
                                                $initials = collect(explode(' ', trim($cleanName)))
                                                    ->map(fn($word) => strtoupper(substr(str_replace("'", '', $word), 0, 1)))
                                                    ->implode('');
                                                echo $initials . $suffix;
                                            } else {
                                                echo 'kosong';
                                            }
                                        @endphp
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">~ Data Kosong ~</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </section>

                <div id="pag-1" class="py-2">
                    {{ $user->links() }}
                </div>

                <script>
                    document.getElementById('searchInput')?.addEventListener('input', function () {
                        const q = this.value.toLowerCase();
                        document.querySelectorAll('.user-card').forEach(card => card.classList.toggle('hidden', !card.innerText.toLowerCase().includes(q)));
                    });
                </script>
            </div>
        </div>
    </x-main-div>
</x-app-layout>
