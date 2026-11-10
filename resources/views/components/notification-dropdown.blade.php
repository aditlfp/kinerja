<div id="notification-dropdown" class="w-full overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xl shadow-slate-900/10">
    <header class="flex items-center justify-between gap-4 border-b border-slate-100 px-4 py-3.5 sm:px-5">
        <div class="flex min-w-0 items-center gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                <i class="ri-notification-3-line text-lg" aria-hidden="true"></i>
            </div>
            <div class="min-w-0">
                <h3 class="text-sm font-bold text-slate-900">Notifikasi</h3>
                <p class="mt-0.5 truncate text-xs text-slate-500">
                    @if ($unreadCount > 0)
                        {{ $unreadCount }} notifikasi belum dibaca
                    @else
                        Semua notifikasi sudah dibaca
                    @endif
                </p>
            </div>
        </div>

        @if ($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" title="Tandai semua sudah dibaca"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-2.5 py-2 text-xs font-semibold text-sky-700 transition hover:bg-sky-50 focus:outline-none focus:ring-2 focus:ring-sky-500/30">
                    <i class="ri-check-double-line text-base" aria-hidden="true"></i>
                    <span class="hidden sm:inline">Tandai dibaca</span>
                </button>
            </form>
        @endif
    </header>

    <div class="max-h-[min(28rem,60vh)] overflow-y-auto overscroll-contain">
        @forelse ($notifications as $notification)
            @php($unread = is_null($notification->read_at))
            <div class="notification-swipe relative isolate overflow-hidden border-b border-slate-100 last:border-b-0" data-notification-swipe>
                <form method="POST" action="{{ route('notifications.delete', $notification->id) }}"
                    class="notification-delete-form pointer-events-none absolute inset-y-0 right-0 z-0 flex w-16 items-center justify-center bg-rose-500 opacity-0 transition-opacity duration-200">
                    @csrf
                    @method('DELETE')
                    <button type="submit" aria-label="Hapus notifikasi" title="Hapus notifikasi"
                        class="flex h-full w-full items-center justify-center text-white transition hover:bg-rose-600 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                        <i class="ri-delete-bin-line text-lg" aria-hidden="true"></i>
                    </button>
                </form>

                <div class="notification-swipe-content relative z-10 transition-transform duration-200">
                    <a href="{{ route('notifications.open', $notification->id) }}"
                        class="group flex gap-3 px-4 py-3.5 transition hover:bg-slate-50 focus:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-sky-500/40 sm:px-5 {{ $unread ? 'bg-sky-50/50' : 'bg-white' }}">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $unread ? 'bg-sky-100 text-sky-700' : 'bg-slate-100 text-slate-500' }}">
                            <i class="{{ $notification->data['icon'] ?? 'ri-notification-3-line' }} text-lg" aria-hidden="true"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <p class="truncate text-sm {{ $unread ? 'font-bold text-slate-900' : 'font-semibold text-slate-700' }}">
                                    {{ $notification->data['title'] ?? 'Notifikasi' }}
                                </p>
                                @if ($unread)
                                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-sky-600" title="Belum dibaca" aria-label="Belum dibaca"></span>
                                @endif
                            </div>
                            <p class="mt-1 line-clamp-2 text-xs leading-relaxed text-slate-500">{{ $notification->data['message'] ?? '' }}</p>
                            <time class="mt-2 block text-[11px] text-slate-400" datetime="{{ $notification->created_at->toIso8601String() }}">
                                {{ $notification->created_at->diffForHumans() }}
                            </time>
                        </div>
                    </a>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center px-6 py-14 text-center">
                <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                    <i class="ri-notification-off-line text-2xl" aria-hidden="true"></i>
                </div>
                <p class="font-semibold text-slate-800">Belum ada notifikasi</p>
                <p class="mt-1 max-w-xs text-xs leading-relaxed text-slate-500">Informasi terbaru akan tampil di sini.</p>
            </div>
        @endforelse
    </div>

    @if ($notifications->count())
        <footer class="flex items-center justify-center gap-2 border-t border-slate-100 bg-slate-50/70 px-3 py-2.5 text-[11px] text-slate-500">
            <i class="ri-arrow-left-line" aria-hidden="true"></i>
            Geser ke kiri untuk menghapus
        </footer>
    @endif
</div>

@once
    <script>
        (() => {
            const init = () => document.querySelectorAll('[data-notification-swipe]').forEach((item) => {
                if (item.dataset.swipeReady) return;
                item.dataset.swipeReady = '1';
                const content = item.querySelector('.notification-swipe-content');
                const deleteForm = item.querySelector('.notification-delete-form');
                let startX = 0;

                content.addEventListener('touchstart', (event) => {
                    startX = event.touches[0].clientX;
                    content.style.transition = 'none';
                }, { passive: true });
                content.addEventListener('touchmove', (event) => {
                    const delta = event.touches[0].clientX - startX;
                    if (delta < -8) content.style.transform = `translateX(${Math.max(delta, -deleteForm.offsetWidth)}px)`;
                }, { passive: true });
                content.addEventListener('touchend', (event) => {
                    const open = event.changedTouches[0].clientX - startX < -40;
                    content.style.transition = 'transform 200ms';
                    content.style.transform = open ? `translateX(-${deleteForm.offsetWidth}px)` : '';
                    deleteForm.classList.toggle('opacity-0', !open);
                    deleteForm.classList.toggle('pointer-events-none', !open);
                });
            });
            document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', init) : init();
        })();
    </script>
@endonce
