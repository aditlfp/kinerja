<x-app-layout>
    <div class="min-h-screen w-full bg-slate-50 px-3 py-4 text-slate-800 sm:px-5 lg:px-6">
        <div class="mx-auto w-full max-w-3xl">
            <div class="mb-4 flex items-center justify-between gap-3">
                <img src="{{ URL::asset('logo/logo_kinerja.png') }}" alt="Logo Kinerja" class="h-auto w-28 sm:w-32">
                <a href="{{ route('dashboard.index') }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-white px-3 text-sm font-semibold text-slate-700 shadow-xs ring-1 ring-slate-200 hover:bg-slate-50"><i class="ri-home-4-line"></i> Dashboard</a>
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
                                Pengajuan Lepas Training
                            </h1>
                            <p class="mt-1 text-sm leading-5 text-slate-500">
                                Catat personil yang selesai masa training.
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('finished-training.history') }}"
                        class="items-center hidden gap-2 px-3 text-sm font-semibold transition bg-white border rounded-lg min-h-10 shrink-0 border-slate-200 text-slate-700 hover:bg-slate-50 sm:inline-flex">
                        <i class="ri-history-line"></i>
                        Riwayat
                    </a>
                </div>
            </div>

            <div id="alertBox" class="mb-4"></div>

            <form id="finishedTrainingForm" x-data="finishedTrainingForm()" enctype="multipart/form-data"
                data-store-url="{{ route('finished-training.store') }}" class="space-y-4">
                <input type="hidden" id="finished_training_id" />

                <section class="p-4 bg-white border rounded-lg shadow-xs border-slate-200 sm:p-5">
                    <label for="user_id" class="mb-1.5 block text-sm font-semibold text-slate-700">
                        Nama Pegawai <span class="text-red-500">*</span>
                    </label>
                    <select id="user_id" x-model="selectedUserId" @change="syncSelectedUser($event)"
                        class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-hidden transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                        :class="showUserError ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : ''" required>
                        <option value="">Pilih nama pegawai</option>
                        <template x-for="user in allUsers" :key="user.id">
                            <option :value="user.id" :data-name="user.nama_lengkap"
                                x-text="`${user.nama_lengkap} (${user.kerjasama?.client?.panggilan || user.kerjasama?.client?.name || 'Mitra kosong'})`"></option>
                        </template>
                    </select>
                    <p class="mt-2 text-xs text-slate-500">Daftar user dibatasi sesuai area kerja/kerjasama Anda.</p>
                </section>

                <section class="p-4 bg-white border rounded-lg shadow-xs border-slate-200 sm:p-5">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="date_in" class="mb-1.5 block text-sm font-semibold text-slate-700">
                                Tanggal Masuk <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="date_in" id="date_in"
                                class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-hidden transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                                required>
                        </div>

                        <div>
                            <label for="date_finish_train" class="mb-1.5 block text-sm font-semibold text-slate-700">
                                Tanggal Lepas Training <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="date_finish_train" id="date_finish_train"
                                class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-hidden transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                                required>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="desc" class="mb-1.5 block text-sm font-semibold text-slate-700">
                                Keterangan <span class="text-red-500">*</span>
                            </label>
                            <textarea name="desc" id="desc" rows="4" placeholder="Masukkan keterangan lepas training"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-hidden transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                                required></textarea>
                        </div>
                    </div>
                </section>

                <div id="formErrors"></div>

                <div
                    class="sticky sm:bottom-16 z-20 mx-1 rounded-md border-t border-slate-200 bg-white/95 p-3 shadow-[0_-8px_18px_rgba(15,23,42,0.08)] backdrop-blur-sm sm:static sm:mx-0 sm:rounded-lg sm:border sm:shadow-xs">
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
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('finishedTrainingForm', () => ({
                selectedUserId: null,
                allUsers: @js($users ?? []),
                showUserError: false,

                syncSelectedUser(event) {
                    this.selectedUserId = event.target.value ? Number(event.target.value) : null;
                    this.showUserError = false;
                },

                init() {
                    this.$nextTick(() => {
                        window.initTomUserSelect?.('user_id', {
                            placeholder: 'Pilih nama pegawai'
                        });
                    });
                },

                resetFormState() {
                    this.selectedUserId = null;
                    this.showUserError = false;
                    const userSelect = document.getElementById('user_id');
                    if (userSelect?.tomselect) {
                        userSelect.tomselect.clear(true);
                    }
                }
            }));
        });

        $(document).ready(function() {
            const $form = $('#finishedTrainingForm');
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
                $('#finished_training_id').val('');
                $('#formErrors').html('');
                $('#btnSaveText').text('Simpan Data');

                const alpineData = Alpine.$data($form[0]);
                if (alpineData) {
                    alpineData.resetFormState();
                }
            }

            function saveData(e) {
                e.preventDefault();
                $('#formErrors').html('');

                const alpineData = Alpine.$data($form[0]);

                if (!alpineData?.selectedUserId) {
                    if (alpineData) alpineData.showUserError = true;
                    showFormErrors(['Nama pegawai wajib dipilih dari user yang tersedia.']);
                    return;
                }

                if (alpineData) alpineData.showUserError = false;

                const payload = {
                    user_id: alpineData.selectedUserId,
                    date_in: $('#date_in').val(),
                    date_finish_train: $('#date_finish_train').val(),
                    desc: $('#desc').val(),
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
                            showAlert('error', xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan data lepas training. Silakan coba lagi.');
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
