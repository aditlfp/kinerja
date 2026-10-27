<?php

namespace Tests\Feature;

use App\Models\CheckPoint;
use App\Models\CheckPointReportSetting;
use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\User;
use App\Services\CheckPointReportWindow;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The admin-configurable weekly window for checkpoint evidence.
 *
 * Default is Saturday: Saturday itself is still open, Sunday is closed and the
 * next week reopens on Monday. Admins and DIREKSI users are exempt.
 */
class CheckPointReportWindowTest extends TestCase
{
    private Divisi $divisi;

    private Jabatan $staffJabatan;

    private User $staff;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        CheckPointReportSetting::query()->delete();

        User::withTrashed()->where('email', 'like', 'cpw-test-%')->forceDelete();
        Jabatan::query()->where('type_jabatan', 'cpw-test')->delete();
        Divisi::query()->where('name', 'like', 'cpw-test-%')->delete();

        // The legacy schema requires divisis.jabatan_id, so create the jabatan
        // first (pointing at any divisi) and then the divisi that owns it.
        $this->staffJabatan = Jabatan::create([
            'divisi_id' => Divisi::query()->value('id') ?? 1,
            'code_jabatan' => 'STAFF',
            'type_jabatan' => 'cpw-test',
            'name_jabatan' => 'cpw-test-staff',
        ]);

        $this->divisi = Divisi::create([
            'name' => 'cpw-test-divisi',
            'jabatan_id' => $this->staffJabatan->id,
        ]);
        $this->staffJabatan->update(['divisi_id' => $this->divisi->id]);

        $this->staff = $this->makeUser($this->staffJabatan, 'staff');
        $this->admin = $this->makeUser($this->staffJabatan, 'admin');
        DB::table('users')->where('id', $this->admin->id)->update(['role_id' => 2]);
        $this->admin->refresh();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        CheckPointReportSetting::query()->delete();
        CheckPoint::query()->whereIn('user_id', User::withTrashed()->where('email', 'like', 'cpw-test-%')->pluck('id'))->delete();
        User::withTrashed()->where('email', 'like', 'cpw-test-%')->forceDelete();
        Jabatan::query()->where('type_jabatan', 'cpw-test')->delete();
        Divisi::query()->where('name', 'like', 'cpw-test-%')->delete();

