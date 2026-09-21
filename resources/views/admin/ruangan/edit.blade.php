<x-admin-layout :fullWidth="true">
    @section('title', 'Edit Ruangan')

    <div class="mx-auto w-full max-w-screen-md space-y-4 px-2 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Edit Ruangan</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Perbarui ruangan dan mitra terkait.</p>
                </div>
                <a href="{{ route('admin.ruangan.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Kembali</a>
            </div>
        </section>

        <form method="POST" action="{{ route('admin.ruangan.update', $ruanganId->id) }}" class="space-y-4" id="form">
            @csrf
            @method('PATCH')
            <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                <div class="space-y-4">
                    <div>
                        <label for="kerjasama_id" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Nama Mitra</label>
                        <select name="kerjasama_id" id="kerjasama_id" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none">
                            <option selected disabled>~ Pilih Mitra ~</option>
                            @foreach ($kerjasama as $i)
                                <option {{ $ruanganId->kerjasama_id == $i->id ? 'selected' : '' }} value="{{ $i->id }}">{{ $i->client->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="nama_ruangan" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Nama Ruangan</label>
                        <input id="nama_ruangan" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none" type="text" name="nama_ruangan" value="{{ $ruanganId->nama_ruangan}}" required autocomplete="nama_ruangan" />
                        <x-input-error :messages="$errors->get('nama_ruangan')" class="mt-2" />
                    </div>
                </div>
                <div class="mt-5 flex justify-end gap-2">
                    <a href="{{ route('admin.ruangan.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Batal</a>
                    <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700">Simpan Perubahan</button>
                </div>
            </section>
        </form>
    </div>
</x-admin-layout>
