<div id="notification-dropdown" class="w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    {{-- Notification Content --}}

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/70 px-4 py-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Notifikasi terbaru</h3>
                <p class="mt-0.5 text-xs text-slate-500">
                    @if ($unreadCount > 0)
                        {{ $unreadCount }} belum dibaca
                    @else
                        Semua informasi sudah dibaca
                    @endif
                </p>
            </div>

            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-1 rounded-lg bg-sky-50 px-2.5 py-1.5 text-xs font-semibold text-sky-700 transition hover:bg-sky-100">
                        <i class="ri-check-double-line"></i>
                        Tandai dibaca
                    </button>
                </form>
            @endif
        </div>

        {{-- Notification List --}}
        <div class="max-h-[420px] overflow-y-auto bg-white">

            @forelse ($notifications as $notification)

                <div class="notification-swipe relative isolate overflow-hidden border-b border-slate-100 last:border-b-0" data-notification-swipe>
                    <form method="POST" action="{{ route('notifications.delete', $notification->id) }}" class="notification-delete-form pointer-events-none absolute inset-y-0 right-0 z-0 flex w-14 items-center justify-center bg-rose-500 opacity-0 transition-opacity duration-200">
                        @csrf @method('DELETE')
                        <button type="submit" class="flex h-full w-full items-center justify-center text-white" aria-label="Hapus notifikasi">
                            <i class="ri-delete-bin-line text-lg"></i>
                        </button>
                    </form>
                    <div class="notification-swipe-content relative z-10 transition-transform duration-200">
                        <a href="{{ route('notifications.open', $notification->id) }}"
                            class="group flex gap-3 px-4 py-3 transition hover:bg-slate-50 {{ is_null($notification->read_at) ? 'bg-sky-50/60' : 'bg-white' }}">
                            <div class="flex-shrink-0">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg {{ is_null($notification->read_at) ? 'bg-sky-100 text-sky-700' : 'bg-slate-100 text-slate-500' }}">
                                    <i class="{{ $notification->data['icon'] ?? 'ri-notification-3-line' }} text-lg"></i>
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="truncate text-sm font-semibold text-slate-800">{{ $notification->data['title'] ?? 'Notification' }}</p>
                                    @if (is_null($notification->read_at)) <span class="mt-1 h-2 w-2 flex-shrink-0 rounded-full bg-sky-700"></span> @endif
                                </div>
                                <p class="mt-0.5 line-clamp-2 text-xs leading-relaxed text-slate-500">{{ $notification->data['message'] ?? '' }}</p>
                                <p class="mt-1.5 text-[11px] text-slate-400">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                        </a>
                    </div>
                </div>
                @once
                    <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            document.querySelectorAll('[data-notification-swipe]').forEach((item) => {
                                const content = item.querySelector('.notification-swipe-content');
                                let startX = 0, deltaX = 0;
                                content.addEventListener('touchstart', e => {
                                    startX = e.touches[0].clientX;
                                    deltaX = 0;
                                    content.style.transition = 'none';
                                    const deleteForm = item.querySelector('.notification-delete-form');
                                    deleteForm.classList.add('opacity-0', 'pointer-events-none');
                                }, { passive: true });
                                content.addEventListener('touchmove', e => {
                                    deltaX = e.touches[0].clientX - startX;
                                    if (deltaX < -8) {
                                        const width = item.querySelector('.notification-delete-form').offsetWidth;
                                        content.style.transform = `translateX(${Math.max(deltaX, -width)}px)`;
                                    }
                                }, { passive: true });
                                content.addEventListener('touchend', () => {
                                    const width = item.querySelector('.notification-delete-form').offsetWidth;
                                    const deleteForm = item.querySelector('.notification-delete-form');
                                    const open = deltaX < -40;
                                    content.style.transition = 'transform 200ms';
                                    content.style.transform = open ? `translateX(-${width}px)` : '';
                                    deleteForm.classList.toggle('opacity-0', !open);
                                    deleteForm.classList.toggle('pointer-events-none', !open);
                                });
                            });
                        });
                    </script>
                @endonce

            @empty

                <div class="flex flex-col items-center justify-center px-6 py-12 text-center">
                    <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-slate-100">
                        <i class="ri-notification-off-line text-2xl text-slate-400"></i>
                    </div>

                    <p class="font-semibold text-slate-800">
                        Belum ada notifikasi
                    </p>

                    <p class="mt-1 text-xs text-slate-500">
                        Informasi terbaru akan tampil di sini.
                    </p>
                </div>

            @endforelse

        </div>

        {{-- Footer --}}
        @if ($notifications->count())
            <div class="border-t border-slate-100 bg-slate-50/70 p-2">
                <span class="flex w-full items-center justify-center gap-2 px-3 py-1 text-xs font-medium text-slate-500">
                    Geser notifikasi ke kiri untuk menghapus
                    <i class="ri-arrow-left-line"></i>
                </span>
            </div>
        @endif
</div>