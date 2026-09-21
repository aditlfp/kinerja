<x-admin-layout :fullWidth="true">
    @section('title', 'Tambah Area')

    <div class="mx-auto w-full max-w-screen-lg space-y-4 px-2 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Tambah Area</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Tambahkan area baru berdasarkan kerjasama client.</p>
                </div>
                <a href="{{ route('area.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Kembali</a>
            </div>
        </section>

        <form method="POST" action="{{ route('area.store') }}" class="space-y-4" id="form">
            @csrf
            <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="kerjasama_id" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Nama Client</label>
                        <select name="kerjasama_id" id="kerjasama_id" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none">
                            <option selected disabled>~ Pilih Client ~</option>
                            @forelse ($kerjasama as $i)
                                <option value="{{ $i->id }}">{{ $i->client->name }}</option>
                            @empty
                                <option disabled>~ Kosong ~</option>
                            @endforelse
                        </select>
                        <x-input-error :messages="$errors->get('kerjasama_id')" class="mt-2" />
                    </div>
                    <div class="md:col-span-2">
                        <label for="nama_area" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Nama Area</label>
                        <input id="nama_area" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none" type="text" name="nama_area" value="{{ old('nama_area') }}" required autocomplete="nama_area" placeholder="Masukkan nama area..." />
                        <x-input-error :messages="$errors->get('nama_area')" class="mt-2" />
                    </div>
                </div>

                <div class="mt-5 flex justify-end gap-2">
                    <a href="{{ route('area.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Batal</a>
                    <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700">Simpan Area</button>
                </div>
            </section>
        </form>
    </div>
</x-admin-layout>
