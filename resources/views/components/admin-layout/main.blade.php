@props([
    'fullWidth' => false,
    'headerTitle' => null,
    'online' => null,
    'ip' => null,
])

<a href="#main-content" class="m3-skip-link">Lewati ke konten utama</a>

<!-- MAIN CONTENT AREA -->
{{-- Offset mengikuti lebar drawer (rail 6rem / drawer 15rem) lewat atribut
     `data-sidebar-collapsed` pada pembungkus, sehingga lebar drawer dan offset
     konten selalu sinkron — termasuk saat JavaScript belum jalan. --}}
<div class="admin-content ml-0 flex flex-1 flex-col lg:ml-60">

    <x-admin-layout.topbar :full-width="$fullWidth" :header-title="$headerTitle" :online="$online" :ip="$ip" />

    <!-- Page Content -->
    <main id="main-content" tabindex="-1" class="p-3 sm:p-4 lg:p-6">
        <div class="legacy-admin {{ $fullWidth ? 'w-full' : 'mx-auto max-w-7xl' }}">
            {{ $slot }}
        </div>
    </main>

</div>
