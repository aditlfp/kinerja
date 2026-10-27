<x-app-layout>
    @php
        $periodOptions = [7 => '7 hari terakhir', 14 => '14 hari terakhir', 30 => '30 hari terakhir', 90 => '90 hari terakhir'];
        $activePeriod = $periodOptions[$days] ?? '14 hari terakhir';
        $hasFilters = $search !== '' || $category !== 'semua' || $confirmation !== 'semua';
        $activeCategory = $category === 'semua'
            ? 'Semua catatan'
            : (\App\Models\CheckPointMessage::NOTE_CATEGORIES[$category]['label'] ?? 'Semua catatan');
        $activeConfirmation = ['semua' => 'Semua status', 'sudah' => 'Sudah dikonfirmasi', 'belum' => 'Belum dikonfirmasi'][$confirmation] ?? 'Semua status';
    @endphp

    <div class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-10 lg:py-10">
        {{-- A quiet headline block: the page is a reading surface, so the
             chrome stays short and the notes start within one screen. --}}
        <header
            class="relative overflow-hidden rounded-3xl bg-white p-5 shadow-[0_1px_2px_rgba(15,23,42,.03),0_24px_60px_-40px_rgba(15,23,42,.35)] ring-1 ring-slate-200/70 sm:p-7">
            <span aria-hidden="true"
                class="pointer-events-none absolute -right-20 -top-28 h-64 w-64 rounded-full bg-[radial-gradient(closest-side,rgba(14,165,233,.14),transparent)]"></span>

            <div class="relative">
                <span
                    class="inline-flex items-center gap-1.5 rounded-full bg-sky-50 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-sky-600 ring-1 ring-sky-100">
                    <i class="ri-shield-user-line text-sm"></i> Direksi
                </span>

                <h1 class="mt-4 text-[1.6rem] font-semibold leading-tight tracking-tight text-slate-900 sm:text-3xl">
                    Riwayat Catatan Bawahan
                </h1>
                <p class="mt-2 max-w-xl text-sm leading-6 text-slate-500">
                    Semua catatan penilaian yang kamu berikan kepada karyawan. Buka detail untuk melihat laporan aslinya.
                </p>

                <div class="mt-5 flex flex-wrap items-center gap-2 text-[11px]">
                    <span
                        class="m-1 inline-flex items-center gap-1.5 rounded-full bg-slate-50 px-3 py-1.5 font-medium text-slate-500 ring-1 ring-slate-200/70">
                        <i class="ri-calendar-line text-sm text-sky-500"></i>
                        {{ $start->translatedFormat('j M Y') }} – {{ $end->translatedFormat('j M Y') }}
                    </span>
                    <span
                        class="m-1 inline-flex items-center gap-1.5 rounded-full bg-slate-50 px-3 py-1.5 font-medium text-slate-500 ring-1 ring-slate-200/70">
                        <i class="ri-filter-3-line text-sm text-sky-500"></i>
                        {{ $activePeriod }}
                    </span>
                </div>
            </div>
        </header>

        {{-- On desktop the numbers and filters hold still while the notes
             scroll; on phones everything stacks in reading order. --}}
        <div class="mt-5 grid grid-cols-1 gap-4 xl:mt-6 xl:grid-cols-[19rem_minmax(0,1fr)] xl:items-start xl:gap-6">
            <div class="space-y-4 xl:sticky xl:top-6">
                <section class="rounded-3xl bg-white p-4 shadow-[0_1px_2px_rgba(15,23,42,.03),0_24px_60px_-40px_rgba(15,23,42,.35)] ring-1 ring-slate-200/70 sm:p-5">
                    <div class="rounded-2xl bg-slate-50/80 p-4">
                        <p class="text-[11px] font-medium tracking-wide text-slate-400">Total catatan</p>
                        <p class="mt-1 flex items-baseline gap-1.5 text-slate-900">
                            <span class="text-3xl font-semibold leading-none tracking-tight overflow-hidden"
                                data-note-total>{{ $totalNotes }}</span>
                        </p>
                    </div>

                    <div class="mt-2.5 grid grid-cols-2 gap-2.5">
                        <div class="rounded-2xl bg-emerald-50/70 p-3 ring-1 ring-emerald-100/80">
                            <p class="flex items-center gap-1 text-[11px] font-medium text-emerald-700">
                                <i class="ri-checkbox-circle-fill"></i> Dikonfirmasi
                            </p>
                            <p class="mt-1 text-2xl font-semibold leading-none text-emerald-700 overflow-hidden"><span
                                    data-note-confirmed>{{ $confirmedNotes }}</span></p>
                        </div>
                        <div class="rounded-2xl bg-amber-50/70 p-3 ring-1 ring-amber-100/80">
                            <p class="flex items-center gap-1 text-[11px] font-medium text-amber-700">
                                <i class="ri-time-line"></i> Menunggu
                            </p>
                            <p class="mt-1 text-2xl font-semibold leading-none text-amber-700 overflow-hidden"><span
                                    data-note-pending>{{ $pendingNotes }}</span></p>
                        </div>
                    </div>

                    @if ($noConfirmationNotes > 0)
                        <p class="mt-3 text-[11px] leading-5 text-slate-400">
                            {{ $noConfirmationNotes }} catatan hanya berisi reaksi singkat, jadi tidak meminta konfirmasi
                            karyawan.
                        </p>
                    @endif
                </section>

                {{-- Phones get the filters folded away so the notes start
                     earlier; desktop keeps the panel open and sticky. --}}
                <details id="note-filters" open data-has-filters="{{ $hasFilters ? '1' : '0' }}"
                    class="group/filters rounded-3xl bg-white shadow-[0_1px_2px_rgba(15,23,42,.03),0_24px_60px_-40px_rgba(15,23,42,.35)] ring-1 ring-slate-200/70">
                    <summary
                        class="flex cursor-pointer list-none items-center justify-between gap-2 px-4 py-3.5 [&::-webkit-details-marker]:hidden xl:hidden">
                        <span class="flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400">
                            <i class="ri-filter-3-line text-sm text-sky-500"></i> Filter
                        </span>
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-sky-600">
                            <span class="group-open/filters:hidden">{{ $hasFilters ? 'Aktif · Ubah' : 'Ubah' }}</span>
                            <span class="hidden group-open/filters:inline">Tutup</span>
                            <i
                                class="ri-arrow-down-s-line text-base transition duration-500 ease-[cubic-bezier(.22,1,.36,1)] group-open/filters:rotate-180"></i>
                        </span>
                    </summary>

                    <form method="GET" action="{{ route('direksi.cp.notes') }}" class="p-4 pt-0 sm:p-5 sm:pt-0 xl:pt-5">
                    <div class="flex items-center justify-between gap-2">
                        <p class="hidden text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400 xl:block">Filter</p>
                        @if ($hasFilters)
                            <a href="{{ route('direksi.cp.notes') }}"
                                class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-[11px] font-semibold text-sky-600 transition duration-500 ease-[cubic-bezier(.22,1,.36,1)] hover:bg-sky-50 hover:text-sky-700">
                                <i class="ri-refresh-line"></i> Reset
                            </a>
                        @endif
                    </div>

                    <label for="note-search" class="mt-3 block text-[11px] font-medium text-slate-400">Cari</label>
                    <div class="relative mt-1">
                        <i class="ri-search-line pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input id="note-search" name="search" type="search" value="{{ $search }}"
                            placeholder="Nama atau isi catatan"
                            class="w-full rounded-xl border border-slate-200/80 bg-slate-50/60 py-2.5 pl-10 pr-12 text-sm text-slate-700 placeholder:text-slate-400 focus:border-sky-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-100">
                        <button type="submit" aria-label="Cari catatan"
                            class="absolute right-1.5 top-1/2 grid h-8 w-8 -translate-y-1/2 place-items-center rounded-lg bg-sky-500 text-white shadow-sm transition duration-500 ease-[cubic-bezier(.22,1,.36,1)] hover:bg-sky-600 active:scale-95">
                            <i class="ri-arrow-right-line text-base"></i>
                        </button>
                    </div>

                    <div class="mt-3 space-y-3 overflow-visible">
                        <div data-nselect>
                            <label id="note-period-label" class="block text-[11px] font-medium text-slate-400">Periode</label>
                            <input type="hidden" name="periode" value="{{ $days }}">
                            <button type="button" data-nselect-toggle aria-haspopup="listbox" aria-expanded="false"
                                aria-controls="note-period-listbox"
                                aria-labelledby="note-period-label note-period-value">
                                <span data-nselect-label id="note-period-value">{{ $activePeriod }}</span>
                                <span data-nselect-chevron aria-hidden="true"><i class="ri-arrow-down-s-line"></i></span>
                            </button>
                            <div data-nselect-panel hidden>
                                <ul id="note-period-listbox" role="listbox" aria-labelledby="note-period-label">
                                    @foreach ($periodOptions as $value => $optionLabel)
                                        <li role="option" id="note-period-opt-{{ $value }}" data-nselect-option
                                            data-value="{{ $value }}"
                                            aria-selected="{{ $days === $value ? 'true' : 'false' }}"
                                            style="animation-delay: {{ $loop->index * 25 }}ms">
                                            <span data-nselect-text>{{ $optionLabel }}</span>
                                            <span data-nselect-check aria-hidden="true"><i class="ri-check-line"></i></span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <div data-nselect>
                            <label id="note-category-label" class="block text-[11px] font-medium text-slate-400">Jenis</label>
                            <input type="hidden" name="jenis" value="{{ $category }}">
                            <button type="button" data-nselect-toggle aria-haspopup="listbox" aria-expanded="false"
                                aria-controls="note-category-listbox"
                                aria-labelledby="note-category-label note-category-value">
                                <span data-nselect-label id="note-category-value">{{ $activeCategory }}</span>
                                <span data-nselect-chevron aria-hidden="true"><i class="ri-arrow-down-s-line"></i></span>
                            </button>
                            <div data-nselect-panel hidden>
                                <ul id="note-category-listbox" role="listbox" aria-labelledby="note-category-label">
                                    <li role="option" id="note-category-opt-semua" data-nselect-option data-value="semua"
                                        aria-selected="{{ $category === 'semua' ? 'true' : 'false' }}" style="animation-delay: 0ms">
                                        <span data-nselect-icon class="bg-slate-50 text-slate-500"><i class="ri-apps-line"
                                                aria-hidden="true"></i></span>
                                        <span data-nselect-text>Semua catatan</span>
                                        <span data-nselect-check aria-hidden="true"><i class="ri-check-line"></i></span>
                                    </li>
                                    @foreach (\App\Models\CheckPointMessage::NOTE_CATEGORIES as $key => $meta)
                                        <li role="option" id="note-category-opt-{{ $key }}" data-nselect-option
                                            data-value="{{ $key }}" aria-selected="{{ $category === $key ? 'true' : 'false' }}"
                                            style="animation-delay: {{ ($loop->index + 1) * 25 }}ms">
                                            <span data-nselect-icon class="{{ $meta['badge'] }}"><i class="{{ $meta['icon'] }}"
                                                    aria-hidden="true"></i></span>
                                            <span data-nselect-text>{{ $meta['label'] }}</span>
                                            <span data-nselect-check aria-hidden="true"><i class="ri-check-line"></i></span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <div data-nselect>
                            <label id="note-confirmation-label" class="block text-[11px] font-medium text-slate-400">Konfirmasi</label>
                            <input type="hidden" name="konfirmasi" value="{{ $confirmation }}">
                            <button type="button" data-nselect-toggle aria-haspopup="listbox" aria-expanded="false"
                                aria-controls="note-confirmation-listbox"
                                aria-labelledby="note-confirmation-label note-confirmation-value">
                                <span data-nselect-label id="note-confirmation-value">{{ $activeConfirmation }}</span>
                                <span data-nselect-chevron aria-hidden="true"><i class="ri-arrow-down-s-line"></i></span>
                            </button>
                            <div data-nselect-panel hidden>
                                <ul id="note-confirmation-listbox" role="listbox" aria-labelledby="note-confirmation-label">
                                    @foreach (['semua' => 'Semua status', 'sudah' => 'Sudah dikonfirmasi', 'belum' => 'Belum dikonfirmasi'] as $key => $optionLabel)
                                        @php $stateMeta = \App\Models\CheckPointMessage::CONFIRMATION_STATES[$key] ?? null; @endphp
                                        <li role="option" id="note-confirmation-opt-{{ $key }}" data-nselect-option
                                            data-value="{{ $key }}" aria-selected="{{ $confirmation === $key ? 'true' : 'false' }}"
                                            style="animation-delay: {{ $loop->index * 25 }}ms">
                                            <span data-nselect-icon
                                                class="{{ $stateMeta['badge'] ?? 'bg-slate-50 text-slate-500' }}"><i
                                                    class="{{ $stateMeta['icon'] ?? 'ri-filter-3-line' }}"
                                                    aria-hidden="true"></i></span>
                                            <span data-nselect-text>{{ $optionLabel }}</span>
                                            <span data-nselect-check aria-hidden="true"><i class="ri-check-line"></i></span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    </form>
                </details>
            </div>

            <section aria-labelledby="note-list-heading">
                <div class="mb-3 flex flex-wrap items-end justify-between gap-2">
                    <div>
                        <h2 id="note-list-heading" class="text-sm font-semibold tracking-tight text-slate-900">Catatan terbaru
                        </h2>
                        <p class="mt-0.5 text-[11px] text-slate-400">
                            @if ($hasFilters)
                                Menampilkan {{ $notes->count() }} dari {{ $totalNotes }} catatan
                            @else
                                {{ $notes->total() }} catatan pada periode ini
                            @endif
                        </p>
                    </div>
                    @if ($notes->count() > 1)
                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-400">
                            <i class="ri-sort-desc"></i> Terbaru di atas
                        </span>
                    @endif
                </div>

                <div class="space-y-3">
                    @forelse ($notes as $note)
                        @php
                            $employee = $note->item?->checkPoint?->user;
                            $meta = \App\Models\CheckPointMessage::NOTE_CATEGORIES[$note->noteCategory()];
                            $reaction = \App\Models\CheckPointItem::reactionFromText($note->body);
                            $text = $reaction && $reaction['rest'] !== '' ? $reaction['rest'] : ($note->body ?: '');
                            $state = $note->confirmationState();
                            $confirmationMeta = \App\Models\CheckPointMessage::CONFIRMATION_STATES[$state];

                            $photo = null;
                            if ($employee?->image && $employee->image !== 'no-image.jpg') {
                                if (\Illuminate\Support\Facades\Storage::disk('public')->exists('images/' . $employee->image)) {
                                    $photo = asset('storage/images/' . $employee->image);
                                } elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists('user/' . $employee->image)) {
                                    $photo = asset('storage/user/' . $employee->image);
                                }
                            }
                        @endphp

                        <article data-note-card data-note-category="{{ $note->noteCategory() }}"
                            style="animation-delay: {{ min($loop->index, 6) * 45 }}ms"
                            class="group relative overflow-hidden rounded-2xl bg-white p-4 shadow-[0_1px_2px_rgba(15,23,42,.03),0_16px_40px_-32px_rgba(15,23,42,.35)] ring-1 ring-slate-200/70 transition duration-500 ease-[cubic-bezier(.22,1,.36,1)] hover:-translate-y-0.5 hover:shadow-[0_2px_6px_rgba(15,23,42,.04),0_24px_48px_-32px_rgba(15,23,42,.35)] hover:ring-slate-300/80 motion-safe:animate-[fadeSlide_.5s_cubic-bezier(.22,1,.36,1)_both] sm:p-5">
                            <span aria-hidden="true"
                                class="absolute inset-y-4 left-0 w-[3px] rounded-r-full {{ $meta['accent'] }} opacity-70"></span>

                            <div class="pl-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex min-w-0 items-center gap-2.5">
                                        <span
                                            class="grid h-9 w-9 shrink-0 place-items-center overflow-hidden rounded-xl bg-sky-50 text-[13px] font-semibold uppercase text-sky-600 ring-1 ring-sky-100/80">
                                            @if ($photo)
                                                <img src="{{ $photo }}" alt="" class="h-full w-full object-cover">
                                            @else
                                                {{ mb_substr($employee?->nama_lengkap ?? '-', 0, 1) }}
                                            @endif
                                        </span>
                                        <div class="min-w-0">
                                            <h3 class="truncate text-sm font-semibold capitalize leading-5 text-slate-900">
                                                {{ strtolower($employee?->nama_lengkap ?? 'Karyawan') }}
                                            </h3>
                                            <p class="mt-0.5 flex flex-wrap items-center gap-x-1.5 gap-y-1 text-[11px] text-slate-400">
                                                <span class="m-1 inline-flex items-center gap-1"><i
                                                        class="ri-calendar-line"></i>{{ $note->created_at->translatedFormat('d M Y') }}</span>
                                                <span
                                                    class="m-1 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1 sm:hidden {{ $meta['badge'] }}">
                                                    <i class="{{ $meta['icon'] }}"></i>{{ $meta['label'] }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>

                                    <span
                                        class="hidden shrink-0 items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-semibold ring-1 sm:inline-flex {{ $meta['badge'] }}">
                                        <i class="{{ $meta['icon'] }}"></i>{{ $meta['label'] }}
                                    </span>
                                </div>

                                <p class="mt-3 line-clamp-3 whitespace-pre-line text-sm leading-6 text-slate-600">
                                    @if ($text !== '')
                                        {{ $text }}
                                    @elseif ($note->audio_path)
                                        <i class="ri-mic-line"></i> Catatan suara
                                    @else
                                        Tidak ada teks catatan.
                                    @endif
                                </p>

                                <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                                    <span data-note-confirmation="{{ $state }}"
                                        class="m-1 inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-semibold ring-1 {{ $confirmationMeta['badge'] }}">
                                        <i class="{{ $confirmationMeta['icon'] }}"></i>{{ $confirmationMeta['label'] }}
                                    </span>

                                    @if ($note->item?->checkPoint)
                                        <a href="{{ route('direksi.cp.history.show', $note->item->check_point_id) }}#item-{{ $note->check_point_item_id }}"
                                            class="group/link inline-flex min-h-9 items-center gap-2 rounded-full bg-slate-50 py-1 pl-3.5 pr-1 text-[11px] font-semibold text-slate-600 ring-1 ring-slate-200/70 transition duration-500 ease-[cubic-bezier(.22,1,.36,1)] hover:bg-sky-50 hover:text-sky-700 hover:ring-sky-200 active:scale-[0.98]">
                                            Lihat detail catatan
                                            <span
                                                class="m-1 grid h-7 w-7 place-items-center rounded-full bg-white text-slate-500 shadow-sm ring-1 ring-slate-200/60 transition duration-500 ease-[cubic-bezier(.22,1,.36,1)] group-hover/link:translate-x-0.5 group-hover/link:text-sky-600">
                                                <i class="ri-arrow-right-up-line"></i>
                                            </span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div
                            class="rounded-3xl border border-dashed border-slate-300 bg-white px-5 py-14 text-center shadow-[0_1px_2px_rgba(15,23,42,.03)]">
                            <span
                                class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-slate-50 text-2xl text-slate-300 ring-1 ring-slate-200/70">
                                <i class="ri-chat-quote-line"></i>
                            </span>
                            <p class="mt-3 text-sm font-medium text-slate-600">Belum ada catatan pada periode ini.</p>
                            <p class="mt-1 text-xs text-slate-400">
                                @if ($hasFilters)
                                    Coba perluas periode atau hapus filter yang aktif.
                                @else
                                    Catatan yang kamu tulis akan muncul di sini.
                                @endif
                            </p>
                            @if ($hasFilters)
                                <a href="{{ route('direksi.cp.notes') }}"
                                    class="mt-4 inline-flex items-center gap-1.5 rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold text-white transition duration-500 ease-[cubic-bezier(.22,1,.36,1)] hover:bg-slate-800 active:scale-[0.98]">
                                    Reset filter
                                    <i class="ri-arrow-right-line"></i>
                                </a>
                            @endif
                        </div>
                    @endforelse
                </div>

                @if ($notes->hasPages())
                    <div class="mt-4 rounded-2xl bg-white px-4 py-3 shadow-[0_1px_2px_rgba(15,23,42,.03)] ring-1 ring-slate-200/70">
                        {{ $notes->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>

    <style>
        /* Custom listboxes: the native popup follows the OS theme, this one
             follows the page — hairline shell, sky highlight, staggered rows. */
        /* Aturan global `* { overflow-x: hidden }` membuat setiap elemen
             menjadi scroll container; pada field ini itu memotong panel dan
             memunculkan scrollbar tipis di samping tombol. */
        [data-nselect] { position: relative; overflow: visible; }

        [data-nselect-toggle] {
            display: flex;
            width: 100%;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            margin-top: .25rem;
            padding: .625rem .75rem;
            border: 1px solid rgba(226, 232, 240, .9);
            border-radius: .75rem;
            background: rgba(248, 250, 252, .6);
            color: rgb(51 65 85);
            font-size: .875rem;
            font-weight: 500;
            text-align: left;
            cursor: pointer;
            transition: border-color .5s cubic-bezier(.22, 1, .36, 1), background-color .5s cubic-bezier(.22, 1, .36, 1), box-shadow .5s cubic-bezier(.22, 1, .36, 1);
        }

        [data-nselect-toggle]:hover {
            border-color: rgb(203 213 225);
            background: #fff;
        }

        [data-nselect-toggle]:focus-visible,
        [data-nselect][data-open] > [data-nselect-toggle] {
            outline: none;
            border-color: rgb(125 211 252);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(186, 230, 253, .55);
        }

        [data-nselect-label] {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        [data-nselect-chevron] {
            display: grid;
            flex: none;
            place-items: center;
            height: 1.5rem;
            width: 1.5rem;
            border-radius: 999px;
            background: #fff;
            color: rgb(148 163 184);
            box-shadow: 0 1px 2px rgba(15, 23, 42, .08);
            transition: transform .5s cubic-bezier(.22, 1, .36, 1), color .5s cubic-bezier(.22, 1, .36, 1);
        }

        [data-nselect-chevron] i { font-size: 1rem; }

        [data-nselect][data-open] [data-nselect-chevron] {
            transform: rotate(180deg);
            color: rgb(2 132 199);
        }

        [data-nselect-panel] {
            position: absolute;
            left: 0;
            right: 0;
            top: calc(100% + 6px);
            z-index: 30;
            padding: .375rem;
            border: 1px solid rgba(226, 232, 240, .9);
            border-radius: .875rem;
            background: #fff;
            box-shadow: 0 2px 8px rgba(15, 23, 42, .06), 0 28px 60px -30px rgba(15, 23, 42, .45);
            animation: nselectPop .22s cubic-bezier(.22, 1, .36, 1) both;
        }

        [data-nselect-panel] ul {
            max-height: 15rem;
            overflow-y: auto;
            overscroll-behavior: contain;
            /* Batang gulir dibuat setipis mungkin supaya panel tetap rapi
                 di dalam kartu filter yang sempit. */
            scrollbar-width: thin;
            scrollbar-color: rgb(203 213 225) transparent;
        }

        [data-nselect-panel] ul::-webkit-scrollbar { width: 6px; }
        [data-nselect-panel] ul::-webkit-scrollbar-track { background: transparent; }
        [data-nselect-panel] ul::-webkit-scrollbar-thumb {
            border-radius: 999px;
            background: rgb(203 213 225);
        }
        [data-nselect-panel] ul::-webkit-scrollbar-thumb:hover { background: rgb(148 163 184); }

        [data-nselect-option] {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .5rem .625rem;
            border-radius: .625rem;
            color: rgb(71 85 105);
            font-size: .8125rem;
            font-weight: 500;
            cursor: pointer;
            user-select: none;
            transition: background-color .25s cubic-bezier(.22, 1, .36, 1), color .25s cubic-bezier(.22, 1, .36, 1);
            animation: nselectOption .18s cubic-bezier(.22, 1, .36, 1) both;
        }

        [data-nselect-option] + [data-nselect-option] { margin-top: 2px; }

        [data-nselect-option]:hover,
        [data-nselect-option][data-active] {
            background: rgb(241 245 249);
        }

        [data-nselect-option][aria-selected="true"] {
            background: rgb(240 249 255);
            color: rgb(2 132 199);
            font-weight: 600;
        }

        [data-nselect-text] {
            flex: 1 1 auto;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        [data-nselect-icon] {
            display: grid;
            flex: none;
            place-items: center;
            height: 1.5rem;
            width: 1.5rem;
            border-radius: .5rem;
            box-shadow: inset 0 0 0 1px rgba(15, 23, 42, .06);
        }

        [data-nselect-icon] i { font-size: .875rem; }

        [data-nselect-check] {
            display: grid;
            flex: none;
            place-items: center;
            height: 1.25rem;
            width: 1.25rem;
            margin-left: auto;
            border-radius: 999px;
            background: #fff;
            color: rgb(2 132 199);
            box-shadow: 0 0 0 1px rgba(186, 230, 253, .9), 0 1px 2px rgba(15, 23, 42, .06);
            opacity: 0;
            transform: scale(.6);
            transition: opacity .3s cubic-bezier(.22, 1, .36, 1), transform .3s cubic-bezier(.22, 1, .36, 1);
        }

        [data-nselect-check] i { font-size: .75rem; }

        [data-nselect-option][aria-selected="true"] [data-nselect-check] {
            opacity: 1;
            transform: scale(1);
        }

        @keyframes nselectPop {
            from { opacity: 0; transform: translateY(-6px) scale(.985); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes nselectOption {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (prefers-reduced-motion: reduce) {
            [data-nselect-panel], [data-nselect-option] { animation: none; }
        }
    </style>

    <script>
        (function () {
            const filters = document.getElementById('note-filters');
            if (filters) {
                const desktop = window.matchMedia('(min-width: 1280px)');
                const sync = function () {
                    if (desktop.matches) {
                        // The sticky panel only exists on desktop; there it is
                        // always unfolded, exactly like a normal sidebar card.
                        filters.open = true;
                    } else if (filters.dataset.hasFilters !== '1') {
                        // On phones start folded so the first note is reachable
                        // without scrolling past four controls.
                        filters.open = false;
                    }
                };

                sync();
                desktop.addEventListener('change', sync);
            }

            // Custom dropdowns: styled listboxes that still submit the filter
            // form like the native selects they replaced.
            const roots = Array.prototype.slice.call(document.querySelectorAll('[data-nselect]'));
            if (!roots.length) return;

            let openRoot = null;

            const optionsOf = function (root) {
                const panel = root._panel || root;
                return Array.prototype.slice.call(panel.querySelectorAll('[data-nselect-option]'));
            };

            // Panel tetap berada di dalam kartu filter (kartu memakai
            // overflow-hidden), jadi tingginya dibatasi oleh sisa ruang kartu:
            // buka ke bawah bila lega, ke atas bila bawahnya sempit.
            const positionPanel = function (root) {
                const panel = root._panel;
                const toggle = root.querySelector('[data-nselect-toggle]');
                if (!panel || panel.hidden || !toggle) return;

                const card = document.getElementById('note-filters') || root.closest('section');
                const list = panel.querySelector('ul');
                if (!card || !list) return;

                const gap = 6;
                const inset = 8;

                // Kembali ke posisi bawaan (menempel di bawah field) sebelum diukur.
                panel.style.top = '';
                panel.style.bottom = '';
                list.style.maxHeight = '';

                const cardRect = card.getBoundingClientRect();
                const toggleRect = toggle.getBoundingClientRect();
                // Batas bawah juga menghormati viewport, karena kartu sticky bisa
                // lebih tinggi dari layar.
                const limitTop = Math.max(8, cardRect.top);
                const limitBottom = Math.min(cardRect.bottom, document.documentElement.clientHeight - 8);
                const spaceBelow = limitBottom - toggleRect.bottom - gap - inset;
                const spaceAbove = toggleRect.top - limitTop - gap;

                const openUp = spaceBelow < 110 && spaceAbove > spaceBelow;
                if (openUp) {
                    panel.style.top = 'auto';
                    panel.style.bottom = 'calc(100% + ' + gap + 'px)';
                }

                const available = Math.max(56, openUp ? spaceAbove : spaceBelow) - 14;
                const desired = list.scrollHeight;
                list.style.maxHeight = Math.floor(Math.max(48, Math.min(desired, 240, available))) + 'px';

                // Bilang daftar pas, jangan tampilkan batang gulir sama sekali.
                list.style.overflowY = list.scrollHeight > list.clientHeight + 1 ? 'auto' : 'hidden';
            };

            const close = function (root, refocus) {
                root.removeAttribute('data-open');
                (root._panel || root.querySelector('[data-nselect-panel]')).hidden = true;
                const toggle = root.querySelector('[data-nselect-toggle]');
                toggle.setAttribute('aria-expanded', 'false');
                toggle.removeAttribute('aria-activedescendant');
                if (openRoot === root) openRoot = null;
                if (refocus) toggle.focus();
            };

            roots.forEach(function (root) {
                const toggle = root.querySelector('[data-nselect-toggle]');
                const panel = root.querySelector('[data-nselect-panel]');
                root._panel = panel;
                const label = root.querySelector('[data-nselect-label]');
                const input = root.querySelector('input[type="hidden"]');
                const form = root.closest('form');
                let active = -1;

                const selectedIndex = function () {
                    return optionsOf(root).findIndex(function (option) {
                        return option.getAttribute('aria-selected') === 'true';
                    });
                };

                const setActive = function (index) {
                    const options = optionsOf(root);
                    if (!options.length) return;
                    active = Math.max(0, Math.min(options.length - 1, index));
                    root._activeIndex = active;
                    options.forEach(function (option, i) {
                        if (i === active) option.setAttribute('data-active', '');
                        else option.removeAttribute('data-active');
                    });
                    toggle.setAttribute('aria-activedescendant', options[active].id);
                    // Scroll only the little list, never the page itself.
                    const current = options[active];
                    const ul = current.parentElement;
                    if (current.offsetTop < ul.scrollTop || current.offsetTop + current.offsetHeight > ul.scrollTop + ul.clientHeight) {
                        ul.scrollTop = Math.max(0, current.offsetTop - (ul.clientHeight - current.offsetHeight) / 2);
                    }
                };

                const open = function () {
                    if (openRoot && openRoot !== root) close(openRoot, false);
                    panel.hidden = false;
                    root.setAttribute('data-open', '');
                    toggle.setAttribute('aria-expanded', 'true');
                    openRoot = root;
                    positionPanel(root);
                    const index = selectedIndex();
                    setActive(index < 0 ? 0 : index);
                };

                const choose = function (option) {
                    const value = option.getAttribute('data-value');
                    const previous = input.value;
                    input.value = value;
                    optionsOf(root).forEach(function (other) {
                        other.setAttribute('aria-selected', other === option ? 'true' : 'false');
                    });
                    const text = option.querySelector('[data-nselect-text]');
                    if (text) label.textContent = text.textContent.trim();
                    close(root, true);
                    if (form && value !== previous) form.submit();
                };

                root._setActive = setActive;
                root._choose = choose;

                toggle.addEventListener('click', function () {
                    if (panel.hidden) open();
                    else close(root, false);
                });

                toggle.addEventListener('keydown', function (event) {
                    if (['Enter', ' ', 'Spacebar', 'ArrowDown', 'ArrowUp'].indexOf(event.key) === -1) return;
                    if (!panel.hidden) return; // open panel: document handler takes over
                    event.preventDefault();
                    event.stopPropagation(); // keep this opening keystroke from reaching it
                    open();
                    if (event.key === 'ArrowUp') setActive(optionsOf(root).length - 1);
                });

                optionsOf(root).forEach(function (option, index) {
                    option.addEventListener('click', function () { choose(option); });
                    option.addEventListener('mouseenter', function () { setActive(index); });
                });

                if (filters) {
                    filters.addEventListener('toggle', function () {
                        if (!filters.open) close(root, false);
                    });
                }
            });

            document.addEventListener('keydown', function (event) {
                const root = openRoot;
                if (!root) return;
                const options = optionsOf(root);

                if (event.key === 'Escape') { event.preventDefault(); close(root, true); return; }
                if (event.key === 'Tab') { close(root, false); return; }
                if (event.key === 'ArrowDown') { event.preventDefault(); root._setActive((root._activeIndex || 0) + 1); return; }
                if (event.key === 'ArrowUp') { event.preventDefault(); root._setActive((root._activeIndex || 0) - 1); return; }
                if (event.key === 'Home') { event.preventDefault(); root._setActive(0); return; }
                if (event.key === 'End') { event.preventDefault(); root._setActive(options.length - 1); return; }
                if (event.key === 'Enter' || event.key === ' ' || event.key === 'Spacebar') {
                    if (options[root._activeIndex]) {
                        event.preventDefault();
                        root._choose(options[root._activeIndex]);
                    }
                }
            });

            document.addEventListener('pointerdown', function (event) {
                if (!openRoot) return;
                const panel = openRoot._panel;
                if (openRoot.contains(event.target) || (panel && panel.contains(event.target))) return;
                close(openRoot, false);
            });

            // Panel ikut field-nya saat halaman digulir/diubah ukurannya,
            // tetapi tinggi daftarnya perlu dihitung ulang.
            let rafId = 0;
            const scheduleReposition = function () {
                if (!openRoot || rafId) return;
                rafId = requestAnimationFrame(function () {
                    rafId = 0;
                    if (openRoot) positionPanel(openRoot);
                });
            };
            window.addEventListener('resize', scheduleReposition);
        })();
    </script>
</x-app-layout>
