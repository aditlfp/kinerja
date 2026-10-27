<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Jabatan;
use App\Models\Kerjasama;
use App\Models\KeteranganLanjutan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The per-mitra and global recap exports joined `users` onto
 * `keterangan_lanjutans` without selecting the note's own columns. MySQL's
 * duplicate `id`/`created_at` columns then overwrote the note with its
 * author, so the "Tanggal" column showed the day the author registered and
 * the row id pointed at the user. The export also filtered notes by the
 * author's jabatan, which hid notes written by anyone outside the viewer's
 * own jabatan list even though a note belongs to the mitra, not to a
 * position.
 */
class RekapKeteranganLanjutanExportTest extends TestCase
{
    private const CLIENT_NAME = 'PT Rekap Keterangan Uji';

    private int $clientId;
    private int $kerjasamaId;

    /** @var array<int, int> */
    private array $createdJabatanIds = [];

    private User $supervisor;
    private User $manager;

    private KeteranganLanjutan $noteBySupervisor;
    private KeteranganLanjutan $noteByManager;

    protected function setUp(): void
    {
        parent::setUp();

        $divisiId = \App\Models\Divisi::query()->value('id') ?? 1;

        $client = Client::create([
            'name' => self::CLIENT_NAME,
            'address' => 'Jl. Uji No. 2',
            'province' => 'Jawa Timur',
            'kabupaten' => 'PONOROGO',
            'zipcode' => '63411',
            'email' => 'rekap-keterangan-test@example.com',
            'phone' => '0352-111111',
            'fax' => '0352-111111',
            'logo' => 'default.png',
        ]);
        $this->clientId = $client->id;

        $kerjasama = Kerjasama::create([
            'client_id' => $this->clientId,
            'value' => '0',
            'experied' => '2030-12-31',
            'approve1' => '1',
            'approve2' => '1',
            'approve3' => '1',
        ]);
        $this->kerjasamaId = $kerjasama->id;

        $this->ensureJabatan(20, 'SPV-W', 'SUPERVISOR WILAYAH CLEANING SERVICE', 'CLEANING SERVICE');
        $this->ensureJabatan(14, 'SPV', 'SPV', 'SUPERVISOR');

        // The author of the note registered in February; the note itself was
        // written in September for the exported period.
        $this->supervisor = $this->makeUser($divisiId, 20, 'rekap-keterangan-supervisor');
        DB::table('users')
            ->where('id', $this->supervisor->id)
            ->update(['created_at' => '2026-02-04 08:00:00']);

        $this->manager = $this->makeUser($divisiId, 14, 'rekap-keterangan-manager', 'Manajer Uji Rekap');
        DB::table('users')
            ->where('id', $this->manager->id)
            ->update(['role_id' => 2]);

        $this->noteBySupervisor = $this->makeNote($this->supervisor, '2026-09-20 10:00:00');
        $this->noteByManager = $this->makeNote($this->manager, '2026-09-25 10:00:00');
    }

    protected function tearDown(): void
    {
        KeteranganLanjutan::query()
            ->whereIn('user_id', [$this->supervisor->id, $this->manager->id])
            ->delete();

        User::withTrashed()
            ->where('email', 'like', 'rekap-keterangan-%')
            ->forceDelete();

        Kerjasama::query()->whereKey($this->kerjasamaId)->delete();
        Client::query()->whereKey($this->clientId)->delete();
        Jabatan::query()->whereKey($this->createdJabatanIds)->delete();

        parent::tearDown();
    }

