<x-admin-layout :fullWidth="true">
    @section('title', 'Tambah Perlengkapan')

    <div class="w-full max-w-screen-md px-2 mx-auto space-y-4 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Tambah Perlengkapan</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Masukkan satu atau beberapa nama perlengkapan sekaligus.</p>
                </div>
                <a href="{{ route('admin.perlengkapan.index') }}" class="inline-flex items-center h-10 px-4 text-sm font-semibold text-gray-700 transition bg-white border border-gray-200 rounded-xl hover:bg-gray-50">Kembali</a>
            </div>
        </section>

        <form action="{{ route('admin.perlengkapan.store') }}" method="POST" class="space-y-4">
            @csrf
            <section class="p-4 bg-white border border-gray-100 shadow-sm rounded-2xl sm:p-5">
                <div id="inputContainer" class="space-y-2">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Nama</label>
                    <input type="text" placeholder="Input Nama Perlengkapan..." class="w-full input input-bordered" name="name[]" />
                </div>
                <div class="flex flex-wrap justify-between gap-2 mt-5">
                    <button type="button" id="add" class="inline-flex items-center h-10 px-4 text-sm font-semibold border rounded-xl border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100">Add Input</button>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.perlengkapan.index') }}" class="inline-flex items-center h-10 px-4 text-sm font-semibold text-gray-700 transition bg-white border border-gray-200 rounded-xl hover:bg-gray-50">Batal</a>
                        <button type="submit" class="inline-flex items-center h-10 px-4 text-sm font-semibold text-white transition bg-blue-600 rounded-xl hover:bg-blue-700">Simpan</button>
                    </div>
                </div>
            </section>
        </form>
    </div>
</x-admin-layout>
