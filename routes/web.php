<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\Admin\HandlingAttendanceToExcelPage;
use App\Http\Controllers\Admin\Rekap\CuttingController as AdminRekapCuttingController;
use App\Http\Controllers\Admin\Rekap\DashboardRekapController as AdminDashboardRekapController;
use App\Http\Controllers\Admin\Rekap\FinishedTrainingController as AdminRekapFinishedTrainingController;
use App\Http\Controllers\Admin\Rekap\OvertimesController as AdminRekapOvertimesController;
use App\Http\Controllers\Admin\Rekap\PersonInController as AdminRekapPersonInController;
use App\Http\Controllers\Admin\Rekap\PersonOutController as AdminRekapPersonOutController;
use App\Http\Controllers\Admin\RekapSettingsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\CheckPointController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DireksiCheckpointController;
use App\Http\Controllers\DivisiController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\IzinController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\JadwalUserController;
use App\Http\Controllers\KerjasamaController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\Mitra_Controller\MainController as MitraController;
use App\Http\Controllers\LEADER_Controller\MainController as LeaderController;
use App\Http\Controllers\LEADER_Controller\DataRekapController;
use App\Http\Controllers\LemburController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\PerlengkapanController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ReportBrefController;
use App\Http\Controllers\RuanganController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\SVP_Controller\MainController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PekerjaanCpController;
use App\Http\Controllers\SubareaController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\FinalisasiController;
use App\Http\Controllers\LEADER_Controller\OvertimeApplicationController;
use App\Http\Controllers\LEADER_Controller\PersonInController;
use App\Http\Controllers\LEADER_Controller\PersonOutController;
use App\Http\Controllers\LEADER_Controller\CuttingController;
use App\Http\Controllers\LEADER_Controller\FinishedTrainingController;
use App\Http\Controllers\LEADER_Controller\KeteranganLanjutanController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\ListPekerjaanController;
use App\Http\Controllers\SlipGajiController;
use App\Http\Controllers\ReportSholatController;
use App\Http\Controllers\MonevController;
use App\Http\Controllers\SVP_Controller\Rekap\DashboardRekapController;
use App\Http\Controllers\SVP_Controller\Rekap\PersonInController as RekapPersonInController;
use App\Http\Controllers\SVP_Controller\Rekap\CuttingController as RekapCuttingController;
use App\Http\Controllers\SVP_Controller\Rekap\FinishedTrainingController as RekapFinishedTrainingController;
use App\Http\Controllers\SVP_Controller\Rekap\OvertimesController;
use App\Http\Controllers\SVP_Controller\Rekap\PersonOutController as RekapPersonOutController;
use App\Http\Controllers\SVP_Controller\Rekap\AllRekapExportController;
use App\Http\Controllers\SPVW_Controller\CuttingController as SPVWCuttingController;
use App\Http\Controllers\SPVW_Controller\DataRekapController as SPVWDataRekapController;
use App\Http\Controllers\SPVW_Controller\FinishedTrainingController as SPVWFinishedTrainingController;
use App\Http\Controllers\SPVW_Controller\KeteranganLanjutanController as SPVWKeteranganLanjutanController;
use App\Http\Controllers\SPVW_Controller\OvertimeApplicationController as SPVWOvertimeApplicationController;
use App\Http\Controllers\SPVW_Controller\PersonInController as SPVWPersonInController;
use App\Http\Controllers\SPVW_Controller\PersonOutController as SPVWPersonOutController;
use App\Models\TempUser;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
})->middleware('guest');


Route::view('/map', 'absensi.maps');
Route::get('/get-uptime', [AdminController::class, 'exportCheck']);
Route::get('/send', [DashboardController::class, 'sendTestEmail']);
// Route::resource('/coba-coba', NewsController::class);
// Route::resource('brief', ReportBrefController::class);
Route::post('/proses-export', [AdminController::class, 'prosesExport'])->name('export_checklist');
Route::get('tes-news/', [NewsController::class, 'NewsBefore']);
Route::get('tes-news-dw/{id}', [NewsController::class, 'NewsDownload'])->name('newsDownload');

Route::get('/kontrak-baru', [UserController::class, 'addKaryawanIndex'])->name('addKaryawanIndex');
Route::post('/addKaryawan/send', [UserController::class, 'addKaryawanStore'])->name('addKaryawanStore');

Route::post('/send-otp-reg', function (\Illuminate\Http\Request $request) {
    return sendOtpReg($request->email, $request->data); // <-- panggil helper dari backend
});
Route::post('/verify-otp-reg', function (\Illuminate\Http\Request $request) {
    return verifOtpReg($request->email, $request->otp);
});
Route::get('/check-email', function (\Illuminate\Http\Request $request) {
    $email = \App\Models\User::where('email', $request->email)->exists();
    $phone = \App\Models\User::where('no_hp', $request->no_hp)->exists();
    return response()->json(['email' => $email, 'phone' => $phone]);
});

Route::get('/seed-username-counter', function (UserService $userService) {
    $number = $userService->initUsernameCounter();

    return "Counter initialized to SAC{$number}";
});

Route::get('/notifications/{id}', function ($id) {
    $notif = auth()->user()
        ->notifications()
        ->where('id', $id)
        ->firstOrFail();

    $notif->markAsRead();

    return match ($notif->data['type']) {
        'overtime' =>
        redirect()->route('manajemen_rekap_indexOvertimes', $notif->data['kerjasama_id']),
        'person_out' =>
        redirect()->route('manajemen_rekap_indexPersonOut', $notif->data['kerjasama_id']),
        'person_in' =>
        redirect()->route('manajemen_rekap_indexPersonIn', $notif->data['kerjasama_id']),
        'cutting' =>
        redirect()->route('manajemen_rekap_indexCutting', $notif->data['kerjasama_id']),
        'finished_training' =>
        redirect()->route('manajemen_rekap_indexFinishedTraining', $notif->data['kerjasama_id']),
        default => back(),
    };
})->middleware('auth')->name('notifications.redirect');

