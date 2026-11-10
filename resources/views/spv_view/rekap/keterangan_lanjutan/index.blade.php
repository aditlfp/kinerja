<x-app-layout>
    <x-main-div>
        <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
            <div class="mb-6 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <a href="{{ route('manajemen_rekap') }}" class="p-2 rounded-lg bg-gray-200 hover:bg-gray-300">
                        <i class="ri-arrow-left-line text-gray-700"></i>
                    </a>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-white">Data Keterangan Lanjutan</h1>
                        <p class="text-gray-300 text-sm">{{ $client->name ?? 'Mitra' }}</p>
                    </div>
                </div>
            </div>

            <div
                class="bg-white rounded-lg p-4 shadow-xs border border-gray-200 mb-4 grid grid-cols-1 sm:grid-cols-5 gap-3">
                <input id="search" type="text" placeholder="Cari nama..."
                    class="w-full rounded-lg border border-gray-300 px-3 py-2">
                <input id="month" type="month" value="{{ now()->format('Y-m') }}"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2">
                <button id="btnReset" class="rounded-lg bg-gray-200 hover:bg-gray-300 px-3 py-2">Reset</button>
                <button onclick="exportToExcel()"
                    class="rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2">Export Excel</button>
                <button onclick="exportToPDF()"
                    class="rounded-lg bg-rose-600 hover:bg-rose-700 text-white px-3 py-2">Export PDF</button>
            </div>

            <div id="tableWrap" class="bg-white rounded-lg shadow-xs border border-gray-200 overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">No</th>
                            <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Nama</th>
                            <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Keterangan</th>
                            <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Nama Penginput</th>
                            <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Dibuat</th>
                            <th class="px-4 py-3 text-center text-xs uppercase text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbody" class="divide-y divide-gray-200"></tbody>
                </table>

                <div id="loading" class="p-6 text-center text-sm text-gray-600">Memuat data...</div>
                <div id="empty" class="hidden p-6 text-center text-sm text-gray-600">Tidak ada data.</div>

                <!-- Detail Modal -->
                <div id="detailModal" class="fixed inset-0 z-50 hidden" aria-labelledby="detailModalTitle" role="dialog" aria-modal="true">
                    <div class="fixed inset-0 bg-black bg-opacity-50" onclick="closeDetailModal()"></div>
                    <div class="fixed inset-0 flex items-center justify-center p-4">
                        <div class="bg-white rounded-lg max-w-2xl w-full p-4 shadow-lg" onclick="event.stopPropagation()">
                            <div class="flex items-start justify-between">
                                <h3 id="detailModalTitle" class="text-lg font-semibold">Detail Keterangan</h3>
                                <button type="button" onclick="closeDetailModal()" class="text-gray-500 hover:text-gray-700">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div id="detailModalBody" class="mt-3 text-sm max-h-80 overflow-y-auto"></div>
                            <div class="mt-4 flex justify-end">
                                <button type="button" onclick="closeDetailModal()" class="px-4 py-2 bg-gray-100 rounded-lg">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-main-div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

    <script>
        let allRows = [];
        let rows = [];
        let details = {};
        let exportRows = [];

        function fmtDate(v) {
            if (!v) return '-';
            return new Date(v).toLocaleDateString('id-ID');
        }

        function buildExportAndDetails() {
            exportRows = rows.map((r, i) => ({
                no: i + 1,
                nama: r.user?.nama_lengkap || r.user_name || '-',
                dibuat: r.created_by?.nama_lengkap || r.createdBy?.nama_lengkap || '-',
                keterangan: Array.isArray(r.keterangan) ? r.keterangan.map(e => (typeof e === 'object' ? ((e.periode? e.periode + ' - ' : '') + (e.judul? e.judul + ': ' : '') + (e.keterangan || '')) : e)).join('\n') : (r.keterangan || '-'),
                created_at: r.created_at ? (new Date(r.created_at)).toISOString().split('T')[0] : (r.createdAt || ''),
            }));

            details = {};
            rows.forEach((r) => {
                details[r.id] = {
                    nama: r.user?.nama_lengkap || r.user_name || '-',
                    dibuat: r.created_by?.nama_lengkap || r.createdBy?.nama_lengkap || '-',
                    entries: Array.isArray(r.keterangan) ? r.keterangan : [r.keterangan || '-'],
                    created: r.created_at ? (new Date(r.created_at)).toISOString().split('T')[0] : (r.createdAt || ''),
                };
            });
        }

        function render() {
            const tbody = document.getElementById('tbody');
            const loadingEl = document.getElementById('loading');
            const emptyEl = document.getElementById('empty');
            tbody.innerHTML = '';
            if (!rows.length) {
                loadingEl.classList.add('hidden');
                emptyEl.classList.remove('hidden');
                return;
            }
            loadingEl.classList.add('hidden');
            emptyEl.classList.add('hidden');

            rows.forEach((r, i) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="px-4 py-3 text-sm">${i+1}</td>
                    <td class="px-4 py-3 text-sm">${r.user?.nama_lengkap || r.user_name || '-'}</td>
                    <td class="px-4 py-3 text-sm">${Array.isArray(r.keterangan) ? r.keterangan.map(e => (typeof e === 'object' ? ((e.periode? '<strong>'+ (e.periode) + '</strong>' : '') + (e.judul? ' - '+ e.judul : '')) : e)).join('<br/>') : (r.keterangan || '-')}</td>
                    <td class="px-4 py-3 text-sm">${r.created_by?.nama_lengkap || r.createdBy?.nama_lengkap || '-'}</td>
                    <td class="px-4 py-3 text-sm">${fmtDate(r.created_at || r.createdAt)}</td>
                    <td class="px-4 py-3 text-sm text-center whitespace-nowrap"><a href="#" onclick="showDetail(${r.id})" class="text-indigo-600 hover:underline">Lihat</a></td>
                `;
                tbody.appendChild(tr);
            });
        }

        async function loadData() {
            try {
                document.getElementById('loading').classList.remove('hidden');
                document.getElementById('empty').classList.add('hidden');
                const kerjasamaId = window.location.pathname.split('/').pop();
                const month = document.getElementById('month').value;
                const res = await fetch(`/api/v1/keterangan-lanjutan-api/${kerjasamaId}?month=${month}`);
                const json = await res.json();
                allRows = json.data || json || [];
                rows = [...allRows];
                buildExportAndDetails();
                render();
            } catch (e) {
                allRows = [];
                rows = [];
                render();
            }
        }

        function exportToExcel() {
            const ws = XLSX.utils.json_to_sheet(exportRows);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Keterangan Lanjutan');
            XLSX.writeFile(wb, `Keterangan_Lanjutan_{{ now()->format('Y-m-d') }}.xlsx`);
        }

        function exportToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('l', 'mm', 'a4');

            doc.setFontSize(14);
            doc.text('Keterangan Lanjutan', 14, 14);

            const headers = [['No', 'Nama', 'Keterangan', 'Nama Penginput', 'Dibuat']];
            const body = exportRows.map(r => [r.no, r.nama, r.keterangan, r.dibuat, r.created_at]);

            doc.autoTable({
                head: headers,
                body,
                startY: 20,
                styles: { fontSize: 9 },
                headStyles: { fillColor: [37, 99, 235] }
            });

            doc.save(`Keterangan_Lanjutan_{{ now()->format('Y-m-d') }}.pdf`);
        }

        function showDetail(id) {
            const d = details[id];
            if (!d) return alert('Detail tidak ditemukan');
            const titleEl = document.getElementById('detailModalTitle');
            const bodyEl = document.getElementById('detailModalBody');
            titleEl.textContent = `${d.nama} — ${d.created ?? ''}`;
            bodyEl.innerHTML = `<div class="mb-4 rounded-lg bg-slate-50 border border-slate-200 p-3">
                <p class="text-xs uppercase tracking-wide text-slate-500">Nama Penginput</p>
                <p class="mt-1 font-semibold text-slate-800">${d.dibuat || '-'}</p>
            </div>`;
            if (Array.isArray(d.entries)) {
                d.entries.forEach(e => {
                    if (e && typeof e === 'object') {
                        const peri = e.periode ? `<div class="font-semibold">${e.periode}</div>` : '';
                        const jud = e.judul ? `<div class="text-sm text-gray-700">${e.judul}</div>` : '';
                        const ket = e.keterangan ? `<div class="text-sm text-gray-600 mt-1">${e.keterangan}</div>` : '';
                        const wrap = document.createElement('div');
                        wrap.className = 'mb-3';
                        wrap.innerHTML = peri + jud + ket;
                        bodyEl.appendChild(wrap);
                    } else {
                        const p = document.createElement('p');
                        p.className = 'mb-2';
                        p.textContent = e || '-';
                        bodyEl.appendChild(p);
                    }
                });
            } else {
                bodyEl.textContent = d.entries || '-';
            }
            document.getElementById('detailModal').classList.remove('hidden');
        }

        function closeDetailModal() {
            document.getElementById('detailModal').classList.add('hidden');
        }

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeDetailModal();
            }
        });

        document.getElementById('search').addEventListener('input', () => {
            const q = document.getElementById('search').value.toLowerCase();
            rows = allRows.filter(r => (r.user?.nama_lengkap || r.user_name || '').toLowerCase().includes(q));
            buildExportAndDetails();
            render();
        });

        document.getElementById('month').addEventListener('change', loadData);
        document.getElementById('btnReset').addEventListener('click', () => {
            document.getElementById('search').value = '';
            document.getElementById('month').value = '{{ now()->format('Y-m') }}';
            loadData();
        });

        // initial load
        loadData();
    </script>
</x-app-layout>
