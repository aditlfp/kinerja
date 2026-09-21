@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl'
])

@php
$maxWidth = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
][$maxWidth];
@endphp

<div
    x-data="{
        show: @js($show),
        focusables() {
            // All focusable element types...
            let selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'
            return [...$el.querySelectorAll(selector)]
                // All non-disabled elements...
                .filter(el => ! el.hasAttribute('disabled'))
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables().slice(-1)[0] },
        nextFocusable() { return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable() },
        prevFocusable() { return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable() },
        nextFocusableIndex() { return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1) },
        prevFocusableIndex() { return Math.max(0, this.focusables().indexOf(document.activeElement)) -1 },
    }"
    {{-- Penguncian scroll TIDAK dilakukan di sini: halaman dikunci oleh skrip
         terpusat di head (menandai <html data-modal-open>), supaya modal native,
         Alpine, dan overlay legacy memakai satu mekanisme yang sama. --}}
    x-init="$watch('show', value => {
        if (value) {
            {{ $attributes->has('focusable') ? 'setTimeout(() => firstFocusable().focus(), 100)' : '' }}
        }
    })"
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
    x-on:keydown.shift.tab.prevent="prevFocusable().focus()"
    x-show="show"
    {{-- Flex + m-auto di panel: modal benar-benar di tengah viewport, dan
         kalau tingginya melebihi layar tetap bisa di-scroll dari atas
         (margin auto pada flex item, bukan absolute centering).
         `m3-overlay` mengunci margin overlay ke 0 supaya utility `space-y-*`
         pada halaman pemanggil tidak menggesernya dari tengah. --}}
    class="m3-overlay fixed inset-0 z-50 flex overflow-y-auto overscroll-contain px-4 py-6 sm:px-6"
    role="dialog" aria-modal="true"
    style="display: {{ $show ? 'flex' : 'none' }};"
>
    <div
        x-show="show"
        class="fixed inset-0"
        x-on:click="show = false"
        x-transition:enter="m3-scrim-fade-enter"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="m3-scrim-fade-leave"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="m3-scrim absolute inset-0"></div>
    </div>

    <div
        x-show="show"
        class="m3-dialog-panel m-auto w-full overflow-hidden rounded-[28px] bg-[var(--md-sys-color-surface-container-high)] shadow-[var(--md-elevation-3)] {{ $maxWidth }}"
        x-transition:enter="m3-dialog-enter"
        x-transition:enter-start="m3-dialog-enter-start"
        x-transition:enter-end="m3-dialog-enter-end"
        x-transition:leave="m3-dialog-leave"
        x-transition:leave-start="m3-dialog-leave-start"
        x-transition:leave-end="m3-dialog-leave-end"
    >
        {{ $slot }}
    </div>
</div>
