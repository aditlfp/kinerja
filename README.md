# Kinerja - SAC Changelog

Dokumen ini berisi riwayat perubahan aplikasi Kinerja - SAC dengan format versioning berbasis **Semantic Versioning (SemVer)**.

## Versioning Policy

- `MAJOR` (`X.0.0`): perubahan besar / breaking changes.
- `MINOR` (`0.X.0`): fitur baru tanpa breaking changes.
- `PATCH` (`0.0.X`): perbaikan bug, optimasi kecil, dan polish UI.

Format rilis:
- `## [vX.Y.Z] - YYYY-MM-DD`
- Gunakan kategori: `Added`, `Changed`, `Fixed`, `Removed`.

## [v2.0.9] - 2026-11-10

### Changed
- Memperketat validasi absensi masuk agar `user_id`, `kerjasama_id`, dan `shift_id` tidak dapat diganti melalui manipulasi request.
- User biasa hanya dapat membuat absensi untuk dirinya sendiri.
- Route absensi CO tetap dapat memilih karyawan dalam kerjasama yang sama.
- `storePrivate()` menggunakan identitas user dan kerjasama dari session login, bukan nilai form.
- Validasi `kerjasama_id` dan `shift_id` ditambah dengan aturan `exists`.

### Fixed
- Membatasi absen pulang hanya pada data absensi milik user yang sedang login.
- Membatasi penandaan absensi belum pulang (`updateAbsenPulang`) agar tidak dapat memodifikasi data user lain.

### Verification
- Lulus `php -l app/Http/Controllers/AbsensiController.php`.
- Lulus `php -l app/Http/Requests/StoreAbsensiRequest.php`.
- Lulus `php artisan view:cache`.
- Lulus `git diff --check`.

## [v2.0.8] - 2026-05-21

### Changed
- Menyelaraskan logika export dengan hanya memasukkan data `Di Setujui` untuk PersonOut, Overtime, dan export global rekap.
- Menghapus override debug `all_status=1` pada export global dan memperbaiki alur fetch data agar hanya menampilkan data yang disetujui.
- Menyempurnakan format export Excel/PDF dengan filter status yang benar dan refresh data setelah update status PersonOut.

### Fixed
- Memperbaiki handler refresh status PersonOut di UI SPV rekap dari panggilan `loadData()` menjadi `fetchPersonOutData()`.
- Memperbaiki filtering PersonOut agar nama kosong tidak termasuk dalam export dan menyesuaikan format hasil export.

## [v2.0.7] - 2026-05-16

### Added
- Menambahkan struktur module baru `resources/js/absensi/` untuk memisahkan logic absensi menjadi `map.js`, `init.js`, `camera.js`, dan `time.js`.
- Menambahkan debug log frontend untuk gate absensi (`[ABSEN TIME]`, `[ABSEN GATE]`, dan `[GPS DEBUG]`) agar penyebab tombol disable lebih mudah dilacak saat troubleshooting.

### Changed
- Merapikan halaman `resources/views/absensi/index.blade.php` dengan memindahkan logic map, GPS, kamera, dan waktu ke file JavaScript terpisah agar Blade tidak terlalu panjang.
- Menyesuaikan query dashboard agar status absen masuk yang belum pulang dibatasi maksimal 24 jam, sambil tetap mendukung skenario shift overnight yang tampil di hari berikutnya.

### Fixed
- Memperbaiki viewport map agar saat user berada di luar radius, marker user dan titik mitra tampil bersamaan pada area peta yang sama.
- Memperbaiki cache tile peta di sekitar mitra agar area sekitar lokasi lebih siap saat map digeser atau dizoom.
- Memperbaiki gate tombol absensi yang sempat stuck di status `Tunggu` meskipun window shift sudah terbuka.
- Memperbaiki wiring state tombol absensi antara gate waktu, GPS, dan kelengkapan form agar status final tombol konsisten.
- Memperbaiki submit handler tombol absensi yang sempat hilang setelah refactor JavaScript sehingga klik tombol tidak mengirim form.
- Memperbaiki status dashboard yang sempat tidak menampilkan "sudah absen masuk" setelah absen berhasil disimpan.

