<x-admin-layout :fullWidth="true">
    @section('title', 'Edit Berita')

    <div class="mx-auto w-full max-w-screen-lg space-y-4 px-2 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Edit Berita</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Perbarui periode tayang dan gambar berita.</p>
                </div>
                <a href="{{ route('admin.news.index') }}" class="inline-flex h-10 items-center rounded-xl border border-red-200 bg-red-50 px-4 text-sm font-semibold text-red-700 transition hover:bg-red-100">Kembali</a>
            </div>
        </section>

        <form method="POST" action="{{ route('admin.news.update', $newsId->id) }}" class="space-y-4" id="form" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                <div class="space-y-4">
                    <div>
                        <label for="img" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Foto Berita</label>
                        <div class="mb-2"><img id="newsPreview" class="h-24 w-40 rounded-lg border border-gray-200 object-cover" src="{{ asset('storage/images/' . $newsId->image) }}" alt="Preview"></div>
                        <input id="img" class="file-input file-input-bordered w-full" type="file" name="image" accept="image/*"/>
                        <x-input-error :messages="$errors->get('image1')" class="mt-2" />
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="tanggal_lihat" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Tanggal Berlaku</label>
                            <input type="date" name="tanggal_lihat" id="tanggal_lihat" value="{{ $newsId->tanggal_lihat }}" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none" required/>
                            <x-input-error :messages="$errors->get('tanggal_lihat')" class="mt-2" />
                        </div>
                        <div>
                            <label for="tanggal_tutup" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Tanggal Berakhir</label>
                            <input type="date" name="tanggal_tutup" id="tanggal_tutup" value="{{ $newsId->tanggal_tutup }}" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none" required/>
                            <x-input-error :messages="$errors->get('tanggal_tutup')" class="mt-2" />
                        </div>
                    </div>
                    <div class="rounded-2xl border border-gray-100 bg-gray-50/70 p-3">
                        <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-600">Muncul Setiap Tanggal</label>
                                <p class="mt-1 text-xs text-gray-500">Pilih tanggal 1-31. Berita hanya tampil pada tanggal yang dipilih.</p>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" data-check-all="tanggal_muncul[]" class="h-8 rounded-lg border border-blue-200 bg-blue-50 px-3 text-xs font-semibold text-blue-700 hover:bg-blue-100">Pilih semua</button>
                                <button type="button" data-clear-all="tanggal_muncul[]" class="h-8 rounded-lg border border-gray-200 bg-white px-3 text-xs font-semibold text-gray-600 hover:bg-gray-100">Hapus</button>
                            </div>
                        </div>
                        <div class="grid grid-cols-7 gap-2 sm:grid-cols-10">
                            @php $tanggalMuncul = old('tanggal_muncul', $newsId->tanggal_muncul ?? []); @endphp
                            @for ($i = 1; $i <= 31; $i++)
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="tanggal_muncul[]" value="{{ $i }}" class="peer sr-only" @checked(in_array($i, $tanggalMuncul))>
                                    <span class="flex h-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-sm font-semibold text-gray-700 shadow-sm transition peer-checked:border-blue-600 peer-checked:bg-blue-600 peer-checked:text-white peer-focus:ring-2 peer-focus:ring-blue-200 hover:border-blue-300 hover:bg-blue-50">{{ $i }}</span>
                                </label>
                            @endfor
                        </div>
                        <x-input-error :messages="$errors->get('tanggal_muncul')" class="mt-2" />
                    </div>
                </div>
                <div class="mt-5 flex justify-end gap-2">
                    <a href="{{ route('admin.news.index') }}" class="inline-flex h-10 items-center rounded-xl border border-red-200 bg-red-50 px-4 text-sm font-semibold text-red-700 transition hover:bg-red-100">Batal</a>
                    <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700">Simpan Perubahan</button>
                </div>
            </section>
        </form>
    </div>

    @push('scripts')
        <script>
            $(function() {
                $('#img').on('change', function(e) {
                    const file = e.target.files && e.target.files[0];
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        $('#newsPreview').attr('src', ev.target.result);
                    };
                    reader.readAsDataURL(file);
                });

                $('[data-check-all]').on('click', function() {
                    $(`[name="${$(this).data('check-all')}"]`).prop('checked', true);
                });

                $('[data-clear-all]').on('click', function() {
                    $(`[name="${$(this).data('clear-all')}"]`).prop('checked', false);
                });
            });
        </script>
    @endpush
</x-admin-layout>