    public function test_the_per_mitra_export_keeps_every_note_with_its_own_date_and_id(): void
    {
        $response = $this->actingAs($this->manager)
            ->getJson(route('management.api-all-rekap-export', [
                'kerjasama' => $this->kerjasamaId,
                'start_date' => '2026-09-18',
                'end_date' => '2026-10-18',
            ]))
            ->assertOk()
            ->assertJsonPath('success', true);

        $rows = collect($response->json('data.keterangan_lanjutan'));

        $supervisorRow = $rows->firstWhere('id', $this->noteBySupervisor->id);

        $this->assertNotNull($supervisorRow, 'the note must keep its own id, not its author\'s id');
        $this->assertStringStartsWith(
            '2026-09-20',
            $supervisorRow['created_at'],
            'the date must be when the note was written, not when its author registered',
        );

        $this->assertSame($this->supervisor->id, $supervisorRow['user_id']);
        $this->assertArrayNotHasKey('password', $supervisorRow, 'joined user columns must not leak into the recap row');
        $this->assertArrayNotHasKey('email', $supervisorRow, 'joined user columns must not leak into the recap row');

        $this->assertNotNull(
            $rows->firstWhere('id', $this->noteByManager->id),
            'notes written by an author outside the viewer\'s jabatan list must still be exported',
        );
    }

    public function test_a_note_written_by_a_resigned_author_is_still_exported(): void
    {
        $supervisor = User::query()->findOrFail($this->supervisor->id);
        $supervisor->delete();

        $response = $this->actingAs($this->manager)
            ->getJson(route('management.api-all-rekap-export', [
                'kerjasama' => $this->kerjasamaId,
                'start_date' => '2026-09-18',
                'end_date' => '2026-10-18',
            ]))
            ->assertOk()
            ->assertJsonPath('success', true);

        $rows = collect($response->json('data.keterangan_lanjutan'));

        $this->assertNotNull(
            $rows->firstWhere('id', $this->noteBySupervisor->id),
            'a note must not disappear from the recap when its author leaves the company',
        );
    }

    public function test_the_global_export_includes_notes_written_by_the_supervisor(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->getJson(route('spvw.api-all-rekap-export-global', [
                'start_date' => '2026-09-18',
                'end_date' => '2026-10-18',
            ]))
            ->assertOk()
            ->assertJsonPath('success', true);

        $rows = collect($response->json('data.keterangan_lanjutan'));

        $supervisorRow = $rows->firstWhere('id', $this->noteBySupervisor->id);

        $this->assertNotNull($supervisorRow, 'the supervisor\'s own note must be exported for their mitra');
        $this->assertStringStartsWith('2026-09-20', $supervisorRow['created_at']);
    }

    private function makeNote(User $author, string $createdAt): KeteranganLanjutan
    {
        $note = KeteranganLanjutan::create([
            'user_id' => $author->id,
            'created_by_user_id' => $author->id,
            'keterangan' => [
                [
                    'periode' => '18 September 2026 - 18 Oktober 2026',
                    'judul' => 'Kedisiplinan',
                    'keterangan' => 'Catatan uji untuk periode ekspor.',
                ],
                [
                    'periode' => 'September 2026',
                    'judul' => 'Kinerja',
                    'keterangan' => 'Catatan kedua pada periode yang sama.',
                ],
            ],
        ]);

        DB::table('keterangan_lanjutans')
            ->where('id', $note->id)
            ->update(['created_at' => $createdAt, 'updated_at' => $createdAt]);

        return $note->refresh();
    }

    private function ensureJabatan(int $id, string $code, string $name, string $type): void
    {
        if (Jabatan::query()->whereKey($id)->exists()) {
            return;
        }

        Jabatan::forceCreate([
            'id' => $id,
            'divisi_id' => \App\Models\Divisi::query()->value('id') ?? 1,
            'code_jabatan' => $code,
            'type_jabatan' => $type,
            'name_jabatan' => $name,
        ]);

        $this->createdJabatanIds[] = $id;
    }

    private function makeUser(int $divisiId, int $jabatanId, string $prefix, ?string $namaLengkap = null): User
    {
        return User::create([
            'kerjasama_id' => $this->kerjasamaId,
            'devisi_id' => $divisiId,
            'jabatan_id' => $jabatanId,
            'name' => $prefix . '-' . uniqid(),
            'nama_lengkap' => $namaLengkap ?? 'Karyawan Uji Rekap',
            'image' => 'default.png',
            'email' => $prefix . '-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
        ]);
    }
}
