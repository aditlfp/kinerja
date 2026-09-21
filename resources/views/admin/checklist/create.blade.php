<x-admin-layout :fullWidth="true">
    @section('title', 'Tambah Checklist')

    <div class="mx-auto w-full max-w-screen-md space-y-4 px-2 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Tambah Checklist</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Catat kondisi kebersihan berdasarkan area dan sub area.</p>
                </div>
                <a href="{{ route('admin.checklist.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Kembali</a>
            </div>
        </section>

        <form method="POST" action="{{ route('admin.checklist.store') }}" class="space-y-4" id="form">
            @csrf
            <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                <div class="space-y-4">
                    <div>
                        <label for="area" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Nama Area</label>
                        <select name="area" id="area" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none">
                            <option selected disabled>~ Pilih Area ~</option>
                            @php $renderedAreas = []; @endphp
                            @forelse($areasub as $ar)
                                @if (!in_array($ar->area->nama_area, $renderedAreas))
                                    <option value="{{ $ar->area->nama_area }}">{{ $ar->area?->nama_area }}</option>
                                    @php $renderedAreas[] = $ar->area->nama_area; @endphp
                                @endif
                            @empty
                                <option disabled>~ Kosong ~</option>
                            @endforelse
                        </select>
                        <x-input-error :messages="$errors->get('area')" class="mt-2" />
                    </div>
                    <div>
                        <label for="subarea" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Nama Sub Area</label>
                        <select name="sub_area" id="subarea" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none">
                            <option selected disabled>~ Pilih Sub Area ~</option>
                            @forelse($areasub as $ar)
                                <option value="{{ $ar->subarea?->name }}" data-name="{{ $ar->area->nama_area }}">{{ $ar->subarea?->name }}</option>
                            @empty
                                <option disabled>~ Kosong ~</option>
                            @endforelse
                        </select>
                        <x-input-error :messages="$errors->get('subarea')" class="mt-2" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Tingkat Kebersihan</label>
                        <div class="space-y-2 rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700">
                            <label class="flex items-center gap-2"><input type="radio" name="tingkat_bersih" value="bersih" class="radio"/>Bersih</label>
                            <label class="flex items-center gap-2"><input type="radio" name="tingkat_bersih" value="cukup" class="radio"/>Cukup</label>
                            <label class="flex items-center gap-2"><input type="radio" name="tingkat_bersih" value="kurang" class="radio"/>Kurang</label>
                        </div>
                        <x-input-error :messages="$errors->get('tingkat_kebersihan')" class="mt-2" />
                    </div>
                </div>
                <div class="mt-5 flex justify-end gap-2">
                    <a href="{{ route('admin.checklist.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Batal</a>
                    <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700">Simpan Checklist</button>
                </div>
            </section>
        </form>
    </div>

    @push('scripts')
        <script>
            $(function() {
                $('#subarea option[data-name]').hide();
                $('#area').on('change', function() {
                    const selectedArea = $(this).val();
                    $('#subarea').val('');
                    $('#subarea option[data-name]').each(function() {
                        $(this).toggle($(this).data('name') === selectedArea);
                    });
                });
            });
        </script>
    @endpush
</x-admin-layout>
