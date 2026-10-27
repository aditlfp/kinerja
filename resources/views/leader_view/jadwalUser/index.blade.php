<x-app-layout>
    @php
        $auth = Auth::user();
        $kode = $auth->divisi->jabatan->code_jabatan ?? $auth->divisi->code_jabatan;
        $canCreate = in_array($kode, ['CO-CS', 'CO-SCR'], true);
        $canMonitor = $canMonitor ?? in_array($kode, ['SPV-A', 'SPV-W', 'CO-SPV'], true);
        $backUrl = match ($kode) {
            'CO-CS' => route('leaderView'),
            'CO-SCR' => route('danruView'),
            'SPV-A', 'SPV-W', 'CO-SPV' => route('dashboard.index'),
            default => route('dashboard.index'),
        };
    @endphp

    <div class="min-h-screen px-3 py-4 sm:px-6 sm:py-8">
        <div class="mx-auto max-w-5xl space-y-4">
            {{-- Header: surface putih solid, hierarki jelas, tanpa glow/dekorasi --}}
            <header class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="text-xs font-semibold text-slate-500">
                            {{ $auth->kerjasama->client->name }}
                        </div>
                        <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                            Jadwal Karyawan
                        </h1>
                        <p class="mt-1 text-sm text-slate-600">
                            @if($canCreate)
                                Kelola jadwal bulanan, import CSV, dan cetak jadwal operasional.
                            @else
                                Pantau jadwal karyawan yang sudah dibuat dan unduh laporan.
                            @endif
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ $backUrl }}" class="btn btn-sm sm:btn-md border-slate-300 text-slate-700">
                            Kembali
                        </a>
                        @if($canCreate && !$jadwalBulanIniAda)
                            <a href="{{ route('leader-jadwal.create', ['month' => now()->format('Y-m')]) }}" class="btn btn-sm sm:btn-md bg-indigo-600 text-white hover:bg-indigo-700 border-indigo-600">
                                Buat Jadwal Bulanan
                            </a>
                        @elseif($canCreate)
                            <span class="btn btn-sm sm:btn-md btn-disabled" title="Jadwal bulan ini sudah dibuat">
                                Jadwal Bulan Ini Sudah Ada
                            </span>
                        @endif
                    </div>
                </div>
            </header>

            {{-- Daftar jadwal bulanan yang sudah dibuat + export --}}
            @if($canCreate || $canMonitor)
                <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                    <div class="mb-3 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Jadwal yang sudah dibuat</h2>
                            <p class="text-xs text-slate-500">Export PDF/Excel sesuai bulan jadwal.</p>
                        </div>
                        <span class="text-xs text-slate-500">Akses: <span class="font-semibold text-slate-900">{{ $kode }}</span></span>
                    </div>

                    @forelse($jadwalBulan as $bulan)
                        <div class="flex flex-col gap-3 border-t border-slate-100 py-3 first:border-t-0 first:pt-0 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-sm font-bold text-indigo-700">
                                    {{ $bulan->format('m') }}
                                </span>
                                <div>
                                    <div class="text-sm font-semibold text-slate-900">{{ $bulan->translatedFormat('F Y') }}</div>
                                    <div class="text-xs text-slate-500">Jadwal bulanan</div>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('leader-jadwal.export', ['month' => $bulan->format('Y-m'), 'format' => 'pdf'] ) }}"
                                   class="btn btn-sm border-red-200 bg-red-50 text-red-700 hover:bg-red-100">
                                    Export PDF
                                </a>
                                <a href="{{ route('leader-jadwal.export', ['month' => $bulan->format('Y-m'), 'format' => 'xlsx']) }}"
                                   class="btn btn-sm border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100">
                                    Export Excel
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-200 p-6 text-center text-sm text-slate-500">
                            Belum ada jadwal yang dibuat.
                        </div>
                    @endforelse
                </section>
            @endif

            {{-- Panel Import & Pencarian (Role ID 2) --}}
            @if($auth->role_id == 2)
                <section class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6 shadow-sm">
                    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Import jadwal CSV</h2>
                            <p class="text-xs text-slate-500">Upload format CSV untuk memperbarui data jadwal sekaligus.</p>
                        </div>
                        <x-search />
                    </div>

                    <form action="{{ route('leader.jadwal.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
                        @csrf
                        <label for="iJDW" class="btn btn-sm btn-outline border-slate-300 text-slate-700">
                            Pilih File CSV
                        </label>
                        <input id="iJDW" name="file" type="file" class="hidden" accept=".csv">
                        <span id="importLabel" class="text-xs text-slate-500">Belum ada file dipilih</span>
                        <button id="btnImport" class="btn btn-sm bg-slate-900 text-white hover:bg-slate-800 hidden" type="submit">
                            Proses Import
                        </button>
                    </form>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6 shadow-sm">
                    <h2 class="text-base font-bold text-slate-900">Export PDF per periode</h2>
                    <p class="text-xs text-slate-500">Filter berdasarkan rentang tanggal dan client.</p>

                    <form action="{{ route('leader.jadwal.export') }}" method="GET" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4 lg:items-end">
                        <label class="text-xs font-semibold text-slate-700">
                            Tanggal Mulai
                            <input class="input input-bordered input-sm mt-1 w-full" type="date" name="str1" required>
                        </label>
                        <label class="text-xs font-semibold text-slate-700">
                            Tanggal Selesai
                            <input class="input input-bordered input-sm mt-1 w-full" type="date" name="end1" required>
                        </label>
                        <label class="text-xs font-semibold text-slate-700">
                            Pilih Client
                            <select name="filter" class="select select-bordered select-sm mt-1 w-full" required>
                                <option value="">~ Pilih Client ~</option>
                                @foreach($kerj as $i)
                                    <option value="{{ $i->id }}">{{ $i->client->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <button class="btn btn-sm bg-slate-900 text-white hover:bg-slate-800 w-full">
                            Unduh PDF
                        </button>
                    </form>
                </section>
            @elseif($kode === 'LEADER')
                <section class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6 shadow-sm">
                    <h2 class="text-base font-bold text-slate-900">Buat jadwal per divisi</h2>
                    <p class="text-xs text-slate-500">Pilih rentang tanggal dan divisi tujuan.</p>

                    <form action="{{ route('store.processDate') }}" method="GET" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4 lg:items-end">
                        <label class="text-xs font-semibold text-slate-700">
                            Tanggal Mulai
                            <input class="input input-bordered input-sm mt-1 w-full" type="date" name="str1" required>
                        </label>
                        <label class="text-xs font-semibold text-slate-700">
                            Tanggal Selesai
                            <input class="input input-bordered input-sm mt-1 w-full" type="date" name="end1" required>
                        </label>
                        <label class="text-xs font-semibold text-slate-700">
                            Pilih Divisi
                            <select name="divisi" class="select select-bordered select-sm mt-1 w-full" required>
                                <option value="">~ Pilih Divisi ~</option>
                                @foreach($divisi as $i)
                                    <option value="{{ $i->id }}">{{ $i->jabatan->name_jabatan }}</option>
                                @endforeach
                            </select>
                        </label>
                        <button class="btn btn-sm bg-indigo-600 text-white hover:bg-indigo-700 border-indigo-600 w-full">
                            Lanjut Isi Jadwal
                        </button>
                    </form>
                </section>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const input = document.getElementById('iJDW');
                const button = document.getElementById('btnImport');
                const label = document.getElementById('importLabel');
                input?.addEventListener('change', () => {
                    const selected = Boolean(input.files?.length);
                    button?.classList.toggle('hidden', !selected);
                    if (label) label.textContent = selected ? input.files[0].name : 'Belum ada file dipilih';
                });
            });
        </script>
    @endpush
</x-app-layout>