        parent::tearDown();
    }

    private function makeUser(Jabatan $jabatan, string $suffix, ?Divisi $divisi = null): User
    {
        return User::create([
            'kerjasama_id' => 1,
            'devisi_id' => ($divisi ?? $this->divisi)->id,
            'jabatan_id' => $jabatan->id,
            'name' => 'cpw-test-'.$suffix,
            'nama_lengkap' => 'Karyawan Uji '.$suffix,
            'image' => 'default.png',
            'email' => 'cpw-test-'.$suffix.'@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_default_window_closes_after_saturday_and_reopens_on_monday(): void
    {
        $window = app(CheckPointReportWindow::class);

        $this->assertSame(6, $window->lastDay(), 'Default batas laporan harus hari Sabtu.');

        Carbon::setTestNow(Carbon::parse('2026-10-24 10:00:00')); // Sabtu
        $this->assertFalse($window->isLocked());

        Carbon::setTestNow(Carbon::parse('2026-10-25 10:00:00')); // Minggu
        $this->assertTrue($window->isLocked());

        Carbon::setTestNow(Carbon::parse('2026-10-26 00:00:00')); // Senin
        $this->assertFalse($window->isLocked());
    }

    public function test_configured_last_day_moves_the_deadline(): void
    {
        CheckPointReportSetting::create(['last_day' => 2]); // Selasa

        $window = app(CheckPointReportWindow::class);

        Carbon::setTestNow(Carbon::parse('2026-10-20 10:00:00')); // Selasa
        $this->assertFalse($window->isLocked());

        Carbon::setTestNow(Carbon::parse('2026-10-21 00:00:00')); // Rabu
        $this->assertTrue($window->isLocked());
    }

    public function test_employee_cannot_open_create_page_after_the_deadline(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-25 10:00:00')); // Minggu

        $this->actingAs($this->staff)
            ->get(route('checkpoint-user.create'))
            ->assertRedirect(route('checkpoint-user.index'))
            ->assertSessionHas('checkpoint_locked');
    }

    public function test_employee_can_open_create_page_within_the_window(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-24 10:00:00')); // Sabtu

        $this->actingAs($this->staff)
            ->get(route('checkpoint-user.create'))
            ->assertOk();
    }

    public function test_store_is_rejected_after_the_deadline(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-25 10:00:00')); // Minggu

        $before = CheckPoint::count();

        $this->actingAs($this->staff)
            ->post(route('checkpoint-user.store'), [
                'user_id' => $this->staff->id,
                'divisi_id' => 1,
                'type_check' => 'dikerjakan',
            ])
            ->assertRedirect(route('checkpoint-user.index'))
            ->assertSessionHas('checkpoint_locked');

        $this->assertSame($before, CheckPoint::count(), 'Data checkpoint tidak boleh tersimpan saat terkunci.');
    }

    public function test_upload_bukti_is_rejected_after_the_deadline(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-25 10:00:00')); // Minggu

        $before = CheckPoint::count();

        $this->actingAs($this->staff)
            ->post(route('uploadBukti-checkpoint-user'), [
                'user_id' => $this->staff->id,
                'divisi_id' => 1,
            ])
            ->assertRedirect(route('checkpoint-user.index'))
            ->assertSessionHas('checkpoint_locked');

        $this->assertSame($before, CheckPoint::count(), 'Bukti pekerjaan tidak boleh masuk saat terkunci.');
    }

    public function test_open_calendar_keeps_the_add_link(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-24 10:00:00')); // Sabtu

        $this->actingAs($this->staff)
            ->get(route('checkpoint-user.index'))
            ->assertOk()
            ->assertSee(route('checkpoint-user.create'), false)
            ->assertDontSee('Laporan pekan ini sudah ditutup');
    }

    public function test_locked_calendar_disables_add_and_empty_days(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-25 10:00:00')); // Minggu

        $this->actingAs($this->staff)
            ->get(route('checkpoint-user.index'))
            ->assertOk()
            ->assertSee('Laporan pekan ini sudah ditutup')
            ->assertSee('Dibuka lagi', false)
            ->assertDontSee(route('checkpoint-user.create'), false);
    }

    public function test_locked_calendar_opens_days_with_evidence_read_only(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-25 10:00:00')); // Minggu

        $checkPoint = CheckPoint::create([
            'user_id' => $this->staff->id,
            'divisi_id' => $this->divisi->id,
            'type_check' => 'dikerjakan',
        ]);
        DB::table('check_points')->where('id', $checkPoint->id)->update([
            'created_at' => '2026-10-20 09:00:00',
            'updated_at' => '2026-10-20 09:00:00',
        ]);

        $this->actingAs($this->staff)
            ->get(route('checkpoint-user.index'))
            ->assertOk()
            ->assertSee(route('checkpoint-user.history.show', ['id' => $checkPoint->id]), false)
            ->assertDontSee(route('checkpoint-user.edit', [
                'checkpoint_user' => $checkPoint->id,
                'tanggal' => '2026-10-20',
            ]), false);
    }

    /**
     * A checkpoint with one evaluated row (Direksi note) and one plain row.
     *
     * @return array{0: CheckPoint, 1: \App\Models\CheckPointItem, 2: \App\Models\CheckPointItem}
     */
    private function makeCheckpointWithNotes(): array
    {
        $checkPoint = CheckPoint::create([
            'user_id' => $this->staff->id,
            'divisi_id' => $this->divisi->id,
            'type_check' => 'dikerjakan',
        ]);

        $noteItem = $checkPoint->items()->create([
            'pekerjaan_cp_id' => null,
            'input_manual' => 'Pekerjaan dengan catatan',
            'deskripsi' => 'Deskripsi lama',
            'tanggal' => '2026-10-20',
            'approve_status' => 'denied',
            'note' => '👍 Baik — perbaiki fotonya',
            'urutan' => 0,
        ]);

        $plainItem = $checkPoint->items()->create([
            'pekerjaan_cp_id' => null,
            'input_manual' => 'Pekerjaan tanpa catatan',
            'deskripsi' => 'Tidak boleh berubah',
            'tanggal' => '2026-10-20',
            'approve_status' => 'proccess',
            'note' => null,
            'urutan' => 1,
        ]);

        return [$checkPoint, $noteItem, $plainItem];
    }

    public function test_locked_week_still_opens_edit_for_rows_with_evaluation_notes(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-25 10:00:00')); // Minggu

        [$checkPoint] = $this->makeCheckpointWithNotes();

        $this->actingAs($this->staff)
            ->get(route('checkpoint-user.edit', ['checkpoint_user' => $checkPoint->id]))
            ->assertOk()
            ->assertSee('Pekan laporan sudah ditutup')
            ->assertSee('Hanya pekerjaan yang punya catatan')
            ->assertDontSee(route('checkpoint-user.create'), false);
    }

    public function test_locked_week_still_rejects_edit_without_evaluation_notes(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-25 10:00:00')); // Minggu

        $checkPoint = CheckPoint::create([
            'user_id' => $this->staff->id,
            'divisi_id' => $this->divisi->id,
            'type_check' => 'dikerjakan',
        ]);

        $this->actingAs($this->staff)
            ->get(route('checkpoint-user.edit', ['checkpoint_user' => $checkPoint->id]))
            ->assertRedirect(route('checkpoint-user.index'))
            ->assertSessionHas('checkpoint_locked');
    }

    public function test_locked_update_only_changes_rows_with_evaluation_notes(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-25 10:00:00')); // Minggu

        [$checkPoint, $noteItem, $plainItem] = $this->makeCheckpointWithNotes();

        $this->actingAs($this->staff)
            ->put(route('checkpoint-user.update', $checkPoint->id), [
                'user_id' => $this->staff->id,
                'divisi_id' => $this->divisi->id,
                'original_index' => [0 => 0, 1 => 1],
                'item_id' => [0 => $noteItem->id, 1 => $plainItem->id],
                'pekerjaan_id' => [0 => 'manual', 1 => 'manual'],
                'input_manual' => [0 => 'Pekerjaan dengan catatan', 1 => 'Pekerjaan tanpa catatan'],
                'deskripsi' => [0 => 'Deskripsi sudah diperbaiki', 1 => 'Percobaan mengubah baris terkunci'],
                'tanggal' => [0 => '2026-10-20', 1 => '2026-10-20'],
                'approve_status' => [0 => 'proccess', 1 => 'proccess'],
            ])
            ->assertRedirect(route('checkpoint-user.index'));

        $this->assertSame('Deskripsi sudah diperbaiki', $noteItem->fresh()->deskripsi);
        $this->assertSame('👍 Baik — perbaiki fotonya', $noteItem->fresh()->note, 'Catatan evaluasi tidak boleh hilang.');
        $this->assertSame('Tidak boleh berubah', $plainItem->fresh()->deskripsi, 'Baris tanpa catatan harus tetap utuh.');
        $this->assertSame(2, $checkPoint->items()->count(), 'Tidak boleh ada baris yang ditambah atau dihapus.');
    }

    public function test_locked_update_without_evaluation_notes_is_rejected(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-25 10:00:00')); // Minggu

        $checkPoint = CheckPoint::create([
            'user_id' => $this->staff->id,
            'divisi_id' => $this->divisi->id,
            'type_check' => 'dikerjakan',
        ]);

        $this->actingAs($this->staff)
            ->put(route('checkpoint-user.update', $checkPoint->id), [
                'user_id' => $this->staff->id,
                'divisi_id' => $this->divisi->id,
                'deskripsi' => [0 => 'Pekerjaan baru'],
            ])
            ->assertRedirect(route('checkpoint-user.index'))
            ->assertSessionHas('checkpoint_locked');

        $this->assertSame(0, $checkPoint->items()->count());
    }

    public function test_locked_calendar_links_days_with_evaluation_notes_to_edit(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-25 10:00:00')); // Minggu

        [$checkPoint] = $this->makeCheckpointWithNotes();

        $this->actingAs($this->staff)
            ->get(route('checkpoint-user.index'))
            ->assertOk()
            ->assertSee(route('checkpoint-user.edit', [
                'checkpoint_user' => $checkPoint->id,
                'tanggal' => '2026-10-20',
            ]), false)
            ->assertSee('Ada catatan evaluasi');
    }

    public function test_admin_calendar_stays_interactive_when_locked(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-25 10:00:00')); // Minggu

        $this->actingAs($this->admin)
            ->get(route('checkpoint-user.index'))
            ->assertOk()
            ->assertSee(route('checkpoint-user.create'), false)
            ->assertDontSee('Laporan pekan ini sudah ditutup');
    }

    public function test_admin_is_exempt_from_the_window(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-25 10:00:00')); // Minggu

        $this->actingAs($this->admin)
            ->get(route('checkpoint-user.create'))
            ->assertOk();
    }

    public function test_direksi_user_is_exempt_from_the_window(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-25 10:00:00')); // Minggu

        $direksiJabatan = Jabatan::create([
            'divisi_id' => $this->divisi->id,
            'code_jabatan' => 'DIREKSI',
            'type_jabatan' => 'cpw-test',
            'name_jabatan' => 'cpw-test-direksi',
        ]);
        $direksi = $this->makeUser($direksiJabatan, 'direksi');

        $this->actingAs($direksi)
            ->get(route('checkpoint-user.create'))
            ->assertOk();
    }

    public function test_admin_can_open_and_update_the_settings_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.cp.settings'))
            ->assertOk()
            ->assertSee('Pengaturan Bukti Pekerjaan');

        $this->actingAs($this->admin)
            ->post(route('admin.cp.settings.update'), ['last_day' => 7])
            ->assertRedirect();

        $this->assertSame(7, CheckPointReportSetting::query()->latest()->first()?->lastDay());
    }

    public function test_admin_can_reset_the_setting_to_the_default(): void
    {
        CheckPointReportSetting::create(['last_day' => 3]);

        $this->actingAs($this->admin)
            ->delete(route('admin.cp.settings.reset'))
            ->assertRedirect();

        $this->assertSame(0, CheckPointReportSetting::query()->count());
        $this->assertSame(6, app(CheckPointReportWindow::class)->lastDay());
    }
}
