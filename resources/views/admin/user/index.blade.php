<x-admin-layout :fullWidth="true">
    @section('title', 'Data User Admin')

    <div x-data="{ delOpen: false }" :class="{ 'overflow-hidden': delOpen }"
        class="w-full px-1 mx-auto space-y-3 overflow-x-hidden max-w-screen sm:px-2 lg:px-3">
        <section class="p-4 border shadow-sm rounded-xl border-gray-100/80 bg-white/80 backdrop-blur-sm sm:p-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-normal leading-8 tracking-tight text-[var(--md-sys-color-on-surface)]">
                        Data All User
                    </h1>
                    <p class="mt-0.5 text-xs text-[var(--md-sys-color-on-surface-variant)]">Kelola akun user, export data,
                        dan tindak lanjut status user.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.user.create') }}"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-blue-700">
                        + Tambah User
                    </a>
                </div>
            </div>
        </section>

        <section class="p-3 border shadow-sm rounded-xl border-gray-100/80 bg-white/85 sm:p-4">
            <div class="grid gap-3 lg:grid-cols-2 lg:items-end">
                <form id="filterForm" action="{{ route('admin.user.index') }}" method="GET"
                    class="flex flex-wrap items-center min-w-0 gap-2">
                    <select name="filterKerjasama" id="filterKerjasama"
                        class="w-full max-w-xs text-xs bg-white border-gray-200 select select-bordered h-9 focus:outline-none">
                        <option selected disabled>~ Kerja Sama ~</option>
                        @foreach ($kerjasama as $i)
                            <option value="{{ $i->id }}" {{ $filterKerjasama == $i->id ? 'selected' : '' }}>
                                {{ $i?->client?->panggilan ?? $i?->client?->name }}
                            </option>
                        @endforeach
                    </select>
                    <label
                        class="flex items-center w-full max-w-full gap-2 bg-white border-gray-200 rounded-lg input input-bordered h-9 lg:w-72">
                        <i class="text-gray-500 ri-search-2-line"></i>
                        <input id="searchInput" data-search-mode="server" name="search"
                            value="{{ $search ?? request('search') }}" type="text"
                            class="w-full text-xs bg-transparent border-none focus:outline-none"
                            placeholder="Search..." />
                    </label>
                    <input type="hidden" name="page" id="pageInput" value="{{ request('page', 1) }}">
                    <button type="submit"
                        class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-blue-700">
                        Filter
                    </button>
                    <button type="button" id="resetFilterBtn"
                        class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-50">
                        Reset
                    </button>
                </form>
                <div class="flex items-center gap-2 text-[11px] text-gray-500 lg:justify-self-end">
                    <span id="filterLoading" class="hidden text-blue-600 loading loading-spinner loading-xs"></span>
                    <p>Tip: Gunakan filter + search untuk mempercepat pencarian user.</p>
                </div>
            </div>
        </section>

        <section class="overflow-hidden border shadow-sm rounded-xl border-gray-100/80 bg-white/90">
            <form action="{{ route('export_checklist') }}" method="POST" id="exportUserForm">
                @csrf
                @method('POST')
                <input type="hidden" name="export_type" id="exportType" value="">

                <div
                    class="flex flex-col gap-2 p-3 border-b border-gray-100 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-wrap items-center gap-2">
                        <label
                            class="inline-flex items-center gap-2 rounded-lg bg-gray-50 px-2.5 py-1.5 text-xs font-medium text-gray-700">
                            <input type="checkbox" name="check_all" id="checkbox_all" value="true"
                                class="checkbox checkbox-sm" />
                            Pilih Semua
                        </label>
                        <button type="button" data-export-type="data"
                            class="export-user-btn rounded-lg bg-blue-600 px-2.5 py-1.5 text-[11px] font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                            disabled>
                            Export Data
                        </button>
                        <button type="button" data-export-type="id_card"
                            class="export-user-btn rounded-lg bg-indigo-600 px-2.5 py-1.5 text-[11px] font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                            disabled>
                            Export ID Card
                        </button>
                        <button type="button"
                            class="rounded-lg bg-red-600 px-2.5 py-1.5 text-[11px] font-semibold text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                            id="delUser" @click="delOpen = true" disabled>
                            Hapus User
                        </button>
                        <span id="selectedCountBadge"
                            class="inline-flex items-center rounded-lg bg-gray-100 px-2.5 py-1.5 text-[11px] font-semibold text-gray-600">0
                            user dipilih</span>
                    </div>
                </div>

                <div class="w-full max-w-full overflow-x-auto">
                    <table class="w-full min-w-[660px] divide-y divide-gray-100 md:min-w-[860px]" id="searchTable">
                        <thead
                            class="text-xs font-semibold tracking-wide text-left text-gray-600 uppercase bg-gray-50/90">
                            <tr>
                                <th class="sticky top-0 z-10 px-3 py-2 bg-gray-50/95"></th>
                                <th class="sticky top-0 z-10 px-3 py-2 bg-gray-50/95">#</th>
                                <th class="sticky top-0 z-10 hidden px-3 py-2 bg-gray-50/95 md:table-cell">Image</th>
                                <th class="sticky top-0 z-10 px-3 py-2 bg-gray-50/95">Nama</th>
                                <th class="sticky top-0 z-10 hidden px-3 py-2 bg-gray-50/95 lg:table-cell">Nama Lengkap
                                </th>
                                <th class="sticky top-0 z-10 px-3 py-2 bg-gray-50/95">Email</th>
                                <th class="sticky top-0 z-10 hidden px-3 py-2 bg-gray-50/95 lg:table-cell">No. HP</th>
                                <th class="sticky top-0 z-10 hidden px-3 py-2 bg-gray-50/95 md:table-cell">Kerjasama
                                </th>
                                <th class="sticky top-0 z-10 px-3 py-2 bg-gray-50/95">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody" class="text-sm text-gray-700 divide-y divide-gray-100">
                            <tr id="clientNoResultRow" class="hidden">
                                <td colspan="10" class="px-4 py-6 text-sm text-center text-gray-500">Tidak ada data
                                    yang cocok dengan filter/search.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </form>
        </section>

        <div id="ajaxPagination"
            class="flex flex-col items-start justify-between gap-2 px-3 py-2 text-xs text-gray-600 border border-gray-100 rounded-lg bg-white/80 sm:flex-row sm:items-center">
            <p id="paginationInfo">Memuat data...</p>
            <div class="flex items-center gap-2">
                <button type="button" id="prevPageBtn"
                    class="rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-semibold text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
                    disabled>Prev</button>
                <span id="paginationState" class="text-xs font-semibold text-gray-500">Page 1 / 1</span>
                <button type="button" id="nextPageBtn"
                    class="rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-semibold text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
                    disabled>Next</button>
            </div>
        </div>

        <div x-show="delOpen" x-cloak style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/35 backdrop-blur-sm">
            <div class="w-full max-w-2xl overflow-hidden bg-white shadow-xl rounded-2xl">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold tracking-wide text-gray-800 uppercase">Konfirmasi Hapus Akun</h3>
                    <button @click="delOpen = false" type="button"
                        class="px-3 py-1 text-sm font-semibold text-gray-600 transition border border-gray-200 rounded-lg hover:bg-gray-50">
                        Tutup
                    </button>
                </div>
                <div class="p-5 space-y-3">
                    <p class="text-sm text-gray-600">Akun user yang akan dihapus:</p>
                    <div id="isiUser"
                        class="p-3 overflow-y-auto text-sm text-gray-700 border border-gray-200 max-h-72 rounded-xl bg-gray-50">
                    </div>
                    <div class="flex justify-end">
                        <button type="button" id="subUser"
                            class="px-4 py-2 text-sm font-semibold text-white transition bg-red-600 rounded-xl hover:bg-red-700">
                            Submit Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function setExportType(type) {
            $('#exportType').val(type);
        }

        let searchDebounceTimer = null;
        const filterForm = $('#filterForm');
        const searchInput = $('#searchInput');
        const filterKerjasama = $('#filterKerjasama');
        const pageInput = $('#pageInput');
        const filterLoading = $('#filterLoading');
        const resetFilterBtn = $('#resetFilterBtn');
        const userTableBody = $('#userTableBody');
        const noResultRow = $('#clientNoResultRow');
        const prevPageBtn = $('#prevPageBtn');
        const nextPageBtn = $('#nextPageBtn');
        const paginationInfo = $('#paginationInfo');
        const paginationState = $('#paginationState');

        let currentPage = Number(pageInput.val() || 1);
        let lastPage = 1;
        let lastSubmittedSearch = (searchInput.val() || '').trim();
        let activeFetchRequest = null;

        function escapeHtml(text) {
            return String(text || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function renderRows(items, page, perPage) {
            userTableBody.empty();

            if (!items.length) {
                noResultRow.removeClass('hidden');
                userTableBody.append(noResultRow);
                return;
            }

            noResultRow.addClass('hidden');

            items.forEach(function(item, index) {
                const rowNumber = ((page - 1) * perPage) + (index + 1);
                const rowHtml = `
                        <tr class="transition-colors odd:bg-white even:bg-gray-50/40 hover:bg-blue-50/40 user-row"
                            data-user-name="${escapeHtml(item.name)}"
                            data-user-fullname="${escapeHtml(item.nama_lengkap)}">
                            <td class="px-3 py-2 align-top">
                                <input class="checkbox checkbox-sm" type="checkbox" name="check[]" id="check_${item.id}" value="${item.id}" />
                            </td>
                            <td class="px-3 py-2 align-top">${rowNumber}</td>
                            <td class="hidden px-3 py-2 align-top md:table-cell">
                                <div class="overflow-hidden border border-gray-200 rounded-lg h-14 w-14">
                                    <img loading="lazy" src="${escapeHtml(item.image_url)}" alt="${escapeHtml(item.name)}" class="object-cover object-center w-full h-full" />
                                </div>
                            </td>
                            <td class="px-3 py-2 align-top">
                                <p class="font-medium text-gray-800">${escapeHtml(item.name).toUpperCase()}</p>
                                <div class="mt-1 space-y-0.5 text-xs text-gray-500 md:hidden">
                                    <p>${escapeHtml(item.kerjasama_name)}</p>
                                    <p>${escapeHtml(item.no_hp)}</p>
                                </div>
                            </td>
                            <td class="hidden px-3 py-2 break-words whitespace-pre-line align-top lg:table-cell">${escapeHtml(item.nama_lengkap).charAt(0).toUpperCase() + escapeHtml(item.nama_lengkap).slice(1).toLowerCase()}</td>
                            <td class="px-3 py-2 break-words whitespace-pre-line align-top">${escapeHtml(item.email)}</td>
                            <td class="hidden px-3 py-2 break-words whitespace-pre-line align-top lg:table-cell">${escapeHtml(item.no_hp)}</td>
                            <td class="hidden px-3 py-2 break-words whitespace-pre-line align-top md:table-cell">${escapeHtml(item.kerjasama_name)}</td>
                            <td class="px-3 py-2 align-top">
                                <a href="${escapeHtml(item.edit_url)}" class="inline-flex gap-1 items-center rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700 transition hover:bg-blue-100">
                                    <i class="ri-pencil-line"></i> Edit
                                </a>
                            </td>
                        </tr>
                    `;
                userTableBody.append(rowHtml);
            });
        }

        function updatePaginationUI(pagination) {
            currentPage = pagination.current_page;
            lastPage = pagination.last_page;
            pageInput.val(currentPage);
            paginationInfo.text(
                `Menampilkan ${pagination.from} - ${pagination.to} dari ${pagination.total} user`);
            paginationState.text(`Page ${pagination.current_page} / ${pagination.last_page}`);
            prevPageBtn.prop('disabled', currentPage <= 1);
            nextPageBtn.prop('disabled', currentPage >= lastPage);
        }

        function refreshSelectionState() {
            const selectedCount = $('input[name="check[]"]:checked').length;
            $('#selectedCountBadge').text(selectedCount + ' user dipilih');
            $('#delUser').prop('disabled', selectedCount === 0);
            $('.export-user-btn').prop('disabled', selectedCount === 0);
        }

        function fetchUsers(page = 1) {
            if (activeFetchRequest) {
                activeFetchRequest.abort();
            }

            filterLoading.removeClass('hidden');
            $('#checkbox_all').prop('checked', false);

            activeFetchRequest = $.ajax({
                url: "{{ route('admin.user.index') }}",
                method: 'GET',
                data: {
                    ajax: 1,
                    page: page,
                    filterKerjasama: filterKerjasama.val() || '',
                    search: (searchInput.val() || '').trim()
                },
                success: function(response) {
                    if (!response || !response.pagination || !Array.isArray(response.data)) {
                        userTableBody.empty();
                        noResultRow.removeClass('hidden');
                        userTableBody.append(noResultRow);
                        paginationInfo.text('Format data tidak valid.');
                        paginationState.text('Page - / -');
                        prevPageBtn.prop('disabled', true);
                        nextPageBtn.prop('disabled', true);
                        return;
                    }

                    renderRows(response.data || [], response.pagination.current_page, response
                        .pagination.per_page);
                    updatePaginationUI(response.pagination);
                    refreshSelectionState();
                    lastSubmittedSearch = (searchInput.val() || '').trim();
                },
                error: function(xhr, textStatus) {
                    if (textStatus === 'abort') {
                        return;
                    }

                    userTableBody.empty();
                    noResultRow.removeClass('hidden');
                    userTableBody.append(noResultRow);
                    paginationInfo.text('Gagal memuat data.');
                    paginationState.text('Page - / -');
                    prevPageBtn.prop('disabled', true);
                    nextPageBtn.prop('disabled', true);
                },
                complete: function() {
                    activeFetchRequest = null;
                    filterLoading.addClass('hidden');
                }
            });
        }

        searchInput.on('input', function() {
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(function() {
                const currentSearch = (searchInput.val() || '').trim();
                if (currentSearch === lastSubmittedSearch) return;
                fetchUsers(1);
            }, 300);
        });

        searchInput.on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchDebounceTimer);
                fetchUsers(1);
            }
        });

        filterKerjasama.on('change', function() {
            fetchUsers(1);
        });

        resetFilterBtn.on('click', function() {
            searchInput.val('');
            filterKerjasama.prop('selectedIndex', 0).val('');
            fetchUsers(1);
        });

        filterForm.on('submit', function(e) {
            e.preventDefault();
            fetchUsers(1);
        });

        prevPageBtn.on('click', function() {
            if (currentPage > 1) fetchUsers(currentPage - 1);
        });

        nextPageBtn.on('click', function() {
            if (currentPage < lastPage) fetchUsers(currentPage + 1);
        });

        $('#checkbox_all').on('change', function() {
            $('input[id^="check_"]').prop('checked', $(this).is(':checked'));
            refreshSelectionState();
        });

        $(document).on('change', 'input[name="check[]"]', function() {
            const total = $('input[name="check[]"]').length;
            const selected = $('input[name="check[]"]:checked').length;
            $('#checkbox_all').prop('checked', total > 0 && total === selected);
            refreshSelectionState();
        });

        $('.export-user-btn').on('click', function(e) {
            e.preventDefault();
            setExportType($(this).data('export-type'));
            $('#exportUserForm').submit();
        });

        $('#delUser').on('click', function() {
            const checkedUsers = $('input[name="check[]"]:checked');
            const isiUser = $('#isiUser');
            isiUser.empty();

            if (checkedUsers.length === 0) {
                isiUser.append('<p class="text-red-600">Tidak ada user yang dipilih.</p>');
                return;
            }

            checkedUsers.each(function(index) {
                const userId = $(this).val();
                const userRow = $(this).closest('tr');
                const userName = (userRow.data('user-name') || '').toString().trim();
                const userFullname = (userRow.data('user-fullname') || '').toString().trim();
                isiUser.append(
                    `<p>${index + 1}. User: ${userId} | ${userName} | ${userFullname}</p>`);
            });
        });

        $('#subUser').on('click', function(e) {
            e.preventDefault();
            setExportType('delete');
            $('#exportUserForm').submit();
        });

        refreshSelectionState();
        fetchUsers(currentPage);
    </script>
</x-admin-layout>
