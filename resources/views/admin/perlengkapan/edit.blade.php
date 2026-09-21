<x-admin-layout :fullWidth="true">
    @section('title', 'Edit Perlengkapan')

    <div class="mx-auto w-full max-w-screen-md space-y-4 px-2 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Edit Perlengkapan</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Perbarui nama perlengkapan.</p>
                </div>
                <a href="{{ route('admin.perlengkapan.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Kembali</a>
            </div>
        </section>

        <form action="{{ route('admin.perlengkapan.update', $data->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PATCH')
            <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Name</label>
                <input type="text" value="{{ $data->name }}" placeholder="Input Name..." class="input input-bordered w-full" name="name[]" />
                <div class="mt-5 flex justify-end gap-2">
                    <a href="{{ route('admin.perlengkapan.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Batal</a>
                    <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700">Simpan Perubahan</button>
                </div>
            </section>
        </form>
    </div>
</x-admin-layout>
