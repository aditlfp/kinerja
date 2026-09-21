@php
    $authJabatan = auth()->user()->divisi->jabatan->code_jabatan ?? (auth()->user()->divisi->code_jabatan ?? null);
    $menu = [
        [
            'route' => 'dashboard.index',
            'active' => 'dashboard.index',
            'icon' => 'ri-dashboard-line',
            'label' => 'Dashboard',
        ],
        [
            'route' => 'direksi.cp.index',
            'active' => 'direksi.cp.index',
            'icon' => 'ri-user-line',
            'label' => 'Karyawan',
        ],
        [
            'route' => 'direksi.cp.history',
            'active' => 'direksi.cp.history*',
            'icon' => 'ri-history-line',
            'label' => 'Riwayat',
        ],
    ];
@endphp

<aside
    class="fixed inset-y-0 left-0 z-40 hidden w-[17.5rem] flex-col border-r border-slate-200/80 bg-white/85 px-6 py-7 text-slate-600 backdrop-blur-xl lg:flex">
    <a href="{{ route('direksi.cp.index') }}" class="group mb-12 flex items-center gap-3">
        <span
            class="grid h-10 w-10 place-items-center rounded-xl bg-sky-500 text-xl text-white shadow-[0_8px_20px_rgba(14,165,233,.18)] transition-transform duration-300 ease-[cubic-bezier(.22,1,.36,1)] group-hover:scale-105"><i
                class="ri-calendar-check-line"></i></span>
        <span>
            <span class="block text-[10px] font-semibold uppercase tracking-[.18em] text-slate-400">Direksi</span>
            <strong
                class="text-[15px] tracking-tight text-slate-900">{{ request()->routeIs('checkpoint-user.index')
                    ? 'Kalender'
                    : (request()->routeIs('direksi.cp.history*')
                        ? 'Riwayat'
                        : 'Karyawan') }}</strong>
        </span>
    </a>

    <p class="mb-3 px-3 text-[10px] font-semibold uppercase tracking-[.16em] text-slate-400">Menu</p>
    <nav class="space-y-1">
        @foreach ($menu as $item)
            <a href="{{ route($item['route']) }}"
                class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-[13px] font-medium transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] hover:bg-slate-50 hover:text-sky-600 {{ request()->routeIs($item['active']) ? 'bg-sky-50 font-semibold text-sky-700' : '' }}">
                <i
                    class="{{ $item['icon'] }} text-lg text-slate-400 transition-transform duration-300 group-hover:translate-x-0.5 group-hover:text-sky-500"></i>
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>

    <div class="mt-auto">
        <div class="mb-4 border-t border-slate-200/80 pt-5 text-xs leading-5 text-slate-400"><i
                class="ri-information-line mr-1 text-sky-500"></i>Pilih karyawan untuk melihat kalender checkpoint.
        </div>

        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit"
                class="group flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-[13px] font-medium text-slate-500 transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] hover:bg-rose-50 hover:text-rose-600"><i
                    class="ri-logout-box-r-line text-lg transition-transform duration-300 group-hover:translate-x-0.5"></i>Logout</button>
        </form>
    </div>
</aside>

<div
    class="fixed inset-x-0 top-0 z-30 rounded-full m-3 flex items-center justify-between border-b border-slate-200/80 bg-white/90 px-4 py-3 text-slate-800 shadow-sm backdrop-blur-xl lg:hidden">
    <button type="button" id="direksi-cp-menu-open" aria-label="Buka menu" aria-controls="direksi-cp-drawer"
        aria-expanded="false"
        class="group grid h-10 w-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-600 transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] hover:border-sky-300 hover:text-sky-600 active:scale-[.96]"><i
            class="ri-menu-3-line text-xl transition-transform duration-300 group-hover:scale-110"></i></button>
    <div class="flex items-center gap-2 text-sm font-semibold">
            {{ request()->routeIs('direksi.cp.index')
            ? 'Karyawan'
            : (request()->routeIs('direksi.cp.history*')
                ? 'Riwayat'
                : 'Kalendar') }}
    </div>
    <div></div>
</div>

