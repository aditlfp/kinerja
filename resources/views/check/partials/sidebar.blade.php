<aside
    class="fixed inset-y-0 left-0 z-40 hidden w-[17.5rem] flex-col border-r border-slate-200/80 bg-white/85 px-6 py-7 text-slate-600 backdrop-blur-xl lg:flex">
    <a href="{{ route('checkpoint-user.index') }}" class="group mb-12 flex items-center gap-3">
        <span
            class="grid h-10 w-10 place-items-center rounded-xl bg-sky-500 text-xl text-white shadow-[0_8px_20px_rgba(14,165,233,.18)] transition-transform duration-300 ease-[cubic-bezier(.22,1,.36,1)] group-hover:scale-105"><i
                class="ri-calendar-check-line"></i></span>
        <span><span
                class="block text-[10px] font-semibold uppercase tracking-[.18em] text-slate-400">Workspace</span><strong
                class="text-[15px] tracking-tight text-slate-900">Check Point</strong></span>
    </a>
    <p class="mb-3 px-3 text-[10px] font-semibold uppercase tracking-[.16em] text-slate-400">Menu</p>
    <nav class="space-y-1">
        <a href="{{ route('dashboard.index') }}"
            class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-[13px] font-medium transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] hover:bg-slate-50 hover:text-sky-600 {{ request()->routeIs('dashboard.*') ? 'bg-sky-50 font-semibold text-sky-700' : '' }}"><i
                class="ri-dashboard-line text-lg text-slate-400 transition-transform duration-300 group-hover:translate-x-0.5 group-hover:text-sky-500"></i>Dashboard</a>
        <a href="{{ route('slip-gaji.index') }}"
            class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-[13px] font-medium transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] hover:bg-slate-50 hover:text-sky-600 {{ request()->routeIs('slip-gaji.*') ? 'bg-sky-50 font-semibold text-sky-700' : '' }}"><i
                class="ri-file-list-3-line text-lg text-slate-400 transition-transform duration-300 group-hover:translate-x-0.5 group-hover:text-sky-500"></i>Slip
            gaji</a>
    </nav>
    <div class="mb-8">
        <button type="button" onclick="toggleCheckpointDropdown('desktop')"
            class="group flex justify-between w-full items-center gap-3 text-left overflow-hidden">
            <p class="px-3 text-[10px] font-semibold uppercase tracking-[.16em] text-slate-400">Master Pekerjaan</p>
            <i id="checkpoint-chevron-desktop"
                class="ri-arrow-down-s-line text-lg text-slate-400 transition-transform duration-300 overflow-hidden"></i>
        </button>

        <div id="checkpoint-dropdown-desktop" class="mt-3 space-y-1">
            <a href="{{ route('checkpoint-user.index') }}"
                class="group flex items-center gap-2 rounded-lg px-3 py-2 text-[13px] font-medium transition duration-300 hover:bg-slate-50 hover:text-sky-600 {{ request()->routeIs('checkpoint-user.index') ? 'bg-sky-50 font-semibold text-sky-700' : '' }}">
                <i class="ri-calendar-2-line text-base text-slate-400 group-hover:text-sky-500"></i>
                Kalender
            </a>

            <a href="{{ route('checkpoint-user.create') }}"
                class="group flex items-center gap-2 rounded-lg px-3 py-2 text-[13px] font-medium transition duration-300 hover:bg-slate-50 hover:text-sky-600 {{ request()->routeIs('checkpoint-user.create') ? 'bg-sky-50 font-semibold text-sky-700' : '' }}">
                <i class="ri-add-line text-base text-slate-400 group-hover:text-sky-500"></i>
                Tambah pekerjaan
            </a>
            <a href="{{ route('checkpoint-user.history') }}"
                class="group flex items-center gap-2 rounded-lg px-3 py-2 text-[13px] font-medium transition duration-300 hover:bg-slate-50 hover:text-sky-600 {{ request()->routeIs('checkpoint-user.history') ? 'bg-sky-50 font-semibold text-sky-700' : '' }}">
                <i class="ri-history-line text-base text-slate-400 group-hover:text-sky-500"></i>
                Riwayat pekerjaan
            </a>
        </div>
    </div>
    <div class="mt-auto">
        <div class="mb-4 border-t border-slate-200/80 pt-5 text-xs leading-5 text-slate-400"><i
                class="ri-information-line mr-1 text-sky-500"></i>Pilih tanggal untuk mengisi pekerjaan.</div>
        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit"
                class="group flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-[13px] font-medium text-slate-500 transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] hover:bg-rose-50 hover:text-rose-600"><i
                    class="ri-logout-box-r-line text-lg transition-transform duration-300 group-hover:translate-x-0.5"></i>Logout</button>
        </form>
    </div>