## [v2.0.6] - 2026-05-16

### Changed
- Merapikan gate tombol absensi jadi evaluator terpusat (waktu, GPS, kelengkapan form).
- Menyesuaikan inisialisasi gate waktu untuk skenario `shift_id` hidden (SPV-W).

### Fixed
- Memperbaiki mismatch bypass radius SPV-W antara frontend dan backend saat absen masuk.
- Memperbaiki tombol absensi SPV-W yang stuck di status `Tunggu` meskipun form sudah lengkap.
- Memperbaiki fallback pembacaan shift agar user non-select shift tidak terkunci validasi waktu.

## [v2.0.5] - 2026-05-13

### Added
- Menambahkan overlay loading full-screen dengan progress bar bertahap saat submit form absensi untuk memberikan feedback visual yang lebih jelas kepada user.
- Menambahkan komponen toast modern dengan desain clean, timer countdown, dan tombol "Hentikan" untuk kontrol manual auto-close.
- Menambahkan icon status di samping title toast untuk memperjelas jenis notifikasi (success, error, warning, info).
- Menambahkan helper `toRupiah()` pada preview nominal input lembur dengan live-format saat user mengetik.

### Changed
- Mendesain ulang komponen toast menjadi single-layer compact card dengan background putih, warna status hanya pada icon/title/progress bar, dan timer visual yang lebih informatif.
- Menyeragamkan desain toast di seluruh layout aplikasi (session toast + flasher toast) agar konsisten dengan referensi modern UI.
- Mengoptimalkan flow submit absensi dengan validasi form native browser sebelum disable tombol dan menampilkan overlay loading.
- Memperbaiki signature `toastr()` di `DataRekapController` agar konsisten dengan pola helper global (message, options, title).
- Menambahkan `<x-flasher-theme />` ke halaman `dashboard.blade.php` dan `absensi/index.blade.php` yang sebelumnya belum include komponen styling toast.

### Fixed
- Memperbaiki bug tombol submit absensi yang tidak mengirim form karena konflik handler event duplikat (GPS block vs submit block).
- Memperbaiki toast yang masih memakai desain lama setelah redirect sukses absen ke dashboard.
- Memperbaiki error JavaScript `Cannot set properties of undefined (setting 'options')` pada flasher-toastr dengan menambahkan dependency `toastr.min.js` sebelum `flasher-toastr.min.js` di config.
- Memperbaiki mapping tipe toast yang salah (typo `succes`, `errorr`, `warn`) agar tetap dapat theme yang benar melalui normalisasi di `session-toast` dan `flasher-theme`.
- Memperbaiki padding, spacing, dan overflow toast yang terlalu besar dengan menyesuaikan CSS override agar lebih compact dan rapi.

## [v2.0.4] - 2026-05-12

### Added
- Menambahkan prefetch tile Leaflet di halaman absensi untuk menghangatkan cache area lokasi user/mitra yang sering dipakai berulang.
- Menambahkan guard permission berbasis frontend pada halaman absensi untuk memastikan izin lokasi dan kamera tervalidasi sebelum tombol absen dapat aktif.
- Menambahkan reminder berkala dan tombol `Minta izin lagi` pada halaman absensi agar user mendapat panduan saat permission lokasi atau kamera belum aktif.

### Changed
- Mengoptimalkan inisialisasi peta absensi dengan lazy tile initialization, layer group terpisah untuk circle lokasi, dan render circle yang lebih ringan.
- Mengoptimalkan query `AbsensiController@index` dengan payload relasi yang lebih kecil, eager loading lokasi aktif, dan penggunaan ulang state waktu/user agar beban query lebih efisien.

