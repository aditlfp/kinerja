<x-app-layout>
    @php
        $spvwClientId = request('client_id', session('spvw.selected_client_id'));
        $appendClient = static fn(string $url) => $spvwClientId
            ? $url . (str_contains($url, '?') ? '&' : '?') . 'client_id=' . $spvwClientId
            : $url;
    @endphp
    <x-main-div>
        <div class="w-full max-w-3xl px-3 py-4 mx-auto sm:px-5 lg:px-6">
            <div class="p-4 mb-4 bg-white border rounded-lg shadow-xs border-white/60 ring-1 ring-slate-900/5">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center min-w-0 gap-3">
                        <a href="{{ route('spvw.rekap.index', array_filter(['client_id' => $spvwClientId])) }}"
                            class="inline-flex items-center justify-center w-10 h-10 ml-1 transition rounded-lg shrink-0 sm:ml-0 bg-slate-100 text-slate-700 hover:bg-slate-200 focus:outline-hidden focus:ring-2 focus:ring-slate-400 focus:ring-offset-2"
                            aria-label="Kembali ke rekapitulasi">
                            <i class="text-xl ri-arrow-left-line"></i>
                        </a>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-slate-500">Data Rekap</p>
                            <h1 class="text-xl font-bold leading-tight truncate text-slate-900 sm:text-2xl">
                                Pengajuan Keterangan Lanjutan
                            </h1>
                            <p class="mt-1 text-sm leading-5 text-slate-500">
                                Catat keterangan lanjutan untuk kebutuhan rekap.
                            </p>
                        </div>
                    </div>
                    <a href="{{ $appendClient(route('spvw.keterangan-lanjutan.history', array_filter(['client_id' => $spvwClientId]))) }}"
                        class="items-center hidden gap-2 px-3 text-sm font-semibold transition bg-white border rounded-lg min-h-10 shrink-0 border-slate-200 text-slate-700 hover:bg-slate-50 sm:inline-flex">
                        <i class="ri-history-line"></i>
                        Riwayat
                    </a>
                </div>
            </div>

            <form action="{{ route('spvw.keterangan-lanjutan.store', array_filter(['client_id' => $spvwClientId])) }}" method="POST" class="space-y-4"
                x-data="{
                    entries: @js(old('entries', [['periode' => '', 'judul' => '', 'keterangan' => '']])),
                    addEntry() {
                        this.entries.push({ periode: '', judul: '', keterangan: '' });
                    },
                    removeEntry(index) {
                        if (this.entries.length === 1) return;
                        this.entries.splice(index, 1);
                    }
                }">
                @csrf

                <section class="p-4 bg-white border rounded-lg shadow-xs border-slate-200 sm:p-5">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                        Keterangan Lanjutan <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-3">
                        <template x-for="(entry, index) in entries" :key="index">
                            <div class="p-3 border rounded-lg border-slate-200 bg-slate-50/60">
                                <div class="grid grid-cols-1 gap-3">
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold text-slate-600">Periode</label>
                                        <input type="text" :name="`entries[${index}][periode]`" x-model="entry.periode"
                                            class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-hidden transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                                            placeholder="Contoh: Mei 2026 / 20-05-2026">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold text-slate-600">Judul Keterangan</label>
                                        <input type="text" :name="`entries[${index}][judul]`" x-model="entry.judul"
                                            class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-hidden transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                                            placeholder="Contoh: Kedisiplinan">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold text-slate-600">Keterangan</label>
                                        <textarea :name="`entries[${index}][keterangan]`" x-model="entry.keterangan" rows="3"
                                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-hidden transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                                            placeholder="Isi detail keterangan"></textarea>
                                    </div>
                                </div>
                                <div class="flex justify-end mt-2">
                                    <button type="button" @click="removeEntry(index)"
                                        class="inline-flex items-center gap-1 text-xs font-semibold text-red-600 hover:text-red-700"
                                        x-show="entries.length > 1">
                                        <i class="ri-delete-bin-line"></i>
                                        Hapus Field
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="addEntry()"
                        class="inline-flex items-center gap-2 px-3 py-2 mt-3 text-xs font-semibold text-white transition rounded-lg bg-sky-600 hover:bg-sky-700">
                        <i class="ri-add-line"></i>
                        Tambah Field
                    </button>
                    <p class="mt-2 text-xs text-slate-500">Setiap field berisi periode, judul, dan isi keterangan. Data disimpan sebagai array.</p>
                </section>

                @if ($errors->any())
                    <div class="p-3 text-sm border rounded-lg bg-red-50 border-red-200 text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="flex flex-col justify-end gap-2 sm:flex-row">
                    <a href="{{ $appendClient(route('spvw.keterangan-lanjutan.history', array_filter(['client_id' => $spvwClientId]))) }}"
                        class="inline-flex items-center justify-center gap-2 px-4 text-sm font-semibold transition bg-white border rounded-lg min-h-10 border-slate-300 text-slate-700 hover:bg-slate-50">
                        <i class="ri-history-line"></i>
                        Lihat Riwayat
                    </a>
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 px-4 text-sm font-semibold text-white transition rounded-lg min-h-10 bg-emerald-600 hover:bg-emerald-700">
                        <i class="ri-save-line"></i>
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </x-main-div>
</x-app-layout>