</aside>

{{-- Mobile top bar --}}
<div
    class="fixed inset-x-0 top-0 z-30 flex items-center justify-between border-b m-3 rounded-full border-slate-200/80 bg-white/90 px-4 py-3 text-slate-800 shadow-sm backdrop-blur-xl lg:hidden">
    <button type="button" id="checkpoint-menu-open" aria-label="Buka menu" aria-controls="checkpoint-mobile-drawer"
        aria-expanded="false"
        class="group grid h-10 w-10 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] hover:border-sky-300 hover:text-sky-600 active:scale-[.96]">
        <i class="ri-menu-3-line text-xl transition-transform duration-300 group-hover:scale-110"></i>
    </button>
    <div class="flex items-center gap-2 text-sm font-semibold">
        {{ request()->routeIs('checkpoint-user.index')
            ? 'Kalender'
            : (request()->routeIs('checkpoint-user.history')
                ? 'Riwayat'
                : 'Tambah pekerjaan') }}
    </div>
    <div></div>
</div>

{{-- Mobile drawer --}}
<div id="checkpoint-mobile-overlay" aria-hidden="true"
    class="pointer-events-none fixed inset-0 z-40 bg-slate-900/40 opacity-0 backdrop-blur-sm transition-opacity duration-300 ease-[cubic-bezier(.22,1,.36,1)] lg:hidden">
</div>
<aside id="checkpoint-mobile-drawer" aria-hidden="true"
    class="fixed inset-y-0 left-0 z-50 flex w-[17.5rem] max-w-[85vw] -translate-x-full flex-col overflow-y-auto border-r border-slate-200/80 bg-white/95 px-6 py-7 text-slate-600 shadow-[0_24px_60px_-24px_rgba(15,23,42,.35)] backdrop-blur-xl transition-transform duration-300 ease-[cubic-bezier(.22,1,.36,1)] will-change-transform lg:hidden">
    <div class="mb-10 flex items-center justify-between">
        <a href="{{ route('checkpoint-user.index') }}" class="group flex items-center gap-3">
            <span
                class="grid h-10 w-10 place-items-center rounded-xl bg-sky-500 text-xl text-white shadow-[0_8px_20px_rgba(14,165,233,.18)] transition-transform duration-300 ease-[cubic-bezier(.22,1,.36,1)] group-hover:scale-105"><i
                    class="ri-calendar-check-line"></i></span>
            <span><span
                    class="block text-[10px] font-semibold uppercase tracking-[.18em] text-slate-400">Workspace</span><strong
                    class="text-[15px] tracking-tight text-slate-900">Check Point</strong></span>
        </a>
        <button type="button" id="checkpoint-menu-close" aria-label="Tutup menu"
            class="grid h-9 w-9 place-items-center rounded-lg text-slate-500 transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] hover:bg-slate-100 hover:text-slate-700 active:scale-[.96]">
            <i class="ri-close-line text-xl"></i>
        </button>
    </div>

    <p class="mb-3 px-3 text-[10px] font-semibold uppercase tracking-[.16em] text-slate-400">Menu</p>
    <nav class="space-y-1">
        <a href="{{ route('dashboard.index') }}"
            class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-[13px] font-medium transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] hover:bg-slate-50 hover:text-sky-600 {{ request()->routeIs('dashboard.*') ? 'bg-sky-50 font-semibold text-sky-700' : '' }}"><i
                class="ri-dashboard-line text-lg text-slate-400 transition-transform duration-300 group-hover:translate-x-0.5 group-hover:text-sky-500"></i>Dashboard</a>
        <a href="{{ route('slip-gaji.index') }}"
            class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-[13px] font-medium transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] hover:bg-slate-50 hover:text-sky-600 {{ request()->routeIs('slip-gaji.*') ? 'bg-sky-50 font-semibold text-sky-700' : '' }}"><i
                class="ri-file-list-3-line text-lg text-slate-400 transition-transform duration-300 group-hover:translate-x-0.5 group-hover:text-sky-500"></i>Slip
            gaji</a>
    </nav>

    <div class="mt-6">
        <button type="button" onclick="toggleCheckpointDropdown('mobile')"
            class="group flex w-full items-center justify-between gap-3 overflow-hidden text-left">
            <p class="px-3 text-[10px] font-semibold uppercase tracking-[.16em] text-slate-400">Master Pekerjaan</p>
            <i id="checkpoint-chevron-mobile"
                class="ri-arrow-down-s-line text-lg text-slate-400 transition-transform duration-300 overflow-hidden"></i>
        </button>

        <div id="checkpoint-dropdown-mobile" class="mt-3 space-y-1">
            <a href="{{ route('checkpoint-user.index') }}"
                class="group flex items-center gap-2 rounded-lg px-3 py-2 text-[13px] font-medium transition duration-300 hover:bg-slate-50 hover:text-sky-600 {{ request()->routeIs('checkpoint-user.index') ? 'bg-sky-50 font-semibold text-sky-700' : '' }}">
                <i class="ri-calendar-2-line text-base text-slate-400 group-hover:text-sky-500"></i>
                Kalender
            </a>

            <a href="{{ route('checkpoint-user.create') }}"
                class="group flex items-center gap-2 rounded-lg px-3 py-2 text-[13px] font-medium transition duration-300 hover:bg-slate-50 hover:text-sky-600 {{ request()->routeIs('checkpoint-user.create') ? 'bg-sky-50 font-semibold text-sky-700' : '' }}">
                <i class="ri-add-line text-base text-slate-400 group-hover:text-sky-500"></i>
                Tambah pekerjaan
            </a>
            <a href="{{ route('checkpoint-user.history') }}"
                class="group flex items-center gap-2 rounded-lg px-3 py-2 text-[13px] font-medium transition duration-300 hover:bg-slate-50 hover:text-sky-600 {{ request()->routeIs('checkpoint-user.history') ? 'bg-sky-50 font-semibold text-sky-700' : '' }}">
                <i class="ri-history-line text-base text-slate-400 group-hover:text-sky-500"></i>
                Riwayat pekerjaan
            </a>
        </div>
    </div>

    <div class="mt-auto pt-8">
        <div class="mb-4 border-t border-slate-200/80 pt-5 text-xs leading-5 text-slate-400"><i
                class="ri-information-line mr-1 text-sky-500"></i>Pilih tanggal untuk mengisi pekerjaan.</div>
        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit"
                class="group flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-[13px] font-medium text-slate-500 transition duration-300 ease-[cubic-bezier(.22,1,.36,1)] hover:bg-rose-50 hover:text-rose-600"><i
                    class="ri-logout-box-r-line text-lg transition-transform duration-300 group-hover:translate-x-0.5"></i>Logout</button>
        </form>
    </div>
