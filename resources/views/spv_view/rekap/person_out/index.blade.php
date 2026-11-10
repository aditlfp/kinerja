<x-app-layout>
    <x-main-div>
        <div class="p-4 mx-auto max-w-7xl sm:p-6 lg:p-8">

            <!-- Header Section with Back Button -->
            <div class="mb-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <a href="{{ route('manajemen_rekap') }}"
                                class="p-2 transition-colors bg-gray-200 rounded-lg hover:bg-gray-300">
                                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </a>
                            <div>
                                <h1 class="text-2xl font-bold text-white sm:text-3xl">Data Personil Keluar</h1>
                                <p class="mt-1 text-sm font-semibold text-gray-300" id="clientName">Loading...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Export Buttons -->
                    <div class="flex flex-wrap gap-2">
                        <button onclick="exportToExcel()"
                            class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium transition-colors flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            <span>Excel</span>
                        </button>
                        <button onclick="exportToPDF()"
                            class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                </path>
                            </svg>
                            <span>PDF</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-4">
                <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-red-600 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium tracking-wide text-gray-500 uppercase">Total Keluar</p>
                            <p class="text-2xl font-bold text-gray-900" id="totalPersonOut">0</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="p-3 rounded-lg bg-emerald-600">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium tracking-wide text-gray-500 uppercase">Di Ajukan</p>
                            <p class="text-2xl font-bold text-gray-900" id="approvedCount">0</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="p-3 rounded-lg bg-amber-600">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium tracking-wide text-gray-500 uppercase">Pending</p>
                            <p class="text-2xl font-bold text-gray-900" id="pendingCount">0</p>
                        </div>
                    </div>
                </div>

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
                            <p class="text-xs font-medium tracking-wide text-gray-500 uppercase">Periode</p>
                            <p class="text-lg font-bold text-gray-900" id="periodDate">{{ now()->format('M Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="p-4 mb-6 bg-white border border-gray-200 rounded-lg shadow-xs">
                <div class="flex flex-col gap-3 sm:flex-row">
                    <div class="flex-1">
                        <label class="block mb-1 text-sm font-medium text-gray-700">Cari Karyawan</label>
                        <input type="text" id="searchEmployee" placeholder="Nama karyawan..."
                            class="w-full px-4 py-2 text-gray-900 placeholder-gray-400 bg-white border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div class="flex-1">
                        <label class="block mb-1 text-sm font-medium text-gray-700">Filter Bulan</label>
                        <input type="month" id="filterMonth" value="{{ now()->format('Y-m') }}"
                            class="w-full px-4 py-2 text-gray-900 bg-white border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div class="flex items-end">
                        <button onclick="resetFilters()"
                            class="w-full px-4 py-2 font-medium text-gray-700 transition-colors bg-gray-200 rounded-lg sm:w-auto hover:bg-gray-300">
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div id="loadingState" class="p-8 text-center bg-white border border-gray-200 rounded-lg shadow-xs">
                <div class="w-12 h-12 mx-auto mb-4 border-b-2 rounded-full animate-spin border-emerald-600"></div>
                <p class="text-gray-600">Memuat data...</p>
            </div>

            <!-- Table Section -->
            <div id="tableContainer"
                class="hidden overflow-hidden bg-white border border-gray-200 rounded-lg shadow-xs">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="personOutTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    No</th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    Nama Karyawan</th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    Nama Penginput</th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    Posisi</th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    Tanggal Keluar</th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    Alasan</th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    Status</th>
                            <th class="px-4 py-3 text-xs text-center text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="tableBody">
                            <!-- Data will be inserted here -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination Section -->
            <div id="paginationContainer"
                class="hidden p-4 mt-4 bg-white border border-gray-200 rounded-lg shadow-xs">
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <!-- Info -->
                    <div class="text-sm text-gray-700">
                        Menampilkan <span class="font-medium" id="showingFrom">1</span> -
                        <span class="font-medium" id="showingTo">10</span> dari
                        <span class="font-medium" id="totalRecords">0</span> data
                    </div>

                    <!-- Items per page -->
                    <div class="flex items-center gap-2 mr-4">
                        <label class="text-sm text-gray-700">Per halaman:</label>
                        <select id="itemsPerPage"
                            class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-gray-900 text-sm focus:outline-hidden focus:ring-2 focus:ring-emerald-500">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="all">Semua</option>
                        </select>
                    </div>
                    <!-- Pagination Controls -->
                    <div class="flex items-center gap-2">

                        <!-- First Page -->
                        <button onclick="goToFirstPage()" id="firstPageBtn"
                            class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                            </svg>
                        </button>

                        <!-- Previous Page -->
                        <button onclick="goToPreviousPage()" id="prevPageBtn"
                            class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>

                        <!-- Page Numbers -->
                        <div id="pageNumbers" class="flex items-center gap-1">
                        </div>

                        <!-- Next Page -->
                        <button onclick="goToNextPage()" id="nextPageBtn"
                            class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </button>

                        <!-- Last Page -->
                        <button onclick="goToLastPage()" id="lastPageBtn"
                            class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div id="emptyState"
                class="hidden p-8 text-center bg-white border border-gray-200 rounded-lg shadow-xs sm:p-12">
                <div class="max-w-sm mx-auto">
                    <div class="flex items-center justify-center w-20 h-20 mx-auto mb-4 bg-gray-100 rounded-full">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                    </div>
                    <p class="mb-1 text-base font-semibold text-gray-800 sm:text-lg" id="emptyStateTitle">Tidak ada
                        data</p>
                    <p class="text-sm text-gray-500" id="emptyStateDesc">Belum ada data personil keluar untuk periode
                        ini</p>
                </div>
            </div>

        </div>
    </x-main-div>

    <!-- Include SheetJS for Excel export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <!-- Include jsPDF for PDF export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

    <script>
        let personOutData = [];
        let filteredData = [];

        // Pagination variables
        let currentPage = 1;
        let itemsPerPage = 10;
        let totalPages = 1;

        // Fetch person out data
        async function fetchPersonOutData() {
            try {
                const kerjasamaId = window.location.pathname.split('/').pop();
                const filterMonth = document.getElementById('filterMonth').value;

                const url = `/api/v1/person-out-api/${kerjasamaId}${filterMonth ? `?month=${filterMonth}` : ''}`;

                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    }
                });


                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();

                // Hide loading state
                document.getElementById('loadingState').classList.add('hidden');

                if (result.success && result.data) {
                    personOutData = result.data;
                    filteredData = personOutData;

                    // Set client name from first record
                    if (personOutData.length > 0 && personOutData[0].user?.kerjasama?.client?.name) {
                        document.getElementById('clientName').textContent = personOutData[0].user.kerjasama.client.name;
                    }

                    if (personOutData.length > 0) {
                        renderTable();
                        updateStats();
                        document.getElementById('tableContainer').classList.remove('hidden');
                        document.getElementById('paginationContainer').classList.remove('hidden');
                    } else {
                        showEmptyState('Tidak ada data', 'Belum ada data personil keluar untuk periode ini');
                    }
                } else {
                    showEmptyState('Gagal memuat data', result.message || 'Terjadi kesalahan saat mengambil data');
                }
            } catch (error) {
                console.error('Fetch Error:', error);
                document.getElementById('loadingState').classList.add('hidden');
                showEmptyState('Terjadi Kesalahan', error.message || 'Tidak dapat terhubung ke server');
            }
        }

        // Show empty state
        function showEmptyState(title, description) {
            document.getElementById('emptyState').classList.remove('hidden');
            document.getElementById('emptyStateTitle').textContent = title;
            document.getElementById('emptyStateDesc').textContent = description;
        }

        // Get status badge HTML
        function getStatusBadge(status) {
            const statusLower = (status || '').toLowerCase();
            const badges = {
                'pending': '<span class="px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded-full">Pending</span>',
                'di ajukan': '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ring-1 ring-inset bg-emerald-50 text-emerald-700 ring-emerald-200">Di Ajukan</span>',
                'di setujui': '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ring-1 ring-inset bg-lime-50 text-lime-700 ring-lime-200">Di Setujui</span>',
                'di tolak': '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ring-1 ring-inset bg-rose-50 text-rose-700 ring-rose-200">Di Tolak</span>',
                'approved': '<span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Approved</span>',
                'rejected': '<span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Rejected</span>'
            };
            return badges[statusLower] ||
                `<span class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full">${status || '-'}</span>`;
        }

        // Render table with pagination
        function renderTable() {
            const tbody = document.getElementById('tableBody');
            tbody.innerHTML = '';

            if (filteredData.length === 0) {
                showEmptyState('Tidak ada hasil', 'Tidak ada data yang sesuai dengan filter');
                document.getElementById('tableContainer').classList.add('hidden');
                document.getElementById('paginationContainer').classList.add('hidden');
                return;
            }

            // Calculate pagination
            const itemsPerPageValue = document.getElementById('itemsPerPage').value;
            itemsPerPage = itemsPerPageValue === 'all' ? filteredData.length : parseInt(itemsPerPageValue);
            totalPages = Math.ceil(filteredData.length / itemsPerPage);

            if (currentPage > totalPages) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;

            // Get data for current page
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = Math.min(startIndex + itemsPerPage, filteredData.length);
            const pageData = filteredData.slice(startIndex, endIndex);

            // Render rows
            pageData.forEach((item, index) => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';

                const statusBadge = getStatusBadge(item.status);

                row.innerHTML = `
                    <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">${startIndex + index + 1}</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">${item.user?.nama_lengkap || '-'}</td>
                    <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">${item.created_by?.nama_lengkap || '-'}</td>
                    <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">${item.user?.jabatan?.name_jabatan || '-'}</td>
                    <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">${formatDate(item.out_date)}</td>
                    <td class="max-w-xs px-6 py-4 text-sm text-gray-600 truncate" title="${item.reason || '-'}">${item.reason == 'lainnya' ? item.reason_manual : item.reason}</td>
                    <td class="px-6 py-4 text-sm whitespace-nowrap">${statusBadge}</td>
                    <td class="px-6 py-4 text-sm text-center whitespace-nowrap">${actionButtons(item)}</td>
                `;

                tbody.appendChild(row);
            });

            updatePaginationUI();
            document.getElementById('emptyState').classList.add('hidden');
            document.getElementById('tableContainer').classList.remove('hidden');
            document.getElementById('paginationContainer').classList.remove('hidden');
        }

        function actionButtons(r) {
            if (!r || (r.status || '').toLowerCase() !== 'di ajukan') return '-';
            return `<div class="flex items-center justify-center gap-2">
                 <button onclick="updateStatus(${r.id}, 'Di Tolak')" class="px-2 py-1 text-xs text-white rounded-sm bg-rose-600 hover:bg-rose-700">Tolak</button>
             </div>`;
        }

        async function updateStatus(id, status) {
            const res = await fetch(`/api/v1/rekap/person-out/${id}/status`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ status })
            });
            const result = await res.json();
            if (!result.success) return alert(result.message || 'Gagal update status');
            await fetchPersonOutData();
        }

        // Update statistics
        function updateStats() {
            const totalPersonOut = filteredData.length;
            const approvedCount = filteredData.filter(item =>
                (item.status || '').toLowerCase() === 'di ajukan'
            ).length;
            const pendingCount = filteredData.filter(item =>
                (item.status || '').toLowerCase() === 'pending'
            ).length;

            document.getElementById('totalPersonOut').textContent = totalPersonOut;
            document.getElementById('approvedCount').textContent = approvedCount;
            document.getElementById('pendingCount').textContent = pendingCount;
        }

        // Format date
        function formatDate(dateString) {
            if (!dateString) return '-';
            try {
                const date = new Date(dateString);
                const options = {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                };
                return date.toLocaleDateString('id-ID', options);
            } catch (e) {
                return dateString;
            }
        }

        // Search filter
        document.getElementById('searchEmployee').addEventListener('input', function(e) {
            applyFilters();
        });

        // Month filter
        document.getElementById('filterMonth').addEventListener('change', function(e) {
            fetchPersonOutData();
        });

        // Apply filters
        function applyFilters() {
            const searchTerm = document.getElementById('searchEmployee').value.toLowerCase();

            filteredData = personOutData.filter(item => {
                const employeeName = (item.user?.nama_lengkap || '').toLowerCase();
                const matchName = !searchTerm || employeeName.includes(searchTerm);
                return matchName;
            });

            currentPage = 1;
            renderTable();
            updateStats();

            if (filteredData.length === 0) {
                document.getElementById('tableContainer').classList.add('hidden');
                document.getElementById('paginationContainer').classList.add('hidden');
                showEmptyState('Tidak ada hasil pencarian', 'Coba ubah filter atau kata kunci pencarian');
            } else {
                document.getElementById('tableContainer').classList.remove('hidden');
                document.getElementById('paginationContainer').classList.remove('hidden');
                document.getElementById('emptyState').classList.add('hidden');
            }
        }

        // Reset filters
        function resetFilters() {
            document.getElementById('searchEmployee').value = '';
            document.getElementById('filterMonth').value = '{{ now()->format('Y-m') }}';
            document.getElementById('itemsPerPage').value = '10';

            currentPage = 1;
            fetchPersonOutData();
        }

        // Pagination functions
        function updatePaginationUI() {
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = Math.min(startIndex + itemsPerPage, filteredData.length);

            document.getElementById('showingFrom').textContent = filteredData.length > 0 ? startIndex + 1 : 0;
            document.getElementById('showingTo').textContent = endIndex;
            document.getElementById('totalRecords').textContent = filteredData.length;

            document.getElementById('firstPageBtn').disabled = currentPage === 1;
            document.getElementById('prevPageBtn').disabled = currentPage === 1;
            document.getElementById('nextPageBtn').disabled = currentPage === totalPages;
            document.getElementById('lastPageBtn').disabled = currentPage === totalPages;

            renderPageNumbers();
        }

        function renderPageNumbers() {
            const pageNumbersContainer = document.getElementById('pageNumbers');
            pageNumbersContainer.innerHTML = '';

            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, startPage + 4);

            if (endPage - startPage < 4) {
                startPage = Math.max(1, endPage - 4);
            }

            if (startPage > 1) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'px-2 text-gray-500';
                ellipsis.textContent = '...';
                pageNumbersContainer.appendChild(ellipsis);
            }

            for (let i = startPage; i <= endPage; i++) {
                const button = document.createElement('button');
                button.className = i === currentPage ?
                    'px-3 py-1.5 bg-emerald-600 text-white rounded-lg font-medium' :
                    'px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors';
                button.textContent = i;
                button.onclick = () => goToPage(i);
                pageNumbersContainer.appendChild(button);
            }

            if (endPage < totalPages) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'px-2 text-gray-500';
                ellipsis.textContent = '...';
                pageNumbersContainer.appendChild(ellipsis);
            }
        }

        function goToPage(page) {
            currentPage = page;
            renderTable();
            document.getElementById('tableContainer').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }

        function goToFirstPage() {
            goToPage(1);
        }

        function goToPreviousPage() {
            if (currentPage > 1) goToPage(currentPage - 1);
        }

        function goToNextPage() {
            if (currentPage < totalPages) goToPage(currentPage + 1);
        }

        function goToLastPage() {
            goToPage(totalPages);
        }

        document.getElementById('itemsPerPage').addEventListener('change', function() {
            currentPage = 1;
            renderTable();
        });

        function toAcronym(text) {
            if (!text) return 'Sheet';

            return text
                .replace(/[^A-Za-z\s]/g, '') // buang simbol
                .split(/\s+/) // pecah kata
                .filter(word => word.length > 2) // buang kata kecil seperti "di", "ke"
                .map(word => word[0].toUpperCase())
                .join('');
        }


        // Export to Excel
        function exportToExcel() {
            const exportRows = filteredData.filter(item => (item.status || '').toLowerCase() === 'di setujui');
            if (exportRows.length === 0) {
                alert('Tidak ada data yang disetujui untuk diekspor');
                return;
            }

            const data = exportRows.map((item, index) => ({
                'No': index + 1,
                'Nama Karyawan': item.user?.nama_lengkap || '-',
                'Mitra Kerja': item.user?.kerjasama?.client?.name || '-',
                'Posisi': item.user?.jabatan?.name_jabatan || '-',
                'Jumlah MK': item.total_mk || '0'
            }));

            const ws = XLSX.utils.json_to_sheet(data);
            ws['!cols'] = [{
                    wch: 5
                },
                {
                    wch: 25
                },
                {
                    wch: 20
                },
                {
                    wch: 30
                },
                {
                    wch: 15
                },
                {
                    wch: 40
                },
                {
                    wch: 12
                }
            ];

            const clientName = document.getElementById('clientName').textContent;
            const wb = XLSX.utils.book_new();
            const sheetName = toAcronym(clientName);
            XLSX.utils.book_append_sheet(wb, ws, sheetName);

            const fileName = `Data_Personil_Keluar_${clientName}_${new Date().toISOString().split('T')[0]}.xlsx`;
            XLSX.writeFile(wb, fileName);
        }

        // Export to PDF
        function exportToPDF() {
            const exportRows = filteredData.filter(item => (item.status || '').toLowerCase() === 'di setujui');
            if (exportRows.length === 0) {
                alert('Tidak ada data yang disetujui untuk diekspor');
                return;
            }

            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF('landscape');

            const clientName = document.getElementById('clientName').textContent;

            doc.setFontSize(16);
            doc.setFont(undefined, 'bold');
            doc.text(`Data Personil Keluar - ${clientName}`, 14, 15);

            doc.setFontSize(10);
            doc.setFont(undefined, 'normal');
            doc.text(`Periode: ${document.getElementById('periodDate').textContent}`, 14, 22);
            doc.text(`Total: ${exportRows.length} | Di Setujui: ${exportRows.length}`,
                14, 28);

            const tableData = exportRows.map((item, index) => [
                index + 1,
                item.user?.nama_lengkap || '-',
                item.user?.kerjasama?.client.name,
                item.user?.jabatan?.name_jabatan || '-',
                item.total_mk ? item.total_mk : '0'
            ]);

            doc.autoTable({
                startY: 35,
                head: [
                    ['No', 'Nama Karyawan', 'Mitra Kerja', 'Posisi', 'Jumlah MK']
                ],
                body: tableData,
                theme: 'grid',
                styles: {
                    fontSize: 9,
                    cellPadding: 3
                },
                headStyles: {
                    fillColor: [16, 185, 129],
                    fontStyle: 'bold'
                },
                columnStyles: {
                    0: {
                        cellWidth: 10
                    },
                    1: {
                        cellWidth: 50
                    },
                    2: {
                        cellWidth: 80
                    },
                    3: {
                        cellWidth: 50
                    },
                    4: {
                        cellWidth: 70
                    },
                    5: {
                        cellWidth: 20
                    }
                },
                alternateRowStyles: {
                    fillColor: [245, 247, 250]
                }
            });

            const fileName = `Data_Personil_Keluar_${clientName}_${new Date().toISOString().split('T')[0]}.pdf`;
            doc.save(fileName);
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            fetchPersonOutData();
        });
    </script>
</x-app-layout>