// Only AUTH
Route::middleware(['auth', 'apdt'])->group(function () {
    Route::view('/scan', 'admin.qrcode.scan');
    Route::get('/laporan/scan/{ruanganId}/{kerjasamaId}', [LaporanController::class, 'getcode'])->name('lapcode');
    Route::put('/data/{id}/updatePulang', [AbsensiController::class, 'updatePulang'])->name('data.update');
    Route::put('/data/{id}/updateSiang', [AbsensiController::class, 'updateSiang'])->name('data.update.siang');
    Route::post('/data/{id}/updateAbsenPulang', [AbsensiController::class, 'updateAbsenPulang'])->name('data-telat.update');
    Route::resource('/dashboard', DashboardController::class);
    Route::resource('/absensi', AbsensiController::class);
    Route::get('/historyAbsensi', [AbsensiController::class, 'historyAbsensi']);
    Route::patch('/profile', [ProfileController::class, 'updateSelf'])->name('profile.self.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.self.destroy');
    Route::resource('/profile', ProfileController::class);
    Route::resource('/lembur', LemburController::class)->only('index', 'store', 'update');
    Route::get('/lembur-history', [LemburController::class, 'lemburIndexUser'])->name('lemburIndexUser');
    Route::get('/rate/{id}', [RatingController::class, 'myRate'])->name('ratingSaya');
    Route::resource('/izin', IzinController::class);
    Route::resource('/laporan', LaporanController::class)->only('index', 'create', 'store');
    Route::get('/mypoint/{id}', [PointController::class, 'myPoint'])->name('mypoint');

    Route::resource('checkpoint-user', CheckPointController::class);
    Route::get('checkpoint-user-history', [CheckPointController::class, 'history'])->name('checkpoint-user.history');
    Route::get('checkpoint-user-history/{id}', [CheckPointController::class, 'historyShow'])->name('checkpoint-user.history.show');
    Route::get('editBukti-checkpoint-user', [CheckPointController::class, 'editBukti'])->name('editBukti-checkpoint-user');
    Route::post('uploadBukti-checkpoint-user', [CheckPointController::class, 'uploadBukti'])->name('uploadBukti-checkpoint-user');

    Route::get('/riwayat-kerja/{id}', [RatingController::class, 'rateKerja'])->name('rate.kerja');
    Route::get('/getJadwal/{id}', [JadwalUserController::class, 'getJadwal'])->name('get-jadwal');

    Route::get('/absensi-private', [AbsensiController::class, 'indexPrivate']);
    Route::post('/absensi-private-store', [AbsensiController::class, 'storePrivate']);

    Route::get('/api/waktu-sholat', [DashboardController::class, 'waktuSholat'])->name('waktu-sholat');
    Route::put('absensi-sholat/{id}', [AbsensiController::class, 'updateAbsenSholat'])->name('absensi-sholat.update');

    Route::get('/get-shifts/{cli}/{jab}', [AbsensiController::class, 'getShift'])->name('olehShift');
    // laporan Mitra
    Route::get('/laporanMitra', [LaporanController::class, 'indexLaporanMitra'])->name('laporanMitra.index');
    Route::get('/laporanMitra/create', [LaporanController::class, 'createLaporanMitra'])->name('laporanMitra.create');
    Route::post('/laporanMitra/post', [LaporanController::class, 'storeLaporanMitra'])->name('laporanMitra.post');
    Route::get('/laporanMitra/{id}', [LaporanController::class, 'editLaporanMitra'])->name('laporanMitra.edit');
    Route::put('/laporanMitra/{id}/update', [LaporanController::class, 'updateLaporanMitra'])->name('laporanMitra.update');
    Route::delete('/laporanMitra/{id}', [LaporanController::class, 'deleteLaporanMitra'])->name('laporanMitra.delete');

    Route::get('/slip-gaji', [SlipGajiController::class, 'index'])->name('slip-gaji.index');
    Route::get('/slip-gaji/export', [SlipGajiController::class, 'exportWith'])->name('slip-gaji.export');

    Route::get('/slip-gaji-karyawan', [SlipGajiController::class, 'leaderIndex'])->name('slip-karyawan');

    Route::get('/form-kontrak/index/{token}', [ProfileController::class, 'indexKontrak'])->middleware('throttle:30,1')->name('form-kontrak-index');
    Route::get('/form-kontrak/pengajuan', [ProfileController::class, 'requestKontrak'])->middleware('throttle:30,1')->name('form-kontrak-request');
    Route::post('/form-kontrak/kirimPengajuan', [ProfileController::class, 'kirimRequest'])->middleware('throttle:10,1')->name('form-kontrak-kirimPengajuan');
    Route::get('/form-kontrak/preview/{token}', [ProfileController::class, 'previewKontrak'])->middleware('throttle:30,1')->name('form-kontrak-preview');
    Route::put('/form-kontrak/update/{token}', [ProfileController::class, 'updateKontrak'])->middleware('throttle:10,1')->name('form-kontrak-update');
});

// Untuk Direksi
Route::middleware(['auth', 'direksi'])->group(function () {
    Route::resource('/direksi-rating', RatingController::class);
    Route::get('/direksi-laporan', [LeaderController::class, 'indexLaporan'])->name('direksi_laporan');
    Route::get('/direksi-lembur', [MainController::class, 'indexLembur'])->name('direksi_lembur');
    Route::get('/direksi-absensi-izin', [IzinController::class, 'indexLead'])->name('direksi_izin');
    Route::get('/direksi-absensi', [LeaderController::class, 'indexAbsen'])->name('direksi_absensi');
    Route::get('/direksi-jadwal', [JadwalUserController::class, 'index'])->name('direksi_jadwal');
    Route::get('/direksi-user', [LeaderController::class, 'indexUser'])->name('direksi_user');
    Route::get('/direksi-checkpoint', [DireksiCheckpointController::class, 'index'])->name('direksi.cp.index');
    Route::get('/direksi-checkpoint/{user}/calendar', [DireksiCheckpointController::class, 'calendar'])->name('direksi.cp.calendar');
    Route::get('/direksi-checkpoint-history', [DireksiCheckpointController::class, 'history'])->name('direksi.cp.history');
    Route::get('/direksi-checkpoint-history/{id}', [DireksiCheckpointController::class, 'historyDetail'])->name('direksi.cp.history.show');
    Route::patch('/direksi-checkpoint-history/{id}/approve', [DireksiCheckpointController::class, 'updateApproval'])->name('direksi.cp.history.approve');
    Route::get('/direksi-lihat-check/{id}', [AdminController::class, 'lihatCheck'])->name('direksi.cp.show');
    Route::put('/direksi-nilai-cp/{id}', [CheckPointController::class, 'uploadNilai'])->name('direksi.uploadNilai');
    Route::delete('/direksi-delete-rk/{id}', [CheckPointController::class, 'deleteRencana'])->name('direksi.deleteRencana');
    // Route::patch('/direksi-approve-cp/{id}', [AdminController::class, 'approveCheck'])->name('direksi.approveCP');
    // Route::patch('/direksi-denied-cp/{id}', [AdminController::class, 'deniedCheck'])->name('direksi.deniedCP');
    Route::get('/direksi-check-koordinat/{id}', [CheckPointController::class, "show"])->name('direksi-lihatMap');

    Route::get('/direksi-kontrak/check-kontrak', [ProfileController::class, 'cekKontrak'])->name('direksi-cekKontrak');
    Route::put('/direksi-kontrak/acc-kontrak', [ProfileController::class, 'accKontrak'])->name('direksi-accKontrak');
});