### Fixed
- Memperbaiki inkonsistensi state permission lokasi pada flow GPS absensi agar status `locationGranted` ikut tersinkron saat `watchPosition` berhasil.
- Memperbaiki potensi penumpukan layer circle pada peta absensi yang sebelumnya bisa memicu render berulang tidak perlu.

## [v2.0.3] - 2026-05-12

### Changed
- Merapikan penamaan route admin agar konsisten memakai namespace `admin.*` pada modul rekap, izin, jadwal, slip, checklist, rating, berita, subarea, pekerjaan checkpoint, list pekerjaan, report sholat, monev, dan resource CRUD admin utama.
- Menyusun ulang group routing admin di `routes/web.php` menjadi blok yang lebih terstruktur untuk area rekap, API rekap, attendance, dan resource admin tanpa mengubah URI utama yang sudah dipakai aplikasi.

### Fixed
- Memperbaiki konflik nama route peta admin dengan memisahkan route checkpoint map dan absensi map ke nama yang berbeda.
- Memperbaiki referensi route lama pada layout, sidebar, dashboard, Blade admin, dan beberapa redirect controller setelah standardisasi naming route admin.
- Memperbaiki anomali route pada `monev/create.blade.php` agar kembali mengarah ke route admin jabatan dan shift yang valid.

## [v2.0.2] - 2026-05-11

### Added
- Menambahkan komponen toast fallback berbasis session (`x-session-toast`) agar notifikasi tetap muncul konsisten di semua layout utama.
- Menambahkan komponen styling toast global (`x-flasher-theme`) untuk menyeragamkan tampilan `success`, `error`, `warning`, dan `info`.
- Menambahkan helper global modal konfirmasi `window.openConfirmModal(...)` pada layout utama untuk konfirmasi aksi kritikal.
- Menambahkan integrasi Tom Select lokal (tanpa CDN) untuk dropdown user searchable pada modul rekap leader.

### Changed
- Menyelaraskan seluruh teks option dropdown user pada form rekap agar menampilkan nama lengkap saja.
- Memoles UI Tom Select (control, dropdown, option, hover, selected state) agar lebih clean dan konsisten dengan design system aplikasi.
- Mengubah seluruh alur konfirmasi browser native (`confirm()`) pada aksi Hapus, Ajukan, dan Ajukan Semua menjadi modal konfirmasi.
- Menyeragamkan flow notifikasi CRUD rekap agar selalu mengirimkan flash toast session pada redirect.

### Fixed
- **Shared / UI Infrastructure**
- Memperbaiki bug tampilan dropdown user yang terjebak di dalam container dengan mengatur `dropdownParent` ke `body` dan z-index dropdown.
- Memperbaiki bug double border pada input select Tom Select dengan membersihkan class warisan dari elemen select asli.
- Memperbaiki bug notifikasi yang tidak muncul setelah aksi Hapus/Ajukan/Ajukan Semua melalui penyelarasan flash toast session di layout utama.

- **Overtime**
- Memperbaiki mismatch status `pending` (case-sensitive) pada bulk submit sehingga status seperti `Pending` tetap terbaca eligible.
- Menambahkan guard due date pada submit single dan bulk agar pengajuan otomatis terkunci setelah melewati due date.
- Menonaktifkan tombol `Ajukan Semua` saat tidak ada data eligible atau saat masa submit terkunci due date.

- **Person Out**
- Memperbaiki bug update gambar person out yang tidak mengganti file dengan benar pada alur submit.
- Memperbaiki error duplikasi `person_outs.user_id` dengan handling validasi + feedback toast yang jelas.
- Menambahkan guard due date pada submit single dan bulk agar pengajuan terkunci setelah melewati due date.
- Menonaktifkan tombol `Ajukan Semua` saat data eligible kosong atau saat masa submit terkunci due date.

- **Person In**
- Memperbaiki bug data user tidak muncul stabil pada select person in akibat render option berbasis template dinamis saat inisialisasi select.
- Menambahkan guard due date pada submit single dan bulk agar pengajuan terkunci setelah melewati due date.
- Menonaktifkan tombol `Ajukan Semua` saat data eligible kosong atau saat masa submit terkunci due date.

