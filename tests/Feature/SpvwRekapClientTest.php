<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Jabatan;
use App\Models\Kerjasama;
use App\Models\PersonIn;
use App\Models\User;
use Tests\TestCase;

/**
 * The SPV-W recap used to hide client 1 twice: the mitra dropdown filtered
 * `where('id', '!=', 1)` and the global export skipped `client_id = 1`.
 *
 * Client 1 is a real mitra (PT SAC Ponorogo), not an internal placeholder, so
 * both halves are pinned down here end to end: the option must render, a
 * `?client_id=1` selection must stick instead of silently resetting to
 * "Semua Mitra", and the export must include its rows.
 */
class SpvwRekapClientTest extends TestCase
{
    private const CLIENT_NAME = 'PT SAC Ponorogo Uji';
    private const PERSON_NAME = 'spvw-rekap-client-one';

    /** @var array<int, int> */
    private array $createdJabatanIds = [];

    private bool $createdClient = false;
    private bool $createdKerjasama = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cleanUp();

        // The id is the point: the old queries excluded client/kerjasama 1 by
        // number, whatever the mitra was called.
        if (!Client::query()->whereKey(1)->exists()) {
            Client::forceCreate([
                'id' => 1,
                'name' => self::CLIENT_NAME,
                'address' => 'Jl. Uji No. 1',
                'province' => 'Jawa Timur',
                'kabupaten' => 'PONOROGO',
                'zipcode' => '63411',
                'email' => 'spvw-rekap-test@example.com',
                'phone' => '0352-000000',
                'fax' => '0352-000000',
                'logo' => 'default.png',
            ]);
            $this->createdClient = true;
        }

        if (!Kerjasama::query()->whereKey(1)->exists()) {
            Kerjasama::forceCreate([
                'id' => 1,
                'client_id' => 1,
                'value' => '0',
                'experied' => '2030-12-31',
                'approve1' => '1',
                'approve2' => '1',
                'approve3' => '1',
            ]);
            $this->createdKerjasama = true;
        }

        // 20 is one of the SPV-W jabatans, which also gives the export a
        // non-empty allowedSeeData() list: [9, 10, 34, 36].
        $this->ensureJabatan(20, 'SPV-W', 'SUPERVISOR WILAYAH CLEANING SERVICE', 'CLEANING SERVICE');
        $this->ensureJabatan(9, 'STAFF', 'spvw-rekap-test-staff', 'spvw-rekap-test');

        $divisiId = \App\Models\Divisi::query()->value('id') ?? 1;

        // Deliberately no staff user in a jabatan this supervisor may see
        // ([9, 10, 34, 36] for jabatan 20): this is the real PT SAC Ponorogo
        // supervisor, whose own mitra has no cleaning-service staff yet. The
        // mitra must still be selectable and exported because it is *their*
        // assignment, not because of the allowed-jabatan intersection.
        $this->makeUser($divisiId, 20, 'spvw-rekap-test-supervisor');

