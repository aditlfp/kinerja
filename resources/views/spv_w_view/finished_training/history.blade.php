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
                                Riwayat Lepas Training
                            </h1>
                            <p class="mt-1 text-sm leading-5 text-slate-500">Pantau status dan kelola pengajuan lepas
                                training.</p>
                        </div>
                    </div>
                    <a href="{{ route('spvw.finished-training.index', array_filter(['client_id' => $spvwClientId])) }}"
                        class="inline-flex items-center justify-center w-full gap-2 px-4 py-2 text-sm font-semibold transition rounded-lg min-h-10 bg-amber-400 text-slate-900 hover:bg-amber-500 focus:outline-hidden focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 sm:w-auto">
                        <i class="ri-add-line"></i><span>Pengajuan Baru</span>
                    </a>
                </div>
            </div>

            <div class="p-3 mb-4 bg-white border rounded-lg shadow-xs border-slate-200 sm:p-4">
                <form action="{{ route('spvw.finished-training.history', array_filter(['client_id' => $spvwClientId])) }}" method="GET"
                    class="grid grid-cols-1 gap-3 sm:grid-cols-4">
                    <select name="status"
                        class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-hidden transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Di Ajukan" {{ request('status') == 'Di Ajukan' ? 'selected' : '' }}>Di Ajukan
                        </option>
                        <option value="Di Setujui" {{ request('status') == 'Di Setujui' ? 'selected' : '' }}>Di Setujui
                        </option>
                        <option value="Di Tolak" {{ request('status') == 'Di Tolak' ? 'selected' : '' }}>Di Tolak
                        </option>
                    </select>
                    <input type="month" name="month" value="{{ request('month') }}"
                        class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-hidden transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari fullname..."
                        class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-hidden transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                    <div class="flex gap-2">
                        <button type="submit"
                            class="inline-flex items-center justify-center flex-1 gap-2 px-4 py-2 text-sm font-semibold text-white transition rounded-lg min-h-11 bg-sky-600 hover:bg-sky-700"><i
                                class="ri-filter-3-line"></i><span>Filter</span></button>
                        <a href="{{ route('spvw.finished-training.history', array_filter(['client_id' => $spvwClientId])) }}"
                            class="inline-flex items-center justify-center flex-1 gap-2 px-4 py-2 text-sm font-semibold transition bg-white border rounded-lg min-h-11 border-slate-300 text-slate-700 hover:bg-slate-50"><i
                                class="ri-refresh-line"></i><span>Reset</span></a>
                    </div>
                @if(isset($spvwClientId) && $spvwClientId)
                    <input type="hidden" name="client_id" value="{{ $spvwClientId }}">
                @endif
            </form>
            </div>

            <div id="alertBox" class="mb-4"></div>

            <div class="overflow-hidden bg-white border rounded-lg shadow-xs border-slate-200">
                <div class="p-3 space-y-3 sm:hidden">
                    @forelse($finishedTrainings as $index => $item)
                        <div class="p-4 bg-white border rounded-lg shadow-xs border-slate-200">
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-800">
                                        {{ $item->user->nama_lengkap ?? '-' }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">Masuk
                                        {{ \Carbon\Carbon::parse($item->date_in)->format('d M Y') }}</p>
                                </div>
                                @if (($item->status ?? 'pending') == 'Di Ajukan')
                                    <span
                                        class="inline-flex shrink-0 items-center gap-1 rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700"><i
                                            class="ri-checkbox-circle-fill"></i>Diajukan</span>
                                @elseif(($item->status ?? 'pending') == 'Di Setujui')
                                    <span
                                        class="inline-flex shrink-0 items-center gap-1 rounded-full bg-lime-100 px-2.5 py-1 text-xs font-medium text-lime-700"><i
                                            class="ri-checkbox-circle-fill"></i>Di Setujui</span>
                                @elseif(($item->status ?? 'pending') == 'Di Tolak')
                                    <span
                                        class="inline-flex shrink-0 items-center gap-1 rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-700"><i
                                            class="ri-close-circle-fill"></i>Ditolak</span>
                                @else
                                    <span
                                        class="inline-flex shrink-0 items-center gap-1 rounded-full bg-yellow-100 px-2.5 py-1 text-xs font-medium text-yellow-700"><i
                                            class="ri-time-fill"></i>Pending</span>
                                @endif
                            </div>
                            <div class="grid grid-cols-2 gap-2 mb-4 text-xs text-slate-600">
                                <div class="p-2 rounded-lg bg-slate-50">
                                    <span class="block text-slate-400">Tanggal Lepas Training</span>
                                    <span
                                        class="font-medium text-slate-700">{{ \Carbon\Carbon::parse($item->date_finish_train)->format('d M Y') }}</span>
                                </div>
                                <div class="p-2 rounded-lg bg-slate-50">
                                    <span class="block text-slate-400">Jumlah Masa Training</span>
                                    <span class="font-medium text-slate-700">{{ $item->masa_training_hari }}
                                        hari</span>
                                </div>
                            </div>
                            <div class="mb-4">
                                <span class="block text-xs text-slate-400">Keterangan</span>
                                <p class="mt-1 text-xs leading-5 text-slate-600">{{ $item->desc }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <button onclick="viewDetail({{ $item->id }})"
                                    class="inline-flex items-center justify-center gap-2 px-3 py-2 text-xs font-semibold rounded-lg min-h-10 bg-sky-50 text-sky-700 hover:bg-sky-100">
                                    <i class="ri-eye-line"></i>Lihat
                                </button>
                                @if (($item->status ?? 'pending') === 'pending' && !($isSubmissionLocked ?? false))
                                    <button onclick="sendFinishedTraining({{ $item->id }})"
                                        class="inline-flex items-center justify-center gap-2 px-3 py-2 text-xs font-semibold text-green-700 rounded-lg min-h-10 bg-green-50 hover:bg-green-100">
                                        <i class="ri-send-plane-fill"></i>Ajukan
                                    </button>
                                    <button onclick="editData({{ $item->id }})"
                                        class="inline-flex items-center justify-center gap-2 px-3 py-2 text-xs font-semibold text-yellow-700 rounded-lg min-h-10 bg-yellow-50 hover:bg-yellow-100">
                                        <i class="ri-edit-line"></i>Edit
                                    </button>
                                    <button onclick="deleteFinishedTraining({{ $item->id }})"
                                        class="inline-flex items-center justify-center gap-2 px-3 py-2 text-xs font-semibold text-red-700 rounded-lg min-h-10 bg-red-50 hover:bg-red-100">
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
                            <p class="mt-3 font-medium text-slate-600">Belum ada riwayat pengajuan lepas training</p>
                            <a href="{{ route('spvw.finished-training.index', array_filter(['client_id' => $spvwClientId])) }}"
                                class="inline-flex items-center gap-2 px-6 py-2 mt-3 text-sm font-semibold rounded-lg min-h-10 bg-amber-400 text-slate-900"><i
                                    class="ri-add-line"></i><span>Buat Pengajuan</span></a>
                        </div>
                    @endforelse
                </div>

                <div class="hidden overflow-x-auto sm:block">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50">
                                <th class="px-4 py-3 text-xs font-semibold text-left text-slate-500">No</th>
                                <th class="px-4 py-3 text-xs font-semibold text-left text-slate-500">Nama
                                    Lengkap</th>
                                <th class="px-4 py-3 text-xs font-semibold text-left text-slate-500">Nama Penginput</th>
                                <th class="px-4 py-3 text-xs font-semibold text-left text-slate-500">Tanggal
                                    Masuk</th>
                                <th class="px-4 py-3 text-xs font-semibold text-left text-slate-500">Tanggal Lepas
                                    Training</th>
                                <th class="px-4 py-3 text-xs font-semibold text-left text-slate-500">Jumlah
                                    Masa Training</th>
                                <th class="px-4 py-3 text-xs font-semibold text-left text-slate-500">Keterangan
                                </th>
                                <th class="px-4 py-3 text-xs font-semibold text-left text-slate-500">Status
                                </th>
                                <th class="px-4 py-3 text-xs font-semibold text-center text-slate-500">Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($finishedTrainings as $index => $item)
                                <tr class="transition-colors hover:bg-slate-50">
                                    <td class="px-4 py-4 text-sm text-slate-700">
                                        {{ $finishedTrainings->firstItem() + $index }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-slate-700 whitespace-nowrap">
                                        {{ $item->user->nama_lengkap ?? '-' }}</td>
                                    <td class="px-4 py-4 text-sm text-slate-700 whitespace-nowrap">
                                        {{ $item->createdBy->nama_lengkap ?? '-' }}</td>
                                    <td class="px-4 py-4 text-sm text-slate-700 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($item->date_in)->format('d M Y') }}</td>
                                    <td class="px-4 py-4 text-sm text-slate-700 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($item->date_finish_train)->format('d M Y') }}</td>
                                    <td class="px-4 py-4 text-sm text-slate-700 whitespace-nowrap">
                                        {{ $item->masa_training_hari }} hari</td>
                                    <td class="px-4 py-4 text-sm text-slate-700">{{ $item->desc }}</td>
                                    <td class="px-4 py-4">
                                        @if (($item->status ?? 'pending') == 'Di Ajukan')
                                            <span
                                                class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full"><i
                                                    class="ri-checkbox-circle-fill"></i>Di Ajukan</span>
                                        @elseif(($item->status ?? 'pending') == 'Di Setujui')
                                    <span
                                        class="inline-flex shrink-0 items-center gap-1 rounded-full bg-lime-100 px-2.5 py-1 text-xs font-medium text-lime-700"><i
                                            class="ri-checkbox-circle-fill"></i>Di Setujui</span>
                                @elseif(($item->status ?? 'pending') == 'Di Tolak')
                                            <span
                                                class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium rounded-full bg-lime-100 text-lime-700"><i
                                                    class="ri-checkbox-circle-fill"></i>Di Setujui</span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-yellow-700 bg-yellow-100 rounded-full"><i
                                                    class="ri-time-fill"></i>Pending</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <button onclick="viewDetail({{ $item->id }})"
                                                class="flex items-center justify-center text-blue-600 bg-blue-100 rounded-lg w-9 h-9 hover:bg-blue-200"
                                                title="Lihat"><i class="ri-eye-line"></i></button>
                                            @if (($item->status ?? 'pending') === 'pending' && !($isSubmissionLocked ?? false))
                                                <button onclick="editData({{ $item->id }})"
                                                    class="flex items-center justify-center text-yellow-600 bg-yellow-100 rounded-lg w-9 h-9 hover:bg-yellow-200"
                                                    title="Edit"><i class="ri-edit-line"></i></button>
                                                <button onclick="deleteFinishedTraining({{ $item->id }})"
                                                    class="flex items-center justify-center text-red-600 bg-red-100 rounded-lg w-9 h-9 hover:bg-red-200"
                                                    title="Hapus"><i class="ri-delete-bin-line"></i></button>
                                                <button onclick="sendFinishedTraining({{ $item->id }})"
                                                    class="flex items-center justify-center text-green-600 bg-green-100 rounded-lg w-9 h-9 hover:bg-green-200"
                                                    title="Ajukan"><i class="ri-send-plane-fill"></i></button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-12 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="flex items-center justify-center w-16 h-16 rounded-full bg-slate-100">
                                                <i class="text-3xl ri-inbox-line text-slate-400"></i>
                                            </div>
                                            <p class="font-medium text-slate-600">Belum ada riwayat pengajuan lepas training</p>
                                        </div>
                                        <a href="{{ route('spvw.finished-training.index', array_filter(['client_id' => $spvwClientId])) }}"
                                            class="inline-flex items-center gap-2 px-6 py-2 mt-3 text-sm font-semibold rounded-lg min-h-10 bg-amber-400 text-slate-900"><i
                                                class="ri-add-line"></i><span>Buat Pengajuan</span></a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($finishedTrainings->total() > 0)
                    <div class="px-4 py-4 border-t border-slate-200">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <form action="{{ route('spvw.finished-training.history', array_filter(['client_id' => $spvwClientId])) }}" method="GET"
                                class="inline-flex items-center gap-2">
                                <input type="hidden" name="status" value="{{ request('status') }}">
                                <input type="hidden" name="month" value="{{ request('month') }}">
                                <input type="hidden" name="search" value="{{ request('search') }}">
                                <label for="per_page" class="text-xs text-slate-500 whitespace-nowrap">Baris per
                                    halaman</label>
                                <select id="per_page" name="per_page" onchange="this.form.submit()"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs">
                                    @foreach ($allowedPerPage ?? [10, 15, 25, 50] as $size)
                                        <option value="{{ $size }}"
                                            {{ (int) request('per_page', $perPage ?? 15) === $size ? 'selected' : '' }}>
                                            {{ $size }}
                                        </option>
                                    @endforeach
                                </select>
                            @if(isset($spvwClientId) && $spvwClientId)
                    <input type="hidden" name="client_id" value="{{ $spvwClientId }}">
                @endif
            </form>
                            <div class="text-xs text-slate-500">
                                Menampilkan {{ $finishedTrainings->firstItem() }}-{{ $finishedTrainings->lastItem() }}
                                dari
                                {{ $finishedTrainings->total() }} data
                            </div>
                        </div>
                        @if ($finishedTrainings->hasPages())
                            <div class="mt-3">
                                {{ $finishedTrainings->links() }}
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex justify-end w-full my-2">
                <form id="bulkFinishedTrainingForm" action="{{ route('spvw.finished-training-bulk.status') }}"
                    method="post" class="w-full mt-3 sm:w-auto">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <input type="hidden" name="month" value="{{ request('month') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="per_page" value="{{ request('per_page', $perPage ?? 15) }}">
                    <button type="button" @disabled(!($canBulkSubmit ?? false)) onclick="openBulkFinishedTrainingModal()"
                        class="inline-flex items-center justify-center w-full gap-2 px-4 py-2 text-sm font-semibold rounded-lg min-h-10 sm:w-auto {{ $canBulkSubmit ?? false ? 'bg-green-100 text-green-700 hover:bg-green-200 transition' : 'cursor-not-allowed bg-slate-100 text-slate-400' }}"><i
                            class="ri-send-plane-fill"></i>Ajukan Semua</button>
                @if(isset($spvwClientId) && $spvwClientId)
                    <input type="hidden" name="client_id" value="{{ $spvwClientId }}">
                @endif
            </form>
            </div>
        </div>
    </div>

    <input type="checkbox" id="modal-edit" class="modal-toggle" />
    <div class="modal">
        <div class="modal-box max-h-[90vh] w-11/12 max-w-2xl overflow-y-auto rounded-lg p-4 sm:p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-slate-800 sm:text-xl">Edit Lepas Training</h3>
                <label for="modal-edit" class="absolute btn btn-sm btn-circle btn-ghost right-2 top-2"><i
                        class="text-xl ri-close-line"></i></label>
            </div>
            <form id="finishedTrainingEditForm">
                <input type="hidden" id="finished_training_id" />
                <div class="mb-4"><label for="user_id"
                        class="block mb-2 text-sm font-semibold text-slate-700">Fullname <span
                            class="text-red-500">*</span></label><select name="user_id" id="user_id"
                        class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm"
                        required>
                        <option value="">-- Pilih Fullname --</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ capitalizeWords($user->nama_lengkap) ?? 'N/A' }}</option>
                        @endforeach
                    </select></div>
                <div class="mb-4"><label for="edit_date_in"
                        class="block mb-2 text-sm font-semibold text-slate-700">Tanggal Masuk <span
                            class="text-red-500">*</span></label><input type="date" name="date_in"
                        id="edit_date_in"
                        class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm"
                        required></div>
                <div class="mb-4"><label for="edit_date_finish_train"
                        class="block mb-2 text-sm font-semibold text-slate-700">Tanggal Lepas Training <span
                            class="text-red-500">*</span></label><input type="date" name="date_finish_train"
                        id="edit_date_finish_train"
                        class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm"
                        required></div>
                <div class="mb-4">
                    <label for="edit_desc" class="block mb-2 text-sm font-semibold text-slate-700">Keterangan
                        <span class="text-red-500">*</span></label>
                    <textarea name="desc" id="edit_desc" rows="4"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" required></textarea>
                </div>
                <div id="formErrors" class="mb-4"></div>
            @if(isset($spvwClientId) && $spvwClientId)
                    <input type="hidden" name="client_id" value="{{ $spvwClientId }}">
                @endif
            </form>
            <div class="grid grid-cols-1 gap-2 modal-action sm:flex"><label for="modal-edit"
                    class="inline-flex items-center justify-center px-4 text-sm font-semibold border rounded-lg min-h-10 border-slate-300 text-slate-700">Batal</label><button
                    id="btnSave"
                    class="inline-flex items-center justify-center gap-2 px-4 text-sm font-semibold rounded-lg min-h-10 bg-amber-400 text-slate-900"><i
                        class="ri-save-line"></i>Update Data</button>
            </div>
        </div>
    </div>

    <div x-data="{ open: false, detail: {} }" x-show="open" @detail-modal.window="open = true; detail = $event.detail"
        @keydown.escape.window="open = false" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display:none;">
        <div class="fixed inset-0 transition-opacity bg-black/50" @click="open = false"></div>
        <div class="flex items-center justify-center min-h-full p-3 sm:p-4">
            <div class="relative max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-4 shadow-xl sm:p-6"
                @click.away="open = false" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-bold text-slate-800 sm:text-xl">Detail Pengajuan Lepas Training</h3><button
                        @click="open = false"
                        class="flex items-center justify-center w-8 h-8 rounded-lg hover:bg-slate-100"><i
                            class="text-xl ri-close-line text-slate-600"></i></button>
                </div>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4 pb-4 border-b sm:grid-cols-3 border-slate-200">
                        <div>
                            <p class="mb-1 text-xs text-slate-500">Fullname</p>
                            <p class="font-semibold text-slate-800" x-text="detail.user?.nama_lengkap || '-' "></p>
                        </div>
                        <div>
                            <p class="mb-1 text-xs text-slate-500">Nama Penginput</p>
                            <p class="font-semibold text-slate-800" x-text="detail.created_by?.nama_lengkap || '-' "></p>
                        </div>
                        <div>
                            <p class="mb-1 text-xs text-slate-500">Tanggal Masuk</p>
                            <p class="font-semibold text-slate-800"
                                x-text="detail.date_in ? new Date(detail.date_in).toLocaleDateString('id-ID') : '-' ">
                            </p>
                        </div>
                        <div>
                            <p class="mb-1 text-xs text-slate-500">Tanggal Lepas Training</p>
                            <p class="font-semibold text-slate-800"
                                x-text="detail.date_finish_train ? new Date(detail.date_finish_train).toLocaleDateString('id-ID') : '-' ">
                            </p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <p class="mb-1 text-xs text-slate-500">Jumlah Masa Training</p>
                            <p class="font-semibold text-slate-800"
                                x-text="(detail.masa_training_hari || 0) + ' hari'"></p>
                        </div>
                        <div>
                            <p class="mb-1 text-xs text-slate-500">Status</p><span x-text="detail.status ?? 'pending'"
                                :class="{
                                    'bg-green-100 text-green-700': detail.status == 'Di Ajukan',
                                    'bg-yellow-100 text-yellow-700': !detail.status || detail.status == 'pending',
                                    'bg-lime-100 text-lime-700': detail.status == 'Di Setujui',
                                    'bg-red-100 text-red-700': detail.status == 'Di Tolak'
                                }"
                                class="inline-block px-3 py-1 text-xs font-medium rounded-full"></span>
                        </div>
                    </div>
                    <div>
                        <p class="mb-1 text-xs text-slate-500">Keterangan</p>
                        <p class="text-slate-800" x-text="detail.desc || '-' "></p>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-4 mt-6 border-t border-slate-200">
                    <button class="inline-flex items-center justify-center w-full px-4 py-2 text-sm font-semibold transition bg-white border rounded-lg min-h-10 border-slate-300 text-slate-700 hover:bg-slate-50 sm:w-auto"
                        @click="open = false" type="button">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showAlert(type, message) {
            window.showAppToast(type, message);
        }

        function resetForm() {
            $('#finishedTrainingEditForm')[0].reset();
            $('#finished_training_id').val('');
            $('#formErrors').html('');
        }

        async function viewDetail(id) {
            const url = "{{ route('spvw.finished-training-id', ':id') }}".replace(':id', id);
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

        function deleteFinishedTraining(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus pengajuan ini?')) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/SPVW/finished-training/${id}`;
            form.innerHTML =
                `<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE">`;
            document.body.appendChild(form);
            form.submit();
        }

        function sendFinishedTraining(id) {
            window.openConfirmModal({
                title: 'Ajukan Pengajuan',
                message: 'Apakah Anda yakin ingin mengajukan pengajuan ini?',
                confirmText: 'Ya, Ajukan',
                cancelText: 'Batal',
                type: 'warning',
                onConfirm: function() {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/SPVW/finished-training-change-status/${id}`;
                    form.innerHTML =
                        `<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="PATCH">`;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function openBulkFinishedTrainingModal() {
            if (!{{ $canBulkSubmit ?? false ? 'true' : 'false' }}) {
                return;
            }

            window.openConfirmModal({
                title: 'Ajukan Semua Pengajuan',
                message: 'Apakah Anda yakin ingin mengajukan semua pengajuan yang belum diajukan?',
                confirmText: 'Ya, Ajukan Semua',
                cancelText: 'Batal',
                type: 'warning',
                onConfirm: function() {
                    const form = document.getElementById('bulkFinishedTrainingForm');
                    if (form) form.submit();
                }
            });
        }

        function editData(id) {
            $.get("{{ route('spvw.finished-training.show', ':id') }}".replace(':id', id))
                .done(response => {
                    const data = response.data;
                    resetForm();
                    $('#finished_training_id').val(data.id);
                    $('#user_id').val(data.user_id || '');
                    $('#edit_date_in').val(data.date_in || '');
                    $('#edit_date_finish_train').val(data.date_finish_train || '');
                    $('#edit_desc').val(data.desc || '');
                    $('#modal-edit').prop('checked', true);
                })
                .fail(() => showAlert('error', 'Gagal mengambil data'));
        }

        function saveData(e) {
            e.preventDefault();
            $('#formErrors').html('');
            const id = $('#finished_training_id').val();
            const payload = {
                user_id: $('#user_id').val(),
                date_in: $('#edit_date_in').val(),
                date_finish_train: $('#edit_date_finish_train').val(),
                desc: $('#edit_desc').val()
            };
            $.ajax({
                    url: "{{ route('spvw.finished-training.update', ':id') }}".replace(':id', id),
                    method: 'PUT',
                    data: payload
                })
                .done(() => {
                    $('#modal-edit').prop('checked', false);
                    showAlert('success', 'Data berhasil diperbarui');
                    setTimeout(() => window.location.reload(), 400);
                })
                .fail(xhr => {
                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        $('#formErrors').html(
                            `<div class="px-4 py-3 text-sm border rounded-lg border-rose-200 bg-rose-50 text-rose-700"><div class="flex gap-2"><i class="ri-error-warning-line mt-0.5"></i><ul class="pl-4 list-disc">${errors.map(err => `<li>${err}</li>`).join('')}</ul></div></div>`
                        );
                    } else showAlert('error', xhr.responseJSON?.message ||
                        'Terjadi kesalahan saat memperbarui data lepas training. Silakan coba lagi.');
                });
        }

        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('#btnSave').on('click', saveData);
        });
    </script>

    
    
<style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</x-app-layout>