- **Cutting**
- Menambahkan guard due date pada submit single dan bulk agar pengajuan terkunci setelah melewati due date.
- Menonaktifkan tombol `Ajukan Semua` saat data eligible kosong atau saat masa submit terkunci due date.

- **Finished Training**
- Menambahkan guard due date pada submit single dan bulk agar pengajuan terkunci setelah melewati due date.
- Menonaktifkan tombol `Ajukan Semua` saat data eligible kosong atau saat masa submit terkunci due date.

## [v2.0.1] - 2026-05-04

### Added
- Menambahkan skill `playwright` ke environment Codex untuk membantu verifikasi UI dashboard dan layout browser-based.

### Changed
- Mendesain ulang dashboard Mitra dengan layout yang lebih sederhana dan fokus pada ringkasan utama, statistik inti, dan akses modul operasional.
- Menyederhanakan bahasa visual dashboard Mitra dengan mengurangi shape dekoratif berlebihan dan merapikan hierarki panel agar lebih selaras dengan sidebar.
- Memoles palet warna dan komposisi panel Mitra agar terasa lebih tenang, editorial, dan cocok untuk dashboard operasional.

### Fixed
- Memperbaiki responsivitas dashboard Mitra pada mobile dengan menyusun ulang summary, statistik, dan grid menu agar tidak lagi bertumpuk sempit atau terasa berantakan.

## [v2.0.0] - 2026-04-30

### Added
- Menambahkan job `test` pada GitHub Actions untuk validasi install dependency, migrate fresh, dan full test suite sebelum deploy.
- Menambahkan self-profile route compatibility untuk flow update dan delete profile bawaan auth.
- Menambahkan rendering notifikasi PHP Flasher pada layout utama, guest, dan halaman absensi.

### Changed
- Meng-upgrade framework aplikasi ke Laravel 12 dan menyesuaikan dependency pendukung agar kompatibel dengan PHP 8.4.
- Mengembalikan flow login agar tetap memakai `name` dan `password` sesuai kebutuhan production.
- Mengarahkan route `/register` ke flow `kontrak-baru` agar entry point registrasi sesuai proses bisnis yang aktif.
- Memperbarui workflow deploy agar asset frontend dibuild setelah test lolos.
- Memigrasikan integrasi notifikasi dari package `yoeunes/toastr` ke `php-flasher/flasher-toastr-laravel`.

### Fixed
- Memperbaiki beberapa migration lama agar `migrate:fresh` dan test environment kembali stabil.
- Memperbaiki autoload PSR-4 untuk class response dan notification controller.
- Memperbaiki compat auth/profile setelah upgrade agar test Breeze tetap berjalan tanpa mengubah flow utama production.
- Memperbaiki tampilan profile agar tidak crash saat relasi user tertentu kosong.

### Removed
- Menghapus dependency `yoeunes/toastr` yang sudah abandoned.
- Menghapus jalur register bawaan Breeze sebagai entry point utama registrasi.

## [v1.13.4] - 2026-04-29

### Added
- Menambahkan toggle tema Mitra dengan mode `System`, `Light`, dan `Dark` yang tersimpan di `localStorage`.
- Menambahkan transisi pergantian tema khusus layout Mitra dengan overlay animasi yang lebih halus.
- Menambahkan styling scrollbar baru pada area Mitra untuk tabel, sidebar, dan halaman utama.
- Menambahkan helper responsive image pada layout Mitra agar thumbnail hanya dimuat untuk viewport yang aktif.

