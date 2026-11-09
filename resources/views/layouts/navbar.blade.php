<nav class="px-2 pt-3 pb-1 sm:px-4 sm:pt-4 md:px-5 md:pt-5">
    <div
        class="mx-auto flex min-h-[56px] w-full max-w-screen-2xl items-center gap-2
               rounded-2xl border border-slate-400/70 bg-slate-500 px-2 py-1.5
               shadow-[0_8px_30px_rgb(15_23_42/0.08)] backdrop-blur-xl
               sm:min-h-[64px] sm:gap-3 sm:px-3 sm:py-2
"
    >

        @auth
            @php
                $user = Auth::user();

                $defaultImg = asset('/logo/person.png');

                $imgSrc = match (true) {
                    $user->image === 'no-image.jpg' => $defaultImg,
                    Storage::disk('public')->exists("images/{$user->image}")
                        => asset("storage/images/{$user->image}"),
                    Storage::disk('public')->exists("user/{$user->image}")
                        => asset("storage/user/{$user->image}"),
                    default => $defaultImg,
                };

                $userKontrak = \Illuminate\Support\Facades\Cache::remember(
                    'navbar.kontrak.' . $user->id,
                    600,
                    fn () => \App\Models\Kontrak::query()
                        ->select(['id', 'nama_pk_kda', 'ttd', 'ttd_atasan', 'created_at'])
                        ->where('nama_pk_kda', $user->nama_lengkap)
                        ->latest('created_at')
                        ->first()
                );

                $hasSignedContract =
                    $userKontrak &&
                    $userKontrak->ttd &&
                    $userKontrak->ttd_atasan;
            @endphp

            {{-- Profile --}}
            <a
                href="{{ route('profile.index') }}"
                class="group flex min-w-0 flex-1 items-center gap-2 rounded-xl px-1.5 py-1
                       transition hover:bg-slate-400/70
                       sm:gap-3 sm:px-2 sm:py-1.5
"
            >
                {{-- Avatar --}}
                <div class="relative shrink-0">
                    <img
                        src="{{ $imgSrc }}"
                        alt="Profile"
                        class="h-9 w-9 rounded-full object-cover ring-2 ring-white/40
                               transition duration-200 group-hover:ring-white/80
                               sm:h-10 sm:w-10
"
                    >

                    @if ($hasSignedContract)
                        <span
                            class="absolute -bottom-0.5 -right-0.5 flex h-4 w-4 items-center
                                   justify-center rounded-full border-2 border-slate-500
                                   bg-emerald-500 text-white shadow-sm
"
                            title="Kontrak telah ditandatangani"
                        >
                            <i class="ri-check-line text-[10px] leading-none"></i>
                        </span>
                    @endif
                </div>

                {{-- User info --}}
                <div class="min-w-0">
                    <p
                        class="truncate text-sm font-semibold text-white
"
                    >
                        {{ ucwords(strtolower($user->nama_lengkap)) }}
                    </p>

                    <p
                        class="hidden text-xs text-slate-200 sm:block
