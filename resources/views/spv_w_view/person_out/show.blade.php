<x-app-layout>
    @php
        $spvwClientId = request('client_id', session('spvw.selected_client_id'));
    @endphp

    <div class="min-h-screen w-full bg-slate-50 px-3 py-4 text-slate-800 sm:px-5 lg:px-6">
        <div class="w-full px-3 py-4 mx-auto max-w-7xl sm:px-5 lg:px-6">
            <div class="p-4 mb-4 bg-white border rounded-lg shadow-xs border-white/60 ring-1 ring-slate-900/5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center min-w-0 gap-3">
                        <a href="{{ route('spvw.rekap.index', array_filter(['client_id' => $spvwClientId])) }}"
                            class="inline-flex items-center justify-center w-10 h-10 ml-1 transition rounded-lg shrink-0 sm:ml-0 bg-slate-100 text-slate-700 hover:bg-slate-200 focus:outline-hidden focus:ring-2 focus:ring-slate-400 focus:ring-offset-2"
                            aria-label="Kembali ke rekapitulasi">
                            <i class="text-xl ri-arrow-left-line"></i>
                        </a>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-slate-500">Data Rekap</p>
                            <h1 class="text-xl font-bold leading-tight truncate text-slate-900 sm:text-2xl">
                                Riwayat Personil Keluar
                            </h1>
                            <p class="mt-1 text-sm leading-5 text-slate-500">Pantau status dan kelola pengajuan personil
                                keluar.</p>
                        </div>
                    </div>
                    <a href="{{ route('spvw.person-is-out.create', array_filter(['client_id' => $spvwClientId])) }}"
                        class="inline-flex items-center justify-center w-full gap-2 px-4 py-2 text-sm font-semibold transition rounded-lg min-h-10 bg-amber-400 text-slate-900 hover:bg-amber-500 focus:outline-hidden focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 sm:w-auto">
                        <i class="ri-add-line"></i>
                        <span>Pengajuan Baru</span>
                    </a>
                </div>
            </div>

            <div class="p-3 mb-4 bg-white border rounded-lg shadow-xs border-slate-200 sm:p-4">
                <form action="{{ route('spvw.person-is-out.history', array_filter(['client_id' => $spvwClientId])) }}" method="GET"
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
                        <a href="{{ route('spvw.person-is-out.history', array_filter(['client_id' => $spvwClientId])) }}"
                            class="inline-flex items-center justify-center flex-1 gap-2 px-4 py-2 text-sm font-semibold transition bg-white border rounded-lg min-h-11 border-slate-300 text-slate-700 hover:bg-slate-50 sm:flex-none">
                            <i class="ri-refresh-line"></i>
                            <span>Reset</span>
                        </a>
                    </div>
                @if(isset($spvwClientId) && $spvwClientId)
                    <input type="hidden" name="client_id" value="{{ $spvwClientId }}">
                @endif
            </form>
            </div>

            <div class="overflow-hidden bg-white border rounded-lg shadow-xs border-slate-200">
                <div class="p-3 space-y-3 sm:hidden">
                    @forelse($personOut as $index => $person)
                        @php
                            $status = $person->status ?? 'pending';
                            $reasonLabel =
                                $person->reason === 'lainnya'
                                    ? ($person->reason_manual ?:
                                    'Lainnya')
                                    : ucfirst((string) $person->reason);
                            $imageUrl = $person->img ? asset('storage/images/' . $person->img) : null;
                        @endphp
                        <div class="p-4 bg-white border rounded-lg shadow-xs border-slate-200">
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold truncate {{ $person->user ? 'text-slate-800' : 'text-red-600' }}">
                                        {{ $person->user->nama_lengkap ?? '' }}
                                        @if(!$person->user)<span class="inline-flex items-center gap-1 text-xs font-medium text-red-500"><i class="ri-delete-bin-line"></i> Dihapus permanen</span>@endif
                                    </p>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        Keluar {{ \Carbon\Carbon::parse($person->out_date ?? now())->format('d M Y') }}
                                    </p>
                                </div>
                                @if ($status == 'Di Ajukan')
                                    <span
                                        class="inline-flex shrink-0 items-center gap-1 rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700">
                                        <i class="ri-checkbox-circle-fill"></i>Diajukan
                                    </span>
                                @elseif($status == 'Di Tolak')
                                    <span
                                        class="inline-flex shrink-0 items-center gap-1 rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-700">
                                        <i class="ri-close-circle-fill"></i>Ditolak
                                    </span>
                                @else
                                    <span
                                        class="inline-flex shrink-0 items-center gap-1 rounded-full bg-yellow-100 px-2.5 py-1 text-xs font-medium text-yellow-700">
                                        <i class="ri-time-fill"></i>Pending
                                    </span>
                                @endif
                            </div>

                            <div class="grid grid-cols-2 gap-2 mb-4 text-xs text-slate-600">
                                <div class="p-2 rounded-lg bg-slate-50">
                                    <span class="block text-slate-400">Jumlah MK</span>
                                    <span class="font-medium text-slate-700">{{ $person->total_mk ?? '-' }}</span>
                                </div>
                                <div class="p-2 rounded-lg bg-slate-50">
                                    <span class="block text-slate-400">Alasan</span>
                                    <span class="font-medium text-slate-700">{{ $reasonLabel }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 p-2 mb-4 rounded-lg bg-slate-50">
                                @if ($imageUrl)
                                    <img src="{{ $imageUrl }}"
                                        class="object-cover border rounded-lg w-14 h-14 border-slate-200"
                                        alt="Bukti">
                                @else
                                    <div
                                        class="flex items-center justify-center text-xs bg-white border rounded-lg w-14 h-14 text-slate-400 border-slate-200">
                                        No Image
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <span class="block text-xs text-slate-400">Bukti</span>
                                    <span
                                        class="text-xs font-medium text-slate-700">{{ $person->img ?: 'Tidak ada bukti' }}</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <button onclick="viewDetail({{ $person->id ?? 0 }})"
                                    class="inline-flex items-center justify-center gap-2 px-3 py-2 text-xs font-semibold rounded-lg min-h-10 bg-sky-50 text-sky-700 hover:bg-sky-100"
                                    type="button">
                                    <i class="ri-eye-line"></i>Lihat
                                </button>
                                @if ($status == 'pending' && !($isSubmissionLocked ?? false))
                                    <button onclick="sendPersonOut({{ $person->id ?? 0 }})"
                                        class="inline-flex items-center justify-center gap-2 px-3 py-2 text-xs font-semibold text-green-700 rounded-lg min-h-10 bg-green-50 hover:bg-green-100"
                                        type="button">
                                        <i class="ri-send-plane-fill"></i>Ajukan
                                    </button>
                                    <a href="{{ route('spvw.person-is-out.edit', array_filter(['id' => $person->id, 'client_id' => $spvwClientId])) }}"
                                        class="inline-flex items-center justify-center gap-2 px-3 py-2 text-xs font-semibold text-yellow-700 rounded-lg min-h-10 bg-yellow-50 hover:bg-yellow-100">
                                        <i class="ri-edit-line"></i>Edit
                                    </a>
                                    <button onclick="deletePersonOut({{ $person->id ?? 0 }})"
                                        class="inline-flex items-center justify-center gap-2 px-3 py-2 text-xs font-semibold text-red-700 rounded-lg min-h-10 bg-red-50 hover:bg-red-100"
                                        type="button">
                                        <i class="ri-delete-bin-line"></i>Hapus
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-10 text-center">
                            <div class="flex items-center justify-center mx-auto rounded-full w-14 h-14 bg-slate-100">
                                <i class="text-3xl ri-inbox-line text-slate-400"></i>
                            </div>
                            <p class="mt-3 font-medium text-slate-600">Belum ada riwayat pengajuan personil keluar</p>
                            <a href="{{ route('spvw.person-is-out.create', array_filter(['client_id' => $spvwClientId])) }}"
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
                                <th class="px-4 py-3 text-xs font-semibold text-left text-slate-500">No</th>
                                <th class="px-4 py-3 text-xs font-semibold text-left text-slate-500">Nama Pegawai</th>
                                <th class="px-4 py-3 text-xs font-semibold text-left text-slate-500">Nama Penginput</th>
                                <th class="px-4 py-3 text-xs font-semibold text-left text-slate-500">Tanggal Keluar</th>
                                <th class="px-4 py-3 text-xs font-semibold text-left text-slate-500">Jumlah MK</th>
                                <th class="px-4 py-3 text-xs font-semibold text-left text-slate-500">Alasan</th>
                                <th class="px-4 py-3 text-xs font-semibold text-left text-slate-500">Bukti</th>
                                <th class="px-4 py-3 text-xs font-semibold text-left text-slate-500">Status</th>
                                <th class="px-4 py-3 text-xs font-semibold text-center text-slate-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($personOut as $index => $person)
                                @php
                                    $status = $person->status ?? 'pending';
                                    $reasonLabel =
                                        $person->reason === 'lainnya'
                                            ? ($person->reason_manual ?:
                                            'Lainnya')
                                            : ucfirst((string) $person->reason);
                                    $imageUrl = $person->img ? asset('storage/images/' . $person->img) : null;
                                @endphp
                                <tr class="transition-colors hover:bg-slate-50">
                                    <td class="px-4 py-4 text-sm text-slate-700">
                                        {{ method_exists($personOut, 'firstItem') ? $personOut->firstItem() + $index : $index + 1 }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex items-center justify-center shrink-0 w-10 h-10 rounded-full bg-sky-100">
                                                <span class="text-sm font-semibold {{ $person->user ? 'text-sky-700' : 'text-red-600' }}">
                                                    {{ $person->user ? substr($person->user->nama_lengkap, 0, 1) : '!' }}
                                                </span>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-medium truncate {{ $person->user ? 'text-slate-800' : 'text-red-600' }}">
                                                    {{ $person->user->name ?? '' }}
                                                    @if(!$person->user)<span class="text-xs font-medium text-red-500"><i class="ri-alert-line"></i> Akun dihapus permanen</span>@endif
                                                </p>
                                                <p class="text-xs text-slate-500 whitespace-nowrap">
                                                    {{ $person->user->nama_lengkap ?? '-' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-slate-700 whitespace-nowrap">
                                        {{ $person->inputBy ? capitalizeWords($person->inputBy->nama_lengkap) : '-' }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-slate-700 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($person->out_date ?? now())->format('d M Y') }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-slate-700 whitespace-nowrap">
                                        {{ $person->total_mk ?? '-' }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <span
                                            class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium rounded-full bg-sky-100 text-sky-700 whitespace-nowrap">
                                            <i class="ri-logout-box-r-line"></i>
                                            {{ $reasonLabel }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        @if ($imageUrl)
                                            <img src="{{ $imageUrl }}"
                                                class="object-cover w-16 h-16 border rounded-lg border-slate-200"
                                                alt="Bukti">
                                        @else
                                            <div
                                                class="flex items-center justify-center w-16 h-16 text-xs rounded-lg bg-slate-100 text-slate-400">
                                                No Image
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
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
                                    <td class="px-4 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <button onclick="viewDetail({{ $person->id ?? 0 }})"
                                                class="flex items-center justify-center text-blue-600 transition bg-blue-100 rounded-lg w-9 h-9 hover:bg-blue-200"
                                                title="Lihat Detail" type="button">
                                                <i class="ri-eye-line"></i>
                                            </button>
                                            @if ($status == 'pending' && !($isSubmissionLocked ?? false))
                                                <a href="{{ route('spvw.person-is-out.edit', array_filter(['id' => $person->id, 'client_id' => $spvwClientId])) }}"
                                                    class="flex items-center justify-center text-yellow-600 transition bg-yellow-100 rounded-lg w-9 h-9 hover:bg-yellow-200"
                                                    title="Edit">
                                                    <i class="ri-edit-line"></i>
                                                </a>
                                                <button onclick="deletePersonOut({{ $person->id ?? 0 }})"
                                                    class="flex items-center justify-center text-red-600 transition bg-red-100 rounded-lg w-9 h-9 hover:bg-red-200"
                                                    title="Hapus" type="button">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                                <button onclick="sendPersonOut({{ $person->id ?? 0 }})"
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
                                                <p class="text-sm text-slate-500">Belum ada riwayat pengajuan personil
                                                    keluar</p>
                                            </div>
                                            <a href="{{ route('spvw.person-is-out.create', array_filter(['client_id' => $spvwClientId])) }}"
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

                @if (isset($personOut) && $personOut->hasPages())
                    <div class="px-4 py-4 border-t border-slate-200">
                        {{ $personOut->links() }}
                    </div>
                @endif
            </div>

            <div class="flex justify-end w-full my-2">
                <form id="bulkPersonOutForm" action="{{ route('spvw.person-is-out-bulk.status') }}" method="post"
                    style="display:none;">
                    @csrf
                    @method('PATCH')
                @if(isset($spvwClientId) && $spvwClientId)
                    <input type="hidden" name="client_id" value="{{ $spvwClientId }}">
                @endif
            </form>
                <button type="button" @disabled(!($canBulkSubmit ?? false)) onclick="openBulkPersonOutModal()"
                    class="inline-flex items-center justify-center w-full gap-2 px-4 py-2 mt-3 text-sm font-semibold rounded-lg min-h-10 sm:mt-0 sm:w-auto {{ $canBulkSubmit ?? false ? 'bg-green-100 text-green-700 hover:bg-green-200 transition' : 'cursor-not-allowed bg-slate-100 text-slate-400' }}">
                    <i class="ri-send-plane-fill"></i>
                    Ajukan Semua
                </button>
            </div>
        </div>
    </div>
    <div x-data="{ open: false, detail: {} }" x-show="open" @detail-modal.window="open = true; detail = $event.detail"
        @keydown.escape.window="open = false" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 transition-opacity bg-black/50" @click="open = false"></div>

        <div class="flex items-center justify-center min-h-full p-3 sm:p-4">
            <div class="relative max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-4 shadow-xl sm:p-6"
                @click.away="open = false" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-bold text-slate-800 sm:text-xl">Detail Personil Keluar</h3>
                    <button class="flex items-center justify-center w-8 h-8 transition rounded-lg hover:bg-slate-100"
                        @click="open = false"
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
                            <p class="mb-1 text-xs text-slate-500">Tanggal Keluar</p>
                            <p class="font-semibold text-slate-800"
                                x-text="detail.out_date ? new Date(detail.out_date.replace(' ', 'T')).toLocaleDateString('id-ID') : '-'">
                            </p>
                        </div>
                        <div>
                            <p class="mb-1 text-xs text-slate-500">Jumlah MK</p>
                            <p class="font-semibold text-slate-800" x-text="detail.total_mk || '-'"></p>
                        </div>
                        <div>
                            <p class="mb-1 text-xs text-slate-500">Nama Penginput</p>
                            <p class="font-semibold text-slate-800"
                                x-text="detail.input_by?.nama_lengkap || detail.created_by?.nama_lengkap || '-'"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <p class="mb-1 text-xs text-slate-500">Alasan Keluar</p>
                            <p class="font-semibold text-slate-800"
                                x-text="detail.reason === 'lainnya' ? (detail.reason_manual || 'Lainnya') : (detail.reason || '-')">
                            </p>
                        </div>
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
                    </div>

                    <div x-data="{ imageUrl() { return detail.img ? `/storage/images/${detail.img}` : null } }">
                        <p class="mb-2 text-xs text-slate-500">Bukti</p>
                        <template x-if="imageUrl()">
                            <img :src="imageUrl()"
                                class="object-cover w-full max-w-xs border rounded-lg h-44 border-slate-200"
                                alt="Bukti">
                        </template>

                        <template x-if="!imageUrl()">
                            <div
                                class="flex items-center justify-center w-full max-w-xs text-xs rounded-lg h-44 bg-slate-100 text-slate-400">
                                No Image
                            </div>
                        </template>
                    </div>

                    <div>
                        <p class="mb-1 text-xs text-slate-500">Tanggal Pengisian</p>
                        <p class="text-sm text-slate-800"
                            x-text="detail.created_at ? new Date(detail.created_at).toLocaleString('id-ID') : '-'"></p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 mt-6 border-t border-slate-200">
                    <button
                        class="inline-flex items-center justify-center w-full px-4 py-2 text-sm font-semibold transition bg-white border rounded-lg min-h-10 border-slate-300 text-slate-700 hover:bg-slate-50 sm:w-auto"
                        @click="open = false"
                        type="button">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let pendingDeletePersonOutId = null;
        let pendingSubmitPersonOutId = null;

        async function viewDetail(id) {
            const url = "{{ route('spvw.person-is-out-id', ':id') }}".replace(':id', id);
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

        function deletePersonOut(id) {
            pendingDeletePersonOutId = id;
            window.openConfirmModal({
                title: 'Hapus Pengajuan',
                message: 'Apakah Anda yakin ingin menghapus pengajuan ini? Data yang dihapus tidak dapat dikembalikan.',
                confirmText: 'Ya, Hapus',
                cancelText: 'Batal',
                type: 'danger',
                onConfirm: deletePersonOutConfirmed,
            });
        }

        function deletePersonOutConfirmed() {
            if (!pendingDeletePersonOutId) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/SPVW/person-is-out/${pendingDeletePersonOutId}`;

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

        function sendPersonOut(id) {
            pendingSubmitPersonOutId = id;
            window.openConfirmModal({
                title: 'Ajukan Pengajuan',
                message: 'Apakah Anda yakin ingin mengajukan pengajuan ini?',
                confirmText: 'Ya, Ajukan',
                cancelText: 'Batal',
                type: 'warning',
                onConfirm: submitPersonOutConfirmed,
            });
        }

        function openBulkPersonOutModal() {
            if (!{{ $canBulkSubmit ?? false ? 'true' : 'false' }}) {
                return;
            }

            window.openConfirmModal({
                title: 'Ajukan Semua Pengajuan',
                message: 'Apakah Anda yakin ingin mengajukan semua pengajuan yang belum diajukan?',
                confirmText: 'Ya, Ajukan Semua',
                cancelText: 'Batal',
                type: 'warning',
                onConfirm: submitBulkPersonOutConfirmed,
            });
        }

        function submitPersonOutConfirmed() {
            if (!pendingSubmitPersonOutId) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/SPVW/person-is-out-change-status/${pendingSubmitPersonOutId}`;

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

        function submitBulkPersonOutConfirmed() {
            const form = document.getElementById('bulkPersonOutForm');
            if (form) form.submit();
        }
    </script>

    
    
<style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</x-app-layout>