### Changed
- Merapikan dan menyeragamkan UI/UX seluruh modul `mitra_view` agar lebih profesional, clean, dan konsisten pada light/dark mode.
- Meningkatkan responsivitas halaman Mitra, termasuk card mode mobile untuk modul data utama dan polish sidebar, pagination, serta spacing layar kecil.
- Memoles interaksi picker tema di desktop menjadi popover klik dengan tampilan yang lebih refined.
- Mengoptimalkan halaman laporan, kehadiran, karyawan, dan lembur dengan lazy loading thumbnail, decoding async, serta pengurangan beban render untuk daftar panjang.
- Menyederhanakan efek visual pada layout Mitra agar biaya render lebih ringan tanpa mengorbankan kualitas tampilan.

### Fixed
- Memperbaiki animasi overlay pergantian tema yang sempat tidak muncul atau terasa bertabrakan dengan perubahan warna elemen halaman.
- Memperbaiki komponen Blade yang salah referensi pada halaman `brief/create`.
- Memperbaiki pengalaman mobile pada beberapa halaman Mitra yang sebelumnya masih terasa padat atau kurang stabil.

### Removed
- Menghapus pemanggilan `jqueryNew.min.js` yang redundan pada layout Mitra.

## [v1.13.3] - 2026-04-23

### Added
- Menambahkan peta mini pada modal absen pulang untuk menampilkan lokasi absen masuk, lokasi pulang saat ini, radius lokasi kerja, dan garis jarak antar titik.
- Menambahkan informasi jarak dari lokasi absen masuk ke posisi pulang saat ini pada modal absen pulang.

### Changed
- Mengoptimalkan tracking GPS pulang agar koordinat yang dikirim selalu memakai posisi valid terbaru.
- Membatasi data lokasi radius pulang berdasarkan client absensi terkait agar pengecekan radius lebih tepat.
- Membuat tampilan modal absen pulang lebih compact dan informatif.
- Memuat Leaflet hanya saat user berada pada kondisi yang membutuhkan tracking pulang.

### Fixed
- Memperbaiki input `lat_user` dan `long_user` yang dapat terkirim kosong saat absen pulang.
- Menambahkan validasi backend untuk koordinat pulang dan memastikan `plg_lat` serta `plg_long` tersimpan pada jalur update pulang.

## [v1.13.2] - 2026-04-22

### Added
- Menambahkan route untuk history overtime application dan person-out.

### Changed
- Memperbarui UI/UX pada halaman person in dan person out untuk konsistensi dan responsivitas.
- Memperbarui hash pada skills-lock.json.

## [v1.13.1] - 2026-04-14

### Fixed
- Memperbaiki perhitungan persentase absensi.
- Menyesuaikan link laporan pada beberapa view agar mengarah ke halaman yang tepat.

## [v1.13.0] - 2026-03-26

### Changed
- Merapikan relasi model untuk konsistensi akses data.
- Menyesuaikan query controller agar lebih seragam dengan relasi model terbaru.

## [v1.12.1] - 2026-03-12

### Fixed
- Menyesuaikan kalkulasi persentase absensi dengan mengeluarkan data keterlambatan dari perhitungan tertentu.

## [v1.12.0] - 2026-03-09

### Added
- Menambahkan layout Mitra.
- Menambahkan view Mitra untuk dashboard, absensi, manajemen karyawan, izin, lembur, dan laporan.
- Menambahkan template laporan Mitra.
- Menambahkan halaman rekap data untuk Mitra.
- Menambahkan pencarian dan pengambilan data dinamis pada halaman rekap.

### Changed
- Membuat halaman laporan memilih view secara dinamis berdasarkan role user.
- Menyesuaikan sidebar admin.

## [v1.11.2] - 2026-02-24

### Changed
- Merapikan handling tanggal pada method `show`.
- Menyesuaikan range tanggal untuk perhitungan lembur.

## [v1.11.1] - 2026-02-23

### Fixed
- Memperbaiki halaman rekap data yang sempat tidak tampil.
- Memperbaiki beberapa bagian pada halaman daftar user admin.

## [v1.11.0] - 2026-02-19