"
                    >
                        {{ $user->role_id == 2 ? 'Administrator' : 'Employee' }}
                    </p>
                </div>

                <i
                    class="ri-arrow-right-s-line ml-auto hidden text-lg text-slate-400
                           transition-transform group-hover:translate-x-0.5 md:block"
                ></i>
            </a>

            {{-- Navigation: icon-only on tablet (md), labels from lg --}}
            <div class="hidden shrink-0 items-center gap-1 md:flex">

                @if ($user->role_id != 2)
                    <a
                        href="{{ route('dashboard.index') }}"
                        title="{{ __('Dashboard') }}"
                        aria-label="{{ __('Dashboard') }}"
                        @class([
                            'nav-item whitespace-nowrap',
                            'nav-item--active' => request()->routeIs('dashboard.index'),
                        ])
                    >
                        <i class="ri-dashboard-line text-lg"></i>
                        <span class="hidden lg:inline">{{ __('Dashboard') }}</span>
                    </a>
                @endif

                @if ($user->role_id == 2)

                    <a
                        href="{{ route('admin.index') }}"
                        title="{{ __('Admin Tool') }}"
                        aria-label="{{ __('Admin Tool') }}"
                        @class([
                            'nav-item whitespace-nowrap',
                            'nav-item--active' => request()->routeIs('admin.index'),
                        ])
                    >
                        <i class="ri-settings-3-line text-lg"></i>
                        <span class="hidden lg:inline">{{ __('Admin Tool') }}</span>
                    </a>

                    <a
                        href="{{ route('admin.slip.index') }}"
                        title="{{ __('Slip Gaji') }}"
                        aria-label="{{ __('Slip Gaji') }}"
                        @class([
                            'nav-item whitespace-nowrap',
                            'nav-item--active' => request()->routeIs('admin.slip.index'),
                        ])
                    >
                        <i class="ri-file-list-3-line text-lg"></i>
                        <span class="hidden lg:inline">{{ __('Slip Gaji') }}</span>
                    </a>

                @else

                    <a
                        href="{{ route('slip-gaji.index', [
                            'bulan' => now()->subMonth()->format('Y-m')
                        ]) }}"
                        title="{{ __('Slip Gaji') }}"
                        aria-label="{{ __('Slip Gaji') }}"
                        @class([
                            'nav-item nav-item--accent whitespace-nowrap',
                            'nav-item--active' => request()->routeIs('slip-gaji.index'),
                        ])
                    >
                        <i class="ri-bank-card-line text-lg"></i>
                        <span class="hidden lg:inline">{{ __('Slip Gaji') }}</span>
                    </a>

                @endif

                {{-- Logout --}}
                <form
                    action="{{ route('logout') }}"
                    method="post"
                    onsubmit="sessionStorage.removeItem('leaderRekapMode')"
                    class="ml-1 shrink-0"
                >
                    @csrf

                    <button
                        type="submit"
                        title="{{ __('Logout') }}"
                        aria-label="{{ __('Logout') }}"
                        class="inline-flex h-9 items-center gap-1.5 whitespace-nowrap rounded-lg px-3
                               text-sm font-medium text-white
                               transition hover:bg-red-500 hover:text-white
"
                    >
                        <i class="ri-logout-box-r-line text-lg"></i>
                        <span class="hidden lg:inline">{{ __('Logout') }}</span>
                    </button>
                </form>
            </div>

            {{-- Mobile navigation --}}
            <details class="relative shrink-0 md:hidden" id="mobile-navbar-menu">
                <summary class="flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-xl
                                text-white transition hover:bg-slate-400 active:scale-95
"
                         aria-label="{{ __('Open navigation') }}">
                    <i class="ri-menu-4-line text-xl"></i>
                </summary>

                <div class="absolute right-0 top-12 z-20 min-w-52 rounded-2xl border border-slate-200
                            bg-slate-50 p-2 shadow-xl shadow-slate-900/10">
                    @if ($user->role_id != 2)
                        <a href="{{ route('dashboard.index') }}" @class(['flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition hover:bg-slate-100', 'bg-slate-100' => request()->routeIs('dashboard.index')])>
                            <i class="ri-dashboard-line text-lg"></i>{{ __('Dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('admin.index') }}" @class(['flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition hover:bg-slate-100', 'bg-slate-100' => request()->routeIs('admin.index')])>
                            <i class="ri-settings-3-line text-lg"></i>{{ __('Admin Tool') }}
                        </a>
                    @endif
                    <a href="{{ $user->role_id == 2 ? route('admin.slip.index') : route('slip-gaji.index', ['bulan' => now()->subMonth()->format('Y-m')]) }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition hover:bg-yellow-50 hover:text-yellow-700">
                        <i class="ri-bank-card-line text-lg"></i>{{ __('Slip Gaji') }}
                    </a>
                    <div class="my-1 border-t border-slate-100"></div>
                    <form action="{{ route('logout') }}" method="post" onsubmit="sessionStorage.removeItem('leaderRekapMode')">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-red-600 transition hover:bg-red-50">
                            <i class="ri-logout-box-r-line text-lg"></i>{{ __('Logout') }}
                        </button>
                    </form>
                </div>
            </details>

        @else

            {{-- Guest --}}
            <div class="ml-auto shrink-0">
                <a
                    href="{{ route('login') }}"
                    class="inline-flex h-10 items-center gap-2 whitespace-nowrap rounded-xl
                           bg-slate-900 px-4 text-sm font-semibold text-white
                           shadow-sm transition
                           hover:bg-slate-800 hover:shadow-md
                           active:scale-[0.98]
                           sm:px-5

"
                >
                    <i class="ri-login-box-line"></i>
                    {{ __('Login') }}
                </a>
            </div>

        @endauth

    </div>
</nav>

<script>
    document.addEventListener('click', (event) => {
        const menu = document.getElementById('mobile-navbar-menu');
        if (menu?.open && !menu.contains(event.target)) menu.removeAttribute('open');
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') document.getElementById('mobile-navbar-menu')?.removeAttribute('open');
    });
</script>