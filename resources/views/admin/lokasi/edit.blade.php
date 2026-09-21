<x-admin-layout :fullWidth="true">
    @section('title', 'Edit Lokasi')

    <div class="mx-auto w-full max-w-screen-lg space-y-4 px-2 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Edit Lokasi: {{ $lokasiId->client->name }}</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Perbarui koordinat dan radius lokasi client.</p>
                </div>
                <a href="{{ route('admin.lokasi.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Kembali</a>
            </div>
        </section>

        <form action="{{ route('admin.lokasi.update', [$lokasiId->id]) }}" method="POST" class="space-y-4">
            @method('put')
            @csrf
            <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="client_id" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Client</label>
                        <select name="client_id" id="client_id" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none">
                            <option disabled>~ Pilih Client ~</option>
                            @foreach ($client as $i)
                                <option {{ $lokasiId->client_id == $i->id ? 'selected' : '' }} value="{{ $i->id }}">{{ $i->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
                    </div>
                    <div>
                        <label for="latitude" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Latitude</label>
                        <input name="latitude" id="latitude" value="{{ $lokasiId->latitude }}" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none" placeholder="Input Latitude..."/>
                    </div>
                    <div>
                        <label for="longtitude" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Longitude</label>
                        <input name="longtitude" id="longtitude" value="{{ $lokasiId->longtitude }}" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none" placeholder="Input Longitude..."/>
                    </div>
                    <div class="md:col-span-2">
                        <label for="radius" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Radius (meter)</label>
                        <input id="radius" name="radius" type="number" value="{{ $lokasiId->radius }}" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none" placeholder="Input radius min 50..."/>
                    </div>
                </div>

                <div class="mt-5 flex justify-end gap-2">
                    <a href="{{ route('admin.lokasi.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Batal</a>
                    <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700">Simpan Perubahan</button>
                </div>
            </section>
        </form>
    </div>
</x-admin-layout>