### Added
- Menambahkan layout admin baru.
- Menambahkan fitur data rekap.
- Menambahkan modul `personIn`, `personOut`, `performanceCutt`, dan `lepasTraining`.
- Menambahkan menu baru pada admin.
- Menambahkan menu baru pada SPV dan Marketing Management.
- Mengimplementasikan view rekap pada layout admin terbaru.

## [v1.10.1] - 2026-02-06

### Added
- Menambahkan tabel notifikasi.

### Changed
- Menyesuaikan perbandingan nilai pada beberapa logic.

### Fixed
- Memperbaiki bug saat memilih minimum requirement hours.

## [v1.10.0] - 2026-02-05

### Added
- Menambahkan section lembur pada Leader.
- Menambahkan section personil keluar pada Leader.
- Menambahkan halaman `personOut` pada SPV.
- Menambahkan export Excel dan PDF untuk SPV dan MRT.
- Menambahkan section rekap data.
- Menambahkan middleware untuk pengecekan jabatan user yang sedang login.
- Menambahkan group routing berdasarkan jabatan.

### Changed
- Mengubah format export PDF dan Excel pada modul lembur.
- Mengubah konfigurasi shift menjadi satu hari.
- Mengubah penggunaan `hari` menjadi `shift`.

### Fixed
- Menyesuaikan logic perform per section.

## [v1.9.3] - 2026-01-30

### Fixed
- Menghapus logic tampilan client dari informasi user pada halaman index.

## [v1.9.2] - 2026-01-29

### Changed
- Menyesuaikan logic user tidak aktif.
- Menyesuaikan konstanta dashboard.
- Mengoptimalkan pengambilan session pada method index.
- Menyesuaikan limit data terbaru yang ditampilkan.

### Fixed
- Memperbaiki pengambilan data user.
- Memperbaiki logic tampilan client pada halaman index.

## [v1.9.1] - 2026-01-21

### Added
- Menambahkan field `is_overnight` pada shift.
- Menambahkan dukungan shift overnight pada model, view create/edit shift, dan logic absensi.

### Changed
- Menyesuaikan tampilan status absensi.
- Menyesuaikan logic batas akhir shift untuk supervisor.
- Memperbarui konstanta `MINUTES_AFTER_SHIFT_END`.
- Mengubah pesan pulang agar tidak lagi menampilkan indikasi lembur.

### Fixed
- Memperbaiki status tombol checkout.
- Menambahkan fungsi tutup modal.
- Menghapus event listener `beforeunload` yang tidak dipakai pada konfirmasi absensi.

## [v1.9.0] - 2026-01-14

### Added
- Menambahkan seed counter username.
- Menambahkan penghapusan client dengan transaction handling.
- Menambahkan penguatan relasi model terkait client.

### Changed
- Menyederhanakan logic pembuatan username.
- Merapikan route dan view Kerjasama.
- Merapikan beberapa view dan controller untuk konsistensi UI dan fungsi.
- Mengoptimalkan handling geolocation dan perhitungan jarak.
- Mengurutkan lokasi terdekat pada pemilihan lokasi.

### Fixed
- Memperbaiki filter role pada query absensi.
- Memperbaiki konfigurasi Redis cache.
- Memperbaiki mismatch collation pada query username.
- Menyesuaikan threshold jarak pada filtering lokasi.
- Mengubah judul riwayat absensi agar memakai nama perusahaan statis.

## [v1.8.0] - 2025-12-26

### Added
- Menambahkan template email notifikasi OTP baru.
- Menambahkan peningkatan layout untuk email OTP.
- Mengubah retrieval laporan agar memakai model `UploadImage`.

### Changed
- Meningkatkan deteksi kualitas gambar kamera.
- Menyesuaikan handling environment pada kamera.
- Memperbarui fungsi kamera.
- Meningkatkan layout detail izin dan laporan.

### Fixed
- Memperbaiki referensi role user pada logic pengambilan shift.

## [v1.7.0] - 2025-11-27

