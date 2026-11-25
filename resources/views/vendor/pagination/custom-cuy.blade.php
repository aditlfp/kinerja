@if ($paginator->hasPages())
    <nav class="flex flex-col items-stretch gap-2 sm:flex-row sm:items-center sm:justify-between" aria-label="Pagination">
        <p class="text-center text-[11px] font-medium text-slate-500 sm:text-left">
            {{ __('Showing') }} <span class="font-bold text-slate-700">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</span>
            {{ __('of') }} <span class="font-bold text-slate-700">{{ $paginator->total() }}</span>
        </p>

        <ul class="mx-auto flex items-center gap-0.5 rounded-sm bg-white p-1 shadow-sm ring-1 ring-slate-200 sm:mx-0 sm:ml-auto">
            @if ($paginator->onFirstPage())
                <li><span class="inline-flex h-8 w-8 cursor-not-allowed items-center justify-center rounded-sm text-sm text-slate-300" aria-disabled="true" aria-label="@lang('pagination.previous')">&lsaquo;</span></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')" class="inline-flex h-8 w-8 items-center justify-center rounded-sm text-base text-slate-500 transition hover:bg-slate-100 hover:text-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-400">&lsaquo;</a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page === 1 || $page === $paginator->lastPage() || abs($page - $paginator->currentPage()) <= 1)
                            <li>
                                @if ($page == $paginator->currentPage())
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-sm bg-emerald-500 px-1.5 text-xs font-bold text-white shadow-[0_0_10px_rgba(16,185,129,0.55)]" aria-current="page">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="inline-flex h-8 w-8 items-center justify-center rounded-sm px-1.5 text-xs font-semibold text-slate-600 transition hover:bg-emerald-50 hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-400">{{ $page }}</a>
                                @endif
                            </li>
                        @elseif ($page === 2 || $page === $paginator->lastPage() - 1)
                            <li><span class="inline-flex h-8 w-8 items-center justify-center rounded-sm text-xs text-slate-400" aria-hidden="true">&hellip;</span></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')" class="inline-flex h-8 w-8 items-center justify-center rounded-sm text-base text-slate-500 transition hover:bg-slate-100 hover:text-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-400">&rsaquo;</a></li>
            @else
                <li><span class="inline-flex h-8 w-8 cursor-not-allowed items-center justify-center rounded-sm text-sm text-slate-300" aria-disabled="true" aria-label="@lang('pagination.next')">&rsaquo;</span></li>
            @endif
        </ul>
    </nav>
@endif
