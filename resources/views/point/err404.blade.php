<x-app-layout>
    <div class="mx-auto w-full max-w-xl px-4 py-8 sm:px-6">
        <section class="px-0.5">
            <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">Point Saya</h1>
            <p class="mt-3 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                Kamu belum mempunyai point.
            </p>
            <div class="mt-5 flex justify-end">
                <a href="{{ url('dashboard') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                    Kembali
                </a>
            </div>
        </section>
    </div>
</x-app-layout>
