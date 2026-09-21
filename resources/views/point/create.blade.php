<x-admin-layout :fullWidth="true">
    @section('title', 'Tambah Poin')

    <div class="mx-auto w-full max-w-screen-md space-y-4 px-2 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Tambah Data Poin</h1>
            <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Atur nilai poin berdasarkan client.</p>
        </section>

        <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
            <form method="POST" action="{{ route('admin.point.store') }}" class="space-y-4">
                @csrf
                <div>
                    <x-input-label for="client_id" :value="__('Nama Client')" />
                    <select name="client_id" id="client_id" class="mt-1 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-yellow-500 focus:outline-none">
                        <option selected disabled>~ Pilih Client ~</option>
                        @foreach ($client as $i)
                            <option value="{{ $i->id }}" {{ old('client_id') == $i->id ? 'selected' : '' }}>{{ $i->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="sac_point" :value="__('Jumlah Poin')" />
                    <input type="number" min="0" name="sac_point" id="sac_point" value="{{ old('sac_point') }}" class="mt-1 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 placeholder:text-gray-400 focus:border-yellow-500 focus:outline-none" placeholder="Masukkan jumlah poin..." />
                    <x-input-error :messages="$errors->get('sac_point')" class="mt-2" />
                </div>

                <div class="flex justify-end gap-2 pt-1">
                    <a href="{{ route('admin.point.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Batal</a>
                    <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-yellow-500 px-4 text-sm font-semibold text-white transition hover:bg-yellow-600">Simpan</button>
                </div>
            </form>
        </section>
    </div>
</x-admin-layout>
