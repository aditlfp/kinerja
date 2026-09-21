<x-admin-layout :fullWidth="true">
    @section('title', 'Edit Divisi')

    <div class="mx-auto w-full max-w-screen-xl space-y-4 px-2 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Edit Divisi</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Perbarui data utama dan atur perlengkapan yang digunakan oleh divisi.</p>
                </div>
                <a href="{{ route('admin.divisi.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                    Kembali
                </a>
            </div>
        </section>

        <form action="{{ url('divisi/' . $data->id) }}" method="POST" class="space-y-4">
            @method('PUT')
            @csrf

            <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="name" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Nama Divisi</label>
                        <input id="name" type="text" name="name" value="{{ old('name', $data->name) }}" required
                            class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none" />
                    </div>

                    <div>
                        <label for="jabatan_id" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Jabatan</label>
                        <select name="jabatan_id" id="jabatan_id" required class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none">
                            <option value="" disabled>~ Pilih Jabatan ~</option>
                            @foreach ($jabatan as $i)
                                <option value="{{ $i->id }}" {{ old('jabatan_id', $data->jabatan_id) == $i->id ? 'selected' : '' }}>{{ $i->code_jabatan }} | {{ $i->name_jabatan }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                <div class="mb-3">
                    <h2 class="text-sm font-semibold text-[var(--md-sys-color-on-surface)]">Perlengkapan</h2>
                    <p class="mt-1 text-xs text-gray-500">Item yang sudah terpasang bisa ditandai untuk dihapus dari divisi.</p>
                </div>
                <div class="grid gap-2.5 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($alat as $i)
                        @php
                            $isChecked = $lengkapan->contains('perlengkapan_id', $i->id);
                        @endphp

                        <div class="rounded-xl border {{ $isChecked ? 'border-blue-200 bg-blue-50/40' : 'border-gray-200 bg-white' }} p-3">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" {{ $isChecked ? '' : 'name=perlengkapan_id[]' }} value="{{ $i->id }}" class="checkbox checkbox-sm" {{ $isChecked ? 'checked' : '' }} />
                                <span class="font-medium">{{ $i->name }}</span>
                            </label>

                            @if($isChecked)
                                <label class="mt-2 inline-flex items-center gap-2 rounded-lg bg-red-50 px-2 py-1 text-[11px] font-medium text-red-700">
                                    <input type="checkbox" name="delete_alat[]" value="{{ $i->id }}" class="checkbox checkbox-xs" />
                                    <span>Hapus dari divisi</span>
                                </label>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="mt-5 flex justify-end gap-2">
                    <a href="{{ route('admin.divisi.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Batal</a>
                    <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700">Simpan Perubahan</button>
                </div>
            </section>
        </form>
    </div>
</x-admin-layout>
