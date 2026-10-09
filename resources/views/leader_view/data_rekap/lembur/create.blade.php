<x-app-layout>
    <div class="min-h-screen w-full bg-slate-50 px-3 py-4 text-slate-800 sm:px-5 lg:px-6">
        <div class="mx-auto w-full max-w-3xl">
            <div class="mb-4 flex items-center justify-between gap-3">
                <img src="{{ URL::asset('logo/logo_kinerja.png') }}" alt="Logo Kinerja" class="h-auto w-28 sm:w-32">
                <a href="{{ route('dashboard.index') }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-white px-3 text-sm font-semibold text-slate-700 shadow-xs ring-1 ring-slate-200 hover:bg-slate-50">
                    <i class="ri-home-4-line"></i> Dashboard
                </a>
            </div>
            <div class="p-4 mb-4 bg-white border rounded-2xl shadow-xs border-white/60 ring-1 ring-slate-900/5">
                <div class="flex items-center gap-3">
                    <a href="{{ route('index.rekap.data.leader') }}"
                        class="inline-flex items-center justify-center w-10 h-10 ml-1 transition rounded-lg sm:ml-0 shrink-0 bg-slate-100 text-slate-700 hover:bg-slate-200 focus:outline-hidden focus:ring-2 focus:ring-slate-400 focus:ring-offset-2"
                        aria-label="Kembali ke rekapitulasi">
                        <i class="text-xl ri-arrow-left-line"></i>
                    </a>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-slate-500">Data Rekap</p>
                        <h1 class="border-l-4 border-sky-500 pl-3 text-xl font-bold leading-tight truncate text-slate-900 sm:text-2xl">
                            Pengajuan Lembur
                        </h1>
                        <p class="mt-1 text-sm leading-5 text-slate-500">
                            Isi data lembur personil dengan lengkap sebelum disimpan.
                        </p>
                    </div>
                </div>
            </div>

            <form action="{{ route('overtime-application.store') }}" method="POST" enctype="multipart/form-data" x-data="overtimeForm()"
                class="space-y-4">
                @csrf

                <section class="p-4 bg-white border rounded-lg shadow-xs border-slate-200 sm:p-5">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="user_id" class="mb-1.5 block text-sm font-semibold text-slate-700">
                                Nama Pegawai <span class="text-red-500">*</span>
                            </label>
                            <select name="user_id" id="user_id"
                                class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-hidden transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                                required>
                                <option value="">Pilih nama pegawai</option>
                                @forelse($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ capitalizeWords($user->nama_lengkap) ?? 'N/A' }}
                                        ({{ $user->kerjasama?->client?->panggilan ?: $user->kerjasama?->client?->name ?? 'Mitra kosong' }})
                                    </option>
                                @empty
                                    <option value="" disabled>Tidak ada data pegawai</option>
                                @endforelse
                            </select>
                            @error('user_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="date_overtime" class="mb-1.5 block text-sm font-semibold text-slate-700">
                                Tanggal Lembur <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="date_overtime" id="date_overtime"
                                value="{{ old('date_overtime') }}"
                                class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-hidden transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                                required>
                            @error('date_overtime')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                <section class="p-4 bg-white border rounded-lg shadow-xs border-slate-200 sm:p-5" x-data="imagePreview()">
                    <label for="foto_bukti" class="mb-1.5 block text-sm font-semibold text-slate-700">
                        Foto Bukti <span class="text-xs font-normal text-slate-500">(Opsional)</span>
                    </label>
                    <p class="mb-3 text-xs text-slate-500">Format PNG, JPG, JPEG, atau WEBP. Maksimal 2MB.</p>
                    <div @click="$refs.fileInput.click()" @drop.prevent="handleDrop($event)"
                        @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false"
                        :class="isDragging ? 'border-sky-500 bg-sky-50' : 'border-slate-300 bg-slate-50'"
                        class="p-4 text-center transition border-2 border-dashed rounded-lg cursor-pointer hover:border-sky-400 hover:bg-sky-50 sm:p-6">
                        <template x-if="!imageUrl">
                            <div class="flex flex-col items-center gap-3">
                                <div class="inline-flex items-center justify-center w-12 h-12 bg-white rounded-full shadow-xs text-slate-500 ring-1 ring-slate-200">
                                    <i class="text-2xl ri-upload-cloud-2-line"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-700">Ketuk untuk unggah bukti</p>
                                    <p class="mt-1 text-xs text-slate-500">Di desktop juga bisa seret dan lepas file.</p>
                                </div>
                            </div>
                        </template>
                        <template x-if="imageUrl">
                            <div class="flex flex-col items-center gap-3">
                                <img :src="imageUrl" alt="Preview bukti"
                                    class="object-cover w-full h-40 max-w-xs border rounded-lg border-slate-200">
                                <div class="max-w-full">
                                    <p class="text-xs font-medium truncate text-slate-700" x-text="fileName"></p>
                                    <button type="button" @click.stop="removeImage"
                                        class="inline-flex items-center justify-center gap-1 px-3 mt-2 text-xs font-semibold text-red-700 rounded-lg min-h-9 bg-red-50 ring-1 ring-red-100">
                                        <i class="ri-delete-bin-line"></i>
                                        Hapus file
                                    </button>
                                </div>
                            </div>
                        </template>
                        <input type="file" name="foto_bukti" id="foto_bukti" accept="image/*" x-ref="fileInput"
                            @change="previewImage" class="hidden">
                    </div>
                    <p class="mt-1 text-xs text-slate-500">Format: JPG/JPEG/PNG/WEBP, maksimal 2MB.</p>
                    @error('foto_bukti')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </section>

                <section class="p-4 bg-white border rounded-lg shadow-xs border-slate-200 sm:p-5">
                    <div class="mb-3">
                        <h2 class="text-sm font-semibold text-slate-800">
                            Jenis Lembur <span class="text-red-500">*</span>
                        </h2>
                        <p class="mt-0.5 text-xs text-slate-500">Pilih salah satu sesuai perhitungan lembur.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-2.5">
                        <label
                            class="flex items-start gap-3 p-3 transition border rounded-lg cursor-pointer min-h-14 border-slate-200 hover:bg-slate-50"
                            :class="selectedType === 'shift' ? 'border-sky-500 bg-sky-50 ring-1 ring-sky-200' : ''">
                            <input type="radio" name="type_overtime" value="shift" x-model="selectedType"
                                class="w-4 h-4 mt-1 text-sky-600 focus:ring-sky-500"
                                {{ old('type_overtime') == 'shift' ? 'checked' : '' }} required>
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold text-slate-800">Shift</span>
                                <span class="mt-0.5 block text-xs leading-4 text-slate-500">Lembur berdasarkan shift
                                    kerja.</span>
                            </span>
                        </label>

                        <label
                            class="flex items-start gap-3 p-3 transition border rounded-lg cursor-pointer min-h-14 border-slate-200 hover:bg-slate-50"
                            :class="selectedType === 'jam' ? 'border-sky-500 bg-sky-50 ring-1 ring-sky-200' : ''">
                            <input type="radio" name="type_overtime" value="jam" x-model="selectedType"
                                class="w-4 h-4 mt-1 text-sky-600 focus:ring-sky-500"
                                {{ old('type_overtime') == 'jam' ? 'checked' : '' }} required>
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold text-slate-800">Jam</span>
                                <span class="mt-0.5 block text-xs leading-4 text-slate-500">Isi jumlah jam lembur. (jika waktu bekerja kurang dari jumlah shift)</span>
                            </span>
                        </label>

                        <div x-show="selectedType === 'jam'" x-collapse class="pl-0 sm:pl-7">
                            <label for="type_overtime_manual_jam" class="mb-1.5 block text-xs font-medium text-slate-600">
                                Jumlah jam
                            </label>
                            <input type="number" inputmode="numeric" min="1" name="type_overtime_manual"
                                id="type_overtime_manual_jam" x-model="manualType" placeholder="Contoh: 2"
                                @input="limitNumericInput($event)"
                                value="{{ old('type_overtime_manual') }}"
                                class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-hidden transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                                :required="selectedType === 'jam'" :disabled="selectedType !== 'jam'">
                            <p class="mt-1 text-xs text-slate-500">Gunakan angka, misalnya 1, 2, atau 3.</p>
                        </div>

                        <label
                            class="flex items-start gap-3 p-3 transition border rounded-lg cursor-pointer min-h-14 border-slate-200 hover:bg-slate-50"
                            :class="selectedType === 'lainnya' ? 'border-sky-500 bg-sky-50 ring-1 ring-sky-200' : ''">
                            <input type="radio" name="type_overtime" value="lainnya" x-model="selectedType"
                                class="w-4 h-4 mt-1 text-sky-600 focus:ring-sky-500"
                                {{ old('type_overtime') == 'lainnya' ? 'checked' : '' }} required>
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold text-slate-800">Lainnya</span>
                                <span class="mt-0.5 block text-xs leading-4 text-slate-500">Isi nominal atau nilai manual.</span>
                            </span>
                        </label>

                        <div x-show="selectedType === 'lainnya'" x-collapse class="pl-0 sm:pl-7">
                            <label for="type_overtime_manual_lainnya"
                                class="mb-1.5 block text-xs font-medium text-slate-600">
                                Nominal / nilai manual
                            </label>
                            <input type="text" inputmode="numeric" autocomplete="off"
                                id="type_overtime_manual_lainnya" x-model="manualTypeDisplay"
                                @input="syncRupiahInput($event)" @blur="normalizeRupiahInput()"
                                placeholder="Contoh: Rp. 50.000"
                                maxlength="13"
                                class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-hidden transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                                :required="selectedType === 'lainnya'" :disabled="selectedType !== 'lainnya'">
                            <input type="hidden" name="type_overtime_manual" :value="manualTypeRaw" :disabled="selectedType !== 'lainnya'">
                            <p class="mt-1 text-xs text-slate-500"
                                x-text="manualTypeRaw ? `Preview: ${formatRupiah(manualTypeRaw)}` : 'Gunakan angka, misalnya 50000 atau 20000.'">
                                {{ old('type_overtime_manual') ? 'Preview: ' . toRupiah(old('type_overtime_manual')) : 'Gunakan angka, misalnya 50000 atau 20000.' }}
                            </p>
                        </div>
                    </div>

                    @error('type_overtime')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    @error('type_overtime_manual')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </section>

                <section class="p-4 bg-white border rounded-lg shadow-xs border-slate-200 sm:p-5">
                    <label for="desc" class="mb-1.5 block text-sm font-semibold text-slate-700">
                        Keperluan Lembur <span class="text-red-500">*</span>
                    </label>
                    <textarea name="desc" id="desc" rows="4" placeholder="Contoh: Menyelesaikan pekerjaan tambahan di area..."
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-hidden transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                        required>{{ old('desc') }}</textarea>
                    <p class="mt-1 text-xs text-slate-500">Tuliskan keperluan lembur secara singkat dan jelas.</p>
                    @error('desc')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </section>

                <div
                    class="sticky sm:bottom-16 z-20 mx-0.5 sm:-mx-3 border-t rounded-md border-slate-200 bg-white/95 p-3 shadow-[0_-8px_18px_rgba(15,23,42,0.08)] backdrop-blur-sm sm:static sm:mx-0 sm:rounded-lg sm:border sm:shadow-xs">
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <button type="submit"
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-amber-400 px-4 py-2.5 text-sm font-semibold text-slate-900 transition hover:bg-amber-500 focus:outline-hidden focus:ring-2 focus:ring-amber-400 focus:ring-offset-2">
                            <i class="ri-save-line"></i>
                            Simpan Pengajuan
                        </button>
                        <a href="{{ route('index.rekap.data.leader') }}"
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-hidden focus:ring-2 focus:ring-slate-400 focus:ring-offset-2">
                            <i class="ri-close-line"></i>
                            Batal
                        </a>
                    </div>
                </div>
            </form>

            <div class="p-4 mt-4 border rounded-lg border-sky-100 bg-sky-50">
                <div class="flex items-start gap-3">
                    <i class="ri-information-line mt-0.5 text-xl text-sky-600"></i>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">Informasi Penting</p>
                        <ul class="mt-2 space-y-1 text-xs leading-5 list-disc list-inside text-slate-600">
                            <li>Pastikan data pegawai dan tanggal lembur sudah benar.</li>
                            <li>Finalisasi data dilakukan sebelum akhir bulan.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function overtimeForm() {
            return {
                selectedType: '{{ old('type_overtime') ?? '' }}',
                manualType: @js(old('type_overtime_manual') ?? ''),
                manualTypeRaw: @js(old('type_overtime_manual') ?? ''),
                manualTypeDisplay: '',

                formatRupiah(value) {
                    const normalized = String(value ?? '').replace(/[^\d]/g, '');
                    if (!normalized) return '';
                    return `Rp. ${new Intl.NumberFormat('id-ID').format(Number(normalized))}`;
                },

                syncRupiahInput(event) {
                    const rawValue = String(event.target.value ?? '').replace(/[^\d]/g, '').slice(0, 7);
                    this.manualTypeRaw = rawValue;
                    this.manualTypeDisplay = rawValue ? this.formatRupiah(rawValue) : '';
                    event.target.value = this.manualTypeDisplay;
                },

                limitNumericInput(event) {
                    const rawValue = String(event.target.value ?? '').replace(/[^\d]/g, '').slice(0, 7);
                    this.manualType = rawValue;
                    this.manualTypeRaw = rawValue;
                    event.target.value = rawValue;
                },

                normalizeRupiahInput() {
                    if (!this.manualTypeRaw) {
                        this.manualTypeDisplay = '';
                        return;
                    }

                    this.manualTypeDisplay = this.formatRupiah(this.manualTypeRaw);
                },

                init() {
                    this.manualTypeDisplay = this.manualTypeRaw ? this.formatRupiah(this.manualTypeRaw) : '';
                    this.$watch('selectedType', value => {
                        if (value !== 'jam') {
                            this.manualType = '';
                        }

                        if (value !== 'lainnya') {
                            this.manualTypeRaw = '';
                            this.manualTypeDisplay = '';
                        }
                    });
                }
            }
        }

        function imagePreview(existingUrl = '', existingName = '') {
            return {
                isDragging: false,
                imageUrl: existingUrl || '',
                fileName: existingName || '',
                previewImage(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    this.imageUrl = URL.createObjectURL(file);
                    this.fileName = file.name;
                },
                handleDrop(event) {
                    this.isDragging = false;
                    const file = event.dataTransfer.files[0];
                    if (!file || !file.type.startsWith('image/')) return;
                    this.$refs.fileInput.files = event.dataTransfer.files;
                    this.imageUrl = URL.createObjectURL(file);
                    this.fileName = file.name;
                },
                removeImage() {
                    this.imageUrl = '';
                    this.fileName = '';
                    this.$refs.fileInput.value = '';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            window.initTomUserSelect?.('user_id', {
                placeholder: 'Pilih nama pegawai'
            });
        });
    </script>
</x-app-layout>