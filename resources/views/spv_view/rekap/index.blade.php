<x-app-layout>
    @push('scripts')
        <script src={{ URL::asset('js/rekap-export.js') }}></script>
    @endpush

    @push('styles')
        <style>
            #detailModal {
                transition: opacity 0.2s ease-in-out;
            }

            #detailModal.opacity-100 {
                opacity: 1;
            }
        </style>
        <style>
            /* Notification Badge Button */
            .notification-badge-btn {
                position: relative;
                padding: 0.5rem;
                background: transparent;
                border: none;
                border-radius: 50%;
                cursor: pointer;
                transition: background-color 0.2s ease;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .notification-badge-btn:hover {
                background-color: rgba(0, 0, 0, 0.04);
            }

            .notification-badge-btn:active {
                background-color: rgba(0, 0, 0, 0.08);
            }

            .notification-badge {
                position: absolute;
                top: 4px;
                right: 4px;
                min-width: 20px;
                height: 20px;
                padding: 0 6px;
                background: #f44336;
                color: white;
                border-radius: 10px;
                font-size: 11px;
                font-weight: 600;
                display: flex;
                align-items: center;
                justify-content: center;
                line-height: 1;
                box-shadow: 0 2px 4px rgba(244, 67, 54, 0.3);
            }

            /* Modal Overlay */
            .notification-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 9998;
                opacity: 0;
                transition: opacity 0.3s ease;
            }

            .notification-overlay.show {
                opacity: 1;
            }

            /* Notification Modal */
            .notification-modal {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 9999;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 16px;
                opacity: 0;
                transition: opacity 0.3s ease;
                pointer-events: none;
            }

            .notification-modal.show {
                opacity: 1;
                pointer-events: auto;
            }

            .notification-modal-container {
                background: white;
                border-radius: 12px;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12),
                    0 2px 8px rgba(0, 0, 0, 0.08);
                width: 100%;
                max-width: 480px;
                max-height: 85vh;
                display: flex;
                flex-direction: column;
                transform: scale(0.9);
                transition: transform 0.3s ease;
            }

            .notification-modal.show .notification-modal-container {
                transform: scale(1);
            }

            /* Header */
            .notification-modal-header {
                padding: 20px 24px;
                border-bottom: 1px solid #e0e0e0;
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-shrink: 0;
            }

            .notification-header-content {
                display: flex;
                align-items: center;
                gap: 12px;
            }

            .notification-modal-title {
                font-size: 20px;
                font-weight: 600;
                color: #1f2937;
                margin: 0;
            }

            .notification-modal-count {
                font-size: 13px;
                color: #6b7280;
                background: #f3f4f6;
                padding: 4px 12px;
                border-radius: 12px;
                font-weight: 500;
            }

            .notification-close-btn {
                width: 36px;
                height: 36px;
                padding: 0;
                background: transparent;
                border: none;
                border-radius: 50%;
                cursor: pointer;
                color: #6b7280;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.2s ease;
            }

            .notification-close-btn:hover {
                background: #f3f4f6;
                color: #1f2937;
            }

            .notification-close-btn:active {
                background: #e5e7eb;
            }

            /* Notification List */
            .notification-modal-list {
                overflow-y: auto;
                flex: 1;
                min-height: 200px;
            }

            .notification-modal-list::-webkit-scrollbar {
                width: 8px;
            }

            .notification-modal-list::-webkit-scrollbar-track {
                background: #f9fafb;
            }

            .notification-modal-list::-webkit-scrollbar-thumb {
                background: #d1d5db;
                border-radius: 4px;
            }

            .notification-modal-list::-webkit-scrollbar-thumb:hover {
                background: #9ca3af;
            }

            /* Notification Item */
            .notification-modal-item {
                display: flex;
                align-items: flex-start;
                gap: 14px;
                padding: 16px 24px;
                border-bottom: 1px solid #f3f4f6;
                text-decoration: none;
                color: inherit;
                transition: background-color 0.15s ease;
                position: relative;
            }

            .notification-modal-item:hover {
                background-color: #f9fafb;
            }

            .notification-modal-item:active {
                background-color: #f3f4f6;
            }

            .notification-modal-item:last-child {
                border-bottom: none;
            }

            /* Icon */
            .notification-modal-icon {
                width: 48px;
                height: 48px;
                border-radius: 50%;
                background: #5b67f5;
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }

            /* Content */
            .notification-modal-content {
                flex: 1;
                min-width: 0;
            }

            .notification-modal-item-title {
                font-size: 15px;
                font-weight: 600;
                color: #111827;
                margin-bottom: 6px;
                line-height: 1.4;
            }

            .notification-modal-item-message {
                font-size: 14px;
                color: #6b7280;
                line-height: 1.5;
                margin-bottom: 8px;
            }

            .notification-modal-item-time {
                font-size: 13px;
                color: #9ca3af;
                display: flex;
                align-items: center;
                gap: 4px;
            }

            /* Unread Indicator */
            .notification-modal-unread {
                width: 10px;
                height: 10px;
                background: #3b82f6;
                border-radius: 50%;
                flex-shrink: 0;
                margin-top: 6px;
            }

            /* Footer */
            .notification-modal-footer {
                padding: 16px 24px;
                border-top: 1px solid #e0e0e0;
                text-align: center;
                flex-shrink: 0;
            }

            .notification-view-all-btn {
                font-size: 14px;
                font-weight: 600;
                color: #5b67f5;
                text-decoration: none;
                display: inline-block;
                transition: color 0.2s ease;
            }

            .notification-view-all-btn:hover {
                color: #4c56d8;
                text-decoration: underline;
            }

            /* Prevent body scroll when modal is open */
            body.modal-open {
                overflow: hidden;
                padding-right: var(--scrollbar-width, 0);
            }

            /* Responsive Design */
            @media (max-width: 640px) {
                .notification-modal {
                    padding: 0;
                    align-items: flex-end;
                }

                .notification-modal-container {
                    max-width: 100%;
                    max-height: 90vh;
                    border-radius: 12px 12px 0 0;
                }

                .notification-modal-header {
                    padding: 16px 20px;
                }

                .notification-modal-title {
                    font-size: 18px;
                }

                .notification-modal-item {
                    padding: 14px 20px;
                }

                .notification-modal-icon {
                    width: 44px;
                    height: 44px;
                }

                .notification-modal-item-title {
                    font-size: 14px;
                }

                .notification-modal-item-message {
                    font-size: 13px;
                }
            }
        </style>
    @endpush
    <x-main-div>
        <div class="p-4 mx-auto max-w-7xl sm:p-6 lg:p-8">

            <!-- Header Section -->
            <div class="mb-6">
                <div class="flex flex-col gap-4">
                    <div>
                        <h1 class="mb-2 text-2xl font-bold text-white sm:text-3xl">Rekap Data Bulanan</h1>
                        <p class="flex items-center gap-2 text-sm text-white/80">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                </path>
                            </svg>
                            Data Berdasarkan Per Mitra
                        </p>
                    </div>

                    <!-- Search Section -->
                    <form action="{{ route('manajemen_rekap') }}" method="GET" class="w-full">
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                                    placeholder="Cari mitra..."
                                    class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                                <svg class="absolute w-5 h-5 text-gray-400 pointer-events-none left-3 top-3"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>

                            <!-- Search Button (visible on mobile) -->
                            <button type="submit"
                                class="sm:hidden px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </button>

                            <!-- Clear Button -->
                            @if (request('search'))
                                <a href="{{ route('manajemen_rekap') }}"
                                    class="px-4 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-medium transition-colors flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Stats Summary -->
            <div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-3">
                <!-- Total Mitra -->
                <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-indigo-600 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium tracking-wide text-gray-500 uppercase">Total Mitra</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $client->total() }}</p>
                        </div>
                    </div>
                </div>

                <!-- Halaman -->
                <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="p-3 rounded-lg bg-emerald-600">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium tracking-wide text-gray-500 uppercase">Halaman</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $client->currentPage() }} /
                                {{ $client->lastPage() }}</p>
                        </div>
                    </div>
                </div>

                <!-- Bulan Ini -->
                <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="p-3 rounded-lg bg-violet-600">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium tracking-wide text-gray-500 uppercase">Bulan Ini</p>
                            <p class="text-2xl font-bold text-gray-900">{{ now()->format('M Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Global Export Section -->
            <div class="my-6">
                <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-xs">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="mb-1 text-lg font-semibold text-gray-900">Export Data Keseluruhan</h3>
                            <p class="text-sm text-gray-600">Export seluruh data rekap bulanan dari semua mitra dalam
                                format Excel atau PDF</p>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <div class="flex flex-col justify-center gap-1 items-center">
                                <label for="bulanRekap" class="label label-text pb-0">Bulan tgl(26-25)</label>
                                <input id="bulanRekap" type="month" name="bulanRekap" value="{{ \Carbon\Carbon::now()->format('Y-m') }}" onchange="bulanRekap = this.value" class="input input-sm input-bordered"/>
                            </div>
                            <div class="flex flex-col justify-center gap-1 items-center">
                                <label for="tanggalDariRekap" class="label label-text pb-0">Dari</label>
                                <input id="tanggalDariRekap" type="date" value="{{ now()->subMonth()->format('Y-m-d') }}" class="input input-sm input-bordered"/>
                            </div>
                            <div class="flex flex-col justify-center gap-1 items-center">
                                <label for="tanggalSampaiRekap" class="label label-text pb-0">Sampai</label>
                                <input id="tanggalSampaiRekap" type="date" value="{{ now()->format('Y-m-d') }}" class="input input-sm input-bordered"/>
                            </div>
                            <button onclick="exportGlobalToExcel()"
                                class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium text-sm transition-colors flex items-center gap-2 whitespace-nowrap">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                <span>Excel</span>
                            </button>
                            <button onclick="exportGlobalToPDF()"
                                class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium text-sm transition-colors flex items-center gap-2 whitespace-nowrap">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                    </path>
                                </svg>
                                <span>PDF</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Info -->
            @if (request('search'))
                <div
                    class="flex flex-col gap-2 p-3 mb-4 border border-indigo-200 rounded-lg bg-indigo-50 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm text-indigo-700">
                            Hasil pencarian: <strong class="text-indigo-900">"{{ request('search') }}"</strong>
                            <span class="text-gray-600">({{ $client->total() }} mitra)</span>
                        </span>
                    </div>
                    <a href="{{ route('manajemen_rekap') }}"
                        class="text-sm font-medium text-indigo-600 transition-colors hover:text-indigo-800">
                        Hapus Filter
                    </a>
                </div>
            @endif

            <!-- Client List -->
            <div class="space-y-3" id="clientList">
                @forelse($client as $mitra)
                    <div
                        class="p-4 transition-all duration-200 bg-white border border-t-2 border-gray-200 rounded-lg border-t-gray-300 first:border-t-0 hover:border-indigo-300 hover:shadow-md">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                            <!-- Avatar and Info -->
                            @php
                                $unread = $notifications[$mitra->id] ?? collect();
                            @endphp

                            <div class="flex items-start flex-1 min-w-0 gap-4 sm:items-center">
                                <!-- Avatar -->
                                <div
                                    class="flex items-center justify-center shrink-0 w-12 h-12 text-lg font-bold text-white bg-indigo-600 rounded-lg sm:w-14 sm:h-14">
                                    {{ strtoupper(substr($mitra->client->name ?? 'M', 0, 1)) }}
                                </div>

                                <!-- Client Info -->
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-gray-900 font-semibold text-base sm:text-lg mb-1.5 wrap-break-word">
                                        {{ $mitra->client->name ?? 'Nama Mitra' }}
                                    </h3>
                                    <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:gap-4">
                                        @if ($mitra->client->email)
                                            <p
                                                class="text-gray-600 text-xs sm:text-sm flex items-center gap-1.5 break-all">
                                                <svg class="shrink-0 w-4 h-4 text-gray-400" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                                    </path>
                                                </svg>
                                                <span class="break-all">{{ $mitra->client->email }}</span>
                                            </p>
                                        @endif
                                        @if ($mitra->client->phone)
                                            <p class="text-gray-600 text-xs sm:text-sm flex items-center gap-1.5">
                                                <svg class="shrink-0 w-4 h-4 text-gray-400" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                                    </path>
                                                </svg>
                                                {{ $mitra->client->phone }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if ($unread->count())
                                <!-- Notification Badge Button -->
                                <div class="relative inline-block">
                                    <button onclick="toggleNotificationModal({{ $mitra->id }})"
                                        class="notification-badge-btn" aria-label="Notifications"
                                        aria-expanded="false" id="notif-btn-{{ $mitra->id }}">
                                        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                        <span class="notification-badge">
                                            {{ $unread->count() > 99 ? '99+' : $unread->count() }}
                                        </span>
                                    </button>
                                </div>

                                <!-- Modal Overlay -->
                                <div id="notif-overlay-{{ $mitra->id }}" class="hidden notification-overlay"
                                    onclick="closeNotificationModal({{ $mitra->id }})"></div>

                                <!-- Notification Modal -->
                                <div id="notif-modal-{{ $mitra->id }}" class="hidden notification-modal"
                                    role="dialog" aria-labelledby="notif-title-{{ $mitra->id }}"
                                    aria-modal="true">
                                    <!-- Modal Container -->
                                    <div class="notification-modal-container" onclick="event.stopPropagation()">

                                        <!-- Header -->
                                        <div class="notification-modal-header">
                                            <div class="notification-header-content">
                                                <h3 id="notif-title-{{ $mitra->id }}"
                                                    class="notification-modal-title">
                                                    Notifications
                                                </h3>
                                                <span class="notification-modal-count">{{ $unread->count() }}
                                                    new</span>
                                            </div>
                                            <button onclick="closeNotificationModal({{ $mitra->id }})"
                                                class="notification-close-btn" aria-label="Close notifications">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>

                                        <!-- Notification List -->
                                        <div class="notification-modal-list">
                                            @foreach ($unread as $notif)
                                                <a href="{{ route('notifications.redirect', $notif->id) }}"
                                                    class="notification-modal-item">
                                                    <!-- Icon -->
                                                    <div class="notification-modal-icon">
                                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                            <path
                                                                d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6z" />
                                                        </svg>
                                                    </div>

                                                    <!-- Content -->
                                                    <div class="notification-modal-content">
                                                        <div class="notification-modal-item-title">
                                                            {{ $notif->data['title'] }}
                                                        </div>
                                                        <div class="notification-modal-item-message">
                                                            {{ $notif->data['message'] }}
                                                        </div>
                                                        <div class="notification-modal-item-time">
                                                            <svg class="w-3.5 h-3.5 inline" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                            {{ $notif->created_at->diffForHumans() }}
                                                        </div>
                                                    </div>

                                                    <!-- Unread Indicator -->
                                                    <div class="notification-modal-unread"></div>
                                                </a>
                                            @endforeach
                                        </div>

                                        <!-- Footer -->
                                        <div class="notification-modal-footer">
                                            <a href="#" class="notification-view-all-btn">
                                                View all notifications
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Action Button -->
                            <button
                                onclick="openModal({{ $mitra->id }}, '{{ addslashes($mitra->client->name ?? 'Nama Mitra') }}')"
                                class="w-full sm:w-auto shrink-0 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors duration-200 flex items-center justify-center gap-2 group">
                                <span>Lihat Detail</span>
                                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center bg-white border border-gray-200 rounded-lg shadow-xs sm:p-12">
                        <div class="max-w-sm mx-auto">
                            <div
                                class="flex items-center justify-center w-20 h-20 mx-auto mb-4 bg-gray-100 rounded-full">
                                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                    </path>
                                </svg>
                            </div>
                            @if (request('search'))
                                <p class="mb-1 text-base font-semibold text-gray-800 sm:text-lg">Tidak ada hasil
                                    pencarian</p>
                                <p class="mb-4 text-sm text-gray-500">Coba kata kunci lain atau hapus filter pencarian
                                </p>
                                <a href="{{ route('manajemen_rekap') }}"
                                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition-colors bg-indigo-600 rounded-lg hover:bg-indigo-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Hapus Filter
                                </a>
                            @else
                                <p class="mb-1 text-base font-semibold text-gray-800 sm:text-lg">Tidak ada data mitra
                                </p>
                                <p class="text-sm text-gray-500">Silakan tambahkan mitra baru untuk memulai</p>
                            @endif
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if ($client->hasPages())
                <div class="mt-6">
                    <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-xs">
                        {{ $client->appends(['search' => request('search')])->links() }}
                    </div>
                </div>
            @endif

            <!-- Modal Overlay -->
            <div id="detailModal" class="fixed inset-0 z-999999 hidden" aria-labelledby="modal-title"
                role="dialog" aria-modal="true">
                <!-- Background overlay -->
                <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75" onclick="closeModal()"></div>

                <!-- Modal Container -->
                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div class="flex items-center justify-center min-h-full p-4">
                        <!-- Modal Content -->
                        <div
                            class="relative w-full max-w-md overflow-hidden transition-all transform bg-white rounded-lg shadow-xl">
                            <!-- Modal Header -->
                            <div class="flex flex-col px-6 pt-6 pb-4 bg-white gap-y-2">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center justify-center shrink-0 w-12 h-12 text-lg font-bold text-white bg-indigo-600 rounded-lg"
                                            id="modalAvatar">
                                            M
                                        </div>
                                        <div>
                                            <h3 class="font-semibold leading-tight text-gray-900 text-md"
                                                id="modalTitle">
                                                Nama Mitra</h3>
                                            <p class="text-sm text-gray-500 mt-0.5">Pilih data yang ingin dilihat</p>
                                        </div>
                                    </div>
                                    <button type="button" onclick="closeModal()"
                                        class="shrink-0 ml-3 text-gray-400 rounded-md hover:text-gray-500 focus:outline-hidden">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="flex items-center justify-end">
                                    <!-- Export Date Range + Buttons -->
                                    <div class="flex flex-wrap items-end gap-2">
                                        <label class="flex flex-col gap-1 text-xs text-gray-600">
                                            Dari
                                            <input id="modalTanggalDariRekap" type="date" class="px-2 py-2 text-sm border border-gray-300 rounded-lg">
                                        </label>
                                        <label class="flex flex-col gap-1 text-xs text-gray-600">
                                            Sampai
                                            <input id="modalTanggalSampaiRekap" type="date" class="px-2 py-2 text-sm border border-gray-300 rounded-lg">
                                        </label>
                                        <button onclick="exportToExcel()"
                                            class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium text-sm transition-colors flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                </path>
                                            </svg>
                                            <span>Excel</span>
                                        </button>
                                        <button onclick="exportToPDF()"
                                            class="px-3.5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium text-sm transition-colors flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                            <span>PDF</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Body -->
                            <div class="px-6 py-4 space-y-3 bg-white">
                                <!-- Data Lembur Button -->
                                <a href="#" id="linkLembur"
                                    class="flex items-center gap-3 p-4 transition-all duration-200 border border-indigo-200 rounded-lg group bg-linear-to-r from-indigo-50 to-indigo-100 hover:from-indigo-100 hover:to-indigo-200">
                                    <div
                                        class="flex items-center justify-center shrink-0 w-10 h-10 bg-indigo-600 rounded-lg">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4
                                            class="text-gray-900 font-semibold text-sm mb-0.5 group-hover:text-indigo-700 transition-colors">
                                            Data Lembur
                                        </h4>
                                        <p class="text-xs text-gray-600">
                                            Lihat rekap data lembur karyawan
                                        </p>
                                    </div>
                                    <svg class="shrink-0 w-5 h-5 text-indigo-600 transition-transform group-hover:translate-x-1"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>

                                <!-- Data Personil Keluar Button -->
                                <a href="#" id="linkPersonilKeluar"
                                    class="flex items-center gap-3 p-4 transition-all duration-200 border rounded-lg group bg-linear-to-r from-emerald-50 to-emerald-100 hover:from-emerald-100 hover:to-emerald-200 border-emerald-200">
                                    <div
                                        class="flex items-center justify-center shrink-0 w-10 h-10 rounded-lg bg-emerald-600">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                            </path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4
                                            class="text-gray-900 font-semibold text-sm mb-0.5 group-hover:text-emerald-700 transition-colors">
                                            Data Personil Keluar
                                        </h4>
                                        <p class="text-xs text-gray-600">
                                            Lihat data karyawan yang keluar
                                        </p>
                                    </div>
                                    <svg class="shrink-0 w-5 h-5 transition-transform text-emerald-600 group-hover:translate-x-1"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>

                                <a href="#" id="linkPersonilMasuk"
                                    class="flex items-center gap-3 p-4 transition-all duration-200 border border-blue-200 rounded-lg group bg-linear-to-r from-blue-50 to-blue-100 hover:from-blue-100 hover:to-blue-200">
                                    <div
                                        class="flex items-center justify-center shrink-0 w-10 h-10 bg-blue-600 rounded-lg">
                                        <i class="text-lg text-white ri-user-follow-line"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4
                                            class="text-gray-900 font-semibold text-sm mb-0.5 group-hover:text-blue-700 transition-colors">
                                            Data Personil Masuk
                                        </h4>
                                        <p class="text-xs text-gray-600">
                                            Lihat data karyawan yang masuk
                                        </p>
                                    </div>
                                    <svg class="shrink-0 w-5 h-5 text-blue-600 transition-transform group-hover:translate-x-1"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>

                                <a href="#" id="linkCutting"
                                    class="flex items-center gap-3 p-4 transition-all duration-200 border rounded-lg group bg-linear-to-r from-rose-50 to-rose-100 hover:from-rose-100 hover:to-rose-200 border-rose-200">
                                    <div
                                        class="flex items-center justify-center shrink-0 w-10 h-10 rounded-lg bg-rose-600">
                                        <i class="text-lg text-white ri-scissors-cut-line"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4
                                            class="text-gray-900 font-semibold text-sm mb-0.5 group-hover:text-rose-700 transition-colors">
                                            Data Cutting
                                        </h4>
                                        <p class="text-xs text-gray-600">
                                            Lihat data pengajuan cutting
                                        </p>
                                    </div>
                                    <svg class="shrink-0 w-5 h-5 transition-transform text-rose-600 group-hover:translate-x-1"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>

                                <a href="#" id="linkFinishedTraining"
                                    class="flex items-center gap-3 p-4 transition-all duration-200 border rounded-lg group bg-linear-to-r from-violet-50 to-violet-100 hover:from-violet-100 hover:to-violet-200 border-violet-200">
                                    <div
                                        class="flex items-center justify-center shrink-0 w-10 h-10 rounded-lg bg-violet-600">
                                        <i class="text-lg text-white ri-graduation-cap-line"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4
                                            class="text-gray-900 font-semibold text-sm mb-0.5 group-hover:text-violet-700 transition-colors">
                                            Data Lepas Training
                                        </h4>
                                        <p class="text-xs text-gray-600">
                                            Lihat data pengajuan lepas training
                                        </p>
                                    </div>
                                    <svg class="shrink-0 w-5 h-5 transition-transform text-violet-600 group-hover:translate-x-1"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>

                                <a href="#" id="linkKeteranganLanjutan"
                                    class="flex items-center gap-3 p-4 transition-all duration-200 border border-yellow-200 rounded-lg group bg-linear-to-r from-yellow-50 to-yellow-100 hover:from-yellow-100 hover:to-yellow-200">
                                    <div
                                        class="flex items-center justify-center shrink-0 w-10 h-10 bg-yellow-600 rounded-lg">
                                        <i class="text-lg text-white ri-file-list-line"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4
                                            class="text-gray-900 font-semibold text-sm mb-0.5 group-hover:text-yellow-700 transition-colors">
                                            Keterangan Lanjutan
                                        </h4>
                                        <p class="text-xs text-gray-600">
                                            Lihat keterangan lanjutan mitra
                                        </p>
                                    </div>
                                    <svg class="shrink-0 w-5 h-5 text-yellow-600 transition-transform group-hover:translate-x-1"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>

                            <!-- Modal Footer -->
                            <div class="px-6 py-4 rounded-b-lg bg-gray-50">
                                <button type="button" onclick="closeModal()"
                                    class="w-full px-4 py-2.5 bg-white hover:bg-gray-100 text-gray-700 border border-gray-300 rounded-lg font-medium transition-colors">
                                    Batal
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </x-main-div>

    <script>
        function openNotif(id) {
            document.getElementById('notif-' + id)
                .classList.toggle('hidden')
        }
        // Auto-submit search on desktop (hidden on mobile since we have button)
        const searchInput = document.getElementById('searchInput');
        let searchTimeout;

        if (searchInput && window.innerWidth >= 640) {
            searchInput.addEventListener('input', function(e) {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    if (e.target.value.length >= 2 || e.target.value.length == 0) {
                        this.form.submit();
                    }
                }, 500);
            });
        }

        // Submit on Enter key for all devices
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key == 'Enter') {
                    clearTimeout(searchTimeout);
                    e.preventDefault();
                    this.form.submit();
                }
            });
        }

        // Modal Functions
        let scrollPosition = 0;

        function openModal(mitraId, mitraName) {
            const modal = document.getElementById('detailModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalAvatar = document.getElementById('modalAvatar');
            const linkLembur = document.getElementById('linkLembur');
            const linkPersonilKeluar = document.getElementById('linkPersonilKeluar');
            const linkPersonilMasuk = document.getElementById('linkPersonilMasuk');
            const linkCutting = document.getElementById('linkCutting');
            const linkFinishedTraining = document.getElementById('linkFinishedTraining');
            const linkKeteranganLanjutan = document.getElementById('linkKeteranganLanjutan');

            // Save current scroll position
            scrollPosition = window.pageYOffset || document.documentElement.scrollTop;

            // Set modal content
            modalTitle.textContent = mitraName;
            modalAvatar.textContent = mitraName.charAt(0).toUpperCase();

            // Set links with mitra ID
            linkLembur.href = `/Management/rekap-overtimes/${mitraId}`;
            linkPersonilKeluar.href = `/Management/rekap-person-out/${mitraId}`;
            linkPersonilMasuk.href = `/Management/rekap-person-in/${mitraId}`;
            linkCutting.href = `/Management/rekap-cutting/${mitraId}`;
            linkFinishedTraining.href = `/Management/rekap-finished-training/${mitraId}`;
            linkKeteranganLanjutan.href = `/Management/rekap-keterangan-lanjutan/${mitraId}`;
            // Show modal
            modal.classList.remove('hidden');

            // Prevent body scroll
            document.body.style.overflow = 'hidden';
            document.body.style.position = 'fixed';
            document.body.style.top = `-${scrollPosition}px`;
            document.body.style.width = '100%';
        }

        function closeModal() {
            const modal = document.getElementById('detailModal');

            // Hide modal
            modal.classList.add('hidden');

            // Restore body scroll
            document.body.style.overflow = '';
            document.body.style.position = '';
            document.body.style.top = '';
            document.body.style.width = '';

            // Restore scroll position
            window.scrollTo(0, scrollPosition);
        }

        // Close modal when clicking outside
        document.getElementById('detailModal').addEventListener('click', function(e) {
            if (e.target == this || e.target.classList.contains('bg-opacity-75')) {
                closeModal();
            }
        });

        // Close modal with ESC key
        document.addEventListener('keydown', function(e) {
            const modal = document.getElementById('detailModal');
            if (e.key == 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });

        function getScrollbarWidth() {
            const outer = document.createElement('div');
            outer.style.visibility = 'hidden';
            outer.style.overflow = 'scroll';
            document.body.appendChild(outer);

            const inner = document.createElement('div');
            outer.appendChild(inner);

            const scrollbarWidth = outer.offsetWidth - inner.offsetWidth;

            outer.parentNode.removeChild(outer);

            return scrollbarWidth;
        }

        function toggleNotificationModal(mitraId) {
            const modal = document.getElementById(`notif-modal-${mitraId}`);
            const overlay = document.getElementById(`notif-overlay-${mitraId}`);
            const button = document.getElementById(`notif-btn-${mitraId}`);
            const isOpen = modal.classList.contains('show');

            if (!isOpen) {
                openNotificationModal(mitraId);
            } else {
                closeNotificationModal(mitraId);
            }
        }

        function openNotificationModal(mitraId) {
            const modal = document.getElementById(`notif-modal-${mitraId}`);
            const overlay = document.getElementById(`notif-overlay-${mitraId}`);
            const button = document.getElementById(`notif-btn-${mitraId}`);

            // Calculate and set scrollbar width
            const scrollbarWidth = getScrollbarWidth();
            document.documentElement.style.setProperty('--scrollbar-width', `${scrollbarWidth}px`);

            // Prevent body scroll
            document.body.classList.add('modal-open');

            // Show modal
            modal.classList.remove('hidden');
            overlay.classList.remove('hidden');

            // Trigger animation
            requestAnimationFrame(() => {
                modal.classList.add('show');
                overlay.classList.add('show');
            });

            button.setAttribute('aria-expanded', 'true');
        }

        function closeNotificationModal(mitraId) {
            const modal = document.getElementById(`notif-modal-${mitraId}`);
            const overlay = document.getElementById(`notif-overlay-${mitraId}`);
            const button = document.getElementById(`notif-btn-${mitraId}`);

            // Hide modal
            modal.classList.remove('show');
            overlay.classList.remove('show');

            // Remove from DOM after animation
            setTimeout(() => {
                modal.classList.add('hidden');
                overlay.classList.add('hidden');

                // Re-enable body scroll
                document.body.classList.remove('modal-open');
                document.documentElement.style.removeProperty('--scrollbar-width');
            }, 300);

            button.setAttribute('aria-expanded', 'false');
        }

        // Rekap Exporter - DRY & SOLID approach
        let currentKerjasamaId = null;
        let bulanRekap = document.getElementById('bulanRekap').value;

        // Override openModal to capture kerjasamaId
        const originalOpenModal = window.openModal;
        window.openModal = function(mitraId, mitraName) {
            currentKerjasamaId = mitraId;
            originalOpenModal(mitraId, mitraName);
        };

        // Export to Excel - All Rekap Data
        function exportToExcel() {
            if (!currentKerjasamaId) {
                alert('Silakan pilih mitra terlebih dahulu');
                return;
            }

            const exporter = new RekapExporter(currentKerjasamaId, bulanRekap, '/Management/api/v1/all-rekap-export', document.getElementById('modalTanggalDariRekap')?.value, document.getElementById('modalTanggalSampaiRekap')?.value);
            exporter.exportToExcel();
        }

        // Export to PDF - All Rekap Data
        function exportToPDF() {
            if (!currentKerjasamaId) {
                alert('Silakan pilih mitra terlebih dahulu');
                return;
            }

            const exporter = new RekapExporter(currentKerjasamaId, bulanRekap, '/Management/api/v1/all-rekap-export', document.getElementById('modalTanggalDariRekap')?.value, document.getElementById('modalTanggalSampaiRekap')?.value);
            exporter.exportToPDF();
        }

        // Global Export - All Data
        function globalExportParams() {
            const params = new URLSearchParams({ month: bulanRekap });
            const startDate = document.getElementById('tanggalDariRekap')?.value;
            const endDate = document.getElementById('tanggalSampaiRekap')?.value;
            if (startDate && endDate) {
                params.set('start_date', startDate);
                params.set('end_date', endDate);
            }
            return params;
        }

        async function exportGlobalToExcel() {
            try {
                const response = await fetch(`/api/v1/all-rekap-export-global?${globalExportParams()}`);
                if (!response.ok) throw new Error('Failed to fetch data');
                const result = await response.json();

                if (!result.success) throw new Error(result.message);
                const data = result.data;

                const exporter = new RekapExporter(null, bulanRekap, '/Management/api/v1/all-rekap-export');
                exporter.exportGlobalToExcel(data);
            } catch (e) {
                alert('Error: ' + e.message);
            }
        }

        async function exportGlobalToPDF() {
            try {
                const response = await fetch(`/api/v1/all-rekap-export-global?${globalExportParams()}`);
                if (!response.ok) throw new Error('Failed to fetch data');
                const result = await response.json();

                if (!result.success) throw new Error(result.message);
                const data = result.data;

                const exporter = new RekapExporter(null, bulanRekap, '/Management/api/v1/all-rekap-export');
                exporter.exportGlobalToPDF(data);
            } catch (e) {
                alert('Error: ' + e.message);
            }
        }

        // Close on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key == 'Escape') {
                const openModals = document.querySelectorAll('.notification-modal.show');
                openModals.forEach(modal => {
                    const mitraId = modal.id.replace('notif-modal-', '');
                    closeNotificationModal(mitraId);
                });
            }
        });
    </script>
</x-app-layout>
