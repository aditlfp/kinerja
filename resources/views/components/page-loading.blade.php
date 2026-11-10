@props([
    'title' => 'Memuat halaman...',
    'description' => 'Menyiapkan data.',
    'target',
])

<div data-page-loading data-loading-target="{{ $target }}"
    class="fixed inset-0 z-[9998] grid min-h-dvh place-items-center bg-[#f4f6f8] px-4 py-6 transition-opacity duration-200 motion-reduce:transition-none"
    role="status" aria-live="polite">
    <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-sm sm:p-6">
        <span class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-sky-50 text-sky-600 sm:h-14 sm:w-14">
            <i class="ri-loader-4-line text-2xl motion-safe:animate-spin sm:text-3xl"></i>
        </span>
        <p class="mt-4 text-sm font-semibold text-slate-900 sm:text-base">{{ $title }}</p>
        <p class="mt-1 text-xs text-slate-500">{{ $description }}</p>
        <div class="mx-auto mt-5 w-full max-w-[15rem] space-y-2" aria-hidden="true">
            <div class="h-2.5 w-3/4 animate-pulse rounded-full bg-slate-100"></div>
            <div class="h-2.5 w-full animate-pulse rounded-full bg-slate-100"></div>
            <div class="h-2.5 w-2/3 animate-pulse rounded-full bg-slate-100"></div>
        </div>
    </div>
</div>

<script>
    (() => {
        const loading = document.currentScript?.previousElementSibling;
        if (loading?.matches('[data-page-loading]') && loading.parentElement !== document.body) {
            document.body.appendChild(loading);
        }
    })();
</script>

@once
    <script>
        (() => {
            const revealPage = (loading, page) => {
                const wait = Math.max(0, 1200 - (performance.now() - loading.dataset.loadingStarted));

                window.setTimeout(() => {
                    page.classList.remove('opacity-0');
                    page.setAttribute('aria-busy', 'false');
                    loading.classList.add('pointer-events-none', 'opacity-0');
                    window.setTimeout(() => loading.remove(), 220);
                }, wait);
            };

            const start = () => {
                document.querySelectorAll('[data-page-loading]').forEach((loading) => {
                    const page = document.getElementById(loading.dataset.loadingTarget);
                    if (!page) return;

                    loading.dataset.loadingStarted = performance.now();
                    window.setTimeout(() => revealPage(loading, page), 0);
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', start, { once: true });
            } else {
                start();
            }
        })();
    </script>
@endonce
