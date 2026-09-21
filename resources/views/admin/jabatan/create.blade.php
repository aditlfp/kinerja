<x-admin-layout :fullWidth="true">
    @section('title', 'Tambah Jabatan')

    <div class="mx-auto w-full max-w-screen-lg space-y-4 px-2 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Tambah Jabatan</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Buat jabatan baru, kaitkan ke divisi, dan pilih mitra terkait.</p>
                </div>
                <a href="{{ route('admin.jabatan.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                    Kembali
                </a>
            </div>
        </section>

        <form method="POST" action="{{ route('admin.jabatan.store') }}" id="form" class="space-y-4">
            @csrf
            <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="divisi_id" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Divisi</label>
                        <select name="divisi_id" id="divisi_id" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none">
                            <option selected disabled>~ Pilih divisi / kosongkan jika belum ada ~</option>
                            @forelse ($divisi as $div)
                                <option value="{{ $div->id }}">{{ $div->name }}</option>
                            @empty
                                <option disabled>~ Data Kosong ~</option>
                            @endforelse
                        </select>
                    </div>

                    <div>
                        <label for="code_jabatan" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Kode Jabatan</label>
                        <input type="text" name="code_jabatan" id="code_jabatan" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 placeholder:text-gray-400 focus:border-blue-300 focus:bg-white focus:outline-none" placeholder="Masukkan kode jabatan..." />
                    </div>

                    <div>
                        <label for="type_jabatan" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Tipe Jabatan</label>
                        <input type="text" name="type_jabatan" id="type_jabatan" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 placeholder:text-gray-400 focus:border-blue-300 focus:bg-white focus:outline-none" placeholder="Contoh: Manajemen" />
                    </div>

                    <div>
                        <label for="name_jabatan" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Nama Jabatan</label>
                        <input type="text" name="name_jabatan" id="name_jabatan" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 placeholder:text-gray-400 focus:border-blue-300 focus:bg-white focus:outline-none" placeholder="Contoh: Staff IT" />
                    </div>
                </div>

                <div class="mt-4">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Pilih Mitra</label>
                    <div class="max-h-64 space-y-1 overflow-y-auto rounded-xl border border-gray-200 bg-gray-50 p-2.5">
                        @forelse ($mitra as $i)
                            <label class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm text-gray-700 hover:bg-white">
                                <input type="checkbox" name="kerjasama_id[]" class="checkbox checkbox-sm" value="{{ $i->id }}">
                                <span class="truncate">{{ $i->client->name }}</span>
                            </label>
                        @empty
                            <p class="px-2 py-1 text-sm text-gray-500">Data mitra kosong.</p>
                        @endforelse
                    </div>
                </div>

                <div class="mt-5 flex justify-end gap-2">
                    <a href="{{ route('admin.jabatan.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Batal</a>
                    <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700">Simpan Jabatan</button>
                </div>
            </section>
        </form>
    </div>
</x-admin-layout>
