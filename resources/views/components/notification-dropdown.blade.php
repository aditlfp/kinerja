<div
    id="notification-dropdown"
    class="dropdown dropdown-end"
>
    {{-- Notification Button --}}
    <button
        type="button"
        class="btn btn-warning btn-circle relative"
        aria-label="Notifications"
    >
        <i class="ri-notification-3-line text-xl"></i>

        @if ($unreadCount > 0)
            <span
                class="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-error px-1 text-[10px] font-bold text-error-content"
            >
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    {{-- Backdrop --}}
    <div
        class="pointer-events-none fixed inset-0 z-[40]
               bg-black/20 backdrop-blur-sm
               opacity-0 invisible
               transition-all duration-200
               [body:has(#notification-dropdown:focus-within)_&]:visible
               [body:has(#notification-dropdown:focus-within)_&]:opacity-100
               [body:has(#notification-dropdown:focus-within)_&]:pointer-events-auto"
    ></div>

    {{-- Dropdown --}}
    <div
        tabindex="0"
        class="dropdown-content z-[50] left-1 mt-3
               w-[300px] sm:w-[380px]
               overflow-hidden rounded-2xl
               border border-base-300
               bg-base-100
               shadow-xl"
    >

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-base-200 px-4 py-3">
            <div>
                <h3 class="font-semibold text-base-content">
                    Notifications
                </h3>

                @if ($unreadCount > 0)
                    <p class="text-xs text-base-content/60">
                        {{ $unreadCount }} Notification Belum Di Baca
                    </p>
                @else
                    <p class="text-xs text-base-content/60">
                        kamu sudah mengikuti semua perkembangan terbaru.
                    </p>
                @endif
            </div>

            @if ($unreadCount > 0)
                <form
                    method="POST"
                    action="{{ route('notifications.read-all') }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="btn btn-ghost btn-xs gap-1 text-sky-700"
                    >
                        <i class="ri-check-double-line"></i>
                        Tandai Semua Di Baca
                    </button>
                </form>
            @endif
        </div>

        {{-- Notification List --}}
        <div class="max-h-[420px] overflow-y-auto">

            @forelse ($notifications as $notification)

                <div class="notification-swipe relative isolate overflow-hidden border-b border-base-200" data-notification-swipe>
                    <form method="POST" action="{{ route('notifications.delete', $notification->id) }}" class="notification-delete-form pointer-events-none absolute inset-y-0 right-0 z-0 flex w-14 items-center justify-center bg-error opacity-0 transition-opacity duration-200">
                        @csrf @method('DELETE')
                        <button type="submit" class="flex h-full w-full items-center justify-center text-error-content" aria-label="Hapus notifikasi">
                            <i class="ri-delete-bin-line text-lg"></i>
                        </button>
                    </form>
                    <div class="notification-swipe-content relative z-10 transition-transform duration-200">
                        <a href="{{ route('notifications.open', $notification->id) }}"
                            class="group flex gap-3 bg-base-100 px-4 py-2 transition hover:bg-base-200/60 {{ is_null($notification->read_at) ? 'bg-sky-700/5' : '' }}">
                            <div class="flex-shrink-0">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl {{ is_null($notification->read_at) ? 'bg-sky-700/10 text-sky-700' : 'bg-base-200 text-base-content/60' }}">
                                    <i class="{{ $notification->data['icon'] ?? 'ri-notification-3-line' }} text-lg"></i>
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="truncate text-sm font-semibold text-base-content">{{ $notification->data['title'] ?? 'Notification' }}</p>
                                    @if (is_null($notification->read_at)) <span class="mt-1 h-2 w-2 flex-shrink-0 rounded-full bg-sky-700"></span> @endif
                                </div>
                                <p class="mt-0.5 line-clamp-2 text-xs leading-relaxed text-base-content/60">{{ $notification->data['message'] ?? '' }}</p>
                                <p class="mt-1.5 text-[11px] text-base-content/40">{{ $notification->created_at->diffForHumans() }}</p>
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
                    <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-base-200">
                        <i class="ri-notification-off-line text-2xl text-base-content/40"></i>
                    </div>

                    <p class="font-medium text-base-content">
                        Tidak Memiliki Notifications
                    </p>

                    <p class="mt-1 text-xs text-base-content/50">
                        Kami akan memberi tahu Anda jika ada sesuatu yang terjadi.
                    </p>
                </div>

            @endforelse

        </div>

        {{-- Footer --}}
        @if ($notifications->count())
            <div class="border-t border-base-200 p-2">
                <a
                    {{-- href="{{ route('notifications.index') }}" --}}
                    class="btn btn-ghost btn-sm w-full gap-2"
                >
                    Lihat semua notifications
                    <i class="ri-arrow-right-line"></i>
                </a>
            </div>
        @endif

    </div>
</div>