<div id="direksi-cp-overlay" aria-hidden="true"
    class="pointer-events-none fixed inset-0 z-40 bg-slate-900/40 opacity-0 backdrop-blur-sm transition-opacity duration-300 ease-[cubic-bezier(.22,1,.36,1)] lg:hidden">
</div>
<aside id="direksi-cp-drawer" aria-hidden="true"
    class="fixed inset-y-0 left-0 z-50 flex w-[17.5rem] max-w-[85vw] -translate-x-full flex-col overflow-y-auto border-r border-slate-200/80 bg-white/95 px-6 py-7 text-slate-600 shadow-[0_24px_60px_-24px_rgba(15,23,42,.35)] backdrop-blur-xl transition-transform duration-300 ease-[cubic-bezier(.22,1,.36,1)] will-change-transform lg:hidden">
    <div class="mb-10 flex items-center justify-between">
        <a href="{{ route('direksi.cp.index') }}" class="group flex items-center gap-3">
            <span
                class="grid h-10 w-10 place-items-center rounded-xl bg-sky-500 text-xl text-white shadow-[0_8px_20px_rgba(14,165,233,.18)] transition-transform duration-300 ease-[cubic-bezier(.22,1,.36,1)] group-hover:scale-105"><i
                    class="ri-calendar-check-line"></i></span>
            <span>
                <span class="block text-[10px] font-semibold uppercase tracking-[.18em] text-slate-400">Direksi</span>
                <strong class="text-[15px] tracking-tight text-slate-900">Check Point</strong>
            </span>
        </a>
        <button type="button" id="direksi-cp-menu-close" aria-label="Tutup menu"
            class="grid h-9 w-9 place-items-center rounded-lg text-slate-500 transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] hover:bg-slate-100 hover:text-slate-700 active:scale-[.96]"><i
                class="ri-close-line text-xl"></i></button>
    </div>

    <p class="mb-3 px-3 text-[10px] font-semibold uppercase tracking-[.16em] text-slate-400">Menu</p>
    <nav class="space-y-1">
        @foreach ($menu as $item)
            <a href="{{ route($item['route']) }}"
                class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-[13px] font-medium transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] hover:bg-slate-50 hover:text-sky-600 {{ request()->routeIs($item['active']) ? 'bg-sky-50 font-semibold text-sky-700' : '' }}">
                <i
                    class="{{ $item['icon'] }} text-lg text-slate-400 transition-transform duration-300 group-hover:translate-x-0.5 group-hover:text-sky-500"></i>
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>

    <div class="mt-auto pt-8">
        <div class="mb-4 border-t border-slate-200/80 pt-5 text-xs leading-5 text-slate-400"><i
                class="ri-information-line mr-1 text-sky-500"></i>Pilih karyawan untuk melihat kalender checkpoint.
        </div>

        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit"
                class="group flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-[13px] font-medium text-slate-500 transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] hover:bg-rose-50 hover:text-rose-600"><i
                    class="ri-logout-box-r-line text-lg transition-transform duration-300 group-hover:translate-x-0.5"></i>Logout</button>
        </form>
    </div>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const drawer = document.getElementById('direksi-cp-drawer');
        const overlay = document.getElementById('direksi-cp-overlay');
        const openButton = document.getElementById('direksi-cp-menu-open');
        const closeButton = document.getElementById('direksi-cp-menu-close');
        if (!drawer || !overlay || !openButton) return;

        const open = () => {
            drawer.classList.remove('-translate-x-full');
            drawer.setAttribute('aria-hidden', 'false');
            overlay.classList.remove('pointer-events-none', 'opacity-0');
            overlay.setAttribute('aria-hidden', 'false');
            openButton.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        };
        const close = () => {
            drawer.classList.add('-translate-x-full');
            drawer.setAttribute('aria-hidden', 'true');
            overlay.classList.add('pointer-events-none', 'opacity-0');
            overlay.setAttribute('aria-hidden', 'true');
            openButton.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        };

        openButton.addEventListener('click', open);
        closeButton?.addEventListener('click', close);
        overlay.addEventListener('click', close);
        drawer.querySelectorAll('a').forEach((link) => link.addEventListener('click', close));
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') close();
        });
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) close();
        });
    });
</script>
