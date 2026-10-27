@php
    /**
     * One voice note, drawn as a chat player: a round play button, a seek bar
     * and the take's length. The script in item-thread binds the behaviour
     * through the data-audio-* hooks below — media events do not bubble, so
     * every player carries its own controller.
     *
     * Expects:
     *   $src      public URL of the recording; empty for the take waiting in the
     *             composer, which the script fills in from the recorded blob
     *   $duration length of the take in seconds
     *   $tone     'incoming' (white management bubble), 'outgoing' (sky bubble)
     *             or 'pending' (composer strip, still on white)
     */
    $tone = $tone ?? 'incoming';
    $duration = (int) $duration;

    $palette = match ($tone) {
        'outgoing' => [
            'button' => 'bg-white/25 text-white hover:bg-white/40',
            'seek' => 'accent-white',
            'time' => 'text-white/80',
        ],
        default => [
            'button' => 'bg-sky-600 text-white hover:bg-sky-700',
            'seek' => 'accent-sky-600',
            'time' => 'text-slate-500',
        ],
    };
@endphp

<div data-audio-controller data-duration="{{ $duration }}" class="flex w-[230px] max-w-full items-center gap-2">
    <button type="button" data-audio-toggle aria-label="Putar voice note"
        class="grid h-8 w-8 shrink-0 place-items-center rounded-full transition {{ $palette['button'] }}">
        <i class="ri-play-fill text-lg" data-audio-icon></i>
    </button>

    <input type="range" min="0" max="100" step="1" value="0" data-audio-seek
        aria-label="Geser posisi pemutaran" class="h-2 w-full flex-1 cursor-pointer {{ $palette['seek'] }}">

    <span data-audio-time
        class="w-9 shrink-0 text-right text-[10px] font-semibold tabular-nums {{ $palette['time'] }}">{{ intdiv($duration, 60) . ':' . str_pad((string) ($duration % 60), 2, '0', STR_PAD_LEFT) }}</span>

    {{-- The engine stays hidden: the duration of a freshly recorded blob is only
         readable once a media element has loaded it. --}}
    <audio data-audio-player preload="metadata" class="hidden" @if (filled($src)) src="{{ $src }}" @endif></audio>
</div>
