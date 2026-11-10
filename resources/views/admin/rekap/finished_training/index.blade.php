<x-admin-layout :fullWidth="true">
    @section('title', 'Rekap Lepas Training')

    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.rekap.index') }}"
                        class="inline-flex items-center gap-2 p-2 rounded-lg bg-blue-600/20 text-blue-700 hover:bg-blue-600 hover:text-white transition-colors">
                        <i class="ri-arrow-left-line"></i>
                    </a>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-700">Data Lepas Training</h1>
                        <p class="text-gray-500 font-semibold text-sm mt-1">{{ $client->name ?? 'Mitra' }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button onclick="exportToExcel()"
                        class="px-4 py-2.5 bg-emerald-600/20 hover:bg-emerald-600 text-emerald-800 hover:text-white rounded-lg font-medium transition-colors inline-flex items-center gap-2">
                        <i class="ri-file-excel-2-line"></i>
                        <span>Excel</span>
                    </button>
                    <button onclick="exportToPDF()"
                        class="px-4 py-2.5 bg-red-600/20 hover:bg-red-700 text-red-800 hover:text-white rounded-lg font-medium transition-colors inline-flex items-center gap-2">
                        <i class="ri-file-pdf-2-line"></i>
                        <span>PDF</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-blue-600/20 rounded-lg">
                        <i class="ri-graduation-cap-line text-blue-700 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs uppercase tracking-wide font-medium">Total Lepas Training</p>
                        <p class="text-gray-700 text-2xl font-bold" id="totalCount">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-emerald-600/20 rounded-lg">
                        <i class="ri-checkbox-circle-line text-emerald-700 text-xl"></i>
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
                        <i class="ri-time-line text-amber-700 text-xl"></i>
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
                        <i class="ri-calendar-line text-violet-700 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs uppercase tracking-wide font-medium">Periode</p>
                        <p class="text-gray-700 text-lg font-bold" id="periodDate">{{ now()->format('M Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-xs mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Cari Karyawan</label>
                    <input id="search" type="text" placeholder="Nama karyawan..."
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-700 placeholder-gray-400 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Filter Bulan</label>
                    <input id="month" type="month" value="{{ now()->format('Y-m') }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-700 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Status</label>
                    <select id="statusFilter"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-700 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Semua Status</option>
                        <option value="di ajukan">Di Ajukan</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button id="btnReset"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-gray-200 hover:bg-gray-300 px-3 py-2 text-gray-700 font-medium transition-colors">
                        <i class="ri-refresh-line"></i>Reset
                    </button>
                </div>
            </div>
        </div>

        <div id="loading" class="bg-white rounded-lg p-8 text-center border border-gray-200 shadow-xs">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600 mx-auto mb-3"></div>
            <p class="text-gray-600">Memuat data...</p>
        </div>

        <div id="tableWrap" class="hidden bg-white rounded-lg shadow-xs border border-gray-200 overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">No</th>
                        <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Nama</th>
                        <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Tanggal Masuk</th>
                        <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Tanggal Lepas</th>
                        <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Masa Training</th>
                        <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Status</th>
                        <th class="px-4 py-3 text-center text-xs uppercase text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbody" class="divide-y divide-gray-200"></tbody>
            </table>
        </div>

        <div id="paginationContainer" class="hidden bg-white border border-gray-200 rounded-lg p-4 shadow-xs mt-4">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-600">
                    Menampilkan <span class="font-medium" id="showingFrom">0</span> -
                    <span class="font-medium" id="showingTo">0</span> dari
                    <span class="font-medium" id="totalRecords">0</span> data
                </div>
                <div class="flex items-center gap-2 mr-4">
                    <label class="text-sm text-gray-600">Per halaman:</label>
                    <select id="itemsPerPage"
                        class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-gray-700 text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="goToFirstPage()" id="firstPageBtn"
                        class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <i class="ri-skip-left-line"></i>
                    </button>
                    <button onclick="goToPreviousPage()" id="prevPageBtn"
                        class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <i class="ri-arrow-left-s-line"></i>
                    </button>
                    <div id="pageNumbers" class="flex items-center gap-1"></div>
                    <button onclick="goToNextPage()" id="nextPageBtn"
                        class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <i class="ri-arrow-right-s-line"></i>
                    </button>
                    <button onclick="goToLastPage()" id="lastPageBtn"
                        class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <i class="ri-skip-right-line"></i>
                    </button>
                </div>
            </div>
        </div>

        <div id="empty" class="hidden bg-white rounded-lg p-8 text-center border border-gray-200 shadow-xs">
            <p class="text-gray-700 font-semibold">Tidak ada data.</p>
            <p class="text-gray-500 text-sm">Coba ubah filter atau periode.</p>
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
                    <button type="button" onclick="closeActionModal()" class="px-3 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Batal</button>
                    <button type="button" onclick="submitActionForm()" class="px-3 py-2 rounded-lg bg-red-600 text-sm font-medium text-gray-100 hover:bg-red-700">Ya, Lanjutkan</button>
                </div>
            </div>
        </div>
    </div>

    <form id="actionForm" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="_method" id="actionMethod" value="DELETE">
    </form>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <script>
        let rows = [];
        let filteredData = [];
        let currentPage = 1;
        let itemsPerPage = 10;
        let totalPages = 1;

        function fmtDate(v) {
            return v ? new Date(v).toLocaleDateString('id-ID') : '-';
        }

        function normalizeStatus(value) {
            return (value || '').toString().trim().toLowerCase();
        }

        function statusBadge(v) {
            const raw = (v || '-').toString().trim();
            const s = normalizeStatus(v);
            if (s === 'di ajukan') {
                return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ring-1 ring-inset bg-emerald-50 text-emerald-700 ring-emerald-200">Di Ajukan</span>';
            }
            if (s === 'pending') {
                return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ring-1 ring-inset bg-amber-50 text-amber-700 ring-amber-200">Pending</span>';
            }
            return `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">${raw}</span>`;
        }

        function applyFilters() {
            const q = document.getElementById('search').value.toLowerCase();
            const status = document.getElementById('statusFilter').value;

            filteredData = rows.filter(r => {
                const name = (r.user?.nama_lengkap || '').toLowerCase();
                const rowStatus = normalizeStatus(r.status);
                const matchName = !q || name.includes(q);
                const matchStatus = !status || rowStatus === status;
                return matchName && matchStatus;
            });

            currentPage = 1;
            renderTable();
            updateStats();
        }

        function renderTable() {
            const tbody = document.getElementById('tbody');
            tbody.innerHTML = '';

            if (!filteredData.length) {
                document.getElementById('tableWrap').classList.add('hidden');
                document.getElementById('paginationContainer').classList.add('hidden');
                document.getElementById('empty').classList.remove('hidden');
                return;
            }

            document.getElementById('empty').classList.add('hidden');
            document.getElementById('tableWrap').classList.remove('hidden');

            itemsPerPage = parseInt(document.getElementById('itemsPerPage').value, 10);
            totalPages = Math.max(1, Math.ceil(filteredData.length / itemsPerPage));
            currentPage = Math.min(currentPage, totalPages);

            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = Math.min(startIndex + itemsPerPage, filteredData.length);
            const pageData = filteredData.slice(startIndex, endIndex);

            pageData.forEach((r, i) => {
                tbody.innerHTML += `<tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm text-gray-700">${startIndex + i + 1}</td>
                    <td class="px-4 py-3 text-sm font-medium text-gray-700">${r.user?.nama_lengkap || '-'}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">${fmtDate(r.date_in)}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">${fmtDate(r.date_finish_train)}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">${r.masa_training_hari || 0} hari</td>
                    <td class="px-4 py-3 text-sm whitespace-nowrap">${statusBadge(r.status)}</td>
                    <td class="px-4 py-3 text-sm text-center whitespace-nowrap">
                        <button onclick="openActionModal(${r.id})" class="inline-flex items-center gap-1 rounded-lg bg-red-100 px-2.5 py-1.5 text-red-700 hover:bg-red-200" title="Hapus">
                            <i class="ri-delete-bin-line"></i>Delete
                        </button>
                    </td>
                </tr>`;
            });

            updatePaginationUI();
            document.getElementById('paginationContainer').classList.remove('hidden');
        }

        function updateStats() {
            const approved = filteredData.filter(item => normalizeStatus(item.status) === 'di ajukan').length;
            const pending = filteredData.filter(item => normalizeStatus(item.status) === 'pending').length;
            document.getElementById('totalCount').textContent = filteredData.length;
            document.getElementById('approvedCount').textContent = approved;
            document.getElementById('pendingCount').textContent = pending;
            const monthVal = document.getElementById('month').value;
            if (monthVal) {
                const [y, m] = monthVal.split('-');
                const dt = new Date(parseInt(y, 10), parseInt(m, 10) - 1, 1);
                document.getElementById('periodDate').textContent = dt.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' });
            }
        }

        function updatePaginationUI() {
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = Math.min(startIndex + itemsPerPage, filteredData.length);

            document.getElementById('showingFrom').textContent = filteredData.length ? startIndex + 1 : 0;
            document.getElementById('showingTo').textContent = endIndex;
            document.getElementById('totalRecords').textContent = filteredData.length;

            document.getElementById('firstPageBtn').disabled = currentPage === 1;
            document.getElementById('prevPageBtn').disabled = currentPage === 1;
            document.getElementById('nextPageBtn').disabled = currentPage === totalPages;
            document.getElementById('lastPageBtn').disabled = currentPage === totalPages;

            renderPageNumbers();
        }

        function renderPageNumbers() {
            const container = document.getElementById('pageNumbers');
            container.innerHTML = '';

            let start = Math.max(1, currentPage - 2);
            let end = Math.min(totalPages, start + 4);
            if (end - start < 4) start = Math.max(1, end - 4);

            for (let page = start; page <= end; page++) {
                const btn = document.createElement('button');
                btn.className = page === currentPage
                    ? 'px-3 py-1.5 bg-indigo-600/20 text-indigo-800 rounded-lg font-medium'
                    : 'px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors';
                btn.textContent = page;
                btn.onclick = () => goToPage(page);
                container.appendChild(btn);
            }
        }

        function goToPage(page) {
            currentPage = page;
            renderTable();
        }

        function goToFirstPage() { goToPage(1); }
        function goToPreviousPage() { if (currentPage > 1) goToPage(currentPage - 1); }
        function goToNextPage() { if (currentPage < totalPages) goToPage(currentPage + 1); }
        function goToLastPage() { goToPage(totalPages); }

        function openActionModal(id) {
            document.getElementById('actionModalMessage').textContent = 'Yakin ingin menghapus data lepas training?';
            document.getElementById('actionForm').action = `/admin/rekap/actions/finished-training/${id}`;
            document.getElementById('actionMethod').value = 'DELETE';
            document.getElementById('actionConfirmModal').classList.remove('hidden');
        }

        function closeActionModal() {
            document.getElementById('actionConfirmModal').classList.add('hidden');
        }

        function submitActionForm() {
            document.getElementById('actionForm').submit();
        }

        function getExportRows() {
            return filteredData.map((r, i) => ({
                no: i + 1,
                nama: r.user?.nama_lengkap || '-',
                tanggal_masuk: fmtDate(r.date_in),
                tanggal_lepas: fmtDate(r.date_finish_train),
                masa_training_hari: `${r.masa_training_hari || 0} hari`,
                keterangan: r.desc || '-',
            }));
        }

        function exportToExcel() {
            const exportRows = getExportRows();
            if (!exportRows.length) return;
            const ws = XLSX.utils.json_to_sheet(exportRows);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Finished Training');
            const clientName = @json($client->name ?? 'Mitra');
            XLSX.writeFile(wb, `Data_Lepas_Training_${clientName}_${new Date().toISOString().split('T')[0]}.xlsx`);
        }

        function exportToPDF() {
            const exportRows = getExportRows();
            if (!exportRows.length) return;
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('l', 'mm', 'a4');
            const clientName = @json($client->name ?? 'Mitra');

            doc.setFontSize(14);
            doc.text(`Data Lepas Training - ${clientName}`, 14, 14);

            const headers = [['No', 'Nama', 'Tanggal Masuk', 'Tanggal Lepas', 'Masa Training', 'Keterangan']];
            const body = exportRows.map(r => [r.no, r.nama, r.tanggal_masuk, r.tanggal_lepas, r.masa_training_hari, r.keterangan]);

            doc.autoTable({
                head: headers,
                body,
                startY: 20,
                styles: { fontSize: 9 },
                headStyles: { fillColor: [109, 40, 217] },
            });

            doc.save(`Data_Lepas_Training_${clientName}_${new Date().toISOString().split('T')[0]}.pdf`);
        }

        async function loadData() {
            try {
                document.getElementById('loading').classList.remove('hidden');
                const kerjasamaId = window.location.pathname.split('/').pop();
                const month = document.getElementById('month').value;
                const res = await fetch(`/admin/api/v1/rekap/finished-training/${kerjasamaId}?month=${month}`);
                const result = await res.json();
                rows = result.data || [];
                filteredData = rows;
                applyFilters();
            } catch (e) {
                rows = [];
                filteredData = [];
                renderTable();
                updateStats();
            } finally {
                document.getElementById('loading').classList.add('hidden');
            }
        }

        document.getElementById('search').addEventListener('input', applyFilters);
        document.getElementById('statusFilter').addEventListener('change', applyFilters);
        document.getElementById('itemsPerPage').addEventListener('change', () => {
            currentPage = 1;
            renderTable();
        });
        document.getElementById('month').addEventListener('change', loadData);
        document.getElementById('btnReset').addEventListener('click', () => {
            document.getElementById('search').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('itemsPerPage').value = '10';
            document.getElementById('month').value = '{{ now()->format('Y-m') }}';
            loadData();
        });

        loadData();
    </script>
</x-admin-layout>