### Changed
- Meningkatkan handling upload image.
- Meningkatkan deteksi kualitas snapshot.
- Meningkatkan view user dan absensi dengan filter serta layout yang lebih baik.

## [v1.6.0] - 2025-10-16

### Added
- Menambahkan export PDF pada report.
- Menambahkan analytic.
- Menambahkan field `hari` pada manajemen shift.
- Menambahkan password untuk export PDF.

### Changed
- Memperbaiki struktur dan formatting beberapa view.
- Merapikan struktur kode untuk maintainability.
- Memperbarui workflow `main.yml`.
- Meningkatkan UI/UX dashboard admin.
- Memperbarui footer component.
- Menyesuaikan opacity menu admin.
- Menyesuaikan logic export ketika data bukan `kerjasama == 1`.
- Memindahkan teks informasi shift.

### Fixed
- Memperbaiki filter shift.
- Menghapus pembuatan `userAbsen` yang tidak dipakai pada `addKaryawanStore`.
- Mencegah password ikut berubah ketika field password tidak diisi.
- Menyesuaikan logic export user dan export name card pada `AdminController`.

## [v1.5.0] - 2025-09-30

### Added
- Menambahkan field alamat pada form tambah karyawan.
- Menambahkan field alamat pada `UserRequest`, `Employe`, dan `User`.
- Menambahkan kontrak baru dan halaman pengecekan kontrak untuk direksi.
- Menambahkan logic edit Excel pada halaman export.
- Menambahkan export PDF pada report.

### Changed
- Meningkatkan struktur form create dan upload gambar.
- Meningkatkan layout form export.
- Merapikan tombol duplicate pada halaman QR code.
- Menyesuaikan tampilan list pekerjaan ketika field `ruangan` kosong.
- Menyesuaikan label dan layout halaman client, divisi, dan shift.
- Melakukan refactor handling absensi dan komponen UI.

### Fixed
- Memperbaiki retrieval `jabatan_id` pada `addKaryawanStore`.
- Memperbaiki `kerjasama_id` pada kontrak baru untuk `userAbsen`.
- Memperbaiki halaman Excel.

## [v1.4.0] - 2025-08-27

### Added
- Menambahkan request komplain pada slip gaji.
- Menambahkan dukungan upload bukti dengan format gambar yang lebih luas.
- Menambahkan alert kompatibilitas browser pada halaman login.
- Menambahkan toggle visibilitas password pada login.

### Changed
- Mengoptimalkan dashboard direksi.
- Mengoptimalkan CP dan tampilan nilai CP.
- Memperbarui index slip user.
- Menyesuaikan kondisi submit form absensi berdasarkan role user.
- Meningkatkan error handling dan feedback UI pada form absensi.
- Meningkatkan layout login dan guest layout dengan styling serta animasi.
- Menyesuaikan placeholder input login.

### Fixed
- Memperbaiki error absen pulang.
- Memperbaiki beberapa error pada history.
- Memperbaiki index laporan.
- Memperbaiki dashboard leader.
- Menghapus `dd()` pada halaman user.
- Memperbaiki name case dan foto absensi.
- Memperbaiki absensi SPV utama.
- Memperbaiki input file pada edit bukti.
- Memperbaiki logic absensi untuk tambahan pengecekan role user.
- Memperbaiki penempatan logo pada guest layout.

## [v1.3.0] - 2025-08-08

### Added
- Mengimpor project awal dari cPanel.
- Menambahkan workflow GitHub Actions `main.yml`.

### Fixed
- Melakukan beberapa perbaikan awal pada absensi, history, CP, dan dashboard.

## [v1.2.0] - 2024-06-11

### Added
- Menambahkan sistem CP baru.
- Menambahkan update QR Code.
- Menambahkan Rencana Kerja V2.

### Changed
- Memperbarui tampilan direksi.
- Menambahkan beberapa backup dan build update dari periode 2024.

## [v1.0.0] - 2023-12-29

### Added
- Commit awal aplikasi Kinerja - SAC.
