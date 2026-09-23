<x-app-layout>
    <x-main-div>
        <div class="p-4 mx-auto max-w-7xl sm:p-6 lg:p-8">
            <div class="flex items-center justify-between gap-3 mb-6">
                <div class="flex items-center gap-3">
                    <a href="{{ route('manajemen_rekap') }}" class="p-2 bg-gray-200 rounded-lg hover:bg-gray-300">
                        <i class="text-gray-700 ri-arrow-left-line"></i>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-white sm:text-3xl">Data Personil Masuk</h1>
                        <p class="text-sm text-gray-300">{{ $client->name ?? 'Mitra' }}</p>
                    </div>
                </div>
            </div>

            <div
                class="grid grid-cols-1 gap-3 p-4 mb-4 bg-white border border-gray-200 rounded-lg shadow-sm sm:grid-cols-5">
                <input id="search" type="text" placeholder="Cari nama..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                <input id="month" type="month" value="{{ now()->format('Y-m') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                <button id="btnReset" class="px-3 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Reset</button>
                <button onclick="exportToExcel()"
                    class="px-3 py-2 text-white rounded-lg bg-emerald-600 hover:bg-emerald-700">Export Excel</button>
                <button onclick="exportToPDF()"
                    class="px-3 py-2 text-white rounded-lg bg-rose-600 hover:bg-rose-700">Export PDF</button>
            </div>

            <div id="loading" class="p-8 text-center bg-white border border-gray-200 rounded-lg">Memuat data...</div>

            <div id="tableWrap" class="hidden overflow-x-auto bg-white border border-gray-200 rounded-lg shadow-sm">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-xs text-left text-gray-500 uppercase">No</th>
                            <th class="px-4 py-3 text-xs text-left text-gray-500 uppercase">Nama</th>
                            <th class="px-4 py-3 text-xs text-left text-gray-500 uppercase">Nama Penginput</th>
                            <th class="px-4 py-3 text-xs text-left text-gray-500 uppercase">Jabatan</th>
                            <th class="px-4 py-3 text-xs text-left text-gray-500 uppercase">Jumlah MK</th>
                            <th class="px-4 py-3 text-xs text-left text-gray-500 uppercase">Tanggal Masuk</th>
                            <th class="px-4 py-3 text-xs text-left text-gray-500 uppercase">Metode Gaji</th>
                            <th class="px-4 py-3 text-xs text-center text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-xs text-center text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbody" class="divide-y divide-gray-200"></tbody>
                </table>
            </div>

            <div id="empty" class="hidden p-8 text-center bg-white border border-gray-200 rounded-lg">Tidak ada
                data.</div>
        </div>
    </x-main-div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <script>
        let rows = [];
        let filtered = [];

        function fmtDate(v) {
            if (!v) return '-';
            return new Date(v).toLocaleDateString('id-ID');
        }

        function statusBadge(v) {
            const raw = (v || '-').toString().trim();
            const s = raw.toLowerCase();
            if (s == 'di ajukan')
                return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ring-1 ring-inset bg-emerald-50 text-emerald-700 ring-emerald-200">Di Ajukan</span>';
            if (s == 'di setujui')
                return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ring-1 ring-inset bg-lime-50 text-lime-700 ring-lime-200">Di Setujui</span>';
            if (s == 'di tolak')
                return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ring-1 ring-inset bg-rose-50 text-rose-700 ring-rose-200">Di Tolak</span>';
            if (s == 'pending')
                return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ring-1 ring-inset bg-amber-50 text-amber-700 ring-amber-200">Pending</span>';
            return `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ring-1 ring-inset bg-slate-100 text-slate-700 ring-slate-200">${raw}</span>`;
        }

        function render() {
            const tbody = document.getElementById('tbody');
            tbody.innerHTML = '';
            if (!filtered.length) {
                document.getElementById('tableWrap').classList.add('hidden');
                document.getElementById('empty').classList.remove('hidden');
                return;
            }
            document.getElementById('empty').classList.add('hidden');
            document.getElementById('tableWrap').classList.remove('hidden');
            filtered.forEach((r, i) => {
                console.log(r);
                tbody.innerHTML += `<tr>
                    <td class="px-4 py-3 text-sm">${i + 1}</td>
                    <td class="px-4 py-3 text-sm">${r.fullname || '-'}</td>
                    <td class="px-4 py-3 text-sm">${r.jabatan?.name_jabatan || '-'}</td>
                    <td class="px-4 py-3 text-sm">${fmtDate(r.date_in)}</td>
                    <td class="px-4 py-3 text-sm">${r.total_mk || '-'}</td>
                    <td class="px-4 py-3 text-sm">${r.method_salary || '-'}</td>
                    <td class="px-4 py-3 text-sm">${r.createdBy?.nama_lengkap || '-'}</td>
                    <td class="px-4 py-3 text-sm text-center whitespace-nowrap">${statusBadge(r.status)}</td>
                    <td class="px-4 py-3 text-sm text-center whitespace-nowrap">${actionButtons(r)}</td>
                </tr>`;
            });
        }

        function actionButtons(r) {
            if ((r.status || '').toLowerCase() !== 'di ajukan') return '-';
            return `<div class="flex items-center justify-center gap-2">
                 <button onclick="updateStatus(${r.id}, 'Di Tolak')" class="px-2 py-1 text-xs text-white rounded bg-rose-600 hover:bg-rose-700">Tolak</button>
             </div>`;
         }

        async function updateStatus(id, status) {
            const res = await fetch(`/api/v1/rekap/person-in/${id}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ status })
            });
            const result = await res.json();
            if (!result.success) {
                alert(result.message || 'Gagal update status');
                return;
            }
            await loadData();
        }

        function applyFilter() {
            const q = document.getElementById('search').value.toLowerCase();
            filtered = rows.filter(r => (r.fullname || '').toLowerCase().includes(q));
            render();
        }

        function getExportRows() {
            return filtered.filter(r => (r.status || '').toLowerCase() === 'di setujui').map((r, i) => ({
                no: i + 1,
                nama: r.fullname || '-',
                jabatan: r.jabatan?.name_jabatan || '-',
                tanggal_masuk: fmtDate(r.date_in),
                total_mk: r.total_mk || '-',
                metode_gaji: r.method_salary || '-',
                no_rek: r.method_salary_manual ? r.method_salary_manual : '-',
            }));
        }

        function exportToExcel() {
            const exportRows = getExportRows();
            const ws = XLSX.utils.json_to_sheet(exportRows);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Person In');
            const clientName = @json($client->name ?? 'Mitra');
            XLSX.writeFile(wb, `Data_Personil_Masuk_${clientName}_${new Date().toISOString().split('T')[0]}.xlsx`);
        }

        function exportToPDF() {
            const exportRows = getExportRows();
            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF('l', 'mm', 'a4');
            const clientName = @json($client->name ?? 'Mitra');

            doc.setFontSize(14);
            doc.text(`Data Personil Masuk - ${clientName}`, 14, 14);

            const headers = [
                ['No', 'Nama', 'Jabatan', 'Tanggal Masuk', 'Total MK', 'Metode Gaji', 'No Rek']
            ];
            const body = exportRows.map(r => [r.no, r.nama, r.jabatan, r.tanggal_masuk, r.total_mk, r.metode_gaji, r.no_rek]);

            doc.autoTable({
                head: headers,
                body,
                startY: 20,
                styles: {
                    fontSize: 9
                },
                headStyles: {
                    fillColor: [37, 99, 235]
                }
            });

            doc.save(`Data_Personil_Masuk_${clientName}_${new Date().toISOString().split('T')[0]}.pdf`);
        }

        async function loadData() {
            try {
                document.getElementById('loading').classList.remove('hidden');
                const kerjasamaId = window.location.pathname.split('/').pop();
                const month = document.getElementById('month').value;
                const res = await fetch(`/api/v1/person-in-api/${kerjasamaId}?month=${month}`);
                const result = await res.json();
                rows = result.data || [];
                filtered = rows;
                applyFilter();
            } catch (e) {
                filtered = [];
                render();
            } finally {
                document.getElementById('loading').classList.add('hidden');
            }
        }

        document.getElementById('search').addEventListener('input', applyFilter);
        document.getElementById('month').addEventListener('change', loadData);
        document.getElementById('btnReset').addEventListener('click', () => {
            document.getElementById('search').value = '';
            document.getElementById('month').value = '{{ now()->format('Y-m') }}';
            loadData();
        });

        loadData();
    </script>
</x-app-layout>