// Untuk Mitra
Route::middleware(['auth', 'mitra'])->group(function () {
    Route::resource('/mitra-rating', RatingController::class);
    Route::get('/mitra-laporan', [LeaderController::class, 'indexLaporan'])->name('mitra_laporan');
    Route::get('/mitra-laporan/{id}', [LeaderController::class, 'showLaporan'])->name('mitra_laporan.show');
    Route::get('/mitra-lembur', [MainController::class, 'indexLembur'])->name('mitra_lembur');
    Route::get('/mitra-absensi-izin', [IzinController::class, 'indexLead'])->name('mitra_izin');
    Route::get('/mitra-rekap', [MitraController::class, 'indexRekap'])->name('mitra_rekap');
    Route::get('/mitra-absensi', [MitraController::class, 'indexKehadiran'])->name('mitra_absensi');
    Route::get('/mitra-check-koordinat/{id}', [MitraController::class, "showLocation"])->name('mitra-lihatMap');
    Route::get('/mitra-jadwal', [JadwalUserController::class, 'index'])->name('mitra_jadwal');
    Route::get('/mitra-user', [MitraController::class, 'indexKaryawan'])->name('mitra_user');

    Route::get('/mitra-laporan-bulanan', [LaporanController::class, 'indexLaporanMitra'])->name('mitra-laporan-bulanan.index');
});

// untuk SPV
Route::middleware(['auth', 'spv', 'apdt'])->group(function () {
    Route::get('/SPV/spv-absensi', [MainController::class, 'indexAbsen'])->name('spv_absensi');
    Route::get('/SPV/spv-laporan', [MainController::class, 'indexLaporan'])->name('spv_laporan');
    Route::get('/SPV/spv-lembur', [MainController::class, 'indexLembur'])->name('spv_lembur');
    Route::get('/SPV/spv-user', [MainController::class, 'indexUser'])->name('spv_user');
});

// untuk Manajemen
Route::middleware(['auth', 'apdt'])->group(function () {
    Route::middleware(['rekap.management'])->group(function () {
        Route::get('/Management/rekap-data', [DashboardRekapController::class, 'index'])->name('manajemen_rekap');
        Route::get('/Management/rekap-overtimes/{id}', [DashboardRekapController::class, 'indexOvertimes'])->name('manajemen_rekap_indexOvertimes');
        Route::get('/Management/rekap-person-out/{id}', [DashboardRekapController::class, 'indexPersonOut'])->name('manajemen_rekap_indexPersonOut');
        Route::get('/Management/rekap-person-in/{id}', [DashboardRekapController::class, 'indexPersonIn'])->name('manajemen_rekap_indexPersonIn');
        Route::get('/Management/rekap-cutting/{id}', [DashboardRekapController::class, 'indexCutting'])->name('manajemen_rekap_indexCutting');
        Route::get('/Management/rekap-finished-training/{id}', [DashboardRekapController::class, 'indexFinishedTraining'])->name('manajemen_rekap_indexFinishedTraining');
        Route::get('/Management/rekap-keterangan-lanjutan/{id}', [DashboardRekapController::class, 'indexKeteranganLanjutan'])->name('manajemen_rekap_indexKeteranganLanjutan');

        //API AJA
        Route::get('/api/v1/overtimes-api/{kerjasama}', [OvertimesController::class, 'index'])->name('api-overtimes');
        Route::get('/api/v1/person-out-api/{kerjasama}', [RekapPersonOutController::class, 'index'])->name('api-person-out');
        Route::get('/api/v1/person-in-api/{kerjasama}', [RekapPersonInController::class, 'index'])->name('api-person-in');
        Route::get('/api/v1/cutting-api/{kerjasama}', [RekapCuttingController::class, 'index'])->name('api-cutting');
        Route::get('/api/v1/finished-training-api/{kerjasama}', [RekapFinishedTrainingController::class, 'index'])->name('api-finished-training');
        Route::get('/Management/api/v1/all-rekap-export/{kerjasama}', [AllRekapExportController::class, 'getAllRekapData'])->name('management.api-all-rekap-export');
        Route::get('/api/v1/all-rekap-export-global', [AllRekapExportController::class, 'getGlobalRekapData'])->name('api-all-rekap-export-global');
        Route::get('/api/v1/keterangan-lanjutan-api/{kerjasama}', [KeteranganLanjutanController::class, 'index'])->name('api-keterangan-lanjutan');
        Route::patch('/api/v1/rekap/overtimes/{id}/status', [OvertimesController::class, 'updateStatus'])->name('api-overtimes-status');
        Route::patch('/api/v1/rekap/person-out/{id}/status', [RekapPersonOutController::class, 'updateStatus'])->name('api-person-out-status');
        Route::patch('/api/v1/rekap/person-in/{id}/status', [RekapPersonInController::class, 'updateStatus'])->name('api-person-in-status');
        Route::patch('/api/v1/rekap/cutting/{id}/status', [RekapCuttingController::class, 'updateStatus'])->name('api-cutting-status');
        Route::patch('/api/v1/rekap/finished-training/{id}/status', [RekapFinishedTrainingController::class, 'updateStatus'])->name('api-finished-training-status');
    });

    Route::get('/Management/spv-absensi', [MainController::class, 'indexAbsen'])->name('manajemen_absensi');
    Route::get('/Management/spv-laporan', [MainController::class, 'indexLaporan'])->name('manajemen_laporan');
    Route::get('/Management/spv-lembur', [MainController::class, 'indexLembur'])->name('manajemen_lembur');
    Route::get('/Management/spv-user', [MainController::class, 'indexUser'])->name('manajemen_user');
});

