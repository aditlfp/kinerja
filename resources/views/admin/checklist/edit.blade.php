<x-admin-layout :fullWidth="true">
    @section('title', 'Edit Checklist')

    <div class="mx-auto w-full max-w-screen-md space-y-4 px-2 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Edit Checklist</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Perbarui area, sub area, dan tingkat kebersihan.</p>
                </div>
                <a href="{{ route('admin.checklist.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Kembali</a>
            </div>
        </section>

        <form method="POST" action="{{ route('admin.checklist.update', $checkId->id) }}" class="space-y-4" id="form">
            @csrf
            @method('PATCH')
            <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                <div class="space-y-4">
                    <div>
                        <label for="area" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Nama Area</label>
                        <input id="area" type="text" name="area" value="{{ old('area', $checkId->area) }}" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none" />
                    </div>
                    <div>
                        <label for="sub_area" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Nama Sub Area</label>
                        <input id="sub_area" type="text" name="sub_area" value="{{ old('sub_area', $checkId->sub_area) }}" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Tingkat Kebersihan</label>
                        <div class="space-y-2 rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700">
                            <label class="flex items-center gap-2"><input type="radio" name="tingkat_bersih" value="bersih" class="radio" {{ old('tingkat_bersih', $checkId->tingkat_bersih) === 'bersih' ? 'checked' : '' }}/>Bersih</label>
                            <label class="flex items-center gap-2"><input type="radio" name="tingkat_bersih" value="cukup" class="radio" {{ old('tingkat_bersih', $checkId->tingkat_bersih) === 'cukup' ? 'checked' : '' }}/>Cukup</label>
                            <label class="flex items-center gap-2"><input type="radio" name="tingkat_bersih" value="kurang" class="radio" {{ old('tingkat_bersih', $checkId->tingkat_bersih) === 'kurang' ? 'checked' : '' }}/>Kurang</label>
                        </div>
                    </div>
                </div>
                <div class="mt-5 flex justify-end gap-2">
                    <a href="{{ route('admin.checklist.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Batal</a>
                    <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700">Simpan Perubahan</button>
                </div>
            </section>
        </form>
    </div>
</x-admin-layout>
