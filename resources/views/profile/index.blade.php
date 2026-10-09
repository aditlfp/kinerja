<x-app-layout>
        <style>
            .profile-image-wrapper {
                border-radius: 50%;
                overflow: hidden;
                width: 100px;
                /* w-24 = 6rem = 96px */
                height: 100px;
                /* h-24 = 6rem = 96px */
                padding: 6px;
                display: flex;
                justify-content: center;
                align-items: flex-center;
                /* top-aligned */
            }

            .profile-image-wrapper .profile-image {
                width: 100%;
                height: 100%;
                border-radius: 50%;
                object-fit: cover;
                object-position: center;
                display: block;
            }
        </style>
        <div class="min-h-screen bg-slate-50 px-4 py-5 text-slate-800 md:flex md:flex-col md:items-center md:justify-center">
            <div class="mb-2 flex w-full max-w-sm items-center justify-between">
                <a href="{{ route('dashboard.index') }}">
                    <img src="{{ URL::asset('logo/logo_kinerja.png') }}" alt="Logo" class="h-auto w-32">
                </a>
                <span class="font-semibold text-sm text-slate-500/50">Profile Page</span>
            </div>
            <div class="my-5 w-full max-w-sm overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="">
                    <span class="mt-5 flex justify-end px-5">
                        <a href="{{ route('profile.edit', Auth::user()->id) }}"
                            class="inline-flex items-center gap-1 rounded-xl bg-amber-100 px-3 py-1.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-200 border border-amber-300/20">
                            <span class="flex">
                                <p class="font-semibold">Edit</p>
                                <i class="ri-edit-2-line"></i>
                            </span>
                        </a>
                    </span>
                    <div class="flex justify-center">
                        <h1 class="text-center text-lg font-bold text-slate-800">Informasi Profile</h1>
                    </div>
                    <div class="flex items-center py-4 justify-center">
                        <div
                            class="mx-2 my-2 overflow-hidden flex items-center justify-center bg-slate-200 rounded-full shadow-md hover:shadow-none transition-all duration-200 ease-in-out w-24 h-24 profile-image-wrapper">
                            @if (Auth::user()->image == 'no-image.jpg')
                                <img class="profile-image" src="{{ URL::asset('/logo/person.png') }}"
                                    alt="profile-logo.png">
                            @elseif(Storage::disk('public')->exists('images/' . Auth::user()->image))
                                <img class="profile-image" src="{{ asset('storage/images/' . Auth::user()->image) }}"
                                    alt="profile-logo.png">
                            @elseif(Storage::disk('public')->exists('user/' . Auth::user()->image))
                                <img class="profile-image" src="{{ asset('storage/user/' . Auth::user()->image) }}"
                                    alt="profile-logo.png">
                            @else
                                <img class="profile-image" src="{{ URL::asset('/logo/person.png') }}"
                                    alt="profile-logo.png">
                            @endif
                        </div>
                    </div>

                    <div class="m-2 rounded-xl bg-slate-50 p-2 py-4 text-sm font-semibold ring-1 ring-slate-200">
                        <div class="text-slate-800 space-y-2">
                            <div class="flex flex-col items-center gap-1">
                                <span class="font-semibold">Username: </span>
                                <span class="font-normal">{{ Auth::user()->name }}</span>
                            </div>
                            <div class="flex flex-col items-center gap-1">
                                <span class="font-semibold">Fullname: </span>
                                <span class="font-normal">{{ ucwords(strtolower(Auth::user()->nama_lengkap)) }}</span>
                            </div>
                            <div class="flex flex-col items-center gap-1">
                                <span class="font-semibold">Email: </span>
                                <span class="font-normal">{{ Auth::user()->email }}</span>
                            </div>
                            <div class="flex flex-col items-center gap-1">
                                <span class="font-semibold">NIK: </span>
                                <span class="font-normal">{{ Auth::user()->nik ? \Illuminate\Support\Facades\Crypt::decryptString(Auth::user()->nik) : '-' }}</span>
                            </div>
                            <div class="flex flex-col items-center gap-1">
                                <span class="font-semibold">No. Hp: </span>
                                <span class="font-normal">{{ Auth::user()->no_hp ? Auth::user()->no_hp : '-' }}</span>
                            </div>
                            <div class="flex flex-col items-center gap-1">
                                <span class="font-semibold">Jabatan: </span>
                                <span class="font-normal">
                                    @if (!Auth::user()->divisi?->jabatan)
                                        Kosong
                                    @else
                                        {{ Auth::user()->divisi?->jabatan?->name_jabatan }}
                                    @endif
                                </span>
                            </div>
                            <div class="flex flex-col items-center gap-1">
                                <span class="font-semibold">Penempatan: </span>
                                <span class="font-normal">
                                    @if (!Auth::user()->kerjasama?->client)
                                        Kosong
                                    @else
                                        {{ Auth::user()->kerjasama?->client?->name }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @if ($kontrak && $kontrak->send_to_atasan == 0 && $kontrak->ttd && $kontrak->ttd_atasan)
                    <div class="min-w-full flex justify-center items-center">
                        <a href="{{ route('form-kontrak-preview', ['token' => \Illuminate\Support\Facades\Crypt::encryptString($kontrak?->id)]) }}"
                            onclick="window.open(this.href, '_blank'); window.location.reload(); return false;"
                            class="mx-5 mb-2 bg-yellow-500 rounded-md p-2 px-5 font-semibold">Lihat Kontrak Saya
                            (Berlaku)</a>
                    </div>
                @endif
            </div>
            <div class="mb-4 flex justify-center">
                <button onclick="window.location.href='{{ route('dashboard.index') }}'"
                    class="rounded-lg bg-red-400 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-300 border border-red-500/20">
                    Kembali
                </button>
            </div>
        </div>
</x-app-layout>
