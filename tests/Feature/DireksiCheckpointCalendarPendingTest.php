<?php

namespace Tests\Feature;

use App\Models\CheckPoint;
use App\Models\CheckPointItem;
use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Above the direksi calendar sits a list of the dates whose evidence has not
 * been approved yet, so a reviewer can jump straight to the days that need
 * them instead of scanning the whole month for the amber marker.
 *
 * Only days that still hold a `proccess` row belong there: a rejected row is
 * back with the employee, and a day whose rows are all accepted (or denied)
 * needs nothing.
 */
class DireksiCheckpointCalendarPendingTest extends TestCase
{
    use DatabaseTransactions;

    private const MONTH = '2026-10';

    private int $seq = 1700;

    private function divisiWithJabatan(string $code, string $name): array
    {
        $divisiId = ++$this->seq;
        $jabatanId = ++$this->seq;
        $now = now();

        DB::table('divisis')->insert([
            'id' => $divisiId,
            'name' => 'CAL ' . $divisiId,
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

        return $this->makeUser($jabatan, $divisi, 'Cal Direksi Tester');
    }

    private function employee(string $nama): User
    {
        [$divisi, $jabatan] = $this->divisiWithJabatan('OCS', 'Cleaning Service');

        return $this->makeUser($jabatan, $divisi, $nama);
    }

    /**
     * One batch of work rows for a single day of the browsed month.
     *
     * @param  list<string>  $statuses
     */
    private function dayWithStatuses(User $employee, string $tanggal, array $statuses, string $type = 'dikerjakan'): CheckPoint
    {
        $checkpoint = CheckPoint::create([
            'user_id' => $employee->id,
            'divisi_id' => $employee->devisi_id,
            'type_check' => $type,
            'tanggal' => $tanggal,
        ]);

        foreach (array_values($statuses) as $index => $status) {
            CheckPointItem::create([
                'check_point_id' => $checkpoint->id,
                'input_manual' => 'Pekerjaan kalender ' . $index,
                'tanggal' => $tanggal,
                'approve_status' => $status,
                'urutan' => $index,
            ]);
        }

        return $checkpoint;
    }

    private function calendarAs(User $direksi, User $employee, string $month = self::MONTH)
    {
        return $this->actingAs($direksi)->get(
            '/direksi-checkpoint/' . $employee->id . '/calendar?month=' . $month
        );
    }

    public function test_only_dates_still_holding_unapproved_work_are_listed(): void
    {
        $employee = $this->employee('Cal Karyawan Satu');

        // Needs direksi: two rows still pending.
        $this->dayWithStatuses($employee, self::MONTH . '-06', ['proccess', 'proccess']);
        // Settled: accepted and rejected rows, nothing left pending.
        $this->dayWithStatuses($employee, self::MONTH . '-07', ['accept', 'accept', 'denied']);
        // Another pending day, so the list is not just "the first one".
        $this->dayWithStatuses($employee, self::MONTH . '-09', ['accept', 'proccess']);

        $this->calendarAs($this->direksi(), $employee)
            ->assertOk()
            ->assertSee('data-pending-date="2026-10-06"', false)
            ->assertSee('data-pending-date-count="2"', false)
            ->assertSee('data-pending-date="2026-10-09"', false)
            ->assertSee('data-pending-date-count="1"', false)
            ->assertDontSee('data-pending-date="2026-10-07"', false);
    }

    public function test_the_list_sits_above_the_calendar(): void
    {
        $employee = $this->employee('Cal Karyawan Dua');
        $this->dayWithStatuses($employee, self::MONTH . '-06', ['proccess']);

        // "Sen" is the calendar's weekday header, which the pending section has
        // to come before.
        $this->calendarAs($this->direksi(), $employee)
            ->assertOk()
            ->assertSeeInOrder(['data-pending-days', 'Sen'], false);
    }

    public function test_a_fully_reviewed_month_says_so_instead_of_listing_dates(): void
    {
        $employee = $this->employee('Cal Karyawan Tiga');
        $this->dayWithStatuses($employee, self::MONTH . '-06', ['accept', 'denied']);

        $this->calendarAs($this->direksi(), $employee)
            ->assertOk()
            ->assertDontSee('data-pending-date=', false)
            ->assertSee('Semua bukti pekerjaan bulan ini sudah di-approve Direksi.');
    }

    public function test_a_month_with_no_work_at_all_keeps_its_own_message(): void
    {
        $employee = $this->employee('Cal Karyawan Empat');

        $this->calendarAs($this->direksi(), $employee)
            ->assertOk()
            ->assertDontSee('data-pending-date=', false)
            ->assertSee('Belum ada bukti pekerjaan yang diisi bulan ini.');
    }

    public function test_the_list_only_covers_the_browsed_month(): void
    {
        $employee = $this->employee('Cal Karyawan Lima');
        $this->dayWithStatuses($employee, self::MONTH . '-06', ['proccess']);
        $this->dayWithStatuses($employee, '2026-09-18', ['proccess']);

        $this->calendarAs($this->direksi(), $employee)
            ->assertOk()
            ->assertSee('data-pending-date="2026-10-06"', false)
            ->assertDontSee('data-pending-date="2026-09-18"', false);

        $this->calendarAs($this->direksi(), $employee, '2026-09')
            ->assertOk()
            ->assertSee('data-pending-date="2026-09-18"', false)
            ->assertDontSee('data-pending-date="2026-10-06"', false);
    }
}
