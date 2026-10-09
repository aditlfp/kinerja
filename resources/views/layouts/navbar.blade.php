<nav class="pt-5 mx-5 mb-5 capitalize">
    <div class="flex justify-between w-full h-auto p-2 rounded-md shadow-md bg-slate-500">
        @auth
            @php
                $user = Auth::user();
                $defaultImg = asset('/logo/person.png');
                $imgSrc = match (true) {
                    $user->image === 'no-image.jpg' => $defaultImg,
                    Storage::disk('public')->exists("images/{$user->image}") => asset("storage/images/{$user->image}"),
                    Storage::disk('public')->exists("user/{$user->image}") => asset("storage/user/{$user->image}"),
                    default => $defaultImg,
                };
                $userKontrak = \App\Models\Kontrak::where('nama_pk_kda', $user->nama_lengkap)->latest()->first();
                $hasSignedContract = $userKontrak && $userKontrak->ttd && $userKontrak->ttd_atasan;
            @endphp

            <a href="{{ route('profile.index') }}" class="flex items-center gap-2"
                style="width: {{ $user->role_id == 2 ? '70%' : '85%' }};">
                <div class="relative shrink-0" style="width: 40px; height: 40px;">
                    <img class="block object-cover object-center rounded-full p-[2px] shadow-md bg-slate-300 shadow-slate-600 hover:shadow-none transition-all duration-200 ease-in-out"
                        src="{{ $imgSrc }}" alt="profile-logo" style="width: 40px; height: 40px;" />
                    @if ($hasSignedContract)
                        <span
                            class="absolute bottom-0 right-0 flex items-center justify-center rounded-full bg-green-500 border-2 border-slate-500 shadow-md z-10"
                            style="width: 18px; height: 18px;">
                            <i class="ri-check-line text-white" style="font-size: 11px; line-height: 1;"></i>
                        </span>
                    @endif
                </div>

                <div class="flex flex-row gap-1 min-w-0">
                    <p class="text-sm font-semibold text-white wrap-break-word line-clamp-1">
                        {{ ucwords(strtolower($user->nama_lengkap)) }}</p>
                </div>
            </a>
        @endauth

        @auth
            {{-- Mobile Slip Gaji Button --}}
            <div class="flex items-center ml-5 md:hidden" style="width: 28%;">
                <form action="{{ route('slip-gaji.index') }}" method="get">
                    <input type="hidden" name="bulan" value="{{ now()->subMonth()->format('Y-m') }}" />
                    <button class="overflow-hidden btn btn-sm btn-warning">
                        <span class="flex items-center gap-1 overflow-hidden">
                            <i class="w-4 h-4 ri-bank-card-line"></i>
                            <p class="overflow-hidden text-center">Slip</p>
                        </span>
                    </button>
                </form>
            </div>

            {{-- Desktop Navigation --}}
            <div class="hidden gap-3 md:flex mr-7">
                @if ($user->role_id != 2)
                    <x-nav-link :href="route('dashboard.index')" :active="request()->routeIs('dashboard.index')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                @endif

                {{-- Admin Tool --}}
                @if ($user->role_id == 2)
                    <x-nav-link :href="route('admin.index')" :active="request()->routeIs('admin.index')">
                        {{ __('Admin Tool') }}
                    </x-nav-link>
                    <x-nav-link :href="route('admin.slip.index')" :active="request()->routeIs('admin.slip.index')">
                        {{ __('Slip Gaji') }}
                    </x-nav-link>
                @else
                    <x-nav-link :href="route('slip-gaji.index', ['bulan' => now()->subMonth()->format('Y-m')])" :active="request()->routeIs('slip-gaji.index')">
                        {{ __('Slip Gaji') }}
                    </x-nav-link>
                @endif

                {{-- Logout --}}
                <form action="{{ route('logout') }}" method="post" onsubmit="sessionStorage.removeItem('leaderRekapMode')">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center px-2 mt-4 font-semibold transition duration-200 ease-in-out bg-yellow-300 rounded-md shadow-md text-slate-700 hover:bg-yellow-400 hover:text-white">
                        Logout
                    </button>
                </form>
            </div>
        @else
            {{-- Guest (Login Button) --}}
            <div class="gap-3 md:flex mr-7">
                <x-nav-link class="px-5 py-1 mr-5" :href="route('login')" :active="true">
                    {{ __('Login') }}
                </x-nav-link>
            </div>
        @endauth
    </div>
</nav>
