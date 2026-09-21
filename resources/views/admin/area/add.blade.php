<x-admin-layout :fullWidth="true">
    @section('title', 'Tambah Sub Area')

    <div class="mx-auto w-full max-w-screen-lg space-y-4 px-2 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Tambah Sub Area</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Pilih sub area yang akan dikaitkan ke area ini.</p>
                </div>
                <a href="{{ route('area.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Kembali</a>
            </div>
        </section>

        <form action="{{ route('add.subarea', $area->id) }}" method="POST" class="space-y-4">
            @csrf
            <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                <div class="grid gap-2.5 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($sub as $i)
                        <label class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-700">
                            <input type="checkbox" name="subarea_id[]" value="{{ $i->id }}" class="checkbox checkbox-sm"/>
                            <span>{{ $i->name }}</span>
                        </label>
                    @endforeach
                </div>
                <div class="mt-5 flex justify-end gap-2">
                    <a class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50" href="{{ route('area.index') }}">Batal</a>
                    <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700">Simpan Pilihan</button>
                </div>
            </section>
        </form>
    </div>
</x-admin-layout>