// SPV W
Route::middleware(['auth', 'spv-w', 'apdt', 'spvw.client-filter'])->group(function () {
    Route::view('spvView', 'leader_view/leaderView')->name('SPVWiew');

    Route::resource('/SPVW/spvw-rating', RatingController::class);
    Route::get('/SPVW/spvw-user', [LeaderController::class, 'indexUser'])->name('spvw_user');
    Route::get('/SPVW/spvw-absensi', [LeaderController::class, 'indexAbsen'])->name('spvw_absensi');
    Route::get('/SPVW/spvw-laporan', [LeaderController::class, 'indexLaporan'])->name('spvw_laporan');
    Route::get('/SPVW/spvw-lembur', [LeaderController::class, 'indexLembur'])->name('spvw_lembur');

    Route::resource('/SPVW/spvw-jadwal', JadwalUserController::class);
    Route::post('/SPVW/jadwal-store', [JadwalUserController::class, 'storeJadwal'])->name("storeJadwalSPVW");
    Route::get('/SPVW/spvw-jadwal-new', [JadwalUserController::class, 'processDate'])->name('store.processDate.SPVW');
    Route::get('/SPVW/spvw-jadwal-export', [JadwalUserController::class, 'exportJadwal'])->name('SPVW_jadwal_export');

    Route::get('/SPVW/spvw-absensi-izin', [IzinController::class, 'indexLead'])->name('spvw_izin');
    Route::patch('/SPVW/spvw-absensi-izin/accept/{id}', [IzinController::class, 'updateSuccess'])->name('spvw_acc');
    Route::patch('/SPVW/spvw-absensi/denied/{id}', [IzinController::class, 'updateDenied'])->name('spvw_denied');

    Route::resource('/spvw-checklist', ChecklistController::class);
    Route::post('/spvw-checklist-ajx', [ChecklistController::class, 'signatureChecklistAJX'])->name('spvw-checklist.ajx');
    Route::resource('/absensi-karyawan-spvw', AbsensiController::class);

    Route::get('/spvw-absenSholat', [LeaderController::class, 'indexAbsenSholat'])->name('spvw-absenSholat');
    Route::post('/spvw-absenSholat-store', [LeaderController::class, 'storeAbsenSholat'])->name('spvw-absenSholat-store');
    Route::get('/spvw-slip-gaji', [SlipGajiController::class, 'leaderIndex'])->name('spvw-slip');

    Route::resource('/spvw-monev', MonevController::class);

    Route::get('/SPVW/rekap-data', [SPVWDataRekapController::class, 'index'])->name('spvw.rekap.index');
    Route::get('/SPVW/api/v1/all-rekap-export-global', [AllRekapExportController::class, 'getGlobalRekapData'])->name('spvw.api-all-rekap-export-global');

    Route::middleware('spvw.client-required')->group(function () {
        Route::get('/SPVW/overtime-application/create', [SPVWOvertimeApplicationController::class, 'create'])->name('spvw.overtime-application.create');
        Route::post('/SPVW/overtime-application', [SPVWOvertimeApplicationController::class, 'store'])->name('spvw.overtime-application.store');
        Route::get('/SPVW/overtime-application/history', [SPVWOvertimeApplicationController::class, 'history'])->name('spvw.overtime-application.history');
        Route::get('/SPVW/overtime-application/{id}/edit', [SPVWOvertimeApplicationController::class, 'edit'])->name('spvw.overtime-application.edit');
        Route::put('/SPVW/overtime-application/{id}', [SPVWOvertimeApplicationController::class, 'update'])->name('spvw.overtime-application.update');
        Route::delete('/SPVW/overtime-application/{id}', [SPVWOvertimeApplicationController::class, 'destroy'])->name('spvw.overtime-application.destroy');
        Route::get('/SPVW/api/v1/get-overtime/{id}', [SPVWOvertimeApplicationController::class, 'fetchApi'])->name('spvw.get-overtime-id');
        Route::patch('/SPVW/overtime-change-status/{id}', [SPVWOvertimeApplicationController::class, 'changeStatus'])->name('spvw.overtime.change_status');
        Route::patch('/SPVW/overtime-change-bulk', [SPVWOvertimeApplicationController::class, 'bulkStatus'])->name('spvw.overtime-bulk.status');

        Route::get('/SPVW/person-is-out/create', [SPVWPersonOutController::class, 'create'])->name('spvw.person-is-out.create');
        Route::post('/SPVW/person-is-out', [SPVWPersonOutController::class, 'store'])->name('spvw.person-is-out.store');
        Route::get('/SPVW/person-is-out/history', [SPVWPersonOutController::class, 'history'])->name('spvw.person-is-out.history');
        Route::get('/SPVW/person-is-out/{id}/edit', [SPVWPersonOutController::class, 'edit'])->name('spvw.person-is-out.edit');
        Route::put('/SPVW/person-is-out/{id}', [SPVWPersonOutController::class, 'update'])->name('spvw.person-is-out.update');
        Route::delete('/SPVW/person-is-out/{id}', [SPVWPersonOutController::class, 'destroy'])->name('spvw.person-is-out.destroy');
        Route::get('/SPVW/api/v1/get-person-is-out/{id}', [SPVWPersonOutController::class, 'fetchApi'])->name('spvw.person-is-out-id');
        Route::patch('/SPVW/person-is-out-change-status/{id}', [SPVWPersonOutController::class, 'changeStatus'])->name('spvw.person-is-out.change_status');
        Route::patch('/SPVW/person-is-out-bulk', [SPVWPersonOutController::class, 'bulkStatus'])->name('spvw.person-is-out-bulk.status');

        Route::get('/SPVW/person-in', [SPVWPersonInController::class, 'index'])->name('spvw.person-in.index');
        Route::post('/SPVW/person-in', [SPVWPersonInController::class, 'store'])->name('spvw.person-in.store');
        Route::get('/SPVW/person-is-in/history', [SPVWPersonInController::class, 'history'])->name('spvw.person.in.history');
        Route::get('/SPVW/person-in/users/search', [SPVWPersonInController::class, 'searchUsers'])->name('spvw.person-in.users.search');
        Route::get('/SPVW/person-in/{id}', [SPVWPersonInController::class, 'show'])->name('spvw.person-in.show');
        Route::put('/SPVW/person-in/{id}', [SPVWPersonInController::class, 'update'])->name('spvw.person-in.update');
        Route::delete('/SPVW/person-in/{id}', [SPVWPersonInController::class, 'destroy'])->name('spvw.person-in.destroy');
        Route::get('/SPVW/api/v1/get-person-in/{id}', [SPVWPersonInController::class, 'fetchApi'])->name('spvw.person-in-id');
        Route::patch('/SPVW/person-in-change-status/{id}', [SPVWPersonInController::class, 'changeStatus'])->name('spvw.person-in.change_status');
        Route::patch('/SPVW/person-in-bulk', [SPVWPersonInController::class, 'bulkStatus'])->name('spvw.person-in-bulk.status');

        Route::get('/SPVW/cutting', [SPVWCuttingController::class, 'index'])->name('spvw.cutting.index');
        Route::post('/SPVW/cutting', [SPVWCuttingController::class, 'store'])->name('spvw.cutting.store');
        Route::get('/SPVW/cutting/history', [SPVWCuttingController::class, 'history'])->name('spvw.cutting.history');
        Route::get('/SPVW/cutting/users/search', [SPVWCuttingController::class, 'searchUsers'])->name('spvw.cutting.users.search');
        Route::get('/SPVW/cutting/{id}', [SPVWCuttingController::class, 'show'])->name('spvw.cutting.show');
        Route::put('/SPVW/cutting/{id}', [SPVWCuttingController::class, 'update'])->name('spvw.cutting.update');
        Route::delete('/SPVW/cutting/{id}', [SPVWCuttingController::class, 'destroy'])->name('spvw.cutting.destroy');
        Route::get('/SPVW/api/v1/get-cutting/{id}', [SPVWCuttingController::class, 'fetchApi'])->name('spvw.cutting-id');
        Route::patch('/SPVW/cutting-change-status/{id}', [SPVWCuttingController::class, 'changeStatus'])->name('spvw.cutting.change_status');
        Route::patch('/SPVW/cutting-bulk', [SPVWCuttingController::class, 'bulkStatus'])->name('spvw.cutting-bulk.status');

        Route::get('/SPVW/finished-training', [SPVWFinishedTrainingController::class, 'index'])->name('spvw.finished-training.index');
        Route::post('/SPVW/finished-training', [SPVWFinishedTrainingController::class, 'store'])->name('spvw.finished-training.store');
        Route::get('/SPVW/finished-training/history', [SPVWFinishedTrainingController::class, 'history'])->name('spvw.finished-training.history');
        Route::get('/SPVW/finished-training/users/search', [SPVWFinishedTrainingController::class, 'searchUsers'])->name('spvw.finished-training.users.search');
        Route::get('/SPVW/finished-training/{id}', [SPVWFinishedTrainingController::class, 'show'])->name('spvw.finished-training.show');
        Route::put('/SPVW/finished-training/{id}', [SPVWFinishedTrainingController::class, 'update'])->name('spvw.finished-training.update');
        Route::delete('/SPVW/finished-training/{id}', [SPVWFinishedTrainingController::class, 'destroy'])->name('spvw.finished-training.destroy');
        Route::get('/SPVW/api/v1/get-finished-training/{id}', [SPVWFinishedTrainingController::class, 'fetchApi'])->name('spvw.finished-training-id');
        Route::patch('/SPVW/finished-training-change-status/{id}', [SPVWFinishedTrainingController::class, 'changeStatus'])->name('spvw.finished-training.change_status');
        Route::patch('/SPVW/finished-training-bulk', [SPVWFinishedTrainingController::class, 'bulkStatus'])->name('spvw.finished-training-bulk.status');

        Route::get('/SPVW/keterangan-lanjutan', [SPVWKeteranganLanjutanController::class, 'index'])->name('spvw.keterangan-lanjutan.index');
        Route::post('/SPVW/keterangan-lanjutan', [SPVWKeteranganLanjutanController::class, 'store'])->name('spvw.keterangan-lanjutan.store');
        Route::get('/SPVW/keterangan-lanjutan/history', [SPVWKeteranganLanjutanController::class, 'history'])->name('spvw.keterangan-lanjutan.history');
    });
});

