<x-admin-layout :fullWidth="true">
    @section('title', 'Edit Client')

    <div class="mx-auto w-full max-w-screen-xl space-y-4 px-2 sm:px-3 lg:px-4">
        <section class="px-0.5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Edit Client</h1>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">Perbarui data client agar informasi kontak dan alamat selalu valid.</p>
                </div>
                <a href="{{ route('admin.client.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                    Kembali
                </a>
            </div>
        </section>

        <form action="{{ route('admin.client.update', [$client->id]) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @method('PUT')
            @csrf
            <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="name" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Nama Client</label>
                        <input id="name" type="text" name="name" value="{{ $client->name }}" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none" autocomplete="name" />
                    </div>
                    <div>
                        <label for="panggilan" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Nama Singkat</label>
                        <input id="panggilan" type="text" name="panggilan" value="{{ $client->panggilan }}" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none" autocomplete="panggilan" />
                    </div>
                    <div class="md:col-span-2">
                        <label for="address" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Alamat</label>
                        <input id="address" type="text" name="address" value="{{ $client->address }}" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none" autocomplete="address" />
                    </div>
                    <div>
                        <label for="province" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Provinsi</label>
                        <input id="province" type="text" name="province" value="{{ $client->province }}" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none" autocomplete="province" />
                    </div>
                    <div>
                        <label for="kabupaten" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Kabupaten</label>
                        <input id="kabupaten" type="text" name="kabupaten" value="{{ $client->kabupaten }}" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none" autocomplete="kabupaten" />
                    </div>
                    <div>
                        <label for="zipcode" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Kode Pos</label>
                        <input id="zipcode" type="text" name="zipcode" value="{{ $client->zipcode }}" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none" autocomplete="zipcode" />
                    </div>
                    <div>
                        <label for="email" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Email</label>
                        <input id="email" type="text" name="email" value="{{ $client->email }}" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none" autocomplete="email" />
                    </div>
                    <div>
                        <label for="phone" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">No. Telepon</label>
                        <input id="phone" type="text" name="phone" value="{{ $client->phone }}" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none" autocomplete="phone" />
                    </div>
                    <div>
                        <label for="fax" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">No. Fax</label>
                        <input id="fax" type="text" name="fax" value="{{ $client->fax }}" class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-800 focus:border-blue-300 focus:bg-white focus:outline-none" autocomplete="fax" />
                    </div>
                </div>

                <div class="mt-4">
                    <label for="img" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-600">Logo Client</label>
                    <div class="mb-2">
                        @if ($client->logo == null)
                            <img id="logoPreview" class="h-24 w-40 rounded-lg border border-gray-200 object-cover" src="{{ URL::asset('logo/no-image.jpg') }}" alt="Image Profil">
                        @else
                            <img id="logoPreview" class="h-24 w-40 rounded-lg border border-gray-200 object-cover" src="{{ asset('storage/images/' . $client->logo) }}" alt="Image Profil">
                        @endif
                    </div>
                    <input type="text" class="hidden" value="{{ $client->logo }}" id="oldimage" name="oldimage">
                    <input id="img" name="logo" type="file" class="file-input file-input-bordered w-full">
                    <x-input-error class="mt-2" :messages="$errors->get('logo')" />
                </div>

                <div class="mt-5 flex justify-end gap-2">
                    <a href="{{ route('admin.client.index') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Batal</a>
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
                        $('#logoPreview').attr('src', ev.target.result);
                    };
                    reader.readAsDataURL(file);
                });
            });
        </script>
    @endpush
</x-admin-layout>
