<?php

namespace Tests\Feature;

use App\Models\CheckPoint;
use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * A work order records who issued it. The column is nullable because legacy
 * rows predate it, and those are attributed to "Direksi".
 *
 * The issuer name surfaces in two places:
 *   - the work-order create page, as "Dibuat {jam} WIB - Dibuat oleh {nama}"
 *   - the checkpoint-user create form, as "Diperintah oleh {nama}"
 */
class WorkOrderCreatedByTest extends TestCase
{
    use DatabaseTransactions;

    private int $seq = 700;

    private function divisiWithJabatan(string $code, string $name, ?int $jabatanId = null): array
    {
        $divisiId = ++$this->seq;
        $jabatanId ??= ++$this->seq;
        $now = now();

        DB::table('divisis')->insert([
            'id' => $divisiId,
            'name' => 'DIVISI ' . $divisiId,
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

    private function mcs(): User
    {
        [$divisi, $jabatan] = $this->divisiWithJabatan('MCS', 'Manager Cleaning Service');

        return $this->makeUser($jabatan, $divisi, 'MCS Tester');
    }

    /** Employee in a division the MCS scope manages (jabatan id 9). */
    private function employee(): User
    {
        [$divisi, $jabatan] = $this->divisiWithJabatan('OCS', 'Cleaning Service', 9);

        return $this->makeUser($jabatan, $divisi, 'Pegawai Lapangan');
    }

    // ---- model ------------------------------------------------------------

    public function test_created_by_is_persisted_with_the_creating_user(): void
    {
        $issuer = $this->mcs();
        $target = $this->employee();
        $tanggal = now()->addDay()->format('Y-m-d');

        $this->actingAs($issuer)->post('/mcs-work-order', [
            'user_id' => $target->id,
            'tanggal' => $tanggal,
            'deskripsi' => 'Bersihkan lobi',
        ]);

        $this->assertDatabaseHas('work_orders', [
            'user_id' => $target->id,
            'deskripsi' => 'Bersihkan lobi',
            'created_by' => $issuer->id,
        ]);
    }

    public function test_creator_name_returns_the_creator_nama_lengkap(): void
    {
        $issuer = $this->mcs();
        $order = WorkOrder::create([
            'user_id' => $this->employee()->id,
            'tanggal' => now()->toDateString(),
            'deskripsi' => 'Cek panel',
            'created_by' => $issuer->id,
        ]);

        $this->assertSame('MCS Tester', $order->fresh()->creator_name);
    }

    public function test_creator_name_falls_back_to_direksi_when_created_by_is_null(): void
    {
        $order = WorkOrder::create([
            'user_id' => $this->employee()->id,
            'tanggal' => now()->toDateString(),
            'deskripsi' => 'Perintah lama',
            'created_by' => null,
        ]);

        $this->assertNull($order->fresh()->created_by);
        $this->assertSame('Direksi', $order->fresh()->creator_name);
    }

    // ---- work-order create page -------------------------------------------

    public function test_create_page_shows_dibuat_oleh_with_creator_name(): void
    {
        $issuer = $this->mcs();
        $target = $this->employee();
        $tanggal = now()->addDay()->format('Y-m-d');

        WorkOrder::create([
            'user_id' => $target->id,
            'tanggal' => $tanggal,
            'deskripsi' => 'Periksa lift',
            'created_by' => $issuer->id,
        ]);

        $body = $this->actingAs($issuer)
            ->get("/mcs-work-order/{$target->id}/create?tanggal={$tanggal}")
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Dibuat oleh', $body);
        $this->assertStringContainsString('MCS Tester', $body);
        $this->assertStringContainsString('WIB', $body);
    }

    public function test_create_page_shows_direksi_for_legacy_order_without_creator(): void
    {
        $issuer = $this->mcs();
        $target = $this->employee();
        $tanggal = now()->addDay()->format('Y-m-d');

        WorkOrder::create([
            'user_id' => $target->id,
            'tanggal' => $tanggal,
            'deskripsi' => 'Perintah warisan',
            'created_by' => null,
        ]);

        $body = $this->actingAs($issuer)
            ->get("/mcs-work-order/{$target->id}/create?tanggal={$tanggal}")
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Dibuat oleh', $body);
        $this->assertStringContainsString('Direksi', $body);
    }

    // ---- checkpoint-user create form --------------------------------------

    public function test_checkpoint_create_shows_diperintah_oleh_above_the_date(): void
    {
        $issuer = $this->mcs();
        $target = $this->employee();

        $order = WorkOrder::create([
            'user_id' => $target->id,
            'tanggal' => now()->toDateString(),
            'deskripsi' => 'Bersihkan area parkir',
            'created_by' => $issuer->id,
        ]);

        $body = $this->actingAs($target)
            ->get('/checkpoint-user/create?work_order=' . $order->id)
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Diperintah oleh', $body);
        $this->assertStringContainsString('MCS Tester', $body);

        // The banner must sit before the date field, per the layout requirement.
        $this->assertLessThan(
            strpos($body, 'Tanggal pekerjaan'),
            strpos($body, 'Diperintah oleh'),
            'Diperintah oleh harus muncul di atas field tanggal.',
        );
    }

    public function test_checkpoint_create_falls_back_to_direksi_for_legacy_order(): void
    {
        $target = $this->employee();

        $order = WorkOrder::create([
            'user_id' => $target->id,
            'tanggal' => now()->toDateString(),
            'deskripsi' => 'Perintah warisan',
            'created_by' => null,
        ]);

        $body = $this->actingAs($target)
            ->get('/checkpoint-user/create?work_order=' . $order->id)
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Diperintah oleh', $body);
        $this->assertStringContainsString('Direksi', $body);
    }

    public function test_checkpoint_create_hides_the_banner_without_a_work_order(): void
    {
        $target = $this->employee();

        $body = $this->actingAs($target)
            ->get('/checkpoint-user/create')
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('Diperintah oleh', $body);
    }
}
