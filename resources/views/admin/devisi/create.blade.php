<x-admin-layout :fullWidth="true">
    @section('title', 'Tambah Divisi')

    <div class="w-full max-w-screen-lg px-2 mx-auto space-y-4 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Tambah Divisi</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Buat divisi baru dan tautkan ke jabatan utama.</p>
                </div>
                <a href="{{ route('admin.divisi.index') }}" class="inline-flex items-center h-10 px-4 text-sm font-semibold text-gray-700 transition bg-white border border-gray-200 rounded-xl hover:bg-gray-50">
                    Kembali
                </a>
            </div>
        </section>

        <form method="POST" action="{{ route('admin.divisi.store') }}" id="form" class="space-y-4">
            @csrf
            <section class="p-4 bg-white border border-gray-100 shadow-sm rounded-2xl sm:p-5">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="name" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Nama Divisi</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                            class="w-full h-10 px-3 text-sm text-gray-800 border border-gray-200 rounded-xl bg-gray-50 focus:border-blue-300 focus:bg-white focus:outline-none" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
                    </div>

                    <div>
                        <label for="jabatan_id" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Jabatan</label>
                        <select name="jabatan_id" id="jabatan_id" required class="w-full h-10 px-3 text-sm text-gray-800 border border-gray-200 rounded-xl bg-gray-50 focus:border-blue-300 focus:bg-white focus:outline-none">
                            <option value="" disabled {{ old('jabatan_id') ? '' : 'selected' }}>~ Pilih Jabatan ~</option>
                            @foreach ($jabatan as $i)
                                <option value="{{ $i->id }}" {{ old('jabatan_id') == $i->id ? 'selected' : '' }}>{{ $i->code_jabatan }} | {{ $i->name_jabatan }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('jabatan_id')" class="mt-1.5" />
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-5">
                    <a href="{{ route('admin.divisi.index') }}" class="inline-flex items-center h-10 px-4 text-sm font-semibold text-gray-700 transition bg-white border border-gray-200 rounded-xl hover:bg-gray-50">Batal</a>
                    <button type="submit" class="inline-flex items-center h-10 px-4 text-sm font-semibold text-white transition bg-blue-600 rounded-xl hover:bg-blue-700">Simpan Divisi</button>
                </div>
            </section>
        </form>
    </div>
</x-admin-layout>
