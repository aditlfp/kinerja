<?php

namespace Tests\Feature;

use App\Models\CheckPoint;
use App\Models\CheckPointItem;
use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The employee calendar carries the same "Tanggal Belum Di Approve" list the
 * direksi calendar has, so a worker sees at a glance which days are still
 * waiting for verification — and can open that day's work in one click.
 *
 * Only days that still hold a `proccess` row belong there: a day whose rows are
 * all accepted (or denied) needs nothing from anyone, and a rejected row is
 * already back with the employee.
 */
class CheckPointIndexPendingTest extends TestCase
{
    use DatabaseTransactions;

    private const MONTH = '2026-10';

    private int $seq = 1900;

    protected function setUp(): void
    {
        parent::setUp();

        // The calendar caches its per-user date maps for 30 seconds, so a stale
        // entry could hide a day this test just seeded.
        Cache::flush();
    }

    private function divisiWithJabatan(string $code, string $name): array
    {
        $divisiId = ++$this->seq;
        $jabatanId = ++$this->seq;
        $now = now();

        DB::table('divisis')->insert([
            'id' => $divisiId,
            'name' => 'IDX ' . $divisiId,
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

    private function employee(string $nama): User
    {
        [$divisi, $jabatan] = $this->divisiWithJabatan('OCS', 'Cleaning Service');

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

    /**
     * One batch of work rows for a single day of the browsed month.
     *
     * @param  list<string>  $statuses
     */
    private function dayWithStatuses(User $employee, string $tanggal, array $statuses): CheckPoint
    {
        $checkpoint = CheckPoint::create([
            'user_id' => $employee->id,
            'divisi_id' => $employee->devisi_id,
            'type_check' => 'dikerjakan',
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

    private function indexAs(User $employee, string $month = self::MONTH)
    {
        return $this->actingAs($employee)->get(
            route('checkpoint-user.index', ['month' => $month])
        );
    }

    /** The href a pending chip for the given date points at, if the chip exists. */
    private function chipHref(string $html, string $date): ?string
    {
        $pattern = '/<a\s+href="([^"]+)"\s+data-pending-date="' . preg_quote($date, '/') . '"/';

        return preg_match($pattern, $html, $matches) === 1 ? $matches[1] : null;
    }

    public function test_only_dates_still_holding_unapproved_work_are_listed(): void
    {
        $employee = $this->employee('Index Karyawan Satu');

        // Needs direksi: two rows still pending.
        $this->dayWithStatuses($employee, self::MONTH . '-06', ['proccess', 'proccess']);
        // Settled: accepted and rejected rows, nothing left pending.
        $this->dayWithStatuses($employee, self::MONTH . '-07', ['accept', 'accept', 'denied']);
        // Another pending day, so the list is not just "the first one".
        $this->dayWithStatuses($employee, self::MONTH . '-09', ['accept', 'proccess']);

        $this->indexAs($employee)
            ->assertOk()
            ->assertSee('data-pending-days', false)
            ->assertSee('data-pending-date="2026-10-06"', false)
            ->assertSee('data-pending-date-count="2"', false)
            ->assertSee('data-pending-date="2026-10-09"', false)
            ->assertSee('data-pending-date-count="1"', false)
            ->assertDontSee('data-pending-date="2026-10-07"', false);
    }

    public function test_each_listed_date_opens_that_days_work(): void
    {
        $employee = $this->employee('Index Karyawan Dua');
        $checkpoint = $this->dayWithStatuses($employee, self::MONTH . '-06', ['proccess']);

        $response = $this->indexAs($employee)->assertOk();

        $this->assertSame(
            route('checkpoint-user.edit', [
                'checkpoint_user' => $checkpoint->id,
                'tanggal' => '2026-10-06',
            ]),
            $this->chipHref($response->getContent(), '2026-10-06'),
        );
    }

    public function test_the_list_sits_above_the_calendar(): void
    {
        $employee = $this->employee('Index Karyawan Tiga');
        $this->dayWithStatuses($employee, self::MONTH . '-06', ['proccess']);

        // "Sen" is the calendar's weekday header, which the pending section has
        // to come before.
        $this->indexAs($employee)
            ->assertOk()
            ->assertSeeInOrder(['data-pending-days', 'Sen'], false);
    }

    public function test_a_fully_reviewed_month_says_so_instead_of_listing_dates(): void
    {
        $employee = $this->employee('Index Karyawan Empat');
        $this->dayWithStatuses($employee, self::MONTH . '-06', ['accept', 'denied']);

        $this->indexAs($employee)
            ->assertOk()
            ->assertDontSee('data-pending-date=', false)
            ->assertSee('Semua bukti pekerjaan bulan ini sudah di-approve Direksi.');
    }

    public function test_a_month_with_no_work_at_all_keeps_its_own_message(): void
    {
        $employee = $this->employee('Index Karyawan Lima');

        $this->indexAs($employee)
            ->assertOk()
            ->assertDontSee('data-pending-date=', false)
            ->assertSee('Belum ada bukti pekerjaan yang diisi bulan ini.');
    }

    public function test_the_list_only_covers_the_browsed_month(): void
    {
        $employee = $this->employee('Index Karyawan Enam');
        $this->dayWithStatuses($employee, self::MONTH . '-06', ['proccess']);
        $this->dayWithStatuses($employee, '2026-09-18', ['proccess']);

        $this->indexAs($employee)
            ->assertOk()
            ->assertSee('data-pending-date="2026-10-06"', false)
            ->assertDontSee('data-pending-date="2026-09-18"', false);

        $this->indexAs($employee, '2026-09')
            ->assertOk()
            ->assertSee('data-pending-date="2026-09-18"', false)
            ->assertDontSee('data-pending-date="2026-10-06"', false);
    }
}