</aside>

<script>
    function toggleCheckpointDropdown(target = '') {
        const suffix = target ? '-' + target : '';
        const dropdown = document.getElementById('checkpoint-dropdown' + suffix);
        const chevron = document.getElementById('checkpoint-chevron' + suffix);

        if (!dropdown || !chevron) return;

        dropdown.classList.toggle('hidden');
        chevron.classList.toggle('rotate-180');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const isCheckpointActive =
            @json(request()->routeIs('checkpoint-user.*'));

        ['desktop', 'mobile'].forEach((target) => {
            const dropdown = document.getElementById('checkpoint-dropdown-' + target);
            const chevron = document.getElementById('checkpoint-chevron-' + target);
            if (!dropdown || !chevron) return;

            if (!isCheckpointActive) {
                dropdown.classList.add('hidden');
            } else {
                chevron.classList.add('rotate-180');
            }
        });

        const drawer = document.getElementById('checkpoint-mobile-drawer');
        const overlay = document.getElementById('checkpoint-mobile-overlay');
        const openButton = document.getElementById('checkpoint-menu-open');
        const closeButton = document.getElementById('checkpoint-menu-close');

        if (!drawer || !overlay || !openButton) return;

        const openDrawer = () => {
            drawer.classList.remove('-translate-x-full');
            drawer.setAttribute('aria-hidden', 'false');
            overlay.classList.remove('pointer-events-none', 'opacity-0');
            overlay.setAttribute('aria-hidden', 'false');
            openButton.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        };

        const closeDrawer = () => {
            drawer.classList.add('-translate-x-full');
            drawer.setAttribute('aria-hidden', 'true');
            overlay.classList.add('pointer-events-none', 'opacity-0');
            overlay.setAttribute('aria-hidden', 'true');
            openButton.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        };

        openButton.addEventListener('click', openDrawer);
        closeButton?.addEventListener('click', closeDrawer);
        overlay.addEventListener('click', closeDrawer);

        drawer.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', closeDrawer);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeDrawer();
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) closeDrawer();
        });
    });
</script>
