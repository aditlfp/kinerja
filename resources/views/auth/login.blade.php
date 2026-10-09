<x-guest-layout>
    <div class="bg-white/80 glass border border-amber-100 rounded-2xl p-6 shadow-xl">
        <div class="flex flex-col items-center gap-3">
            <img src="{{ asset('logo/sac.png') }}" alt="SAC" class="w-24">
            <h2 class="text-lg font-extrabold text-amber-900">Kinerja - SAC</h2>
            <p class="text-sm text-stone-700 font-medium">Masuk ke Sistem Kinerja</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4" novalidate>
            @csrf

            <div>
                <label for="name" class="block text-xs font-semibold text-stone-700">Nama Pengguna</label>
                <div class="mt-1">
                    <input id="name" name="name" value="{{ old('name') }}" type="text" required autofocus
                        class="w-full rounded-lg border border-amber-200 px-3 py-2 focus:outline-hidden focus:ring-2 focus:ring-amber-300 transition"
                        placeholder="Masukkan Username..." />
                </div>
                @error('name')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div x-data="{ show: false }">
                <label for="password" class="block text-xs font-semibold text-stone-700">Kata Sandi</label>
                <div class="mt-1 relative">
                    <input id="password" name="password" :type="show ? 'text' : 'password'" required
                        class="w-full rounded-lg border border-amber-200 px-3 py-2 pr-10 focus:outline-hidden focus:ring-2 focus:ring-amber-300 transition"
                        placeholder="Masukkan Password..." />
                    <button type="button" @click="show = !show" :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-xs text-amber-700 hover:text-amber-900">
                        <!-- Eye (when hidden) -->
                        <i class="ri-eye-line text-xl" x-show="!show"></i>

                        <!-- Eye Off (when shown) -->
                        <i class="ri-eye-off-line text-xl" x-show="show"></i>
                    </button>
                </div>
                @error('password')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="remember" class="rounded-sm text-amber-500 focus:ring-0" />
                    <span class="text-stone-700">Ingat saya</span>
                </label>
            </div>

            <div>
                <button type="submit"
                    class="w-full btnLogin bg-amber-500 hover:bg-amber-600 text-white font-semibold px-4 py-2 rounded-lg shadow-sm">
                    Masuk
                </button>
            </div>
        </form>

        <div class="mt-4 text-center text-xs text-stone-600">
            Belum punya akun? <a href="https://www.absensi-sac.sac-po.com/kontrak-baru" target="_blank" rel="noopener"
                class="text-amber-600 hover:underline">Buat Akun</a>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('form').on('submit', function() {
                $('.btnLogin').html(
                    '<div class="animate-pulse flex justify-center items-center gap-2"><svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Tunggu...</div>'
                ).attr('disabled', true);
            });
        });
    </script>
</x-guest-layout>
