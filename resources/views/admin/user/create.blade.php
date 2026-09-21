<x-admin-layout :fullWidth="true">
    @section('title', 'Tambah User')

    <div
        x-data="{
            density: 'normal',
            init() {
                const params = new URLSearchParams(window.location.search);
                const queryDensity = params.get('density');
                const savedDensity = localStorage.getItem('create_user_density');
                if (queryDensity === 'ultra_compact' || queryDensity === 'normal') {
                    this.density = queryDensity;
                } else if (savedDensity === 'ultra_compact' || savedDensity === 'normal') {
                    this.density = savedDensity;
                }
                this.persistDensity();
            },
            setDensity(mode) {
                this.density = mode;
                this.persistDensity();
            },
            persistDensity() {
                localStorage.setItem('create_user_density', this.density);
            }
        }"
        x-init="init()"
        class="mx-auto w-full max-w-screen px-1 sm:px-2 lg:px-4"
        :class="density === 'ultra_compact' ? 'space-y-2' : 'space-y-5'"
    >
        <section
            class="border border-gray-100/80 bg-white/90 shadow-sm backdrop-blur-sm"
            :class="density === 'ultra_compact' ? 'rounded-lg p-3' : 'rounded-2xl p-6'"
        >
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]"
                        :class="density === 'ultra_compact' ? 'text-lg' : 'text-2xl'">Tambah User</h1>
                    <p class="mt-1 text-[var(--md-sys-color-on-surface-variant)]"
                        :class="density === 'ultra_compact' ? 'text-[11px]' : 'text-sm'">
                        Mode `Normal` untuk kenyamanan visual, mode `Ultra Compact` untuk input cepat.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1">
                        <button
                            type="button"
                            @click="setDensity('ultra_compact')"
                            class="rounded-md px-2.5 py-1 text-xs font-semibold transition"
                            :class="density === 'ultra_compact' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-50'"
                        >
                            Ultra Compact
                        </button>
                        <button
                            type="button"
                            @click="setDensity('normal')"
                            class="rounded-md px-2.5 py-1 text-xs font-semibold transition"
                            :class="density === 'normal' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-50'"
                        >
                            Normal
                        </button>
                    </div>

                    <a
                        href="{{ route('admin.user.index') }}"
                        class="inline-flex items-center border border-gray-200 bg-white font-semibold text-gray-700 transition hover:bg-gray-50"
                        :class="density === 'ultra_compact' ? 'rounded-lg px-3 py-1.5 text-xs' : 'rounded-xl px-4 py-2 text-sm'"
                    >
                        Kembali ke Data User
                    </a>
                </div>
            </div>
        </section>

        <form
            method="POST"
            action="{{ route('admin.user.store') }}"
            id="form"
            enctype="multipart/form-data"
            :class="density === 'ultra_compact' ? 'space-y-2' : 'space-y-5'"
        >
            @csrf

            <div
                :class="density === 'ultra_compact'
                    ? 'grid gap-2 xl:grid-cols-[1fr_220px]'
                    : 'grid gap-5 xl:grid-cols-[1fr_320px]'"
            >
                <section
                    class="border border-gray-100/80 bg-white/95 shadow-sm"
                    :class="density === 'ultra_compact' ? 'rounded-lg p-3' : 'rounded-2xl p-6'"
                >
                    <h3 class="font-semibold uppercase tracking-wide text-gray-700" :class="density === 'ultra_compact' ? 'text-[11px]' : 'text-sm'">Form User</h3>

                    <div
                        :class="density === 'ultra_compact'
                            ? 'mt-2 grid gap-2 md:grid-cols-2 xl:grid-cols-3'
                            : 'mt-4 grid gap-4 md:grid-cols-2'"
                    >
                        <div>
                            <label for="name" class="font-semibold text-gray-700" :class="density === 'ultra_compact' ? 'mb-1 block text-[11px]' : 'mb-1.5 block text-sm'">Username</label>
                            <input
                                id="name"
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                required
                                autocomplete="name"
                                placeholder="Username (available: {{ $lastUser ?? '-' }})"
                                class="input input-bordered w-full border-gray-200 bg-white focus:outline-none"
                                :class="density === 'ultra_compact' ? 'input-sm h-9 text-xs' : 'h-11 text-sm'"
                            />
                            <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
                        </div>

                        <div>
                            <label for="nama_lengkap" class="font-semibold text-gray-700" :class="density === 'ultra_compact' ? 'mb-1 block text-[11px]' : 'mb-1.5 block text-sm'">Nama Lengkap</label>
                            <input
                                id="nama_lengkap"
                                type="text"
                                name="nama_lengkap"
                                value="{{ old('nama_lengkap') }}"
                                required
                                autocomplete="nama_lengkap"
                                placeholder="Nama lengkap"
                                class="input input-bordered w-full border-gray-200 bg-white focus:outline-none"
                                :class="density === 'ultra_compact' ? 'input-sm h-9 text-xs' : 'h-11 text-sm'"
                            />
                            <x-input-error :messages="$errors->get('nama_lengkap')" class="mt-1.5" />
                        </div>

                        <div>
                            <label for="email" class="font-semibold text-gray-700" :class="density === 'ultra_compact' ? 'mb-1 block text-[11px]' : 'mb-1.5 block text-sm'">Email</label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autocomplete="username"
                                placeholder="Email user"
                                class="input input-bordered w-full border-gray-200 bg-white focus:outline-none"
                                :class="density === 'ultra_compact' ? 'input-sm h-9 text-xs' : 'h-11 text-sm'"
                            />
                            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                        </div>

                        <div>
                            <label for="no_hp" class="font-semibold text-gray-700" :class="density === 'ultra_compact' ? 'mb-1 block text-[11px]' : 'mb-1.5 block text-sm'">No. HP</label>
                            <input
                                id="no_hp"
                                type="text"
                                name="no_hp"
                                value="{{ old('no_hp') }}"
                                maxlength="14"
                                autocomplete="no_hp"
                                placeholder="No HP aktif"
                                class="input input-bordered w-full border-gray-200 bg-white focus:outline-none"
                                :class="density === 'ultra_compact' ? 'input-sm h-9 text-xs' : 'h-11 text-sm'"
                            />
                            <x-input-error :messages="$errors->get('no_hp')" class="mt-1.5" />
                        </div>

                        <div>
                            <label for="NIK" class="font-semibold text-gray-700" :class="density === 'ultra_compact' ? 'mb-1 block text-[11px]' : 'mb-1.5 block text-sm'">NIK</label>
                            <input
                                id="NIK"
                                type="text"
                                name="nik"
                                value="{{ old('nik') }}"
                                maxlength="16"
                                pattern="[0-9]*"
                                autocomplete="nik"
                                placeholder="NIK"
                                class="input input-bordered w-full border-gray-200 bg-white focus:outline-none"
                                :class="density === 'ultra_compact' ? 'input-sm h-9 text-xs' : 'h-11 text-sm'"
                            />
                            <x-input-error :messages="$errors->get('nik')" class="mt-1.5" />
                        </div>

                        <div>
                            <label for="kerjasama_id" class="font-semibold text-gray-700" :class="density === 'ultra_compact' ? 'mb-1 block text-[11px]' : 'mb-1.5 block text-sm'">Client</label>
                            <select
                                name="kerjasama_id"
                                id="kerjasama_id"
                                required
                                class="select select-bordered w-full border-gray-200 bg-white focus:outline-none"
                                :class="density === 'ultra_compact' ? 'select-sm h-9 text-xs' : 'h-11 text-sm'"
                            >
                                <option value="" disabled {{ old('kerjasama_id') ? '' : 'selected' }}>Pilih Client</option>
                                @foreach ($data as $i)
                                    <option value="{{ $i->id }}" {{ old('kerjasama_id') == $i->id ? 'selected' : '' }}>{{ $i->client->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('kerjasama_id')" class="mt-1.5" />
                        </div>

                        <div>
                            <label for="devisi_id" class="font-semibold text-gray-700" :class="density === 'ultra_compact' ? 'mb-1 block text-[11px]' : 'mb-1.5 block text-sm'">Divisi</label>
                            <select
                                name="devisi_id"
                                id="devisi_id"
                                required
                                class="select select-bordered w-full border-gray-200 bg-white focus:outline-none"
                                :class="density === 'ultra_compact' ? 'select-sm h-9 text-xs' : 'h-11 text-sm'"
                            >
                                <option value="" disabled {{ old('devisi_id') ? '' : 'selected' }}>Pilih Divisi</option>
                                @foreach ($dev as $i)
                                    <option value="{{ $i->id }}" {{ old('devisi_id') == $i->id ? 'selected' : '' }}>{{ $i->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('devisi_id')" class="mt-1.5" />
                        </div>

                        <div>
                            <label for="jabatan_id" class="font-semibold text-gray-700" :class="density === 'ultra_compact' ? 'mb-1 block text-[11px]' : 'mb-1.5 block text-sm'">Jabatan</label>
                            <select
                                name="jabatan_id"
                                id="jabatan_id"
                                required
                                class="select select-bordered w-full border-gray-200 bg-white focus:outline-none"
                                :class="density === 'ultra_compact' ? 'select-sm h-9 text-xs' : 'h-11 text-sm'"
                            >
                                <option value="" disabled {{ old('jabatan_id') ? '' : 'selected' }}>Pilih Jabatan</option>
                                @foreach ($jabatan as $i)
                                    <option value="{{ $i->id }}" {{ old('jabatan_id') == $i->id ? 'selected' : '' }}>{{ $i->name_jabatan }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('jabatan_id')" class="mt-1.5" />
                        </div>

                        <div>
                            <label for="password" class="font-semibold text-gray-700" :class="density === 'ultra_compact' ? 'mb-1 block text-[11px]' : 'mb-1.5 block text-sm'">Password</label>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                autocomplete="new-password"
                                placeholder="Password"
                                class="input input-bordered w-full border-gray-200 bg-white focus:outline-none"
                                :class="density === 'ultra_compact' ? 'input-sm h-9 text-xs' : 'h-11 text-sm'"
                            />
                            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
                        </div>

                        <div>
                            <label for="password_confirmation" class="font-semibold text-gray-700" :class="density === 'ultra_compact' ? 'mb-1 block text-[11px]' : 'mb-1.5 block text-sm'">Confirm Password</label>
                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                                placeholder="Konfirmasi password"
                                class="input input-bordered w-full border-gray-200 bg-white focus:outline-none"
                                :class="density === 'ultra_compact' ? 'input-sm h-9 text-xs' : 'h-11 text-sm'"
                            />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
                        </div>
                    </div>
                </section>

                <section
                    class="h-fit border border-gray-100/80 bg-white/95 shadow-sm xl:sticky xl:top-20"
                    :class="density === 'ultra_compact' ? 'rounded-lg p-3' : 'rounded-2xl p-5'"
                >
                    <h3 class="font-semibold uppercase tracking-wide text-gray-700" :class="density === 'ultra_compact' ? 'text-[11px]' : 'text-sm'">Foto Profil</h3>
                    <div class="space-y-2" :class="density === 'ultra_compact' ? 'mt-2' : 'mt-4'">
                        <div class="flex items-center justify-center">
                            <img
                                id="previewImage"
                                src="{{ asset('/logo/person.png') }}"
                                alt="Preview"
                                class="border border-gray-200 object-cover object-center shadow-sm"
                                :class="density === 'ultra_compact' ? 'h-24 w-24 rounded-lg' : 'h-36 w-36 rounded-2xl'"
                            />
                        </div>
                        <input
                            id="img"
                            type="file"
                            name="image"
                            accept="image/*"
                            class="file-input file-input-bordered w-full border-gray-200 bg-white focus:outline-none"
                            :class="density === 'ultra_compact' ? 'file-input-sm text-xs' : 'text-sm'"
                        />
                        <p class="text-gray-500" :class="density === 'ultra_compact' ? 'text-[10px]' : 'text-xs'">Format JPG/PNG proporsional.</p>
                        <x-input-error :messages="$errors->get('image')" class="mt-1.5" />
                    </div>
                </section>
            </div>

            <div class="flex justify-end gap-2" :class="density === 'ultra_compact' ? 'pb-1' : 'pb-2'">
                <a
                    href="{{ route('admin.user.index') }}"
                    class="inline-flex items-center border border-gray-200 bg-white font-semibold text-gray-700 transition hover:bg-gray-50"
                    :class="density === 'ultra_compact' ? 'rounded-lg px-3 py-1.5 text-xs' : 'rounded-xl px-4 py-2 text-sm'"
                >
                    Batal
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center bg-blue-600 font-semibold text-white transition hover:bg-blue-700"
                    :class="density === 'ultra_compact' ? 'rounded-lg px-3 py-1.5 text-xs' : 'rounded-xl px-4 py-2 text-sm'"
                >
                    Simpan User
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <style>
            [x-cloak] { display: none !important; }
        </style>
        <script>
            $(function() {
                $('#img').on('change', function(e) {
                    const file = e.target.files && e.target.files[0];
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        $('#previewImage').attr('src', evt.target.result);
                    };
                    reader.readAsDataURL(file);
                });
            });
        </script>
    @endpush
</x-admin-layout>