Route::middleware(['auth', 'only:CO-CS,CO-SCR', 'apdt'])->group(function () {
    Route::get('/rekap-data', [DataRekapController::class, 'index'])->name('index.rekap.data.leader');
    Route::get('/api/v1/all-rekap-export/{kerjasama}', [AllRekapExportController::class, 'getAllRekapData'])->name('leader.api-all-rekap-export');
    Route::post('/rekap/exemption/self', [DataRekapController::class, 'exemptSelf'])->name('rekap.exemption.self');
    Route::get('/overtime-application/history', [OvertimeApplicationController::class, 'history'])->name('overtime-application.history');
    Route::resource('/overtime-application', OvertimeApplicationController::class);
    Route::get('/api/v1/get-overtime/{id}', [OvertimeApplicationController::class, 'fetchApi'])->name('get-overtime-id');
    Route::patch('/overtime-change-status/{id}', [OvertimeApplicationController::class, 'changeStatus'])->name('overtime.change_status');
    Route::patch('/overtime-change-bulk', [OvertimeApplicationController::class, 'bulkStatus'])->name('overtime-bulk.status');

    Route::get('/person-is-out/history', [PersonOutController::class, 'history'])->name('person-is-out.history');
    Route::resource('/person-is-out', PersonOutController::class);
    Route::get('/api/v1/get-person-is-out/{id}', [PersonOutController::class, 'fetchApi'])->name('person-is-out-id');
    Route::patch('/person-is-out-change-status/{id}', [PersonOutController::class, 'changeStatus'])->name('person-is-out.change_status');
    Route::patch('/person-is-out-bulk', [PersonOutController::class, 'bulkStatus'])->name('person-is-out-bulk.status');

    Route::resource('/person-in', PersonInController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::get('/person-is-in/history', [PersonInController::class, 'history'])->name('person.in.history');
    Route::get('/person-in/users/search', [PersonInController::class, 'searchUsers'])->name('person-in.users.search');
    Route::get('/api/v1/get-person-in/{id}', [PersonInController::class, 'fetchApi'])->name('person-in-id');
    Route::patch('/person-in-change-status/{id}', [PersonInController::class, 'changeStatus'])->name('person-in.change_status');
    Route::patch('/person-in-bulk', [PersonInController::class, 'bulkStatus'])->name('person-in-bulk.status');

    Route::get('/cutting/history', [CuttingController::class, 'history'])->name('cutting.history');
    Route::resource('/cutting', CuttingController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::get('/cutting/users/search', [CuttingController::class, 'searchUsers'])->name('cutting.users.search');
    Route::get('/api/v1/get-cutting/{id}', [CuttingController::class, 'fetchApi'])->name('cutting-id');
    Route::patch('/cutting-change-status/{id}', [CuttingController::class, 'changeStatus'])->name('cutting.change_status');
    Route::patch('/cutting-bulk', [CuttingController::class, 'bulkStatus'])->name('cutting-bulk.status');

    Route::get('/finished-training/history', [FinishedTrainingController::class, 'history'])->name('finished-training.history');
    Route::resource('/finished-training', FinishedTrainingController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::get('/finished-training/users/search', [FinishedTrainingController::class, 'searchUsers'])->name('finished-training.users.search');
    Route::get('/api/v1/get-finished-training/{id}', [FinishedTrainingController::class, 'fetchApi'])->name('finished-training-id');
    Route::patch('/finished-training-change-status/{id}', [FinishedTrainingController::class, 'changeStatus'])->name('finished-training.change_status');
    Route::patch('/finished-training-bulk', [FinishedTrainingController::class, 'bulkStatus'])->name('finished-training-bulk.status');

    Route::get('/keterangan-lanjutan/history', [KeteranganLanjutanController::class, 'history'])->name('keterangan-lanjutan.history');
    Route::get('/keterangan-lanjutan', [KeteranganLanjutanController::class, 'index'])->name('keterangan-lanjutan.index');
    Route::post('/keterangan-lanjutan', [KeteranganLanjutanController::class, 'store'])->name('keterangan-lanjutan.store');
});

// leader
Route::middleware(['auth', 'leader', 'apdt'])->group(function () {
    Route::resource('/LEADER/leader-rating', RatingController::class);
    Route::get('/LEADER/leader-absensi', [LeaderController::class, 'indexAbsen'])->name('lead_absensi');
    Route::get('/LEADER/leader-laporan', [LeaderController::class, 'indexLaporan'])->name('lead_laporan');
    Route::get('/LEADER/leader-lembur', [LeaderController::class, 'indexLembur'])->name('lead_lembur');
    Route::get('/LEADER/leader-user', [LeaderController::class, 'indexUser'])->name('lead_user');

    Route::resource('/LEADER/leader-jadwal', JadwalUserController::class);
    Route::post('/LEADER/jadwal-store-new', [JadwalUserController::class, 'storeJadwal'])->name("storeJadwalLeader");
    Route::get('/LEADER/leader-jadwal-new', [JadwalUserController::class, 'processDate'])->name('store.processDate');
    Route::get('/LEADER/leader-jadwal-export', [JadwalUserController::class, 'exportJadwal'])->name('lead_jadwal_export');

    Route::get('/LEADER/leader-absensi-izin', [IzinController::class, 'indexLead'])->name('lead_izin');
    Route::patch('/LEADER/leader-absensi-izin/accept/{id}', [IzinController::class, 'updateSuccess'])->name('lead_acc');
    Route::patch('/LEADER/leader-absensi/denied/{id}', [IzinController::class, 'updateDenied'])->name('lead_denied');
    Route::view('leaderView', 'leader_view/leaderView')->name('leaderView');

    Route::resource('/leader-checklist', ChecklistController::class);
    Route::post('/leader-checklist-ajx', [ChecklistController::class, 'signatureChecklistAJX'])->name('leader-checklist.ajx');
    Route::resource('/absensi-karyawan-co-cs', AbsensiController::class);

    Route::get('/leader-absenSholat', [LeaderController::class, 'indexAbsenSholat'])->name('leader-absenSholat');
    Route::post('/leader-absenSholat-store', [LeaderController::class, 'storeAbsenSholat'])->name('leader-absenSholat-store');
    Route::get('/leader-slip-gaji', [SlipGajiController::class, 'leaderIndex'])->name('leader-slip');
});
// danru
Route::middleware(['auth', 'danru', 'apdt'])->group(function () {
    Route::resource('/DANRU/danru-rating', RatingController::class);
    Route::get('/DANRU/danru-absensi', [LeaderController::class, 'indexAbsen'])->name('danru_absensi');
    Route::get('/DANRU/danru-laporan', [LeaderController::class, 'indexLaporan'])->name('danru_laporan');
    Route::get('/DANRU/danru-lembur', [LeaderController::class, 'indexLembur'])->name('danru_lembur');
    Route::get('/DANRU/danru-user', [LeaderController::class, 'indexUser'])->name('danru_user');

    Route::resource('/DANRU/danru-jadwal', JadwalUserController::class);
    Route::post('/DANRU/danru-store', [JadwalUserController::class, 'storeJadwal'])->name("storeJadwaldanru");
    Route::get('/DANRU/danru-jadwal-new', [JadwalUserController::class, 'processDate'])->name('danruStore.processDate');
    Route::get('/DANRU/danru-jadwal-export', [JadwalUserController::class, 'exportJadwal'])->name('danru_jadwal_export');

    Route::get('/DANRU/danru-absensi-izin', [IzinController::class, 'indexLead'])->name('danru_izin');
    Route::patch('/DANRU/danru-absensi-izin/accept/{id}', [IzinController::class, 'updateSuccess'])->name('danru_acc');
    Route::patch('/DANRU/danru-absensi/denied/{id}', [IzinController::class, 'updateDenied'])->name('danru_denied');
    Route::view('danruView', 'leader_view/leaderView')->name('danruView');
    Route::resource('/absensi-karyawan-co-scr', AbsensiController::class);

    Route::get('/danru-absenSholat', [LeaderController::class, 'indexAbsenSholat'])->name('danru-absenSholat');
    Route::post('/danru-absenSholat-store', [LeaderController::class, 'storeAbsenSholat'])->name('danru-absenSholat-store');
    Route::get('/danru-slip-gaji', [SlipGajiController::class, 'leaderIndex'])->name('danru-slip');
});


// ADIMIN
Route::middleware(['auth', 'admin', 'apdt'])->group(function () {
    Route::prefix('admin')->group(function () {
        Route::prefix('rekap')->name('admin.rekap.')->group(function () {
            Route::get('/', [AdminDashboardRekapController::class, 'index'])->name('index');
            Route::get('/overtimes/{kerjasama}', [AdminDashboardRekapController::class, 'indexOvertimes'])->name('overtimes');
            Route::get('/person-out/{kerjasama}', [AdminDashboardRekapController::class, 'indexPersonOut'])->name('person-out');
            Route::get('/person-in/{kerjasama}', [AdminDashboardRekapController::class, 'indexPersonIn'])->name('person-in');
            Route::get('/cutting/{kerjasama}', [AdminDashboardRekapController::class, 'indexCutting'])->name('cutting');
            Route::get('/finished-training/{kerjasama}', [AdminDashboardRekapController::class, 'indexFinishedTraining'])->name('finished-training');

            Route::prefix('actions')->name('actions.')->group(function () {
                Route::delete('/overtimes/{id}', [AdminRekapOvertimesController::class, 'destroyAction'])->name('overtimes.destroy');
                Route::delete('/person-out/{id}', [AdminRekapPersonOutController::class, 'destroyAction'])->name('person-out.destroy');
                Route::patch('/person-out/{id}/restore-user', [AdminRekapPersonOutController::class, 'restoreUserAction'])->name('person-out.restore-user');
                Route::delete('/person-in/{id}', [AdminRekapPersonInController::class, 'destroyAction'])->name('person-in.destroy');
                Route::delete('/cutting/{id}', [AdminRekapCuttingController::class, 'destroyAction'])->name('cutting.destroy');
                Route::delete('/finished-training/{id}', [AdminRekapFinishedTrainingController::class, 'destroyAction'])->name('finished-training.destroy');
            });

            Route::get('/settings', [RekapSettingsController::class, 'index'])->name('settings');
            Route::post('/settings', [RekapSettingsController::class, 'update'])->name('settings.update');
            Route::delete('/settings', [RekapSettingsController::class, 'reset'])->name('settings.reset');
        });

        Route::prefix('api/v1/rekap')->name('admin.rekap.api.')->group(function () {
            Route::get('/overtimes/{kerjasama}', [AdminRekapOvertimesController::class, 'index'])->name('overtimes');
            Route::delete('/overtimes/{id}', [AdminRekapOvertimesController::class, 'destroy'])->name('overtimes.destroy');
            Route::get('/person-out/{kerjasama}', [AdminRekapPersonOutController::class, 'index'])->name('person-out');
            Route::delete('/person-out/{id}', [AdminRekapPersonOutController::class, 'destroy'])->name('person-out.destroy');
            Route::patch('/person-out/{id}/restore-user', [AdminRekapPersonOutController::class, 'restoreUser'])->name('person-out.restore-user');
            Route::get('/person-in/{kerjasama}', [AdminRekapPersonInController::class, 'index'])->name('person-in');
            Route::delete('/person-in/{id}', [AdminRekapPersonInController::class, 'destroy'])->name('person-in.destroy');
            Route::get('/cutting/{kerjasama}', [AdminRekapCuttingController::class, 'index'])->name('cutting');
            Route::delete('/cutting/{id}', [AdminRekapCuttingController::class, 'destroy'])->name('cutting.destroy');
            Route::get('/finished-training/{kerjasama}', [AdminRekapFinishedTrainingController::class, 'index'])->name('finished-training');
            Route::delete('/finished-training/{id}', [AdminRekapFinishedTrainingController::class, 'destroy'])->name('finished-training.destroy');
        });

        Route::resource('qrcode', QrCodeController::class);
        Route::post('qrcode/export', [QrCodeController::class, 'exportPDF'])->name('qrcode.export');

        Route::prefix('data-absen')->group(function () {
            Route::get('/', [AdminController::class, 'absen'])->name('admin.absen');
            Route::get('/users/search', [AdminController::class, 'searchAbsenUsers'])->name('admin.absen.users.search');
        });

        Route::get('/attendance/update', [HandlingAttendanceToExcelPage::class, 'index'])->name('admin.attendance.report');
        Route::post('/attendance/update', [HandlingAttendanceToExcelPage::class, 'update'])->name('admin.attendance.update');
        Route::post('/attendance/fetch', [HandlingAttendanceToExcelPage::class, 'fetch'])->name('admin.attendance.fetch');
        Route::get('/attendance/to-pdf', [HandlingAttendanceToExcelPage::class, 'reportToPDF'])->name('admin.attendance.reportToPDF');
    });

    Route::get('/report/sholat/by-admin', [ReportSholatController::class, 'index'])->name('admin.report-sholat.index');
    Route::get('/report/sholat/download-as-admin', [ReportSholatController::class, 'download'])->name('admin.report-sholat.download');
    Route::get('/admin/report-sholat/{id}/detail', [ReportSholatController::class, 'detail'])->name('admin.report-sholat.detail');
    Route::get('/admin/export', [AdminController::class, 'export'])->name('admin.export');
    Route::get('/admin/exportV2', [AdminController::class, 'exportWith'])->name('admin.exportV2');
    Route::get('/admin/export-izin', [AdminController::class, 'exp'])->name('admin.export-izin');
    Route::resource('/admin', AdminController::class);
    Route::resource('/client/data-client', ClientController::class)->names('admin.client');
    Route::resource('/users', UserController::class)->names('admin.user');
    Route::resource('/kerjasama', KerjasamaController::class)->names('admin.kerjasama');
    Route::resource('/divisi', DivisiController::class)->names('admin.divisi');
    Route::resource('/perlengkapan', PerlengkapanController::class)->names('admin.perlengkapan');

    Route::get('/divisi/{divisiId}/add-equipment', [DivisiController::class, 'editEquipment'])->name('editRquipment');
    Route::post('/divisi/{divisiId}/add-equipment', [DivisiController::class, 'addEquipment'])->name('addEquipment');


    Route::get('/area/{areaId}/add-subarea', [SubareaController::class, 'editSubarea'])->name('edit.subarea');
    Route::post('/area/{areaId}/add-subarea', [SubareaController::class, 'addSub'])->name('add.subarea');

    Route::resource('/data-lembur', LemburController::class);
    Route::get('/data-lembur-saat-ini', [LemburController::class, 'lemburIndexAdmin'])->name('lemburList');
    Route::resource('/shift', ShiftController::class)->names('admin.shift');
    Route::patch('/shift/{id}/toggle-active', [ShiftController::class, 'toggleActive'])->name('admin.shift.toggle-active');
    Route::resource('/jabatan', JabatanController::class)->names('admin.jabatan');

    Route::delete('/laporans/{id}', [LaporanController::class, 'destroy']);
    Route::get('/export/laporans', [LaporanController::class, 'exportWith'])->name('export.laporans');
    Route::post('/admin-laporan-hapus-foto', [LaporanController::class, 'hapusFotoLaporan'])->name('laporan.hapusFotoLaporan');

    Route::resource('/ruangan', RuanganController::class)->names('admin.ruangan');
    Route::resource('/point', PointController::class)->names('admin.point');
    Route::patch('/claim-point/{id}', [AbsensiController::class, 'claimPoint'])->name('claim.point');
    Route::resource('holiday', HolidayController::class)->names('admin.holiday');
    Route::resource('/lokasi', LokasiController::class)->names('admin.lokasi');
    Route::resource('/area', AreaController::class);

    Route::get('/admin-checkpoint-koordinat/{id}', [CheckPointController::class, "show"])->name('admin.cp.map');

    Route::get('/admin-checkpoint', [AdminController::class, 'checkPoint'])->name('admin.cp.index');
    Route::patch('/admin-check-approve/{id}', [AdminController::class, 'approveCheck'])->name('admin.cp.approve');
    Route::patch('/admin-check-denied/{id}', [AdminController::class, 'deniedCheck'])->name('admin.cp.denied');
    Route::get('/admin-lihat-check/{id}', [AdminController::class, 'lihatCheck'])->name('admin.cp.show');
    Route::delete('/admin-checkpoint-delete/{id}', [AdminController::class, 'destroyCheck'])->name('admin.cp.delete');

    Route::resource('admin-jadwal', JadwalUserController::class)->names('admin.jadwal');
    Route::post('storeJadwalAdmin', [JadwalUserController::class, 'storeJadwal'])->name('admin.jadwal.store-bulk');
    Route::post('jadwal-import', [JadwalUserController::class, 'import'])->name('admin.jadwal.import');
    Route::get('admin-jadwal-new', [JadwalUserController::class, 'processDate'])->name('admin.jadwal.process-date');
    Route::get('admin-jadwal-export', [JadwalUserController::class, 'exportJadwal'])->name('admin.jadwal.export');
    Route::get('admin-cp-export', [CheckPointController::class, 'exportWith'])->name('admin.cp.export');

    Route::post('admin-ruangan-import', [RuanganController::class, 'import'])->name('admin.ruangan.import');

    Route::get('/data-izin', [IzinController::class, 'indexAdmin'])->name('admin.izin.index');
    Route::patch('/absensi-izin/admin-accept/{id}', [IzinController::class, 'updateSuccess'])->name('admin.izin.approve');
    Route::patch('/absensi-izin/admin-denied/{id}', [IzinController::class, 'updateDenied'])->name('admin.izin.deny');
    Route::delete('/absensi-izin/{id}/deleted', [IzinController::class, 'deleteAdmin'])->name('admin.izin.destroy');

    Route::post('/pekerjaanCp-import', [PekerjaanCpController::class, 'import'])->name('admin.pekerjaan-cp.import');
    Route::get('/pekerjaan/template', [PekerjaanCpController::class, 'exportTemplate'])->name('admin.pekerjaan.template');
    Route::resource('/pekerjaanCp', PekerjaanCpController::class)->names('admin.pekerjaan-cp');
    Route::resource('/admin-rating', RatingController::class)->names('admin.rating');
    // Tanpa `show`: controller tidak punya method show dan tidak ada halamannya,
    // jadi route GET /news/{id} hanya menghasilkan error 500.
    Route::resource('/news', NewsController::class)->names('admin.news')->except(['show']);
    Route::resource('/subarea', SubareaController::class)->names('admin.subarea');
    Route::resource('/admin-checklist', ChecklistController::class)->names('admin.checklist');
    Route::post('/admin-checklist-ajx', [ChecklistController::class, 'signatureChecklistAJX'])->name('admin.checklist.ajx');
    Route::get('/admin-finalisasi/{start_date}/{end_date}', [FinalisasiController::class, 'exportPDF'])->name('admin.finalisasi.export');

    Route::resource('/listPekerjaan', ListPekerjaanController::class)->names('admin.list-pekerjaan');
    Route::post('/listPekerjaan-import', [ListPekerjaanController::class, 'importExcel'])->name('admin.list-pekerjaan.import');

    Route::post('/admin-user-massUpdate', [UserController::class, 'massUpdate'])->name('admin.user.mass-update');
    Route::get('/admin-user-check-relations/{id}', [AdminController::class, 'checkUserRelations'])->name('admin.user.check-relations');
    Route::delete('/admin-user-hard-delete/{id}', [AdminController::class, 'hardDeleteUser'])->name('admin.user.hard-delete');
    Route::post('/admin-users-bulk-hard-delete', [AdminController::class, 'bulkHardDeleteUsers'])->name('admin.user.bulk-hard-delete');
    Route::post('/admin-absen-hapus-foto', [AdminController::class, 'hapusFotoAbsen'])->name('absen.hapusFotoAbsen');

    Route::get('/admin-check-koordinat/{id}', [AbsensiController::class, "showLocation"])->name('admin.absen.map');

    Route::get('/admin-monev', [MonevController::class, "index"])->name('admin.monev.index');
    Route::get('/admin-monev/create', [MonevController::class, "create"])->name('admin.monev.create');

    Route::get('/admin-slip-gaji', [AdminController::class, 'indexSlip'])->name('admin.slip.index');

    Route::get('/admin-addKaryawan/index', [UserController::class, 'addKaryawanAdminIndex'])->name('addKaryawanAdminIndex');
    Route::put('/admin-addKaryawan/{id}', [UserController::class, 'addKaryawanStatus'])->name('addKaryawanStatus');
});


require __DIR__ . '/auth.php';
