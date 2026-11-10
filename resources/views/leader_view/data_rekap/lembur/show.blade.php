<x-app-layout>
    <div class="min-h-screen w-full bg-slate-50 px-3 py-4 text-slate-800 sm:px-5 lg:px-6">
        <div class="mx-auto w-full max-w-7xl">
            <div class="mb-4 flex items-center justify-between gap-3">
                <img src="{{ URL::asset('logo/logo_kinerja.png') }}" alt="Logo Kinerja" class="h-auto w-28 sm:w-32">
                <a href="{{ route('dashboard.index') }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-white px-3 text-sm font-semibold text-slate-700 shadow-xs ring-1 ring-slate-200 hover:bg-slate-50">
                    <i class="ri-home-4-line"></i> Dashboard
                </a>
            </div>
            <div class="p-4 mb-4 bg-white border rounded-2xl shadow-xs border-white/60 ring-1 ring-slate-900/5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center min-w-0 gap-3">
                        <a href="{{ route('index.rekap.data.leader') }}"
                            class="inline-flex items-center justify-center w-10 h-10 ml-1 transition rounded-lg shrink-0 sm:ml-0 bg-slate-100 text-slate-700 hover:bg-slate-200 focus:outline-hidden focus:ring-2 focus:ring-slate-400 focus:ring-offset-2"
                            aria-label="Kembali ke rekapitulasi">
                            <i class="text-xl ri-arrow-left-line"></i>
                        </a>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-slate-500">Data Rekap</p>
                            <h1 class="border-l-4 border-sky-500 pl-3 text-xl font-bold leading-tight truncate text-slate-900 sm:text-2xl">
                                Riwayat Lembur
                            </h1>
                            <p class="mt-1 text-sm leading-5 text-slate-500">Pantau status dan kelola pengajuan lembur.
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('overtime-application.create') }}"
                        class="inline-flex items-center justify-center w-full gap-2 px-4 py-2 text-sm font-semibold transition rounded-lg min-h-10 bg-amber-400 text-slate-900 hover:bg-amber-500 focus:outline-hidden focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 sm:w-auto">
                        <i class="ri-add-line"></i>
                        <span>Pengajuan Baru</span>
                    </a>
                </div>
            </div>

            <div class="p-3 mb-4 bg-white border rounded-lg shadow-xs border-slate-200 sm:p-4">
                <form action="{{ route('overtime-application.history') }}" method="GET"
                    class="grid grid-cols-1 gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]">
                    <select name="status"
                        class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-hidden transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Di Ajukan" {{ request('status') == 'Di Ajukan' ? 'selected' : '' }}>Di Ajukan
                        </option>
                        <option value="rejected" {{ request('status') == 'Di Tolak' ? 'selected' : '' }}>Ditolak
                        </option>
                    </select>
                    <input type="month" name="month" value="{{ request('month') }}"
                        class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-hidden transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                    <div class="flex gap-2">
                        <button type="submit"
                            class="inline-flex items-center justify-center flex-1 gap-2 px-4 py-2 text-sm font-semibold text-white transition rounded-lg min-h-11 bg-sky-600 hover:bg-sky-700 sm:flex-none">
                            <i class="ri-filter-3-line"></i>
                            <span>Filter</span>
                        </button>
                        <a href="{{ route('overtime-application.history') }}"
                            class="inline-flex items-center justify-center flex-1 gap-2 px-4 py-2 text-sm font-semibold transition bg-white border rounded-lg min-h-11 border-slate-300 text-slate-700 hover:bg-slate-50 sm:flex-none">
                            <i class="ri-refresh-line"></i>
                            <span>Reset</span>
                        </a>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden bg-white border rounded-lg shadow-xs border-slate-200">
                <div class="p-3 space-y-3 sm:hidden">
                    @forelse($overtimes ?? [] as $index => $overtime)
                        @php
                            $status = $overtime->status ?? 'pending';
                            $type = $overtime->type_overtime ?? 'shift';
                            $typeLabel =
                                $type === 'shift' ? '1 Shift' : ($overtime->type_overtime_manual ?: ucfirst($type));
                        @endphp
                        <div class="p-3 bg-white border rounded-lg shadow-xs border-slate-200">
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold truncate text-slate-800">
                                        {{ $overtime->user->nama_lengkap ?? '-' }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ \Carbon\Carbon::parse($overtime->date_overtime ?? now())->format('d M Y') }}
                                    </p>
                                </div>
                                @if ($status == 'Di Ajukan')
                                    <span
                                        class="inline-flex shrink-0 items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-[11px] font-medium text-green-700">
                                        <i class="ri-checkbox-circle-fill"></i>Diajukan
                                    </span>
                                @elseif($status == 'Di Tolak')
                                    <span
                                        class="inline-flex shrink-0 items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-medium text-red-700">
                                        <i class="ri-close-circle-fill"></i>Ditolak
                                    </span>
                                @else
                                    <span
                                        class="inline-flex shrink-0 items-center gap-1 rounded-full bg-yellow-100 px-2 py-0.5 text-[11px] font-medium text-yellow-700">
                                        <i class="ri-time-fill"></i>Pending
                                    </span>
                                @endif
                            </div>

                            <div class="grid grid-cols-2 gap-2 mb-4 text-xs text-slate-600">
                                <div class="rounded-lg bg-slate-50 p-1.5">
                                    <span class="block text-slate-400">Tanggal Pengisian</span>
                                    <span class="font-medium text-slate-700">
                                        {{ \Carbon\Carbon::parse($overtime->created_at ?? now())->format('d M Y H:i') }}
                                    </span>
                                </div>
                                <div class="rounded-lg bg-slate-50 p-1.5">
                                    <span class="block text-slate-400">Jenis Lembur</span>
                                    <span class="font-medium text-slate-700">{{ $typeLabel }}</span>
                                </div>
                            </div>

                            <div class="mb-4">
                                <span class="block text-xs text-slate-400">Keterangan</span>
                                <p class="mt-1 text-xs leading-5 text-slate-600">
                                    {{ $overtime->desc ?? 'Tidak ada keterangan' }}
                                </p>
                                @if (!empty($overtime->foto_bukti))
                                    <a href="{{ asset('storage/' . $overtime->foto_bukti) }}" target="_blank"
                                        class="inline-flex items-center gap-1 mt-2 text-xs font-semibold text-sky-700 hover:underline">
                                        <i class="ri-image-line"></i>
                                        Lihat Foto Bukti
                                    </a>
                                @endif
                            </div>

                            <div class="grid grid-cols-2 gap-1.5">
                                <button onclick="viewDetail({{ $overtime->id ?? 0 }})"
                                    class="inline-flex min-h-8 items-center justify-center gap-1.5 rounded-lg bg-sky-50 px-2 py-1.5 text-xs font-semibold text-sky-700 hover:bg-sky-100"
                                    type="button">
                                    <i class="ri-eye-line"></i>Lihat
                                </button>
                                @if ($status == 'pending' && !($isSubmissionLocked ?? false))
                                    <button onclick="sendOvertime({{ $overtime->id ?? 0 }})"
                                        class="inline-flex min-h-8 items-center justify-center gap-1.5 rounded-lg bg-green-50 px-2 py-1.5 text-xs font-semibold text-green-700 hover:bg-green-100"
                                        type="button">
                                        <i class="ri-send-plane-fill"></i>Ajukan
                                    </button>
                                    <a href="{{ route('overtime-application.edit', $overtime->id) }}"
                                        class="inline-flex min-h-8 items-center justify-center gap-1.5 rounded-lg bg-yellow-50 px-2 py-1.5 text-xs font-semibold text-yellow-700 hover:bg-yellow-100">
                                        <i class="ri-edit-line"></i>Edit
                                    </a>
                                    <button onclick="deleteOvertime({{ $overtime->id ?? 0 }})"
                                        class="inline-flex min-h-8 items-center justify-center gap-1.5 rounded-lg bg-red-50 px-2 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100"
                                        type="button">
                                        <i class="ri-delete-bin-line"></i>Hapus
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-10 text-center">
                            <div class="flex items-center justify-center w-14 h-14 mx-auto rounded-full bg-slate-100">
                                <i class="text-3xl ri-inbox-line text-slate-400"></i>
                            </div>
                            <p class="mt-3 font-medium text-slate-600">Belum ada riwayat pengajuan lembur</p>
                            <a href="{{ route('overtime-application.create') }}"
                                class="inline-flex items-center gap-2 px-6 py-2 mt-3 text-sm font-semibold rounded-lg min-h-10 bg-amber-400 text-slate-900">
                                <i class="ri-add-line"></i>
                                <span>Buat Pengajuan</span>
                            </a>
                        </div>
                    @endforelse
                </div>

                <div class="hidden overflow-x-auto sm:block">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50">
                                <th class="px-3 py-2 text-xs font-semibold text-left text-slate-500">No</th>
                                <th class="px-3 py-2 text-xs font-semibold text-left text-slate-500">Nama Pegawai</th>
                                <th class="px-3 py-2 text-xs font-semibold text-left text-slate-500">Tanggal Pengisian
                                </th>
                                <th class="px-3 py-2 text-xs font-semibold text-left text-slate-500">Tanggal Lembur</th>
                                <th class="px-3 py-2 text-xs font-semibold text-left text-slate-500">Jenis Lembur</th>
                                <th class="px-3 py-2 text-xs font-semibold text-left text-slate-500">Keterangan</th>
                                <th class="px-3 py-2 text-xs font-semibold text-left text-slate-500">Status</th>
                                <th class="px-3 py-2 text-xs font-semibold text-center text-slate-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($overtimes ?? [] as $index => $overtime)
                                @php
                                    $status = $overtime->status ?? 'pending';
                                    $type = $overtime->type_overtime ?? 'shift';
                                    $typeLabel =
                                        $type === 'shift'
                                            ? '1 Shift'
                                            : ($overtime->type_overtime_manual ?:
                                            ucfirst($type));
                                @endphp
                                <tr class="transition-colors hover:bg-slate-50">
                                    <td class="px-3 py-2 text-sm text-slate-700">
                                        {{ method_exists($overtimes, 'firstItem') ? $overtimes->firstItem() + $index : $index + 1 }}
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex items-center justify-center shrink-0 w-10 h-10 rounded-full bg-sky-100">
                                                <span class="text-sm font-semibold text-sky-700">
                                                    {{ substr($overtime->user->nama_lengkap ?? 'N', 0, 1) }}
                                                </span>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-medium truncate text-slate-800">
                                                    {{ $overtime->user->name ?? 'N/A' }}
                                                </p>
                                                <p class="text-xs text-slate-500 whitespace-nowrap">
                                                    {{ $overtime->user->nama_lengkap ?? '-' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 text-sm text-slate-700 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($overtime->created_at ?? now())->format('d M Y H:i') }}
                                    </td>
                                    <td class="px-3 py-2 text-sm text-slate-700 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($overtime->date_overtime ?? now())->format('d M Y') }}
                                    </td>
                                    <td class="px-3 py-2">
                                        @if ($type == 'shift')
                                            <span
                                                class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium rounded-full bg-sky-100 text-sky-700 whitespace-nowrap">
                                                <i class="ri-time-line"></i>
                                                1 Shift
                                            </span>
                                        @elseif($type == 'jam')
                                            <span
                                                class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-indigo-700 rounded-full bg-indigo-100 whitespace-nowrap">
                                                <i class="ri-timer-line"></i>
                                                {{ $typeLabel }}
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-slate-700 rounded-full bg-slate-100 whitespace-nowrap">
                                                <i class="ri-more-line"></i>
                                                {{ $typeLabel }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="max-w-xs px-4 py-4 text-sm text-slate-700">
                                        <div class="line-clamp-2"
                                            title="{{ $overtime->desc ?? 'Tidak ada keterangan' }}">
                                            {{ $overtime->desc ?? 'Tidak ada keterangan' }}
                                        </div>
                                        @if (!empty($overtime->foto_bukti))
                                            <a href="{{ asset('storage/' . $overtime->foto_bukti) }}" target="_blank"
                                                class="inline-flex items-center gap-1 mt-1 text-xs font-semibold text-sky-700 hover:underline">
                                                <i class="ri-image-line"></i>
                                                Foto Bukti
                                            </a>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2">
                                        @if ($status == 'Di Ajukan')
                                            <span
                                                class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full whitespace-nowrap">
                                                <i class="ri-checkbox-circle-fill"></i>Di Ajukan
                                            </span>
                                        @elseif($status == 'Di Tolak')
                                            <span
                                                class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-red-700 bg-red-100 rounded-full whitespace-nowrap">
                                                <i class="ri-close-circle-fill"></i>Ditolak
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-yellow-700 bg-yellow-100 rounded-full whitespace-nowrap">
                                                <i class="ri-time-fill"></i>Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="flex items-center justify-center gap-2">
                                            <button onclick="viewDetail({{ $overtime->id ?? 0 }})"
                                                class="flex items-center justify-center text-blue-600 transition bg-blue-100 rounded-lg w-9 h-9 hover:bg-blue-200"
                                                title="Lihat Detail" type="button">
                                                <i class="ri-eye-line"></i>
                                            </button>
                                            @if ($status == 'pending' && !($isSubmissionLocked ?? false))
                                                <a href="{{ route('overtime-application.edit', $overtime->id) }}"
                                                    class="flex items-center justify-center text-yellow-600 transition bg-yellow-100 rounded-lg w-9 h-9 hover:bg-yellow-200"
                                                    title="Edit">
                                                    <i class="ri-edit-line"></i>
                                                </a>
                                                <button onclick="deleteOvertime({{ $overtime->id ?? 0 }})"
                                                    class="flex items-center justify-center text-red-600 transition bg-red-100 rounded-lg w-9 h-9 hover:bg-red-200"
                                                    title="Hapus" type="button">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                                <button onclick="sendOvertime({{ $overtime->id ?? 0 }})"
                                                    class="flex items-center justify-center text-green-600 transition bg-green-100 rounded-lg w-9 h-9 hover:bg-green-200"
                                                    title="Ajukan" type="button">
                                                    <i class="ri-send-plane-fill"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-12 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <div
                                                class="flex items-center justify-center w-16 h-16 rounded-full bg-slate-100">
                                                <i class="text-3xl ri-inbox-line text-slate-400"></i>
                                            </div>
                                            <div>
                                                <p class="font-medium text-slate-600">Tidak ada data</p>
                                                <p class="text-sm text-slate-500">Belum ada riwayat pengajuan lembur
                                                </p>
                                            </div>
                                            <a href="{{ route('overtime-application.create') }}"
                                                class="inline-flex items-center gap-2 px-6 py-2 mt-2 text-sm font-semibold rounded-lg min-h-10 bg-amber-400 text-slate-900">
                                                <i class="ri-add-line"></i>
                                                <span>Buat Pengajuan</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if (isset($overtimes) && $overtimes->hasPages())
                    <div class="px-3 py-2 border-t border-slate-200">
                        {{ $overtimes->links() }}
                    </div>
                @endif
            </div>

            <div class="flex justify-end w-full my-2">
                <form id="bulkOvertimeForm" action="{{ route('overtime-bulk.status') }}" method="post"
                    style="display: none;">
                    @csrf
                    @method('PATCH')
                </form>
                <button type="button" @disabled(!($canBulkSubmit ?? false)) onclick="openBulkOvertimeModal()"
                    class="inline-flex items-center justify-center w-full gap-2 px-4 py-2 mt-3 text-sm font-semibold rounded-lg min-h-10 sm:mt-0 sm:w-auto {{ $canBulkSubmit ?? false ? 'bg-green-100 text-green-700 hover:bg-green-200 transition' : 'cursor-not-allowed bg-slate-100 text-slate-400' }}">
                    <i class="ri-send-plane-fill"></i>
                    Ajukan Semua
                </button>
            </div>
        </div>
    </div>

    <div x-data="{ open: false, detail: {} }" x-show="open" @detail-modal.window="open = true; detail = $event.detail" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 transition-opacity bg-black/50"></div>

        <div class="flex items-center justify-center min-h-full p-3 sm:p-4">
            <div class="relative max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-4 shadow-xl sm:p-6"
                @click.away="open = false" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-bold text-slate-800 sm:text-xl">Detail Pengajuan Lembur</h3>
                    <button class="flex items-center justify-center w-8 h-8 transition rounded-lg hover:bg-slate-100"
                        type="button">
                        <i class="text-xl ri-close-line text-slate-600"></i>
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4 pb-4 border-b sm:grid-cols-3 border-slate-200">
                        <div>
                            <p class="mb-1 text-xs text-slate-500">Nama Pegawai</p>
                            <p class="font-semibold text-slate-800" x-text="detail.user?.nama_lengkap || '-'"></p>
                        </div>
                        <div>
                            <p class="mb-1 text-xs text-slate-500">Tanggal Lembur</p>
                            <p class="font-semibold text-slate-800"
                                x-text="detail.date_overtime ? new Date(detail.date_overtime).toLocaleDateString('id-ID') : '-'">
                            </p>
                        </div>
                        <div>
                            <p class="mb-1 text-xs text-slate-500">Jenis Lembur</p>
                            <p class="font-semibold text-slate-800"
                                x-text="detail.type_overtime == 'shift' ? '1 Shift' : (detail.type_overtime_manual || detail.type_overtime || '-')">
                            </p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <p class="mb-1 text-xs text-slate-500">Status</p>
                            <span x-text="detail.status ?? 'pending'"
                                :class="{
                                    'bg-green-100 text-green-700': detail.status == 'Di Ajukan',
                                    'bg-yellow-100 text-yellow-700': !detail.status || detail.status == 'pending',
                                    'bg-red-100 text-red-700': detail.status == 'Di Tolak'
                                }"
                                class="inline-block px-3 py-1 text-xs font-medium rounded-full"></span>
                        </div>
                        <div>
                            <p class="mb-1 text-xs text-slate-500">Tanggal Pengisian</p>
                            <p class="font-semibold text-slate-800"
                                x-text="detail.created_at ? new Date(detail.created_at).toLocaleString('id-ID') : '-'">
                            </p>
                        </div>
                    </div>
                    <div>
                        <p class="mb-1 text-xs text-slate-500">Keterangan</p>
                        <p class="text-sm leading-6 text-slate-700" x-text="detail.desc || '-'"></p>
                    </div>
                    <div x-show="detail.foto_bukti" x-cloak>
                        <p class="mb-2 text-xs text-slate-500">Foto Bukti</p>
                        <a :href="`/storage/${detail.foto_bukti}`" target="_blank"
                            class="inline-flex items-center gap-1 text-xs font-semibold text-sky-700 hover:underline">
                            <i class="ri-image-line"></i>
                            Buka gambar penuh
                        </a>
                        <img :src="`/storage/${detail.foto_bukti}`" alt="Foto Bukti"
                            class="object-cover w-full max-w-md mt-2 border rounded-lg border-slate-200">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 mt-6 border-t border-slate-200">
                    <button
                        class="inline-flex items-center justify-center w-full px-4 py-2 text-sm font-semibold transition bg-white border rounded-lg min-h-10 border-slate-300 text-slate-700 hover:bg-slate-50 sm:w-auto"
                        type="button">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let pendingDeleteId = null;
        let pendingSubmitId = null;

        async function viewDetail(id) {
            const url = "{{ route('get-overtime-id', ':id') }}".replace(':id', id);

            try {
                const res = await fetch(url);
                const response = await res.json();

                window.dispatchEvent(new CustomEvent('detail-modal', {
                    detail: response.data
                }));
            } catch (err) {
                console.error(err);
            }
        }

        function deleteOvertime(id) {
            pendingDeleteId = id;
            window.openConfirmModal({
                title: 'Hapus Pengajuan',
                message: 'Apakah Anda yakin ingin menghapus pengajuan ini? Data yang dihapus tidak dapat dikembalikan.',
                confirmText: 'Ya, Hapus',
                cancelText: 'Batal',
                type: 'danger',
                onConfirm: deleteOvertimeConfirmed,
            });
        }

        function deleteOvertimeConfirmed() {
            if (!pendingDeleteId) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/overtime-application/${pendingDeleteId}`;

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';

            form.appendChild(csrfInput);
            form.appendChild(methodInput);
            document.body.appendChild(form);
            form.submit();
        }

        function sendOvertime(id) {
            pendingSubmitId = id;
            window.openConfirmModal({
                title: 'Ajukan Pengajuan',
                message: 'Apakah Anda yakin ingin mengajukan pengajuan ini?',
                confirmText: 'Ya, Ajukan',
                cancelText: 'Batal',
                type: 'warning',
                onConfirm: submitOvertimeConfirmed,
            });
        }

        function openBulkOvertimeModal() {
            if (!{{ $canBulkSubmit ?? false ? 'true' : 'false' }}) {
                return;
            }

            window.openConfirmModal({
                title: 'Ajukan Semua Pengajuan',
                message: 'Apakah Anda yakin ingin mengajukan semua pengajuan yang belum diajukan?',
                confirmText: 'Ya, Ajukan Semua',
                cancelText: 'Batal',
                type: 'warning',
                onConfirm: submitBulkOvertimeConfirmed,
            });
        }

        function submitOvertimeConfirmed() {
            if (!pendingSubmitId) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/overtime-change-status/${pendingSubmitId}`;

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PATCH';

            form.appendChild(csrfInput);
            form.appendChild(methodInput);
            document.body.appendChild(form);
            form.submit();
        }

        function submitBulkOvertimeConfirmed() {
            const form = document.getElementById('bulkOvertimeForm');
            if (form) {
                form.submit();
            }
            window.dispatchEvent(new CustomEvent('close-modal-bulksubmit-modal'));
        }
    </script>

    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</x-app-layout>
