<nav class="px-2 pt-3 pb-1 sm:px-4 sm:pt-4 md:px-5 md:pt-5">
    <div
        class="mx-auto flex min-h-[56px] w-full max-w-screen-2xl items-center gap-2
               rounded-2xl border px-2 py-1.5
               shadow-sm backdrop-blur-xl
               sm:min-h-[64px] sm:gap-3 sm:px-3 sm:py-2
               border-slate-500 bg-slate-500"
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

                $userKontrak = \App\Models\Kontrak::where(
                    'nama_pk_kda',
                    $user->nama_lengkap
                )->latest()->first();

                $hasSignedContract =
                    $userKontrak &&
                    $userKontrak->ttd &&
                    $userKontrak->ttd_atasan;
            @endphp

            {{-- Profile --}}
            <a
                href="{{ route('profile.index') }}"
                class="group flex min-w-0 flex-1 items-center gap-2 rounded-xl px-1.5 py-1
                       transition hover:bg-slate-100
                       sm:gap-3 sm:px-2 sm:py-1.5
                       dark:hover:bg-slate-600"
            >
                {{-- Avatar --}}
                <div class="relative shrink-0">
                    <img
                        src="{{ $imgSrc }}"
                        alt="Profile"
                        class="h-9 w-9 rounded-full object-cover ring-2 ring-slate-100
                               transition duration-200 group-hover:ring-slate-300
                               sm:h-10 sm:w-10
                               dark:ring-slate-700 dark:group-hover:ring-slate-600"
                    >

                    @if ($hasSignedContract)
                        <span
                            class="absolute -bottom-0.5 -right-0.5 flex h-4 w-4 items-center
                                   justify-center rounded-full border-2 border-white
                                   bg-emerald-500 text-white shadow-sm
                                   dark:border-slate-500"
                            title="Kontrak telah ditandatangani"
                        >
                            <i class="ri-check-line text-[10px] leading-none"></i>
                        </span>
                    @endif
                </div>

                {{-- User info --}}
                <div class="min-w-0">
                    <p
                        class="truncate text-sm font-semibold text-slate-500
                               dark:text-slate-100"
                    >
                        {{ ucwords(strtolower($user->nama_lengkap)) }}
                    </p>

                    <p
                        class="hidden text-xs text-slate-500 sm:block
                               dark:text-slate-400"
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
                               text-sm font-medium text-slate-600
                               transition hover:bg-red-500 hover:text-white
                               dark:text-slate-300 dark:hover:bg-red-500 dark:hover:text-white"
                    >
                        <i class="ri-logout-box-r-line text-lg"></i>
                        <span class="hidden lg:inline">{{ __('Logout') }}</span>
                    </button>
                </form>
            </div>

            {{-- Mobile Actions (< md; the icon nav takes over from md) --}}
            <div class="flex shrink-0 items-center gap-1 md:hidden">

                {{-- Slip Gaji (mobile: mirrors desktop role routing) --}}
                <a
                    href="{{ $user->role_id == 2
                        ? route('admin.slip.index')
                        : route('slip-gaji.index', ['bulan' => now()->subMonth()->format('Y-m')]) }}"
                    title="{{ __('Slip Gaji') }}"
                    aria-label="{{ __('Slip Gaji') }}"
                    @class([
                        'flex h-9 w-9 items-center justify-center rounded-full transition active:scale-95 sm:h-10 sm:w-10',
                        'bg-yellow-500 text-white shadow-sm shadow-yellow-500/30' => request()->routeIs('slip-gaji.index') || request()->routeIs('admin.slip.index'),
                        'text-slate-600 hover:bg-yellow-500 hover:text-white dark:text-slate-300 dark:hover:bg-yellow-500 dark:hover:text-white' => ! (request()->routeIs('slip-gaji.index') || request()->routeIs('admin.slip.index')),
                    ])
                >
                    <i class="ri-bank-card-line text-xl"></i>
                </a>

                {{-- Divider --}}
                <span class="mx-0.5 h-6 w-px rounded-full bg-slate-400/40 dark:bg-slate-500/60"></span>

                {{-- Logout --}}
                <form
                    action="{{ route('logout') }}"
                    method="post"
                    onsubmit="sessionStorage.removeItem('leaderRekapMode')"
                >
                    @csrf

                    <button
                        type="submit"
                        title="{{ __('Logout') }}"
                        aria-label="{{ __('Logout') }}"
                        class="flex h-9 w-9 items-center justify-center rounded-full
                               text-slate-600 transition active:scale-95
                               hover:bg-red-500 hover:text-white
                               sm:h-10 sm:w-10
                               dark:text-slate-300 dark:hover:bg-red-500 dark:hover:text-white"
                    >
                        <i class="ri-logout-box-r-line text-xl"></i>
                    </button>
                </form>
            </div>

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
                           dark:bg-white dark:text-slate-900
                           dark:hover:bg-slate-100"
                >
                    <i class="ri-login-box-line"></i>
                    {{ __('Login') }}
                </a>
            </div>

        @endauth

    </div>
</nav>