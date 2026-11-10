<x-app-layout>
    <div class="min-h-screen w-full bg-slate-50 px-3 py-4 text-slate-800 sm:px-5 lg:px-6">
        <div class="mx-auto w-full max-w-3xl" x-data="{
            users: @js($users),
            jabatanId: '',
            selectedUserId: null,
        
            syncSelectedUser(event) {
                this.selectedUserId = event.target.value ?
                    Number(event.target.value) :
                    null;
        
                const user = this.users.find(
                    u => u.id == this.selectedUserId
                );
        
                this.jabatanId = user?.jabatan_id ?? '';
            }
        }">
            <div class="mb-4 flex items-center justify-between gap-3">
                <img src="{{ URL::asset('logo/logo_kinerja.png') }}" alt="Logo Kinerja" class="h-auto w-28 sm:w-32">
                <a href="{{ route('dashboard.index') }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-white px-3 text-sm font-semibold text-slate-700 shadow-xs ring-1 ring-slate-200 hover:bg-slate-50">
                    <i class="ri-home-4-line"></i> Dashboard
                </a>
            </div>
            <div class="p-4 mb-4 bg-white border rounded-2xl shadow-xs border-white/60 ring-1 ring-slate-900/5">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center min-w-0 gap-3">
                        <a href="{{ route('index.rekap.data.leader') }}"
                            class="inline-flex items-center justify-center w-10 h-10 ml-1 transition rounded-lg shrink-0 sm:ml-0 bg-slate-100 text-slate-700 hover:bg-slate-200 focus:outline-hidden focus:ring-2 focus:ring-slate-400 focus:ring-offset-2"
                            aria-label="Kembali ke rekapitulasi">
                            <i class="text-xl ri-arrow-left-line"></i>
                        </a>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-slate-500">Data Rekap</p>
                            <h1 class="border-l-4 border-sky-500 pl-3 text-xl font-bold leading-tight truncate text-slate-900 sm:text-2xl">
                                Pengajuan Personil Masuk
                            </h1>
                            <p class="mt-1 text-sm leading-5 text-slate-500">
                                Tambahkan personil masuk dan lengkapi detail awalnya.
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('person.in.history') }}"
                        class="items-center hidden gap-2 px-3 text-sm font-semibold transition bg-white border rounded-lg min-h-10 shrink-0 border-slate-200 text-slate-700 hover:bg-slate-50 sm:inline-flex">
                        <i class="ri-history-line"></i>
                        Riwayat
                    </a>
                </div>
            </div>

            <div id="alertBox" class="mb-4"></div>

            <form id="personInForm" x-data="personInForm()" enctype="multipart/form-data"
                data-store-url="{{ route('person-in.store') }}" class="space-y-4">
                <input type="hidden" id="person_in_id" />
                <input type="hidden" name="has_account" :value="hasAccount">
                <input type="hidden" name="fullname" :value="getResolvedFullname()">

                <section class="p-4 bg-white border rounded-lg shadow-xs border-slate-200 sm:p-5">
                    <div class="mb-3">
                        <h2 class="text-sm font-semibold text-slate-800">Status Akun</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Pilih apakah personil sudah memiliki akun di sistem.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                        <label
                            class="flex items-start gap-3 p-3 transition border rounded-lg cursor-pointer min-h-14 border-slate-200 hover:bg-slate-50"
                            :class="hasAccount === 'yes' ? 'border-sky-500 bg-sky-50 ring-1 ring-sky-200' : ''">
                            <input type="radio" name="has_account_option" value="yes" x-model="hasAccount"
                                @change="setAccountMode('yes')" class="w-4 h-4 mt-1 text-sky-600 focus:ring-sky-500">
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold text-slate-800">Sudah memiliki akun</span>
                                <span class="mt-0.5 block text-xs leading-4 text-slate-500">Cari dari data user yang
                                    tersedia.</span>
                            </span>
                        </label>
                        <label
                            class="flex items-start gap-3 p-3 transition border rounded-lg cursor-pointer min-h-14 border-slate-200 hover:bg-slate-50"
                            :class="hasAccount === 'no' ? 'border-sky-500 bg-sky-50 ring-1 ring-sky-200' : ''">
                            <input type="radio" name="has_account_option" value="no" x-model="hasAccount"
                                @change="setAccountMode('no')" class="w-4 h-4 mt-1 text-sky-600 focus:ring-sky-500">
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold text-slate-800">Belum memiliki akun</span>
                                <span class="mt-0.5 block text-xs leading-4 text-slate-500">Isi nama lengkap secara
                                    manual.</span>
                            </span>
                        </label>
                    </div>

                    <div class="mt-4" x-show="hasAccount === 'yes'" x-collapse>
                        <label for="user_id" class="mb-1.5 block text-sm font-semibold text-slate-700">
                            Cari User <span class="text-red-500">*</span>
                        </label>
                        <select id="user_id" x-model="selectedUserId" @change="syncSelectedUser($event)"
                            :required="hasAccount === 'yes'"
                            class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-hidden transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                            :class="showFullnameError ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : ''">
                            <option value="">Pilih user</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" data-name="{{ $user->nama_lengkap }}">
                                    {{ $user->nama_lengkap }}
                                    ({{ $user->kerjasama?->client?->panggilan ?: $user->kerjasama?->client?->name ?? 'Mitra kosong' }})
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-slate-500">Daftar user dibatasi sesuai area kerja/kerjasama Anda.
                        </p>
                    </div>

                    <div class="mt-4" x-show="hasAccount === 'no'" x-collapse>
                        <label for="fullname_manual" class="mb-1.5 block text-sm font-semibold text-slate-700">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="fullname_manual" placeholder="Masukkan nama lengkap"
                            class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-hidden transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                            :class="showFullnameError ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : ''"
                            x-model="manualName">
                    </div>
                </section>

                <section class="p-4 bg-white border rounded-lg shadow-xs border-slate-200 sm:p-5">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="jabatan_id" class="mb-1.5 block text-sm font-semibold text-slate-700">
                                Jabatan <span class="text-red-500">*</span>
                            </label>
                            <select name="jabatan_id" id="jabatan_id" x-model="jabatanId"
                                class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-hidden transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                                required>
                                <option value="">Pilih jabatan</option>
                                @foreach ($jabatans as $jabatan)
                                    <option value="{{ $jabatan->id }}">{{ $jabatan->name_jabatan }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="date_in" class="mb-1.5 block text-sm font-semibold text-slate-700">
                                Tanggal Masuk <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="date_in" id="date_in"
                                class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-hidden transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                                required>
                        </div>

                        <div>
                            <label for="total_mk" class="mb-1.5 block text-sm font-semibold text-slate-700">
                                Jumlah MK <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="total_mk" id="total_mk" value="{{ old('total_mk') }}"
                                class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-hidden transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                                required placeholder="Contoh: 12 bulan">
                            @error('total_mk')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                <section class="p-4 bg-white border rounded-lg shadow-xs border-slate-200 sm:p-5">
                    <div class="mb-3">
                        <h2 class="text-sm font-semibold text-slate-800">
                            Metode Gaji <span class="text-red-500">*</span>
                        </h2>
                        <p class="mt-0.5 text-xs text-slate-500">Pilih metode pembayaran yang disepakati.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-2.5">
                        <label
                            class="flex items-start gap-3 p-3 transition border rounded-lg cursor-pointer min-h-14 border-slate-200 hover:bg-slate-50"
                            :class="selectedMethod === 'transfer' ? 'border-sky-500 bg-sky-50 ring-1 ring-sky-200' : ''">
                            <input type="radio" name="method_salary" value="transfer" x-model="selectedMethod"
                                class="w-4 h-4 mt-1 text-sky-600 focus:ring-sky-500" required>
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold text-slate-800">Transfer</span>
                                <span class="mt-0.5 block text-xs leading-4 text-slate-500">Pembayaran melalui transfer
                                    bank.</span>
                            </span>
                        </label>

                        <div x-show="selectedMethod === 'transfer'" x-collapse class="pl-0 sm:pl-7">
                            <label for="method_salary_manual" class="mb-1.5 block text-xs font-medium text-slate-600">
                                Nomor rekening
                            </label>
                            <input type="text" name="method_salary_manual" id="method_salary_manual"
                                x-model="manualMethod" placeholder="Masukkan nomor rekening"
                                class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-hidden transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                                :required="selectedMethod === 'transfer'" :disabled="selectedMethod !== 'transfer'">
                        </div>

                        <label
                            class="flex items-start gap-3 p-3 transition border rounded-lg cursor-pointer min-h-14 border-slate-200 hover:bg-slate-50"
                            :class="selectedMethod === 'cash' ? 'border-sky-500 bg-sky-50 ring-1 ring-sky-200' : ''">
                            <input type="radio" name="method_salary" value="cash" x-model="selectedMethod"
                                class="w-4 h-4 mt-1 text-sky-600 focus:ring-sky-500" required>
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold text-slate-800">Manual / Cash</span>
                                <span class="mt-0.5 block text-xs leading-4 text-slate-500">Pembayaran tunai/manual
                                    tanpa rekening.</span>
                            </span>
                        </label>
                    </div>
                </section>
                <section class="p-4 bg-white border rounded-lg shadow-xs border-slate-200 sm:p-5">
                    <div class="mb-3">
                        <h2 class="text-sm font-semibold text-slate-800">
                            Keterangan <span class="text-red-blue">(opsional)</span>
                        </h2>
                        <p class="mt-0.5 text-xs text-slate-500">Silahkan ketik keterangan singkat & jelas.</p>
                        <textarea type="text" name="additional_reason" id="additional_reason" x-model="additionalReason"
                            placeholder="Masukkan keterangan dengan singkat namun jelas"
                            class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-hidden transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"></textarea>
                    </div>
                </section>

                <div id="formErrors"></div>

                <div
                    class="sticky sm:bottom-16 z-20 mx-1 border-t rounded-md border-slate-200 bg-white/95 p-3 shadow-[0_-8px_18px_rgba(15,23,42,0.08)] backdrop-blur-sm sm:static sm:mx-0 sm:rounded-lg sm:border sm:shadow-xs">
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <button type="submit" id="btnSave"
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-amber-400 px-4 py-2.5 text-sm font-semibold text-slate-900 transition hover:bg-amber-500 focus:outline-hidden focus:ring-2 focus:ring-amber-400 focus:ring-offset-2">
                            <i class="ri-save-line"></i>
                            <span id="btnSaveText">Simpan Data</span>
                        </button>
                        <button type="button" id="btnReset"
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-hidden focus:ring-2 focus:ring-slate-400 focus:ring-offset-2">
                            <i class="ri-refresh-line"></i>
                            Reset Form
                        </button>
                    </div>
                </div>
            </form>

            <div class="p-4 mt-4 border rounded-lg border-sky-100 bg-sky-50">
                <div class="flex items-start gap-3">
                    <i class="ri-information-line mt-0.5 text-xl text-sky-600"></i>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">Informasi Penting</p>
                        <ul class="mt-2 space-y-1 text-xs leading-5 list-disc list-inside text-slate-600">
                            <li>Pastikan data personil dan jabatan sudah benar.</li>
                            <li>Pilih metode gaji sesuai kesepakatan.</li>
                            <li>Data dapat diubah kembali setelah disimpan.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('personInForm', () => ({
                hasAccount: 'yes',
                selectedUserId: null,
                selectedUserName: '',
                manualName: '',
                selectedMethod: '',
                manualMethod: '',
                additionalReason: '',
                showFullnameError: false,

                getResolvedFullname() {
                    return this.hasAccount === 'yes' ? (this.selectedUserName || '').trim() : (this
                        .manualName || '').trim();
                },

                setAccountMode(mode) {
                    this.hasAccount = mode;
                    this.showFullnameError = false;
                    this.selectedUserId = null;
                    this.selectedUserName = '';
                    this.manualName = '';
                },

                syncSelectedUser(event) {
                    this.selectedUserId = event.target.value ?
                        Number(event.target.value) :
                        null;

                    const selectedOption =
                        event.target.options[event.target.selectedIndex];

                    this.selectedUserName = this.selectedUserId ?
                        (
                            selectedOption?.dataset?.name ||
                            selectedOption?.text ||
                            ''
                        ) :
                        '';

                    // ambil user berdasarkan id
                    const user = this.users.find(
                        u => u.id == this.selectedUserId
                    );

                    // set otomatis jabatan
                    this.jabatanId = user?.jabatan_id ?? '';

                    this.showFullnameError = false;
                },

                init() {
                    this.$watch('selectedMethod', value => {
                        if (value !== 'transfer') this.manualMethod = '';
                    });

                    this.$nextTick(() => {
                        window.initTomUserSelect?.('user_id', {
                            placeholder: 'Pilih user'
                        });
                    });
                },

                resetFormState() {
                    this.setAccountMode('yes');
                    this.selectedMethod = '';
                    this.manualMethod = '';
                    this.additionalReason = '';
                    this.showFullnameError = false;
                    const userSelect = document.getElementById('user_id');
                    if (userSelect?.tomselect) {
                        userSelect.tomselect.clear(true);
                    }
                }
            }));
        });

        $(document).ready(function() {
            const $form = $('#personInForm');
            const storeUrl = $form.data('storeUrl');

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            function showAlert(type, message) {
                $('#alertBox').html('');
                if (typeof window.showAppToast === 'function') {
                    window.showAppToast(type, message);
                    return;
                }
            }

            function showFormErrors(errors) {
                const errorList = errors.map(err => `<li>${err}</li>`).join('');
                $('#formErrors').html(`
                    <div class="px-4 py-3 text-sm border rounded-lg shadow-xs border-rose-200 bg-rose-50 text-rose-700">
                        <div class="flex items-start gap-2">
                            <i class="ri-error-warning-line mt-0.5"></i>
                            <ul class="pl-5 list-disc">${errorList}</ul>
                        </div>
                    </div>
                `);
            }

            function resetForm() {
                $form[0].reset();
                $('#person_in_id').val('');
                $('#formErrors').html('');
                $('#btnSaveText').text('Simpan Data');

                const alpineData = Alpine.$data($form[0]);
                if (alpineData) alpineData.resetFormState();
            }

            function saveData(e) {
                e.preventDefault();
                $('#formErrors').html('');

                const alpineData = Alpine.$data($form[0]);
                const fullname = alpineData ? alpineData.getResolvedFullname() : '';

                if (!fullname) {
                    if (alpineData) alpineData.showFullnameError = true;
                    showFormErrors(['Nama lengkap wajib diisi.']);
                    return;
                }

                if (alpineData) alpineData.showFullnameError = false;

                const payload = {
                    has_account: alpineData ? alpineData.hasAccount : 'yes',
                    fullname: fullname,
                    jabatan_id: $('#jabatan_id').val(),
                    date_in: $('#date_in').val(),
                    total_mk: $('#total_mk').val(),
                    method_salary: $('input[name="method_salary"]:checked').val(),
                    method_salary_manual: alpineData ? alpineData.manualMethod : '',
                    additional_reason: $('#additional_reason').val()
                };

                $.ajax({
                        url: storeUrl,
                        method: 'POST',
                        data: payload
                    })
                    .done(() => {
                        resetForm();
                        showAlert('success', 'Data berhasil ditambahkan');
                    })
                    .fail(xhr => {
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            showFormErrors(Object.values(xhr.responseJSON.errors).flat());
                        } else {
                            showAlert('error', xhr.responseJSON?.message ||
                                'Terjadi kesalahan saat menyimpan data personil masuk. Silakan coba lagi.');
                        }
                    });
            }

            $form.on('submit', saveData);

            $('#btnReset').on('click', function() {
                resetForm();
                showAlert('success', 'Form berhasil direset');
            });
        });
    </script>
</x-app-layout>
