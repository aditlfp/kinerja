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
                                Riwayat Personil Masuk
                            </h1>
                            <p class="mt-1 text-sm leading-5 text-slate-500">Pantau status dan kelola pengajuan personil
                                masuk.</p>
                        </div>
                    </div>
                    <a href="{{ route('person-in.index') }}"
                        class="inline-flex items-center justify-center w-full gap-2 px-4 py-2 text-sm font-semibold transition rounded-lg min-h-10 bg-amber-400 text-slate-900 hover:bg-amber-500 focus:outline-hidden focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 sm:w-auto">
                        <i class="ri-add-line"></i><span>Pengajuan Baru</span>
                    </a>
                </div>
            </div>

            <div class="p-3 mb-4 bg-white border rounded-lg shadow-xs border-slate-200 sm:p-4">
                <form action="{{ route('person.in.history') }}" method="GET"
                    class="grid grid-cols-1 sm:grid-cols-4 gap-3">
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
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari nama personil..."
                        class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-hidden transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                    <div class="flex gap-2">
                        <button type="submit"
                            class="inline-flex items-center justify-center flex-1 gap-2 px-4 py-2 text-sm font-semibold text-white transition rounded-lg min-h-11 bg-sky-600 hover:bg-sky-700"><i
                                class="ri-filter-3-line"></i><span>Filter</span></button>
                        <a href="{{ route('person.in.history') }}"
                            class="inline-flex items-center justify-center flex-1 gap-2 px-4 py-2 text-sm font-semibold transition bg-white border rounded-lg min-h-11 border-slate-300 text-slate-700 hover:bg-slate-50"><i
                                class="ri-refresh-line"></i><span>Reset</span></a>
                    </div>
                </form>
            </div>

            <div id="alertBox" class="mb-4"></div>

            <div class="overflow-hidden bg-white border rounded-lg shadow-xs border-slate-200">
                <div class="space-y-2 p-2 sm:hidden">
                    @forelse($personIn as $index => $person)
                        <div class="p-3 bg-white border rounded-lg shadow-xs border-slate-200">
                            <div class="mb-2 flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-800">{{ $person->fullname }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ $person->jabatan->name_jabatan ?? '-' }}</p>
                                </div>
                                @if (($person->status ?? 'pending') == 'Di Ajukan')
                                    <span
                                        class="inline-flex shrink-0 items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-[11px] font-medium text-green-700"><i
                                            class="ri-checkbox-circle-fill"></i>Diajukan</span>
                                @elseif(($person->status ?? 'pending') == 'Di Setujui')
                                    <span
                                        class="inline-flex shrink-0 items-center gap-1 rounded-full bg-lime-100 px-2 py-0.5 text-[11px] font-medium text-lime-700"><i
                                            class="ri-checkbox-circle-fill"></i>Di Setujui</span>
                                @elseif(($person->status ?? 'pending') == 'Di Tolak')
                                    <span
                                        class="inline-flex shrink-0 items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-medium text-red-700"><i
                                            class="ri-close-circle-fill"></i>Ditolak</span>
                                @else
                                    <span
                                        class="inline-flex shrink-0 items-center gap-1 rounded-full bg-yellow-100 px-2 py-0.5 text-[11px] font-medium text-yellow-700"><i
                                            class="ri-time-fill"></i>Pending</span>
                                @endif
                            </div>
                            <div class="mb-3 grid grid-cols-2 gap-1.5 text-xs text-slate-600">
                                <div class="rounded-lg bg-slate-50 p-1.5">
                                    <span class="block text-slate-400">Tanggal Masuk</span>
                                    <span
                                        class="font-medium text-slate-700">{{ \Carbon\Carbon::parse($person->date_in)->format('d M Y') }}</span>
                                </div>
                                <div class="rounded-lg bg-slate-50 p-1.5">
                                    <span class="block text-slate-400">Metode Gaji</span>
                                    <span
                                        class="font-medium text-slate-700">{{ ($person->method_salary ?? '') === 'transfer' ? 'Transfer' : 'Manual / Cash' }}</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-1.5">
                                <button onclick="viewDetail({{ $person->id }})"
                                    class="inline-flex min-h-8 items-center justify-center gap-1.5 rounded-lg bg-sky-50 px-2 py-1.5 text-xs font-semibold text-sky-700 hover:bg-sky-100">
                                    <i class="ri-eye-line"></i>Lihat
                                </button>
                                @if (($person->status ?? 'pending') === 'pending' && !($isSubmissionLocked ?? false))
                                    <button onclick="sendPersonIn({{ $person->id }})"
                                        class="inline-flex min-h-8 items-center justify-center gap-1.5 rounded-lg bg-green-50 px-2 py-1.5 text-xs font-semibold text-green-700 hover:bg-green-100">
                                        <i class="ri-send-plane-fill"></i>Ajukan
                                    </button>
                                    <button onclick="editData({{ $person->id }})"
                                        class="inline-flex min-h-8 items-center justify-center gap-1.5 rounded-lg bg-yellow-50 px-2 py-1.5 text-xs font-semibold text-yellow-700 hover:bg-yellow-100">
                                        <i class="ri-edit-line"></i>Edit
                                    </button>
                                    <button onclick="deletePersonIn({{ $person->id }})"
                                        class="inline-flex min-h-8 items-center justify-center gap-1.5 rounded-lg bg-red-50 px-2 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">
                                        <i class="ri-delete-bin-line"></i>Hapus
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-10 text-center">
                            <p class="font-medium text-slate-600">Belum ada riwayat pengajuan personil masuk</p>
                            <a href="{{ route('person-in.index') }}"
                                class="inline-flex items-center gap-2 px-6 py-2 mt-3 text-sm font-semibold rounded-lg min-h-10 bg-amber-400 text-slate-900"><i
                                    class="ri-add-line"></i><span>Buat Pengajuan</span></a>
                        </div>
                    @endforelse
                </div>

                <div class="hidden overflow-x-auto sm:block">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50">
                                <th class="px-3 py-2 text-xs font-semibold text-left text-slate-500">No</th>
                                <th class="px-3 py-2 text-xs font-semibold text-left text-slate-500">Nama</th>
                                <th class="px-3 py-2 text-xs font-semibold text-left text-slate-500">Jabatan
                                </th>
                                <th class="px-3 py-2 text-xs font-semibold text-left text-slate-500">Tanggal
                                    Masuk</th>
                                <th class="px-3 py-2 text-xs font-semibold text-left text-slate-500">Metode
                                    Gaji</th>
                                <th class="px-3 py-2 text-xs font-semibold text-left text-slate-500">Status
                                </th>
                                <th class="px-3 py-2 text-xs font-semibold text-center text-slate-500">Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($personIn as $index => $person)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-3 py-2 text-sm text-slate-700">{{ $personIn->firstItem() + $index }}
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                <span
                                                    class="text-blue-600 font-semibold text-sm">{{ strtoupper(substr($person->fullname ?? 'N', 0, 1)) }}</span>
                                            </div>
                                            <div>
                                                <p class="font-medium text-slate-800 whitespace-nowrap">
                                                    {{ $person->fullname }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 text-sm text-slate-700 whitespace-nowrap">
                                        {{ $person->jabatan->name_jabatan ?? '-' }}</td>
                                    <td class="px-3 py-2 text-sm text-slate-700 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($person->date_in)->format('d M Y') }}</td>
                                    <td class="px-3 py-2 text-sm text-slate-700 whitespace-nowrap">
                                        {{ ($person->method_salary ?? '') === 'transfer' ? 'Transfer' : 'Manual / Cash' }}
                                    </td>
                                    <td class="px-3 py-2">
                                        @if (($person->status ?? 'pending') == 'Di Ajukan')
                                            <span
                                                class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-medium"><i
                                                    class="ri-checkbox-circle-fill"></i>Di Ajukan</span>
                                        @elseif(($person->status ?? 'pending') == 'Di Setujui')
                                    <span
                                        class="inline-flex shrink-0 items-center gap-1 rounded-full bg-lime-100 px-2 py-0.5 text-[11px] font-medium text-lime-700"><i
                                            class="ri-checkbox-circle-fill"></i>Di Setujui</span>
                                @elseif(($person->status ?? 'pending') == 'Di Tolak')
                                            <span
                                                class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-lime-100 text-lime-700 text-xs font-medium"><i
                                                    class="ri-checkbox-circle-fill"></i>Di Setujui</span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-xs font-medium"><i
                                                    class="ri-time-fill"></i>Pending</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="flex items-center justify-center gap-2">
                                            <button onclick="viewDetail({{ $person->id }})"
                                                class="h-8 w-8 rounded-lg bg-blue-100 hover:bg-blue-200 text-blue-600 flex items-center justify-center"
                                                title="Lihat"><i class="ri-eye-line"></i></button>
                                            @if (($person->status ?? 'pending') === 'pending' && !($isSubmissionLocked ?? false))
                                                <button onclick="editData({{ $person->id }})"
                                                    class="h-8 w-8 rounded-lg bg-yellow-100 hover:bg-yellow-200 text-yellow-600 flex items-center justify-center"
                                                    title="Edit"><i class="ri-edit-line"></i></button>
                                                <button onclick="deletePersonIn({{ $person->id }})"
                                                    class="h-8 w-8 rounded-lg bg-red-100 hover:bg-red-200 text-red-600 flex items-center justify-center"
                                                    title="Hapus"><i class="ri-delete-bin-line"></i></button>
                                                <button onclick="sendPersonIn({{ $person->id }})"
                                                    class="h-8 w-8 rounded-lg bg-green-100 hover:bg-green-200 text-green-600 flex items-center justify-center"
                                                    title="Ajukan"><i class="ri-send-plane-fill"></i></button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-12 text-center">
                                        <p class="text-slate-600 font-medium">Belum ada riwayat pengajuan personil
                                            masuk</p>
                                        <a href="{{ route('person-in.index') }}"
                                            class="inline-flex items-center gap-2 px-6 py-2 mt-3 text-sm font-semibold rounded-lg min-h-10 bg-amber-400 text-slate-900"><i
                                                class="ri-add-line"></i><span>Buat Pengajuan</span></a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($personIn->total() > 0)
                    <div class="border-t border-slate-200 px-4 py-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <form action="{{ route('person.in.history') }}" method="GET"
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
                            </form>
                            <div class="text-xs text-slate-500">
                                Menampilkan {{ $personIn->firstItem() }}-{{ $personIn->lastItem() }} dari
                                {{ $personIn->total() }} data
                            </div>
                        </div>
                        @if ($personIn->hasPages())
                            <div class="mt-3">
                                {{ $personIn->links() }}
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex justify-end w-full my-2">
                <form id="bulkPersonInForm" action="{{ route('person-in-bulk.status') }}" method="post"
                    style="display:none;">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <input type="hidden" name="month" value="{{ request('month') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="per_page" value="{{ request('per_page', $perPage ?? 15) }}">
                </form>
                <button type="button" @disabled(!($canBulkSubmit ?? false)) onclick="openBulkPersonInModal()"
                    class="inline-flex items-center justify-center w-full gap-2 px-4 py-2 mt-3 text-sm font-semibold rounded-lg min-h-10 sm:mt-0 sm:w-auto {{ $canBulkSubmit ?? false ? 'bg-green-100 text-green-700 hover:bg-green-200 transition' : 'cursor-not-allowed bg-slate-100 text-slate-400' }}"><i
                        class="ri-send-plane-fill"></i>Ajukan Semua</button>
            </div>
        </div>
    </div>
    <input type="checkbox" id="modal-edit" class="modal-toggle" />
    <div class="modal">
        <div class="modal-box max-h-[90vh] w-11/12 max-w-2xl overflow-y-auto rounded-lg p-4 sm:p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-slate-800 sm:text-xl">Edit Personil Masuk</h3>
                <label for="modal-edit" class="absolute btn btn-sm btn-circle btn-ghost right-2 top-2"><i
                        class="text-xl ri-close-line"></i></label>
            </div>
            <form id="personInForm" x-data="personInFormData()">
                <input type="hidden" id="person_in_id" />
                <div class="mb-4"><label for="fullname"
                        class="block mb-2 text-sm font-semibold text-slate-700">Nama Lengkap <span
                            class="text-red-500">*</span></label><input type="text" name="fullname"
                        id="fullname"
                        class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm"
                        required></div>
                <div class="mb-4"><label for="jabatan_id"
                        class="block mb-2 text-sm font-semibold text-slate-700">Jabatan <span
                            class="text-red-500">*</span></label><select name="jabatan_id" id="jabatan_id"
                        class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm"
                        required>
                        <option value="">-- Pilih Jabatan --</option>
                        @foreach ($jabatans as $jabatan)
                            <option value="{{ $jabatan->id }}">{{ $jabatan->name_jabatan }}</option>
                        @endforeach
                    </select></div>
                <div class="mb-4"><label for="date_in"
                        class="block mb-2 text-sm font-semibold text-slate-700">Tanggal Masuk <span
                            class="text-red-500">*</span></label><input type="date" name="date_in" id="date_in"
                        class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm"
                        required></div>
                <div class="mb-4">
                    <label class="block mb-3 text-sm font-semibold text-slate-700">Metode Gaji <span
                            class="text-red-500">*</span></label>
                    <div class="space-y-2.5">
                        <label
                            class="flex items-start gap-3 p-3 transition border rounded-lg cursor-pointer border-slate-200 hover:bg-slate-50"
                            :class="selectedMethod === 'transfer' ? 'border-blue-500 bg-blue-50' : ''"><input
                                type="radio" name="method_salary" value="transfer" x-model="selectedMethod"
                                class="w-4 h-4 mt-1" required>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-slate-800">Transfer</p>
                            </div>
                        </label>
                        <label
                            class="flex items-start gap-3 p-3 transition border rounded-lg cursor-pointer border-slate-200 hover:bg-slate-50"
                            :class="selectedMethod === 'cash' ? 'border-blue-500 bg-blue-50' : ''"><input
                                type="radio" name="method_salary" value="cash" x-model="selectedMethod"
                                class="w-4 h-4 mt-1" required>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-slate-800">Manual / Cash</p>
                            </div>
                        </label>
                        <div x-show="selectedMethod === 'transfer'" x-transition><input type="text"
                                name="method_salary_manual" id="method_salary_manual" x-model="manualMethod"
                                placeholder="Masukkan nomor rekening..."
                                class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm"
                                :required="selectedMethod === 'transfer'" :disabled="selectedMethod !== 'transfer'">
                        </div>
                    </div>
                </div>
                <div id="formErrors" class="mb-4"></div>
            </form>
            <div class="grid grid-cols-1 gap-2 modal-action sm:flex"><label for="modal-edit"
                    class="inline-flex items-center justify-center px-4 text-sm font-semibold border rounded-lg min-h-10 border-slate-300 text-slate-700">Batal</label><button
                    id="btnSave"
                    class="inline-flex items-center justify-center gap-2 px-4 text-sm font-semibold rounded-lg min-h-10 bg-amber-400 text-slate-900"><i
                        class="ri-save-line"></i>Update Data</button>
            </div>
        </div>
    </div>

    <div x-data="{ open: false, detail: {} }" x-show="open" @detail-modal.window="open = true; detail = $event.detail" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto" style="display:none;">
        <div class="fixed inset-0 bg-black/50"></div>
        <div class="flex items-center justify-center min-h-full p-3 sm:p-4">
            <div class="relative max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-4 shadow-xl sm:p-6"
                @click.away="open = false">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-bold text-slate-800 sm:text-xl">Detail Pengajuan Personil Masuk</h3><button
                        class="flex items-center justify-center w-8 h-8 rounded-lg hover:bg-slate-100"><i
                            class="text-xl ri-close-line text-slate-600"></i></button>
                </div>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4 pb-4 border-b sm:grid-cols-3 border-slate-200">
                        <div>
                            <p class="mb-1 text-xs text-slate-500">Nama Pegawai</p>
                            <p class="font-semibold text-slate-800" x-text="detail.fullname || '-' "></p>
                        </div>
                        <div>
                            <p class="mb-1 text-xs text-slate-500">Jabatan</p>
                            <p class="font-semibold text-slate-800" x-text="detail.jabatan?.name_jabatan || '-' "></p>
                        </div>
                        <div>
                            <p class="mb-1 text-xs text-slate-500">Tanggal Masuk</p>
                            <p class="font-semibold text-slate-800"
                                x-text="detail.date_in ? new Date(detail.date_in).toLocaleDateString('id-ID') : '-' ">
                            </p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <p class="mb-1 text-xs text-slate-500">Metode Gaji</p>
                            <p class="font-semibold text-slate-800"
                                x-text="detail.method_salary === 'transfer' ? 'Transfer' : 'Manual / Cash'"></p>
                        </div>
                        <div>
                            <p class="mb-1 text-xs text-slate-500">Nomor Rekening</p>
                            <p class="font-semibold text-slate-800" x-text="detail.method_salary_manual || '-' "></p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <p class="mb-1 text-xs text-slate-500">Status</p><span x-text="detail.status ?? 'pending'"
                                :class="{
                                    'bg-green-100 text-green-700': detail.status ==
                                        'Di Ajukan',
                                    'bg-yellow-100 text-yellow-700': !detail.status || detail.status ==
                                        'pending',
                                    'bg-lime-100 text-lime-700': detail.status == 'Di Setujui',
                                    'bg-red-100 text-red-700': detail.status == 'Di Tolak'
                                }"
                                class="inline-block px-3 py-1 text-xs font-medium rounded-full"></span>
                        </div>
                        <div>
                            <p class="mb-1 text-xs text-slate-500">Tanggal Pengisian</p>
                            <p class="text-slate-800"
                                x-text="detail.created_at?.replace('T', ' ').split('.')[0] || '-'"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function personInFormData() {
            return {
                selectedMethod: '',
                manualMethod: '',
                init() {
                    this.$watch('selectedMethod', value => {
                        if (value !== 'transfer') {
                            this.manualMethod = '';
                            $('#method_salary_manual').val('');
                        }
                    });
                }
            }
        }

        function showAlert(type, message) {
            $('#alertBox').html('');
            if (typeof window.showAppToast === 'function') {
                window.showAppToast(type, message);
                return;
            }
        }

        function resetForm() {
            $('#personInForm')[0].reset();
            $('#person_in_id').val('');
            $('#formErrors').html('');
            const formElement = document.getElementById('personInForm');
            if (formElement && formElement._x_dataStack) {
                const alpineData = formElement._x_dataStack[0];
                if (alpineData) {
                    alpineData.selectedMethod = '';
                    alpineData.manualMethod = '';
                }
            }
        }

        async function viewDetail(id) {
            const url = "{{ route('person-in-id', ':id') }}".replace(':id', id);
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

        let pendingDeletePersonInId = null;
        let pendingSubmitPersonInId = null;

        function deletePersonIn(id) {
            pendingDeletePersonInId = id;
            window.openConfirmModal({
                title: 'Hapus Pengajuan',
                message: 'Apakah Anda yakin ingin menghapus pengajuan ini? Data yang dihapus tidak dapat dikembalikan.',
                confirmText: 'Ya, Hapus',
                cancelText: 'Batal',
                type: 'danger',
                onConfirm: deletePersonInConfirmed,
            });
        }

        function deletePersonInConfirmed() {
            if (!pendingDeletePersonInId) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/person-in/${pendingDeletePersonInId}`;
            form.innerHTML =
                `<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE">`;
            document.body.appendChild(form);
            form.submit();
        }

        function sendPersonIn(id) {
            pendingSubmitPersonInId = id;
            window.openConfirmModal({
                title: 'Ajukan Pengajuan',
                message: 'Apakah Anda yakin ingin mengajukan pengajuan ini?',
                confirmText: 'Ya, Ajukan',
                cancelText: 'Batal',
                type: 'warning',
                onConfirm: submitPersonInConfirmed,
            });
        }

        function openBulkPersonInModal() {
            if (!{{ $canBulkSubmit ?? false ? 'true' : 'false' }}) {
                return;
            }

            window.openConfirmModal({
                title: 'Ajukan Semua Pengajuan',
                message: 'Apakah Anda yakin ingin mengajukan semua pengajuan yang belum diajukan?',
                confirmText: 'Ya, Ajukan Semua',
                cancelText: 'Batal',
                type: 'warning',
                onConfirm: submitBulkPersonInConfirmed,
            });
        }

        function submitPersonInConfirmed() {
            if (!pendingSubmitPersonInId) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/person-in-change-status/${pendingSubmitPersonInId}`;
            form.innerHTML =
                `<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="PATCH">`;
            document.body.appendChild(form);
            form.submit();
        }

        function submitBulkPersonInConfirmed() {
            const form = document.getElementById('bulkPersonInForm');
            if (form) form.submit();
        }

        function editData(id) {
            $.get("{{ route('person-in.show', ':id') }}".replace(':id', id))
                .done(response => {
                    const data = response.data;
                    resetForm();
                    $('#person_in_id').val(data.id);
                    $('#fullname').val(data.fullname || '');
                    $('#jabatan_id').val(data.jabatan_id || '');
                    $('#date_in').val(data.date_in || '');
                    $('#method_salary_manual').val(data.method_salary_manual || '');
                    $(`input[name="method_salary"][value="${data.method_salary}"]`).prop('checked', true);
                    setTimeout(() => {
                        const formElement = document.getElementById('personInForm');
                        if (formElement && formElement._x_dataStack) {
                            const alpineData = formElement._x_dataStack[0];
                            if (alpineData) {
                                alpineData.selectedMethod = data.method_salary || '';
                                alpineData.manualMethod = data.method_salary_manual || '';
                            }
                        }
                    }, 100);
                    $('#modal-edit').prop('checked', true);
                })
                .fail(() => showAlert('error', 'Gagal mengambil data'));
        }

        function saveData(e) {
            e.preventDefault();
            $('#formErrors').html('');
            const id = $('#person_in_id').val();
            const payload = {
                fullname: $('#fullname').val(),
                jabatan_id: $('#jabatan_id').val(),
                date_in: $('#date_in').val(),
                method_salary: $('input[name="method_salary"]:checked').val(),
                method_salary_manual: $('#method_salary_manual').val()
            };
            $.ajax({
                    url: "{{ route('person-in.update', ':id') }}".replace(':id', id),
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
                            `<div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><div class="flex gap-2"><i class="ri-error-warning-line mt-0.5"></i><ul class="list-disc pl-4">${errors.map(err => `<li>${err}</li>`).join('')}</ul></div></div>`
                        );
                    } else showAlert('error', xhr.responseJSON?.message ||
                        'Terjadi kesalahan saat memperbarui data personil masuk. Silakan coba lagi.');
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

