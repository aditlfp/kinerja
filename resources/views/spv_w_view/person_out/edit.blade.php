<x-app-layout>
    @php
        $spvwClientId = request('client_id', session('spvw.selected_client_id'));
    @endphp

    <div class="min-h-screen w-full bg-slate-50 px-3 py-4 text-slate-800 sm:px-5 lg:px-6">
        <div class="w-full max-w-3xl px-3 py-4 mx-auto sm:px-5 lg:px-6">
            <div class="p-4 mb-4 bg-white border rounded-lg shadow-xs border-white/60 ring-1 ring-slate-900/5">
                <div class="flex items-center gap-3">
                    <a href="{{ route('spvw.person-is-out.history', array_filter(['client_id' => $spvwClientId])) }}"
                        class="inline-flex items-center justify-center w-10 h-10 ml-1 transition rounded-lg sm:ml-0 shrink-0 bg-slate-100 text-slate-700 hover:bg-slate-200 focus:outline-hidden focus:ring-2 focus:ring-slate-400 focus:ring-offset-2"
                        aria-label="Kembali ke riwayat personil keluar">
                        <i class="text-xl ri-arrow-left-line"></i>
                    </a>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-slate-500">Data Rekap</p>
                        <h1 class="text-xl font-bold leading-tight truncate text-slate-900 sm:text-2xl">
                            Edit Personil Keluar
                        </h1>
                        <p class="mt-1 text-sm leading-5 text-slate-500">
                            Perbarui data personil keluar dan bukti pendukung jika diperlukan.
                        </p>
                    </div>
                </div>
            </div>

            <form action="{{ route('spvw.person-is-out.update', $personOut->id) }}" method="POST"
                enctype="multipart/form-data" x-data="personOutForm()" class="space-y-4">
                @csrf
                @method('PUT')

                <section class="p-4 bg-white border rounded-lg shadow-xs border-slate-200 sm:p-5">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                                Nama Pegawai
                            </label>
                            <select
                                class="min-h-11 w-full rounded-lg border border-slate-300 bg-slate-100 px-3 py-2.5 text-sm text-slate-700"
                                disabled>
                                @foreach ($users as $user)
                                    <option {{ $personOut->user_id == $user->id ? 'selected' : '' }}>
                                        {{ capitalizeWords($user->name) }} - {{ capitalizeWords($user->nama_lengkap) ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="user_id" value="{{ $personOut->user_id }}">
                        </div>

                        <div>
                            <label for="out_date" class="mb-1.5 block text-sm font-semibold text-slate-700">
                                Tanggal Keluar <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="out_date" id="out_date"
                                value="{{ old('out_date', \Carbon\Carbon::parse($personOut->out_date)->format('Y-m-d')) }}"
                                class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-hidden transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                                required>
                            @error('out_date')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="total_mk" class="mb-1.5 block text-sm font-semibold text-slate-700">
                                Jumlah MK <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="total_mk" id="total_mk"
                                value="{{ old('total_mk', $personOut->total_mk) }}"
                                class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-hidden transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                                required placeholder="Masukkan Total Hari Kerja (Misal: 11 Hari)">
                            @error('total_mk')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                <section class="p-4 bg-white border rounded-lg shadow-xs border-slate-200 sm:p-5">
                    <div class="mb-3">
                        <h2 class="text-sm font-semibold text-slate-800">
                            Alasan Keluar <span class="text-red-500">*</span>
                        </h2>
                        <p class="mt-0.5 text-xs text-slate-500">Pilih alasan yang sesuai dengan kondisi personil.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-2.5">
                        @foreach (['resign' => ['Resign', 'Personil mengundurkan diri.'], 'evaluasi' => ['Evaluasi', 'Personil keluar berdasarkan evaluasi.'], 'lainnya' => ['Lainnya', 'Isi alasan manual jika tidak ada di pilihan.']] as $key => [$label, $desc])
                            <label
                                class="flex items-start gap-3 p-3 transition border rounded-lg cursor-pointer min-h-14 border-slate-200 hover:bg-slate-50"
                                :class="selectedReason === '{{ $key }}' ? 'border-sky-500 bg-sky-50 ring-1 ring-sky-200' : ''">
                                <input type="radio" name="reason" value="{{ $key }}" x-model="selectedReason"
                                    class="w-4 h-4 mt-1 text-sky-600 focus:ring-sky-500"
                                    {{ old('reason', $personOut->reason) === $key ? 'checked' : '' }} required>
                                <span class="min-w-0">
                                    <span class="block text-sm font-semibold text-slate-800">{{ $label }}</span>
                                    <span class="mt-0.5 block text-xs leading-4 text-slate-500">{{ $desc }}</span>
                                </span>
                            </label>
                        @endforeach

                        <div x-show="selectedReason === 'lainnya'" x-collapse class="pl-0 sm:pl-7">
                            <label for="reason_manual" class="mb-1.5 block text-xs font-medium text-slate-600">
                                Alasan lainnya
                            </label>
                            <input type="text" name="reason_manual" id="reason_manual" x-model="manualReason"
                                value="{{ old('reason_manual', $personOut->reason_manual) }}"
                                placeholder="Contoh: pindah domisili"
                                class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-hidden transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                                :required="selectedReason === 'lainnya'" :disabled="selectedReason !== 'lainnya'">
                            <p class="mt-1 text-xs text-slate-500">Isi singkat dan jelas sesuai kondisi sebenarnya.</p>
                        </div>
                    </div>

                    @error('reason')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    @error('reason_manual')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </section>

                <section class="p-4 bg-white border rounded-lg shadow-xs border-slate-200 sm:p-5" x-data="imagePreview()">
                    <label for="img" class="mb-1.5 block text-sm font-semibold text-slate-700">
                        Bukti Pendukung
                    </label>
                    <p class="mb-3 text-xs text-slate-500">Kosongkan jika tidak ingin mengganti bukti. Maksimal 2MB.</p>

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
                                    <p class="text-sm font-semibold text-slate-700">Ketuk untuk ganti bukti</p>
                                    <p class="mt-1 text-xs text-slate-500">Di desktop juga bisa seret dan lepas file.</p>
                                </div>
                            </div>
                        </template>

                        <template x-if="imageUrl">
                            <div class="flex flex-col items-center gap-3">
                                <img :src="imageUrl" alt="Preview bukti"
                                    class="object-cover w-full h-44 max-w-xs border rounded-lg border-slate-200">
                                <div class="max-w-full">
                                    <p class="text-xs font-medium truncate text-slate-700" x-text="fileName"></p>
                                    <button type="button" @click.stop="removeImage"
                                        class="inline-flex items-center justify-center gap-1 px-3 mt-2 text-xs font-semibold text-red-700 rounded-lg min-h-9 bg-red-50 ring-1 ring-red-100">
                                        <i class="ri-delete-bin-line"></i>
                                        Hapus preview
                                    </button>
                                </div>
                            </div>
                        </template>

                        <input type="file" name="img" id="img" accept="image/*" x-ref="fileInput"
                            @change="previewImage" class="hidden">
                    </div>

                    @error('img')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </section>

                <div
                    class="sticky sm:bottom-16 z-20 mx-0.5 rounded-md border-t border-slate-200 bg-white/95 p-3 shadow-[0_-8px_18px_rgba(15,23,42,0.08)] backdrop-blur-sm sm:static sm:mx-0 sm:rounded-lg sm:border sm:shadow-xs">
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <button type="submit"
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-amber-400 px-4 py-2.5 text-sm font-semibold text-slate-900 transition hover:bg-amber-500 focus:outline-hidden focus:ring-2 focus:ring-amber-400 focus:ring-offset-2">
                            <i class="ri-save-line"></i>
                            Update Data
                        </button>
                        <a href="{{ route('spvw.person-is-out.history', array_filter(['client_id' => $spvwClientId])) }}"
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-hidden focus:ring-2 focus:ring-slate-400 focus:ring-offset-2">
                            <i class="ri-close-line"></i>
                            Batal
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function personOutForm() {
            return {
                selectedReason: '{{ old('reason', $personOut->reason) }}',
                manualReason: '{{ old('reason_manual', $personOut->reason_manual) }}',

                init() {
                    this.$watch('selectedReason', value => {
                        if (value !== 'lainnya') {
                            this.manualReason = '';
                        }
                    });
                }
            }
        }

        function imagePreview() {
            return {
                imageUrl: '{{ $personOut->img ? asset('storage/images/' . $personOut->img) : '' }}',
                fileName: '{{ $personOut->img ?? '' }}',
                isDragging: false,

                previewImage(event) {
                    this.processFile(event.target.files[0]);
                },

                handleDrop(event) {
                    this.isDragging = false;
                    const file = event.dataTransfer.files[0];

                    if (!file || !file.type.startsWith('image/')) {
                        return;
                    }

                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    this.$refs.fileInput.files = dataTransfer.files;
                    this.processFile(file);
                },

                processFile(file) {
                    if (!file || !file.type.startsWith('image/')) {
                        return;
                    }

                    if (file.size > 2 * 1024 * 1024) {
                        alert('Ukuran file terlalu besar. Maksimal 2MB.');
                        this.removeImage();
                        return;
                    }

                    this.fileName = file.name;
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.imageUrl = e.target.result;
                    };
                    reader.readAsDataURL(file);
                },

                removeImage() {
                    this.imageUrl = '';
                    this.fileName = '';
                    this.$refs.fileInput.value = '';
                }
            }
        }
    </script>
</x-app-layout>
