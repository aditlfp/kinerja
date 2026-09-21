<x-admin-layout :fullWidth="true">
    @section('title', 'Data Jadwal Karyawan')

    @php
        $starte = \Carbon\Carbon::now('Asia/Jakarta')->startOfMonth()->toDateString();
        $ende = \Carbon\Carbon::now('Asia/Jakarta')->endOfMonth();
    @endphp

    <div class="mx-auto w-full max-w-screen-xl space-y-4 px-2 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Data Jadwal Karyawan</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">{{ Auth::user()->kerjasama->client->name }}</p>
                </div>
                <div class="flex items-center gap-2">
                    @if(Auth::user()->divisi->jabatan->code_jabatan == "CO-CS")
                        <a href="{{ route('leaderView') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50">Kembali</a>
                    @elseif(Auth::user()->divisi->jabatan->code_jabatan == "CO-SCR")
                        <a href="{{ route('danruView') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50">Kembali</a>
                    @else
                        <a href="{{ route('dashboard.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50">Kembali</a>
                    @endif
                </div>
            </div>
        </section>

        @if(Auth::user()->role_id == 2)
            <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                <div class="flex flex-col justify-center items-center gap-3 md:flex-row md:justify-between w-full">
                    <form action="{{ route('admin.jadwal.import') }}" method="POST" class="flex items-center gap-2 overflow-hidden" enctype="multipart/form-data">
                        @csrf
                        <label for="iJDW" class="inline-flex h-10 items-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 text-sm font-semibold text-emerald-700 cursor-pointer hover:bg-emerald-100"><i class="ri-file-excel-2-line text-lg mr-1.5"></i><span id="importLabel">Import Jadwal</span></label>
                        <input id="iJDW" name="file" type="file" class="hidden" accept=".csv"/>
                        <button class="inline-flex h-10 items-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white hidden" type="submit" id="btnImport">Import</button>
                    </form>
                    <x-search />
                </div>
            </section>

            <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                <form action="{{ route('admin.jadwal.export') }}" method="get" class="grid gap-3 md:grid-cols-4 items-end">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Mulai</label>
                        <input class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm" type="date" name="str1" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Selesai</label>
                        <input class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm" type="date" name="end1" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Pilih Client</label>
                        <select name="filter" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm" required>
                            <option class="disabled">~Pilih Client~</option>
                            @forelse($kerj as $i)
                                <option value="{{ $i->id }}">{{ $i->client->name }}</option>
                            @empty
                                <option class="disabled">~ Client Kosong ~</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-amber-500 px-4 text-sm font-semibold text-white hover:bg-amber-600"><i class="ri-file-download-line mr-1.5"></i>Print PDF</button>
                    </div>
                </form>
            </section>
        @elseif(Auth::user()->divisi->jabatan->code_jabatan == 'LEADER')
            <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                <form action="{{ route('store.processDate') }}" method="GET" class="grid gap-3 md:grid-cols-4 items-end">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Mulai</label>
                        <input class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm" type="date" name="str1" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Selesai</label>
                        <input class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm" type="date" name="end1" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Pilih Divisi</label>
                        <select name="divisi" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm" required>
                            <option class="disabled">~Pilih Divisi~</option>
                            @forelse($divisi as $i)
                                <option value="{{ $i->id }}">{{ $i->jabatan->name_jabatan }}</option>
                            @empty
                                <option class="disabled">~Divisi Kosong~</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700">+ Jadwal</button>
                    </div>
                </form>
            </section>
        @endif
    </div>

    @push('scripts')
    <script>
        $(function () {
            $('#iJDW').on('change', function () {
                const hasFile = !!$(this).val();
                $('#importLabel').text(hasFile ? 'Klik import' : 'Import Jadwal');
                $('#btnImport').toggleClass('hidden', !hasFile);
            });
        });
    </script>
    @endpush
</x-admin-layout>
