<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The MCS (Manager Cleaning Service) work-order flow shares its controller and
 * views with direksi, differing only by route prefix and the set of employees
 * that is visible.
 *
 * These tests pin the guarantees that make that sharing safe:
 *   - every link/redirect an MCS user sees stays inside the `mcs.` route group,
 *     so they are never bounced through direksi middleware (which logs out);
 *   - MCS is scoped to the divisions it manages, not the whole kerjasama;
 *   - direksi is unaffected by the MCS branch;
 *   - `only:MCS` still keeps other jabatan out.
 */
class McsWorkOrderTest extends TestCase
{
    use DatabaseTransactions;

    private int $divisiSeq = 900;

    /**
     * Build a divisi+jabatan pair with pinned ids. `id` is not fillable on
     * either model, so rows are written through the query builder.
     */
    private function divisiWithJabatan(string $code, string $name, ?int $divisiId = null, ?int $jabatanId = null): array
    {
        $divisiId ??= ++$this->divisiSeq;
        $jabatanId ??= ++$this->divisiSeq;
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
        $slug = strtolower(preg_replace('/[^a-z0-9]/i', '', $nama)) . $this->divisiSeq . rand(100, 999);

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

    /**
     * Employee in an OCS division. The controller's MCS scope matches the
     * production jabatan ids (9 = OCS), so the fixture pins that id.
     */
    private function managedEmployee(): User
    {
        [$divisi, $ocs] = $this->divisiWithJabatan('OCS', 'Cleaning Service', null, 9);

        return $this->makeUser($ocs, $divisi, 'Managed Employee');
    }

    public function test_mcs_can_open_work_order_index(): void
    {
        $res = $this->actingAs($this->mcs())->get('/mcs-work-order');

        $res->assertOk();
        $res->assertSee('MANAGER CS');
        $res->assertSee('Perintah Kerja');
    }

    public function test_index_renders_only_mcs_links(): void
    {
        $body = $this->actingAs($this->mcs())->get('/mcs-work-order')->assertOk()->getContent();

        $this->assertStringContainsString('mcs-work-order', $body);
        $this->assertStringNotContainsString('direksi-work-order', $body);
    }

    public function test_calendar_renders_without_direksi_routes(): void
    {
        $target = $this->managedEmployee();

        $body = $this->actingAs($this->mcs())
            ->get("/mcs-work-order/{$target->id}/calendar")
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('mcs-work-order', $body);
        $this->assertStringNotContainsString('direksi-work-order', $body);
    }

    public function test_create_form_posts_to_mcs_store_route(): void
    {
        $target = $this->managedEmployee();
        $tanggal = now()->addDay()->format('Y-m-d');

        $body = $this->actingAs($this->mcs())
            ->get("/mcs-work-order/{$target->id}/create?tanggal={$tanggal}")
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(route('mcs.work-order.store'), $body);
        $this->assertStringNotContainsString(route('direksi.work-order.store'), $body);
    }

    public function test_store_redirects_back_to_mcs_calendar_not_direksi(): void
    {
        $target = $this->managedEmployee();

        $this->actingAs($this->mcs())
            ->post('/mcs-work-order', [
                'user_id' => $target->id,
                'tanggal' => now()->addDay()->format('Y-m-d'),
                'deskripsi' => 'Tes perintah kerja MCS',
            ])
            ->assertRedirect(route('mcs.work-order.calendar', ['user' => $target->id]));
    }

    public function test_store_actually_persists_the_work_order(): void
    {
        $target = $this->managedEmployee();

        $this->actingAs($this->mcs())->post('/mcs-work-order', [
            'user_id' => $target->id,
            'tanggal' => now()->addDay()->format('Y-m-d'),
            'deskripsi' => 'Bersihkan lobi',
        ]);

        $this->assertDatabaseHas('work_orders', [
            'user_id' => $target->id,
            'deskripsi' => 'Bersihkan lobi',
        ]);
    }

    public function test_mcs_scope_excludes_employees_outside_managed_divisions(): void
    {
        [$divisi, $jabatan] = $this->divisiWithJabatan('MRT', 'Marketing');
        $outsider = $this->makeUser($jabatan, $divisi, 'Outside Scope');

        $visible = $this->actingAs($this->mcs())
            ->get('/mcs-work-order')
            ->assertOk()
            ->viewData('users')
            ->pluck('id');
        $this->assertNotContains($outsider->id, $visible);
    }

    public function test_mcs_scope_includes_managed_employees(): void
    {
        $target = $this->managedEmployee();

        $visible = $this->actingAs($this->mcs())
            ->get('/mcs-work-order')
            ->assertOk()
            ->viewData('users')
            ->pluck('id');

        $this->assertContains($target->id, $visible);
    }

    public function test_mcs_cannot_post_to_direksi_store_route(): void
    {
        $target = $this->managedEmployee();

        // Direksi middleware rejects and logs the MCS user out.
        $this->actingAs($this->mcs())
            ->post('/direksi-work-order', [
                'user_id' => $target->id,
                'tanggal' => now()->addDay()->format('Y-m-d'),
                'deskripsi' => 'harus ditolak',
            ])
            ->assertStatus(302);

        $this->assertDatabaseMissing('work_orders', ['deskripsi' => 'harus ditolak']);
    }

    public function test_mcs_checkpoint_history_route_exists(): void
    {
        $this->assertNotNull(route('mcs.cp.history.show', ['id' => 1]));
    }

    public function test_non_mcs_jabatan_is_forbidden(): void
    {
        $target = $this->managedEmployee();

        $this->actingAs($target)->get('/mcs-work-order')->assertForbidden();
    }

    public function test_direksi_is_unaffected_and_still_uses_direksi_routes(): void
    {
        [$divisi, $jabatan] = $this->divisiWithJabatan('DIREKSI', 'Direksi');
        $direksi = $this->makeUser($jabatan, $divisi, 'Direksi Tester');

        $body = $this->actingAs($direksi)->get('/direksi-work-order')->assertOk()->getContent();

        $this->assertStringContainsString('direksi-work-order', $body);
        $this->assertStringNotContainsString('mcs-work-order', $body);
    }

    public function test_direksi_store_redirects_to_direksi_calendar(): void
    {
        [$divisi, $jabatan] = $this->divisiWithJabatan('DIREKSI', 'Direksi');
        $direksi = $this->makeUser($jabatan, $divisi, 'Direksi Tester');
        $target = $this->managedEmployee();

        $this->actingAs($direksi)
            ->post('/direksi-work-order', [
                'user_id' => $target->id,
                'tanggal' => now()->addDay()->format('Y-m-d'),
                'deskripsi' => 'Perintah dari direksi',
            ])
            ->assertRedirect(route('direksi.work-order.calendar', ['user' => $target->id]));

        $this->assertDatabaseHas('work_orders', ['deskripsi' => 'Perintah dari direksi']);
    }
}
