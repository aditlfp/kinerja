<?php

namespace Tests\Feature;

use App\Models\CheckPoint;
use App\Models\CheckPointItem;
use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\User;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The Direksi employee calendar carries a monthly recap of attendance,
 * checkpoint evidence and work orders. It is only readable at the end of a
 * week or at the end of a month, so an employee cannot be graded on a period
 * that is still running.
 */
class DireksiCheckpointPerformanceRecapTest extends TestCase
{
    use DatabaseTransactions;

    private const MONTH = '2026-10';

    private int $seq = 2400;

    private ?int $shiftId = null;

    private ?int $kerjasamaId = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Keep the holiday feed out of the tests; each one decides its own
        // holidays through the cache or the local table.
        Cache::put('holidays.national.names.2026', []);
        DB::table('holidays')->where('tanggal_libur', 'like', '2026-%')->delete();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function divisiWithJabatan(string $code, string $name): array
    {
        $divisiId = ++$this->seq;
        $jabatanId = ++$this->seq;
        $now = now();

        DB::table('divisis')->insert([
            'id' => $divisiId,
            'name' => 'RECAP ' . $divisiId,
            'jabatan_id' => $jabatanId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('jabatans')->insert([
            'id' => $jabatanId,
            'divisi_id' => $divisiId,
            'code_jabatan' => $code,
            'type_jabatan' => 'staff',
            'name_jabatan' => $name,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [Divisi::find($divisiId), Jabatan::find($jabatanId)];
    }

    private function makeUser(Jabatan $jabatan, Divisi $divisi, string $nama): User
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]/i', '', $nama)) . $this->seq . rand(100, 999);

        return User::create([
            'name' => $slug,
            'nama_lengkap' => $nama,
            'email' => $slug . '@test.local',
            'password' => bcrypt('secret'),
            'image' => 'no-image.jpg',
            'jabatan_id' => $jabatan->id,
            'devisi_id' => $divisi->id,
            'kerjasama_id' => 1,
        ]);
    }

    private function direksi(): User
    {
        [$divisi, $jabatan] = $this->divisiWithJabatan('DIREKSI', 'Direktur');

        return $this->makeUser($jabatan, $divisi, 'Recap Direksi Tester');
    }

    private function employee(string $nama): User
    {
        [$divisi, $jabatan] = $this->divisiWithJabatan('OCS', 'Cleaning Service');

        return $this->makeUser($jabatan, $divisi, $nama);
    }

    /**
     * Attendance rows require a client and a shift; the test database has no
     * seed rows to borrow when it is migrated fresh.
     */
    private function workspace(Jabatan $jabatan): array
    {
        if ($this->shiftId !== null) {
            return [$this->kerjasamaId, $this->shiftId];
        }

        $now = now();

        $clientId = DB::table('clients')->insertGetId([
            'name' => 'Recap Client',
            'address' => '-',
            'province' => '-',
            'kabupaten' => '-',
            'zipcode' => '-',
            'email' => 'recap-client@test.local',
            'phone' => '-',
            'fax' => '-',
            'logo' => '-',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->kerjasamaId = DB::table('kerjasamas')->insertGetId([
            'client_id' => $clientId,
            'value' => '0',
            'experied' => '-',
            'approve1' => '-',
            'approve2' => '-',
            'approve3' => '-',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->shiftId = DB::table('shifts')->insertGetId([
            'jabatan_id' => $jabatan->id,
            'client_id' => $clientId,
            'shift_name' => 'Pagi',
            'jam_start' => '08:00',
            'jam_end' => '17:00',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [$this->kerjasamaId, $this->shiftId];
    }

    private function absensi(User $employee, string $tanggal): void
    {
        $now = now();
        [$kerjasamaId, $shiftId] = $this->workspace($employee->jabatan);

        DB::table('absensis')->insert([
            'user_id' => $employee->id,
            'kerjasama_id' => $kerjasamaId,
            'shift_id' => $shiftId,
            'keterangan' => 'masuk',
            'deskripsi' => '-',
            'absensi_type_masuk' => '08:00',
            'tanggal_absen' => $tanggal,
            'absensi_type_pulang' => '17:00',
            'image' => 'no-image.jpg',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function evidence(User $employee, string $tanggal): void
    {
        $checkpoint = CheckPoint::create([
            'user_id' => $employee->id,
            'divisi_id' => $employee->devisi_id,
            'type_check' => 'dikerjakan',
        ]);

        CheckPointItem::create([
            'check_point_id' => $checkpoint->id,
            'input_manual' => 'Pekerjaan recap',
            'tanggal' => $tanggal,
            'approve_status' => 'accept',
            'urutan' => 0,
        ]);
    }

    private function workOrder(User $employee, string $tanggal, bool $done): void
    {
        WorkOrder::create([
            'user_id' => $employee->id,
            'tanggal' => $tanggal,
            'deskripsi' => 'Perintah kerja recap',
            'has_complete' => $done,
        ]);
    }

    private function calendarAs(User $direksi, User $employee, string $month = self::MONTH)
    {
        return $this->actingAs($direksi)->get(
            '/direksi-checkpoint/' . $employee->id . '/calendar?month=' . $month
        );
    }

    public function test_the_recap_sits_below_the_employee_card_and_above_the_pending_dates(): void
    {
        Carbon::setTestNow('2026-10-24 09:00:00');
        $employee = $this->employee('Recap Karyawan Satu');

        $this->calendarAs($this->direksi(), $employee)
            ->assertOk()
            ->assertSeeInOrder(['Kembali', 'data-performance-recap', 'data-pending-days'], false);
    }

    public function test_it_shows_the_three_monthly_percentages_on_a_saturday(): void
    {
        Carbon::setTestNow('2026-10-24 09:00:00');
        $employee = $this->employee('Recap Karyawan Dua');

        // 17 working days have already happened (Mon 5 .. Fri 23).
        $this->absensi($employee, '2026-10-01');
        $this->absensi($employee, '2026-10-02');
        $this->absensi($employee, '2026-10-05');

        $this->evidence($employee, '2026-10-06');
        $this->evidence($employee, '2026-10-07');

        $this->workOrder($employee, '2026-10-08', true);
        $this->workOrder($employee, '2026-10-09', true);
        $this->workOrder($employee, '2026-10-12', true);
        $this->workOrder($employee, '2026-10-13', false);

        $this->calendarAs($this->direksi(), $employee)
            ->assertOk()
            ->assertDontSee('data-recap-locked', false)
            ->assertSee('data-recap-metric="attendance"', false)
            ->assertSee('data-recap-percent="18"', false)
            ->assertSee('data-recap-metric="checkpoint"', false)
            ->assertSee('data-recap-percent="12"', false)
            ->assertSee('data-recap-metric="work_order"', false)
            ->assertSee('data-recap-percent="75"', false)
            ->assertSee('3 dari 17 hari kerja')
            ->assertSee('3 dari 4 perintah kerja')
            ->assertSee('Tidak ada hari libur nasional pada periode ini.');
    }

    public function test_it_is_locked_on_an_ordinary_weekday(): void
    {
        Carbon::setTestNow('2026-10-21 09:00:00');
        $employee = $this->employee('Recap Karyawan Tiga');

        $this->absensi($employee, '2026-10-01');
        $this->evidence($employee, '2026-10-06');

        $this->calendarAs($this->direksi(), $employee)
            ->assertOk()
            ->assertSee('data-recap-locked', false)
            ->assertDontSee('data-recap-percent=', false)
            ->assertSee('Rekap hanya bisa dilihat pada hari Sabtu &amp; Minggu dan 3 hari terakhir bulan.', false)
            ->assertSee('Dibuka lagi');
    }

    public function test_a_finished_month_stays_readable_on_an_ordinary_weekday(): void
    {
        Carbon::setTestNow('2026-10-21 09:00:00');
        $employee = $this->employee('Recap Karyawan Tujuh');

        // September 2026 has 22 working days; one day of evidence is 5%.
        $this->evidence($employee, '2026-09-18');

        $this->calendarAs($this->direksi(), $employee, '2026-09')
            ->assertOk()
            ->assertDontSee('data-recap-locked', false)
            ->assertSee('data-recap-metric="checkpoint"', false)
            ->assertSee('data-recap-percent="5"', false);
    }

    public function test_it_opens_again_in_the_last_three_days_of_the_month(): void
    {
        Carbon::setTestNow('2026-10-29 09:00:00');
        $employee = $this->employee('Recap Karyawan Empat');

        $this->absensi($employee, '2026-10-01');

        $this->calendarAs($this->direksi(), $employee)
            ->assertOk()
            ->assertDontSee('data-recap-locked', false)
            ->assertSee('data-recap-percent=', false);
    }

    public function test_national_holidays_are_marked_in_the_calendar_grid(): void
    {
        Carbon::setTestNow('2026-10-24 09:00:00');
        Cache::put('holidays.national.names.2026', [
            '2026-10-05' => 'Libur Uji Satu',
            '2026-10-06' => 'Libur Uji Dua',
        ]);
        $employee = $this->employee('Recap Karyawan Sembilan');

        // One holiday is empty, the other carries evidence, so the marker must
        // show on both kinds of cell.
        $this->evidence($employee, '2026-10-06');

        $emptyTitle = Carbon::parse('2026-10-05')->translatedFormat('l, d F Y') . ' — Libur Uji Satu';
        $busyTitle = Carbon::parse('2026-10-06')->translatedFormat('l, d F Y') . ' — Libur Uji Dua';

        $this->calendarAs($this->direksi(), $employee)
            ->assertOk()
            ->assertSee('data-calendar-holiday="2026-10-05"', false)
            ->assertSee('data-calendar-holiday="2026-10-06"', false)
            ->assertDontSee('data-calendar-holiday="2026-10-07"', false)
            ->assertSee('Libur nasional')
            ->assertSee($emptyTitle)
            ->assertSee($busyTitle)
            ->assertSee('Libur nasional: Libur Uji Satu');
    }

    public function test_national_holidays_are_excluded_from_working_days(): void
    {
        Carbon::setTestNow('2026-10-24 09:00:00');
        $employee = $this->employee('Recap Karyawan Delapan');

        // One holiday from the feed, one added by an admin: 17 weekdays minus
        // these two leaves 15 working days.
        Cache::put('holidays.national.names.2026', ['2026-10-05' => 'Libur Uji Satu']);
        DB::table('holidays')->insert([
            'name' => 'Libur uji recap',
            'tanggal_libur' => '2026-10-06',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->absensi($employee, '2026-10-01');

        $excluded = 'Hari libur nasional yang dikecualikan: '
            . Carbon::parse('2026-10-05')->translatedFormat('d M Y') . ' (Libur Uji Satu), '
            . Carbon::parse('2026-10-06')->translatedFormat('d M Y') . ' (Libur uji recap)';

        $this->calendarAs($this->direksi(), $employee)
            ->assertOk()
            ->assertSee('1 dari 15 hari kerja')
            ->assertSee('data-recap-percent="7"', false)
            ->assertSee('Hari libur nasional dan akhir pekan tidak dihitung sebagai hari kerja.')
            ->assertSee($excluded);
    }

    public function test_a_work_order_free_month_is_not_scored_as_zero_percent(): void
    {
        Carbon::setTestNow('2026-10-24 09:00:00');
        $employee = $this->employee('Recap Karyawan Lima');

        $this->calendarAs($this->direksi(), $employee)
            ->assertOk()
            ->assertSee('data-recap-metric="work_order"', false)
            ->assertSee('Belum ada perintah kerja')
            ->assertSee('0 dari 17 hari kerja');
    }

    public function test_only_the_browsed_month_counts(): void
    {
        Carbon::setTestNow('2026-10-24 09:00:00');
        $employee = $this->employee('Recap Karyawan Enam');

        $this->workOrder($employee, '2026-09-30', true);
        $this->workOrder($employee, '2026-10-08', false);

        $this->calendarAs($this->direksi(), $employee)
            ->assertOk()
            ->assertSee('0 dari 1 perintah kerja')
            ->assertSee('data-recap-percent="0"', false);
    }
}