        PersonIn::create([
            'fullname' => self::PERSON_NAME,
            'client_id' => 1,
            'jabatan_id' => 9,
            'date_in' => now()->toDateString(),
            'total_mk' => '12',
            'method_salary' => 'bulanan',
            'status' => 'Diterima',
        ]);
    }

    protected function tearDown(): void
    {
        $this->cleanUp();

        if ($this->createdKerjasama) {
            Kerjasama::query()->whereKey(1)->delete();
        }

        if ($this->createdClient) {
            Client::query()->whereKey(1)->delete();
        }

        if ($this->createdJabatanIds !== []) {
            Jabatan::query()->whereKey($this->createdJabatanIds)->delete();
        }

        parent::tearDown();
    }

    private function cleanUp(): void
    {
        PersonIn::query()->where('fullname', self::PERSON_NAME)->delete();
        User::withTrashed()->where('email', 'like', 'spvw-rekap-test-%')->forceDelete();
    }

    private function ensureJabatan(int $id, string $code, string $name, string $type): Jabatan
    {
        $jabatan = Jabatan::query()->find($id);

        if ($jabatan) {
            return $jabatan;
        }

        $jabatan = Jabatan::forceCreate([
            'id' => $id,
            'divisi_id' => \App\Models\Divisi::query()->value('id') ?? 1,
            'code_jabatan' => $code,
            'type_jabatan' => $type,
            'name_jabatan' => $name,
        ]);

        $this->createdJabatanIds[] = $id;

        return $jabatan;
    }

    private function makeUser(int $divisiId, int $jabatanId, string $prefix, string $namaLengkap = 'Karyawan Uji SPVW'): User
    {
        return User::create([
            'kerjasama_id' => 1,
            'devisi_id' => $divisiId,
            'jabatan_id' => $jabatanId,
            'name' => $prefix . '-' . uniqid(),
            'nama_lengkap' => $namaLengkap,
            'image' => 'default.png',
            'email' => $prefix . '-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    private function supervisor(): User
    {
        return User::query()
            ->where('email', 'like', 'spvw-rekap-test-supervisor-%')
            ->firstOrFail();
    }

    public function test_client_one_is_listed_even_without_staff_in_the_supervisors_jabatans(): void
    {
        $this->actingAs($this->supervisor())
            ->get(route('spvw.rekap.index'))
            ->assertOk()
            // The dropdown must offer client 1, not merely the mitra name
            // somewhere else on the page.
            ->assertSee('<option value="1"', false);
    }

    public function test_selecting_client_one_sticks_instead_of_resetting_to_all_mitra(): void
    {
        $this->actingAs($this->supervisor())
            ->get(route('spvw.rekap.index', ['client_id' => 1]))
            ->assertOk()
            ->assertSee('Filter mitra tetap aktif sampai logout.');

        $this->assertSame(
            1,
            (int) session('spvw.selected_client_id'),
            'client 1 must survive the "is this client selectable?" check',
        );
    }

    public function test_only_the_supervisors_own_account_is_selectable_for_client_one_in_the_overtime_form(): void
    {
        $this->ensureJabatan(2, 'IT', 'spvw-rekap-test-it', 'spvw-rekap-test');

        $supervisor = $this->supervisor();
        $colleague = $this->makeUser(
            (int) (\App\Models\Divisi::query()->value('id') ?? 1),
            2,
            'spvw-rekap-test-colleague',
            'Kolega Uji SPVW',
        );

        $this->actingAs($supervisor)
            ->get(route('spvw.overtime-application.create', ['client_id' => 1]))
            ->assertOk()
            ->assertSee('<option value="' . $supervisor->id . '"', false)
            ->assertDontSee('<option value="' . $colleague->id . '"', false);
    }

    public function test_only_the_supervisors_own_account_is_selectable_for_client_one_in_cutting(): void
    {
        $this->ensureJabatan(2, 'IT', 'spvw-rekap-test-it', 'spvw-rekap-test');

        $supervisor = $this->supervisor();
        $colleague = $this->makeUser(
            (int) (\App\Models\Divisi::query()->value('id') ?? 1),
            2,
            'spvw-rekap-test-colleague',
            'Kolega Uji SPVW',
        );

        $this->actingAs($supervisor)
            ->get(route('spvw.cutting.index', ['client_id' => 1]))
            ->assertOk()
            ->assertSee('Karyawan Uji SPVW')
            ->assertDontSee('Kolega Uji SPVW');
    }

    public function test_the_global_export_includes_client_one_rows(): void
    {
        $response = $this->actingAs($this->supervisor())
            ->getJson(route('spvw.api-all-rekap-export-global', [
                'month' => now()->format('Y-m'),
            ]))
            ->assertOk()
            ->assertJsonPath('success', true);

        $fullnames = collect($response->json('data.person_ins'))->pluck('fullname')->all();

        $this->assertContains(
            self::PERSON_NAME,
            $fullnames,
            'the "Semua Mitra" export must not drop client 1',
        );
    }
}
