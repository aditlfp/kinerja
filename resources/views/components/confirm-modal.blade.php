@props([
    'id' => 'confirmModal',
    'title' => 'Konfirmasi',
    'message' => 'Apakah Anda yakin?',
    'confirmText' => 'Ya, Lanjutkan',
    'cancelText' => 'Batal',
    'type' => 'danger',
    'onConfirm' => null,
])

@php
    $variant = match ($type) {
        'warning' => 'm3-btn--warning',
        'info' => 'm3-btn--filled',
        'success' => 'm3-btn--success',
        default => 'm3-btn--danger',
    };

    $iconStyles = match ($type) {
        'warning' => ['icon' => 'warning', 'class' => 'bg-[var(--md-sys-color-warning-container)] text-[var(--md-sys-color-on-warning-container)]'],
        'info' => ['icon' => 'info', 'class' => 'bg-[var(--md-sys-color-secondary-container)] text-[var(--md-sys-color-on-secondary-container)]'],
        'success' => ['icon' => 'check_circle', 'class' => 'bg-[var(--md-sys-color-tertiary-container)] text-[var(--md-sys-color-on-tertiary-container)]'],
        default => ['icon' => 'error', 'class' => 'bg-[var(--md-sys-color-error-container)] text-[var(--md-sys-color-on-error-container)]'],
    };
@endphp

<div x-data="{ show: false, onConfirm: null }" x-show="show" x-cloak
    @open-modal-{{ $id }}.window="show = true"
    @close-modal-{{ $id }}.window="show = false"
    @set-confirm-{{ $id }}.window="onConfirm = $event.detail"
    @keydown.escape.window="show = false" role="alertdialog" aria-modal="true" aria-labelledby="{{ $id }}-title"
    class="fixed inset-0 z-[9999] flex items-center justify-center overflow-y-auto overscroll-contain px-4 py-6"
    style="display: none;">

    <div x-show="show" x-transition:enter="m3-scrim-fade-enter" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="m3-scrim-fade-leave"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="m3-scrim fixed inset-0" @click="show = false"></div>

    <div x-show="show" x-transition:enter="m3-dialog-enter"
        x-transition:enter-start="m3-dialog-enter-start"
        x-transition:enter-end="m3-dialog-enter-end" x-transition:leave="m3-dialog-leave"
        x-transition:leave-start="m3-dialog-leave-start"
        x-transition:leave-end="m3-dialog-leave-end"
        class="relative w-full max-w-md overflow-hidden rounded-[28px] bg-[var(--md-sys-color-surface-container-high)] shadow-[var(--md-elevation-3)]">

        <div class="p-6">
            <div class="flex items-start gap-4">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl {{ $iconStyles['class'] }}">
                    <span class="material-symbols-outlined" aria-hidden="true">{{ $iconStyles['icon'] }}</span>
                </span>
                <div class="min-w-0 flex-1">
                    <h3 id="{{ $id }}-title"
                        class="text-base font-semibold leading-6 text-[var(--md-sys-color-on-surface)]">
                        {{ $title }}</h3>
                    <p class="mt-1 text-sm leading-5 text-[var(--md-sys-color-on-surface-variant)]">{{ $message }}</p>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap justify-end gap-2 px-6 pb-6">
            <button type="button" @click="show = false" class="m3-btn m3-btn--text">
                {{ $cancelText }}
            </button>
            <button type="button" @click="if (onConfirm) onConfirm(); show = false"
                class="m3-btn {{ $variant }}">
                {{ $confirmText }}
            </button>
        </div>
    </div>
</div>
