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
 * The direksi checkpoint list doubles as a to-do list: an employee whose
 * evidence is still waiting for review carries a count bubble next to their
 * name, and an employee with nothing waiting carries none.
 *
 * "Waiting for review" is `approve_status = 'proccess'` (the stored default,
 * legacy misspelling included) on a `dikerjakan` checkpoint — a rejected row
 * goes back to `proccess` once the employee answers it, so it belongs to the
 * employee, not to direksi, while it is still `denied`.
 */
class DireksiCheckpointPendingBadgeTest extends TestCase
{
    use DatabaseTransactions;

    private int $seq = 1500;

    /**
     * Pin divisi/jabatan ids so the fixtures never collide with rows the rest
     * of the suite leaves in the test database.
     */
    private function divisiWithJabatan(string $code, string $name): array
    {
        $divisiId = ++$this->seq;
        $jabatanId = ++$this->seq;
        $now = now();

        DB::table('divisis')->insert([
            'id' => $divisiId,
            'name' => 'BADGE ' . $divisiId,
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

        return $this->makeUser($jabatan, $divisi, 'Badge Direksi Tester');
    }

    private function employee(string $nama): User
    {
        [$divisi, $jabatan] = $this->divisiWithJabatan('OCS', 'Cleaning Service');

        return $this->makeUser($jabatan, $divisi, $nama);
    }

    /**
     * One checkpoint batch holding a job row per given approve_status, dated
     * `$tanggal` (today by default).
     */
    private function checkpointWithStatuses(User $employee, string $type, array $statuses, ?string $tanggal = null): CheckPoint
    {
        $tanggal ??= now()->toDateString();

        $checkpoint = CheckPoint::create([
            'user_id' => $employee->id,
            'divisi_id' => $employee->devisi_id,
            'type_check' => $type,
            'tanggal' => $tanggal,
        ]);

        foreach (array_values($statuses) as $index => $status) {
            CheckPointItem::create([
                'check_point_id' => $checkpoint->id,
                'input_manual' => 'Pekerjaan badge ' . $index,
                'tanggal' => $tanggal,
                'approve_status' => $status,
                'urutan' => $index,
            ]);
        }

        return $checkpoint;
    }

    /**
     * The list is paginated, so every assertion narrows it with `search`; the
     * count follows the month the page is showing.
     */
    private function listAs(User $direksi, string $search, ?string $month = null)
    {
        $url = '/direksi-checkpoint?search=' . urlencode($search);

        if ($month) {
            $url .= '&month=' . $month;
        }

        return $this->actingAs($direksi)->get($url);
    }

    public function test_evidence_waiting_for_review_gets_a_bubble_next_to_the_name(): void
    {
        $employee = $this->employee('Badge Karyawan Satu');
        $this->checkpointWithStatuses($employee, 'dikerjakan', ['proccess', 'proccess', 'accept', 'denied']);

        $this->listAs($this->direksi(), 'Badge Karyawan Satu')
            ->assertOk()
            ->assertSeeInOrder(['badge karyawan satu', 'data-pending-count="2"'], false);
    }

    public function test_nothing_waiting_means_no_bubble(): void
    {
        $employee = $this->employee('Badge Karyawan Dua');
        $this->checkpointWithStatuses($employee, 'dikerjakan', ['accept', 'denied']);

        $this->listAs($this->direksi(), 'Badge Karyawan Dua')
            ->assertOk()
            ->assertSee('badge karyawan dua')
            ->assertDontSee('data-pending-count');
    }

    public function test_plan_rows_and_rejected_rows_are_left_out_of_the_count(): void
    {
        $employee = $this->employee('Badge Karyawan Tiga');
        // `denied` is back with the employee, and a plan row is not the
        // reviewer's queue, so neither belongs in the bubble.
        $this->checkpointWithStatuses($employee, 'dikerjakan', ['denied']);
        $this->checkpointWithStatuses($employee, 'rencana', ['proccess']);

        $this->listAs($this->direksi(), 'Badge Karyawan Tiga')
            ->assertOk()
            ->assertSee('badge karyawan tiga')
            ->assertDontSee('data-pending-count');
    }

    public function test_each_employee_gets_their_own_number(): void
    {
        $direksi = $this->direksi();

        $twoPending = $this->employee('Badge Karyawan Empat A');
        $this->checkpointWithStatuses($twoPending, 'dikerjakan', ['proccess', 'proccess', 'accept']);

        $onePending = $this->employee('Badge Karyawan Empat B');
        $this->checkpointWithStatuses($onePending, 'dikerjakan', ['proccess']);

        $response = $this->listAs($direksi, 'Badge Karyawan Empat')
            ->assertOk();

        // Two rows, two different counts — the map is keyed per employee, not
        // summed across the page.
        $response->assertSee('data-pending-count="2"', false);
        $response->assertSee('data-pending-count="1"', false);
    }

    public function test_a_pending_row_without_an_approved_twin_still_counts(): void
    {
        $employee = $this->employee('Badge Karyawan Lima');
        $this->checkpointWithStatuses($employee, 'dikerjakan', ['proccess']);

        $this->listAs($this->direksi(), 'Badge Karyawan Lima')
            ->assertOk()
            ->assertSee('data-pending-count="1"', false);
    }

    public function test_the_count_only_covers_the_month_being_browsed(): void
    {
        $direksi = $this->direksi();
        $employee = $this->employee('Badge Karyawan Enam');

        // startOfMonth() first: subMonth() on the 31st would overflow into the
        // following month.
        $lastMonth = now()->startOfMonth()->subMonth();

        $this->checkpointWithStatuses($employee, 'dikerjakan', ['proccess', 'proccess'], $lastMonth->toDateString());
        $this->checkpointWithStatuses($employee, 'dikerjakan', ['proccess'], now()->toDateString());

        $this->listAs($direksi, 'Badge Karyawan Enam')
            ->assertOk()
            ->assertSee('data-pending-count="1"', false);

        $this->listAs($direksi, 'Badge Karyawan Enam', $lastMonth->format('Y-m'))
            ->assertOk()
            ->assertSee('data-pending-count="2"', false);
    }

    public function test_an_undated_row_is_counted_by_its_batch_date(): void
    {
        $employee = $this->employee('Badge Karyawan Tujuh');

        // Legacy rows can sit on a batch with no `tanggal` of their own; they
        // belong to the month the batch was created in.
        $checkpoint = CheckPoint::create([
            'user_id' => $employee->id,
            'divisi_id' => $employee->devisi_id,
            'type_check' => 'dikerjakan',
            'tanggal' => now()->toDateString(),
        ]);

        CheckPointItem::create([
            'check_point_id' => $checkpoint->id,
            'input_manual' => 'Baris tanpa tanggal',
            'tanggal' => null,
            'approve_status' => 'proccess',
            'urutan' => 0,
        ]);

        $this->listAs($this->direksi(), 'Badge Karyawan Tujuh')
            ->assertOk()
            ->assertSee('data-pending-count="1"', false);
    }
}
