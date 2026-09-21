<x-admin-layout :fullWidth="true">
    @section('title', 'Data Shift')

    <div x-data="{ delOpen: false, deleteId: null, deleteLabel: '' }" class="w-full px-2 mx-auto space-y-4 max-w-screen-2xl sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Data Shift</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Kelola jadwal shift operasional berdasarkan jabatan dan client.</p>
                </div>
                <div class="flex flex-col w-full gap-2 sm:w-auto sm:flex-row sm:items-center">
                    <label class="flex items-center w-full h-10 gap-2 px-3 border border-gray-200 rounded-xl bg-gray-50 sm:w-72">
                        <i class="text-base text-gray-500 ri-search-2-line"></i>
                        <input type="search" id="searchInput" class="w-full text-sm text-gray-700 bg-transparent border-none placeholder:text-gray-400 focus:outline-none" placeholder="Cari shift, jabatan, client..." />
                    </label>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.shift.create') }}" class="inline-flex items-center h-10 px-4 text-sm font-semibold text-white transition bg-blue-600 rounded-xl hover:bg-blue-700">
                            <i class="ri-add-line mr-1.5 text-base"></i> Shift
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-2xl">
            <div class="flex flex-wrap items-center gap-2 px-4 py-3 text-xs text-gray-500 border-b border-gray-100 sm:px-5">
                <span class="rounded-full bg-blue-50 px-2.5 py-1 font-semibold text-blue-700">Total: {{ $shift->total() }}</span>
                <span>Jadwal shift aktif.</span>
            </div>
            <div class="w-full overflow-x-auto">
                <table class="w-full min-w-[980px] divide-y divide-gray-100" id="searchTable">
                    <thead class="text-xs font-semibold tracking-wide text-left text-gray-600 uppercase bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 sm:px-5">#</th>
                            <th class="px-4 py-3 sm:px-5">Jabatan</th>
                            <th class="px-4 py-3 sm:px-5">Client</th>
                            <th class="px-4 py-3 sm:px-5">Shift</th>
                            <th class="hidden px-4 py-3 md:table-cell sm:px-5">Mulai</th>
                            <th class="hidden px-4 py-3 md:table-cell sm:px-5">Selesai</th>
                            <th class="hidden px-4 py-3 lg:table-cell sm:px-5">Overnight</th>
                            <th class="px-4 py-3 sm:px-5">Status</th>
                            <th class="px-4 py-3 sm:px-5">Hari</th>
                            <th class="px-4 py-3 text-right sm:px-5">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700 divide-y divide-gray-100">
                        @php $no = 1; @endphp
                        @forelse ($shift as $i)
                            <tr class="align-top transition-colors hover:bg-blue-50/40">
                                <td class="px-4 py-3 text-gray-500 sm:px-5">{{ $no++ }}</td>
                                <td class="px-4 py-3 sm:px-5">{{ $i->jabatan->name_jabatan }}</td>
                                <td class="px-4 py-3 sm:px-5">{{ $i->client?->name ?? 'Kosong' }}</td>
                                <td class="px-4 py-3 sm:px-5">
                                    <p class="font-semibold text-gray-800">{{ $i->shift_name }}</p>
                                    <p class="text-xs text-gray-500 md:hidden">{{ $i->jam_start }} - {{ $i->jam_end }}</p>
                                </td>
                                <td class="hidden px-4 py-3 md:table-cell sm:px-5">{{ $i->jam_start }}</td>
                                <td class="hidden px-4 py-3 md:table-cell sm:px-5">{{ $i->jam_end }}</td>
                                <td class="hidden px-4 py-3 lg:table-cell sm:px-5">{{ $i->is_overnight == 1 ? 'Ya' : 'Tidak' }}</td>
                                <td class="px-4 py-3 sm:px-5">
                                    <button
                                        type="button"
                                        data-id="{{ $i->id }}"
                                        data-active="{{ $i->is_active ? '1' : '0' }}"
                                        onclick="toggleShiftActive(this)"
                                        class="shift-toggle relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $i->is_active ? 'bg-green-500' : 'bg-gray-300' }}"
                                        title="{{ $i->is_active ? 'Aktif' : 'Nonaktif' }}"
                                        aria-label="Toggle status shift {{ $i->shift_name }}"
                                        aria-checked="{{ $i->is_active ? 'true' : 'false' }}"
                                        role="switch"
                                    >
                                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $i->is_active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                    </button>
                                </td>
                                <td class="px-4 py-3 sm:px-5">
                                    <div class="flex flex-wrap gap-1">
                                        @php
                                            $daysArray = $i->hari ? json_decode($i->hari, true) : [];
                                            $dayMap = ['Senin' => 'Sen', 'Selasa' => 'Sel', 'Rabu' => 'Rab', 'Kamis' => 'Kam', 'Jumat' => 'Jum', 'Sabtu' => 'Sab', 'Minggu' => 'Min'];
                                            $dayColorMap = [
                                                'Senin' => 'bg-blue-100 text-blue-700',
                                                'Selasa' => 'bg-purple-100 text-purple-700',
                                                'Rabu' => 'bg-indigo-100 text-indigo-700',
                                                'Kamis' => 'bg-amber-100 text-amber-700',
                                                'Jumat' => 'bg-emerald-100 text-emerald-700',
                                                'Sabtu' => 'bg-pink-100 text-pink-700',
                                                'Minggu' => 'bg-rose-100 text-rose-700',
                                            ];
                                        @endphp
                                        @if (count($daysArray) === 7)
                                            <span class="rounded-md bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">Setiap Hari</span>
                                        @elseif (count($daysArray) > 0)
                                            @foreach ($daysArray as $d)
                                                <span class="rounded-md px-2 py-0.5 text-xs font-semibold {{ $dayColorMap[$d] ?? 'bg-gray-100 text-gray-700' }}">{{ $dayMap[$d] ?? $d }}</span>
                                            @endforeach
                                        @else
                                            <span class="rounded-md bg-gray-100 px-2 py-0.5 text-xs text-gray-500">Kosong</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 sm:px-5">
                                    <div class="flex justify-end gap-2">
                                        <a
                                            href="{{ url('shift/' . $i->id . '/edit') }}"
                                            class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-2.5 text-[11px] font-semibold text-blue-700 transition hover:bg-blue-100"
                                        >
                                            <i class="text-xs ri-edit-line"></i>
                                            Edit
                                        </a>
                                        <button
                                            type="button"
                                            @click="delOpen = true; deleteId = {{ $i->id }}; deleteLabel = '{{ addslashes($i->shift_name) }}'"
                                            class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-2.5 text-[11px] font-semibold text-red-700 transition hover:bg-red-100"
                                        >
                                            <i class="text-xs ri-delete-bin-6-line"></i>
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-sm text-center text-gray-500 sm:px-5">Data shift masih kosong.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-100 sm:px-5">
                {{ $shift->links() }}
            </div>
        </section>

        @if (Auth::user()->role_id == 2)
            <div x-show="delOpen" x-cloak style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/35 backdrop-blur-sm">
                <div class="w-full max-w-md overflow-hidden bg-white shadow-xl rounded-2xl">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-800">Konfirmasi Hapus Shift</h3>
                        <button @click="delOpen = false" type="button" class="rounded-lg border border-gray-200 px-2.5 py-1 text-xs font-semibold text-gray-600 hover:bg-gray-50">Tutup</button>
                    </div>
                    <form :action="`{{ url('shift') }}/${deleteId}`" method="POST" class="p-5 space-y-4">
                        @csrf
                        @method('DELETE')
                        <p class="text-sm text-gray-600">Apakah Anda yakin ingin menghapus shift <span class="font-semibold text-gray-800" x-text="deleteLabel"></span>?</p>
                        <div class="flex justify-end gap-2">
                            <button type="button" @click="delOpen = false" class="px-3 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50">Batal</button>
                            <button type="submit" class="px-3 py-2 text-xs font-semibold text-white bg-red-600 rounded-xl hover:bg-red-700">Hapus</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
        <style>
            [x-cloak] { display: none !important; }
        </style>
        <script>
            function toggleShiftActive(btn) {
                const id = btn.dataset.id;
                const isActive = btn.dataset.active === '1';
                fetch(`/shift/${id}/toggle-active`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                })
                .then(r => r.json())
                .then(data => {
                    const active = data.is_active;
                    btn.dataset.active = active ? '1' : '0';
                    btn.className = btn.className
                        .replace(active ? 'bg-gray-300' : 'bg-green-500', active ? 'bg-green-500' : 'bg-gray-300');
                    btn.querySelector('span').className = btn.querySelector('span').className
                        .replace(active ? 'translate-x-0' : 'translate-x-5', active ? 'translate-x-5' : 'translate-x-0');
                    btn.title = active ? 'Aktif' : 'Nonaktif';
                    btn.setAttribute('aria-checked', active ? 'true' : 'false');
                })
                .catch(() => alert('Gagal mengubah status shift.'));
            }
        </script>
    @endpush
</x-admin-layout>
