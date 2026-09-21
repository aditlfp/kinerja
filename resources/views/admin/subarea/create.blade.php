<x-admin-layout :fullWidth="true">
    @section('title', 'Tambah Sub Area')

    <div class="mx-auto w-full max-w-screen-md space-y-4 px-2 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Tambah Sub Area</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Masukkan satu atau beberapa sub area sekaligus.</p>
                </div>
                <a href="{{ route('admin.subarea.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Kembali</a>
            </div>
        </section>

        <form action="{{ route('admin.subarea.store') }}" method="POST" class="space-y-4">
            @csrf
            <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                <div id="inputContainer" class="space-y-2">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Name</label>
                    <input type="text" placeholder="Input Name..." class="input input-bordered w-full" name="name[]" />
                </div>
                <div class="mt-5 flex flex-wrap justify-between gap-2">
                    <button type="button" id="add" class="inline-flex h-10 items-center rounded-xl border border-amber-200 bg-amber-50 px-4 text-sm font-semibold text-amber-700 hover:bg-amber-100">Add Input</button>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.subarea.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Batal</a>
                        <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700">Simpan</button>
                    </div>
                </div>
            </section>
        </form>
    </div>
</x-admin-layout>
