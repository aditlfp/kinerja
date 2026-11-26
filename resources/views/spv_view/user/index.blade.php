<x-app-layout>
            @php
                $jabatan = Auth::user()->divisi->jabatan->code_jabatan ?? Auth::user()->divisi->code_jabatan;
                $isMcs = (Auth::user()->jabatan->code_jabatan ?? null) === 'MCS';
                $penempatan = auth()->id() == 175 || $isMcs ? 'Semua Mitra' : (Auth::user()->kerjasama->client->name ?? '-');
            @endphp
            <div class="pt-5 bg-transparent ring-0 shadow-none"></div>
                <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs sm:p-5 lg:p-6 mb-5">
                    <div class="mx-auto w-full max-w-7xl">

                        {{-- Header Logo --}}
                        <div class="flex items-center justify-between gap-3">
                            <a href="{{ route('dashboard.index') }}" class="shrink-0">
                                <img
                                    src="{{ URL::asset('logo/logo_kinerja.png') }}"
                                    alt="Logo"
                                    class="h-auto w-28 sm:w-32 lg:w-36"
                                >
                            </a>

                            <span class="text-xs font-semibold text-slate-400 sm:text-sm">
                                Data Karyawan
                            </span>
                        </div>

                        {{-- Content Header --}}
                        <div class="mt-6 flex flex-col gap-4 pt-5
                                    lg:mt-8 lg:flex-row lg:items-center lg:justify-between">

                            {{-- Title --}}
                            <div class="min-w-0">
                                <p class="border-l-4 border-sky-500 pl-3 text-lg font-black uppercase
                                        text-slate-800 sm:text-xl">
                                    Data Karyawan
                                </p>
                            </div>

                            {{-- Actions --}}
                            <div class="flex w-full flex-col gap-3 sm:flex-row sm:items-center lg:w-auto">

                                <a href="{{
                                    $jabatan === 'CO-CS' ? route('leaderView') :
                                    ($jabatan === 'CO-SCR' ? route('danruView') : route('dashboard.index'))
                                }}"
                                class="inline-flex w-full items-center justify-center rounded-lg
                                        bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700
                                        transition hover:bg-slate-200
                                        sm:w-auto">
                                    Kembali
                                </a>

                                <div class="w-full sm:min-w-[260px] sm:max-w-sm lg:w-72">
                                    <x-search />
                                </div>

                            </div>
                        </div>

                    </div>
                </section>


                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs mb-5">
                <details class="group">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 p-4 sm:p-5">
                        <div class="flex items-center gap-3">
                            <div>
                                <h2 class="text-sm font-bold text-slate-800">
                                    Total user per jabatan
                                </h2>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    Klik untuk melihat rincian user
                                </p>
                            </div>

                            <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-sky-700">
                                {{ $jabatanCounts->sum() }} User
                            </span>
                        </div>

                        <svg
                            class="h-5 w-5 shrink-0 text-slate-400 transition-transform duration-300 group-open:rotate-180"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                        </svg>
                    </summary>

                    
                    <div class="border-t border-slate-100 p-4 sm:p-5">
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                            @forelse ($jabatanCounts as $jabatanName => $total)
                                <div class="group relative overflow-hidden rounded-xl border border-slate-200 bg-white p-4 transition duration-200 hover:-translate-y-1 hover:border-sky-200 hover:shadow-md">

                                    {{-- Accent --}}
                                    <div class="absolute left-0 top-0 h-full w-1 bg-sky-500"></div>

                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="truncate text-xs font-semibold uppercase tracking-wider text-slate-500">
                                                {{ $jabatanName }}
                                            </p>

                                            <p class="mt-2 text-3xl font-black tracking-tight text-slate-800">
                                                {{ $total }}
                                            </p>

                                            <p class="mt-1 text-xs font-medium text-slate-400">
                                                Total pengguna
                                            </p>
                                        </div>

                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-sky-50 text-sky-600 transition group-hover:bg-sky-100">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="h-5 w-5"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m12-13a4 4 0 11-8 0 4 4 0 018 0zm5 13v-2a4 4 0 00-3-3.87M17 3.13a4 4 0 010 7.75" />
                                            </svg>
                                        </div>
                                    </div>

                                    <div class="mt-4 h-1 w-full overflow-hidden rounded-full bg-slate-100">
                                        <div
                                            class="h-full rounded-full bg-sky-500"
                                            style="width: {{ $jabatanCounts->sum() > 0 ? ($total / $jabatanCounts->sum()) * 100 : 0 }}%">
                                        </div>
                                    </div>

                                </div>
                            @empty
                                <div class="col-span-full rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                                    <p class="text-sm font-medium text-slate-500">
                                        Belum ada data karyawan
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                </details>
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
                                        <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-[11px] font-bold text-indigo-700">{{ $i->divisi->jabatan->code_jabatan ?? 'Jabatan Kosong ?' }}</span>
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
                <section class="hidden overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-900/[0.02] md:block">
                    <div class="overflow-x-auto">
                    <table id="searchTable" class="w-full min-w-[820px] divide-y divide-slate-200 text-sm">
                        <thead>
                            <tr class="bg-slate-100 text-left text-xs font-bold uppercase tracking-wide text-slate-600">
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
</x-app-layout>
