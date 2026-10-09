<x-admin-layout :fullWidth="true">
    @section('title', 'Rekap Personil Keluar')
        <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">

            <!-- Header Section with Back Button -->
            <div class="mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <a href="{{ route('admin.rekap.index') }}"
                                class="inline-flex items-center gap-2 p-2 bg-blue-600/20 text-blue-700 hover:bg-blue-600 hover:text-white rounded-lg transition-colors">
                                <i class="ri-arrow-left-line"></i>
                            </a>
                            <div>
                                <h1 class="text-2xl sm:text-3xl font-bold text-gray-700">Data Personil Keluar</h1>
                                <p class="text-gray-500 font-semibold text-sm mt-1" id="clientName">Loading...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Export Buttons -->
                    <div class="flex flex-wrap gap-2">
                        <button onclick="exportToExcel()"
                            class="px-4 py-2.5 bg-emerald-600/20 hover:bg-emerald-600 text-emerald-800 hover:text-white rounded-lg font-medium transition-colors flex items-center gap-2">
                            <i class="ri-file-excel-2-line"></i>
                            <span>Excel</span>
                        </button>
                        <button onclick="exportToPDF()"
                            class="px-4 py-2.5 bg-red-600/20 hover:bg-red-700 text-red-800 hover:text-white rounded-lg font-medium transition-colors flex items-center gap-2">
                            <i class="ri-file-pdf-2-line"></i>
                            <span>PDF</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-red-600/20 rounded-lg">
                            <svg class="w-6 h-6 text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs uppercase tracking-wide font-medium">Total Keluar</p>
                            <p class="text-gray-700 text-2xl font-bold" id="totalPersonOut">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-emerald-600/20 rounded-lg">
                            <svg class="w-6 h-6 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs uppercase tracking-wide font-medium">Di Ajukan</p>
                            <p class="text-gray-700 text-2xl font-bold" id="approvedCount">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-amber-600/20 rounded-lg">
                            <svg class="w-6 h-6 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs uppercase tracking-wide font-medium">Pending</p>
                            <p class="text-gray-700 text-2xl font-bold" id="pendingCount">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-violet-600/20 rounded-lg">
                            <svg class="w-6 h-6 text-violet-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs uppercase tracking-wide font-medium">Periode</p>
                            <p class="text-gray-700 text-lg font-bold" id="periodDate">{{ now()->format('M Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-xs mb-6">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cari Karyawan</label>
                        <input type="text" id="searchEmployee" placeholder="Nama karyawan..."
                            class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 placeholder-gray-400 focus:outline-hidden focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Filter Bulan</label>
                        <input type="month" id="filterMonth" value="{{ now()->format('Y-m') }}"
                            class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 focus:outline-hidden focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div class="flex items-end">
                        <button onclick="resetFilters()"
                            class="w-full sm:w-auto px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-medium transition-colors inline-flex items-center justify-center gap-2">
                            <i class="ri-refresh-line"></i>Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div id="loadingState" class="bg-white border border-gray-200 rounded-lg p-8 text-center shadow-xs">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-emerald-600 mx-auto mb-4"></div>
                <p class="text-gray-600">Memuat data...</p>
            </div>

            <!-- Table Section -->
            <div id="tableContainer"
                class="hidden bg-white border border-gray-200 rounded-lg shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="personOutTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    No</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama Karyawan</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Posisi</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tanggal Keluar</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Alasan</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi</th>
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
                class="hidden bg-white border border-gray-200 rounded-lg p-4 shadow-xs mt-4">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
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
                            class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-gray-700 text-sm focus:outline-hidden focus:ring-2 focus:ring-emerald-500">
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
                            <i class="ri-skip-left-line"></i>
                        </button>

                        <!-- Previous Page -->
                        <button onclick="goToPreviousPage()" id="prevPageBtn"
                            class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <i class="ri-arrow-left-s-line"></i>
                        </button>

                        <!-- Page Numbers -->
                        <div id="pageNumbers" class="flex items-center gap-1">
                        </div>

                        <!-- Next Page -->
                        <button onclick="goToNextPage()" id="nextPageBtn"
                            class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <i class="ri-arrow-right-s-line"></i>
                        </button>

                        <!-- Last Page -->
                        <button onclick="goToLastPage()" id="lastPageBtn"
                            class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <i class="ri-skip-right-line"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div id="emptyState"
                class="hidden bg-white border border-gray-200 rounded-lg p-8 sm:p-12 text-center shadow-xs">
                <div class="max-w-sm mx-auto">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                    </div>
                    <p class="text-gray-700 text-base sm:text-lg font-semibold mb-1" id="emptyStateTitle">Tidak ada
                        data</p>
                    <p class="text-gray-500 text-sm" id="emptyStateDesc">Belum ada data personil keluar untuk periode
                        ini</p>
                </div>
            </div>

        </div>

    <div id="actionConfirmModal" class="fixed inset-0 z-9999 hidden">
        <div class="absolute inset-0 bg-gray-900/50" onclick="closeActionModal()"></div>
        <div class="relative flex min-h-full items-center justify-center p-4">
            <div class="w-full max-w-md rounded-xl bg-white shadow-xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-700">Konfirmasi Aksi</h3>
                    <button type="button" class="text-gray-500 hover:text-gray-700" onclick="closeActionModal()"><i class="ri-close-line text-xl"></i></button>
                </div>
                <div class="px-5 py-4">
                    <p class="text-sm text-gray-600" id="actionModalMessage">Yakin ingin melanjutkan aksi ini?</p>
                </div>
                <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeActionModal()" class="px-3 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</button>
                    <button type="button" onclick="submitActionForm()" class="px-3 py-2 rounded-lg bg-red-600 text-sm font-medium text-white hover:bg-red-700">Ya, Lanjutkan</button>
                </div>
            </div>
        </div>
    </div>

    <form id="actionForm" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="_method" id="actionMethod" value="DELETE">
    </form>

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

                const url = `/admin/api/v1/rekap/person-out/${kerjasamaId}${filterMonth ? `?month=${filterMonth}` : ''}`;

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
                'pending': '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>',
                'approved': '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Approved</span>',
                'rejected': '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>'
            };
            return badges[statusLower] ||
                `<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">${status || '-'}</span>`;
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
                const canRestore = Boolean(item.user?.deleted_at);
                const actions = `
                    <button onclick="openActionModal(${item.id}, 'delete')" class="inline-flex items-center gap-1 rounded-lg bg-red-100 px-2 py-1 text-xs font-medium text-red-700 hover:bg-red-200" title="Hapus">
                        <i class="ri-delete-bin-line"></i>Delete
                    </button>
                    ${canRestore ? `<button onclick="openActionModal(${item.id}, 'restore')" class="inline-flex items-center gap-1 rounded-lg bg-emerald-100 px-2 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-200" title="Restore User"><i class="ri-arrow-go-back-line"></i>Restore</button>` : ''}
                `;

                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${startIndex + index + 1}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">${item.user?.nama_lengkap || '-'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${item.user?.jabatan?.name_jabatan || '-'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${formatDate(item.out_date)}</td>
                    <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate" title="${item.reason || '-'}">${item.reason == 'lainnya' ? item.reason_manual : item.reason}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">${statusBadge}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                        <div class="inline-flex items-center gap-1">${actions}</div>
                    </td>
                `;

                tbody.appendChild(row);
            });

            updatePaginationUI();
            document.getElementById('emptyState').classList.add('hidden');
            document.getElementById('tableContainer').classList.remove('hidden');
            document.getElementById('paginationContainer').classList.remove('hidden');
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

        function openActionModal(id, type) {
            if (type === 'delete') {
                document.getElementById('actionModalMessage').textContent = 'Yakin ingin menghapus data personil keluar?';
                document.getElementById('actionForm').action = `/admin/rekap/actions/person-out/${id}`;
                document.getElementById('actionMethod').value = 'DELETE';
            } else {
                document.getElementById('actionModalMessage').textContent = 'Yakin ingin restore user terkait data ini?';
                document.getElementById('actionForm').action = `/admin/rekap/actions/person-out/${id}/restore-user`;
                document.getElementById('actionMethod').value = 'PATCH';
            }

            document.getElementById('actionConfirmModal').classList.remove('hidden');
        }

        function closeActionModal() {
            document.getElementById('actionConfirmModal').classList.add('hidden');
        }

        function submitActionForm() {
            document.getElementById('actionForm').submit();
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
                    'px-3 py-1.5 bg-emerald-600/20 text-emerald-800 rounded-lg font-medium' :
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
            if (filteredData.length === 0) {
                alert('Tidak ada data untuk diekspor');
                return;
            }

            const data = filteredData.map((item, index) => ({
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
            if (filteredData.length === 0) {
                alert('Tidak ada data untuk diekspor');
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
            doc.text(`Total: ${filteredData.length} | Di Ajukan: ${document.getElementById('approvedCount').textContent}`,
                14, 28);

            const tableData = filteredData.map((item, index) => [
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
</x-admin-layout>



