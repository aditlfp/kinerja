<?php

namespace Tests\Feature;

use App\Models\CheckPoint;
use App\Models\PekerjaanCp;
use App\Models\User;
use App\Services\CheckPointSyncService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Regression coverage for the checkpoint normalisation refactor.
 *
 * Two production bugs motivated this:
 *
 *  1. `img` used to be a JSON array stuffed into a varchar(255) column, so a
 *     handful of evidence photos overflowed it and MySQL rejected the row
 *     with "Data too long for column 'img'".
 *  2. All fields were stored as lock-step parallel arrays, so removing a row
 *     in the form shifted a photo or a description onto the wrong job.
 *
 * These tests use the real MySQL test database configured in phpunit.xml.
 */
class CheckPointNormalisationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();

        // Keep the suite hermetic without needing RefreshDatabase, which would
        // roll back the whole migrated schema.
        CheckPoint::query()->delete();
        PekerjaanCp::query()->delete();
        User::query()->where('email', 'like', 'cp-test-%')->delete();
    }

    private function user(): User
    {
        return User::create([
            'kerjasama_id' => 1,
            'devisi_id' => \App\Models\Divisi::query()->value('id') ?? 1,
            'jabatan_id' => \App\Models\Jabatan::query()->value('id') ?? 1,
            'name' => 'cp-test-' . uniqid(),
            'nama_lengkap' => 'Karyawan Uji',
            'image' => 'default.png',
            'email' => 'cp-test-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    /** A single job used to overflow the varchar(255) `img` column. */
    public function test_many_photos_on_a_single_job_are_stored_without_overflow(): void
    {
        $checkpoint = CheckPoint::create([
            'user_id' => $this->user()->id,
            'type_check' => 'dikerjakan',
        ]);

        $item = $checkpoint->items()->create([
            'pekerjaan_cp_id' => '1',
            'deskripsi' => 'Pekerjaan dengan banyak bukti',
            'approve_status' => 'proccess',
            'urutan' => 0,
        ]);

        // 12 photos at ~41 characters each is far beyond the old 255 byte cap.
        for ($i = 0; $i < 12; $i++) {
            $item->images()->create([
                'path' => 'data' . str_pad((string) $i, 36, '0') . '.jpg',
                'urutan' => $i,
            ]);
        }

        $this->assertSame(12, $checkpoint->fresh()->items->first()->images()->count());
    }

    /** A long description must not be truncated by a varchar column. */
    public function test_long_description_is_stored_in_full(): void
    {
        $checkpoint = CheckPoint::create([
            'user_id' => $this->user()->id,
            'type_check' => 'dikerjakan',
        ]);

        $description = str_repeat('deskripsi panjang ', 100);

        $checkpoint->items()->create([
            'pekerjaan_cp_id' => '1',
            'deskripsi' => $description,
            'approve_status' => 'proccess',
            'urutan' => 0,
        ]);

        $this->assertSame($description, $checkpoint->fresh()->items->first()->deskripsi);
    }

    /**
     * Deleting a middle job must not shift the remaining jobs' photos or
     * descriptions (the old parallel-array bug).
     */
    public function test_deleting_a_job_does_not_shift_other_jobs(): void
    {
        $checkpoint = CheckPoint::create([
            'user_id' => $this->user()->id,
            'type_check' => 'dikerjakan',
        ]);

        foreach (['A', 'B', 'C'] as $urutan => $label) {
            $item = $checkpoint->items()->create([
                'pekerjaan_cp_id' => (string) ($urutan + 1),
                'deskripsi' => 'Deskripsi ' . $label,
                'approve_status' => 'proccess',
                'urutan' => $urutan,
            ]);
            $item->images()->create([
                'path' => 'data' . $label . '.jpg',
                'urutan' => 0,
            ]);
        }

        // Remove the middle job, as the form's "Hapus" button does.
        $middle = $checkpoint->items()->where('deskripsi', 'Deskripsi B')->firstOrFail();
        $middle->delete();

        $remaining = $checkpoint->fresh()->items;

        $this->assertSame(['Deskripsi A', 'Deskripsi C'], $remaining->pluck('deskripsi')->all());
        $this->assertSame(
            ['dataA.jpg', 'dataC.jpg'],
            $remaining->flatMap(fn ($item) => $item->images->pluck('path'))->all(),
        );
    }

    /** Approval is addressed by item id, so it can never hit the wrong job. */
    public function test_approval_targets_the_addressed_item_only(): void
    {
        $checkpoint = CheckPoint::create([
            'user_id' => $this->user()->id,
            'type_check' => 'dikerjakan',
        ]);

        $first = $checkpoint->items()->create([
            'pekerjaan_cp_id' => '1',
            'deskripsi' => 'Pertama',
            'approve_status' => 'proccess',
            'urutan' => 0,
        ]);
        $second = $checkpoint->items()->create([
            'pekerjaan_cp_id' => '2',
            'deskripsi' => 'Kedua',
            'approve_status' => 'proccess',
            'urutan' => 1,
        ]);

        $this->patch(route('direksi.cp.history.approve', $checkpoint->id), [
            'index' => 1,
            'status' => 'accept',
            'note' => 'Bagus',
        ])->assertRedirect();

        $this->assertSame('proccess', $first->fresh()->approve_status);
        $this->assertSame('accept', $second->fresh()->approve_status);
        $this->assertSame('Bagus', $second->fresh()->note);
    }

    /**
     * uploadBukti keys the row by the PekerjaanCp id (there is no
     * pekerjaan_id[] field on that form), so the service must read the key.
     */
    public function test_upload_bukti_reads_the_job_from_the_row_key(): void
    {
        $userId = $this->user()->id;
        $this->actingAs(User::find($userId));

        $this->post(route('uploadBukti-checkpoint-user'), [
            'user_id' => $userId,
            'divisi_id' => 1,
            'latitude' => '-7.8',
            'longtitude' => '111.5',
            'pekerjaan_cp_id' => ['540' => '540', '579' => '579'],
            'deskripsi' => ['540' => 'Deskripsi 540', '579' => 'Deskripsi 579'],
            'tanggal' => ['540' => '2026-10-01', '579' => '2026-10-02'],
        ])->assertRedirect();

        $checkpoint = CheckPoint::with('items')->where('user_id', $userId)->firstOrFail();

        $this->assertSame(2, $checkpoint->items->count());
        $this->assertSame(
            ['540', '579'],
            $checkpoint->items->sortBy('urutan')->pluck('pekerjaan_cp_id')->all(),
        );
        $this->assertSame(
            ['Deskripsi 540', 'Deskripsi 579'],
            $checkpoint->items->sortBy('urutan')->pluck('deskripsi')->all(),
        );
        $this->assertSame('2026-10-01', $checkpoint->items->sortBy('urutan')->first()->tanggal->format('Y-m-d'));
    }

    /** A bare `PekerjaanCp` id key still resolves (legacy uploadBukti form). */
    public function test_upload_bukti_still_accepts_a_bare_id_key(): void
    {
        $userId = $this->user()->id;
        $this->actingAs(User::find($userId));

        $this->post(route('uploadBukti-checkpoint-user'), [
            'user_id' => $userId,
            'divisi_id' => 1,
            'pekerjaan_cp_id' => ['540' => '540'],
            'deskripsi' => ['540' => 'Deskripsi lama'],
        ])->assertRedirect();

        $item = CheckPoint::with('items')->where('user_id', $userId)->firstOrFail()->items->first();

        $this->assertSame('540', $item->pekerjaan_cp_id);
        $this->assertSame('Deskripsi lama', $item->deskripsi);
    }

    /** An `item_<id>` key copies the job, so two ad-hoc rows cannot collide. */
    public function test_upload_bukti_appends_ad_hoc_row_from_item_key(): void
    {
        $userId = $this->user()->id;
        $this->actingAs(User::find($userId));

        $source = CheckPoint::create(['user_id' => $userId, 'divisi_id' => 1, 'type_check' => 'harian']);
        $sourceItem = $source->items()->create([
            'input_manual' => 'Bersih-bersih',
            'urutan' => 0,
            'approve_status' => 'proccess',
        ]);

        $this->post(route('uploadBukti-checkpoint-user'), [
            'user_id' => $userId,
            'divisi_id' => 1,
            'pekerjaan_cp_id' => ['item_' . $sourceItem->id => 'item_' . $sourceItem->id],
            'deskripsi' => ['item_' . $sourceItem->id => 'Lanjutan bersih-bersih'],
            'note' => ['item_' . $sourceItem->id => 'ad hoc'],
        ])->assertRedirect();

        $item = CheckPoint::with('items')
            ->where('user_id', $userId)
            ->whereKeyNot($source->id)
            ->firstOrFail()
            ->items
            ->first();

        $this->assertNull($item->pekerjaan_cp_id);
        $this->assertSame('Bersih-bersih', $item->input_manual, 'the ad-hoc label must be copied, not parsed as an id');
        $this->assertSame('Lanjutan bersih-bersih', $item->deskripsi);
        $this->assertSame('ad hoc', $item->note);
    }

    /** More than the allowed photos must be refused with a message, not trimmed. */
    public function test_exceeding_the_photo_limit_is_rejected_with_a_message(): void
    {
        $user = $this->user();
        $this->actingAs($user);

        $photos = [];
        $limit = CheckPointSyncService::MAX_IMAGES_PER_ITEM;
        for ($i = 0; $i < $limit + 1; $i++) {
            $photos[] = UploadedFile::fake()->create("p{$i}.jpg", 5, 'image/jpeg');
        }

        $this->post(route('checkpoint-user.store'), [
            'user_id' => $user->id,
            'divisi_id' => 1,
            'pekerjaan_id' => [0 => '1'],
            'deskripsi' => [0 => 'Satu foto lebih dari batas'],
            'img' => [0 => $photos],
        ])->assertRedirect();

        $this->assertDatabaseCount('check_point_items', 0);
    }

    /** The limit message names the number so the user knows the rule. */
    public function test_image_limit_error_message_reports_the_limit(): void
    {
        $limit = CheckPointSyncService::MAX_IMAGES_PER_ITEM;
        $photos = [];
        for ($i = 0; $i < $limit + 1; $i++) {
            $photos[] = UploadedFile::fake()->create("p{$i}.jpg", 5, 'image/jpeg');
        }

        $request = Request::create('/upload-bukti', 'POST');
        $request->files->set('img', [0 => $photos]);

        $message = app(CheckPointSyncService::class)->imageLimitError($request);

        $this->assertNotNull($message);
        $this->assertStringContainsString('Foto maksimal ' . $limit, $message);
        $this->assertStringContainsString((string) ($limit + 1), $message);
    }

    /** A request within the limit produces no error message. */
    public function test_image_limit_error_is_null_within_the_limit(): void
    {
        $request = Request::create('/upload-bukti', 'POST');
        $request->files->set('img', [0 => [UploadedFile::fake()->create('ok.jpg', 5, 'image/jpeg')]]);

        $this->assertNull(app(CheckPointSyncService::class)->imageLimitError($request));
    }

    /** Exactly the allowed number of photos is accepted. */
    public function test_exactly_seven_photos_are_accepted(): void
    {
        $user = $this->user();
        $this->actingAs($user);

        $photos = [];
        $limit = CheckPointSyncService::MAX_IMAGES_PER_ITEM;
        for ($i = 0; $i < $limit; $i++) {
            $photos[] = UploadedFile::fake()->create("p{$i}.jpg", 5, 'image/jpeg');
        }

        $this->post(route('checkpoint-user.store'), [
            'user_id' => $user->id,
            'divisi_id' => 1,
            'pekerjaan_id' => [0 => '1'],
            'deskripsi' => [0 => 'Tujuh foto'],
            'img' => [0 => $photos],
        ])->assertRedirect();

        $item = CheckPoint::with('items.images')->where('user_id', $user->id)->firstOrFail()->items->first();

        $this->assertSame($limit, $item->images->count());
    }

    /** A single-file input (no `[]`) arrives as a bare UploadedFile. */
    public function test_a_single_uploaded_file_is_stored(): void
    {
        $user = $this->user();
        $this->actingAs($user);

        $this->post(route('checkpoint-user.store'), [
            'user_id' => $user->id,
            'divisi_id' => 1,
            'pekerjaan_id' => [0 => '1'],
            'deskripsi' => [0 => 'Satu foto tanpa []'],
            'img' => [0 => UploadedFile::fake()->create('solo.jpg', 5, 'image/jpeg')],
        ])->assertRedirect();

        $item = CheckPoint::with('items.images')->where('user_id', $user->id)->firstOrFail()->items->first();

        $this->assertSame(1, $item->images->count());
    }

    /**
     * End-to-end create: the browser posts stable-index arrays and the
     * controller must persist one item per row with its own photos.
     */
    public function test_store_via_http_persists_items_and_photos(): void
    {
        $user = $this->user();
        $this->actingAs($user);

        $photos = [
            UploadedFile::fake()->create('a.jpg', 5, 'image/jpeg'),
            UploadedFile::fake()->create('b.jpg', 5, 'image/jpeg'),
            UploadedFile::fake()->create('c.jpg', 5, 'image/jpeg'),
            UploadedFile::fake()->create('d.jpg', 5, 'image/jpeg'),
        ];

        $this->post(route('checkpoint-user.store'), [
            'user_id' => $user->id,
            'divisi_id' => 1,
            'latitude' => '-7.8',
            'longtitude' => '111.5',
            'pekerjaan_id' => [0 => '1', 1 => 'manual'],
            'input_manual' => [1 => 'Bersih-bersih'],
            'deskripsi' => [0 => 'Deskripsi A', 1 => 'Deskripsi B'],
            'tanggal' => [0 => '2026-10-05', 1 => '2026-10-06'],
            'approve_status' => [0 => 'proccess', 1 => 'proccess'],
            'img' => [0 => $photos],
        ])->assertRedirect();

        $checkpoint = CheckPoint::with('items.images')->where('user_id', $user->id)->firstOrFail();

        $this->assertSame(2, $checkpoint->items->count());

        $first = $checkpoint->items->firstWhere('urutan', 0);
        $second = $checkpoint->items->firstWhere('urutan', 1);

        $this->assertSame('1', $first->pekerjaan_cp_id);
        $this->assertSame(4, $first->images->count(), 'four photos on one job must survive');
        $this->assertNull($second->pekerjaan_cp_id);
        $this->assertSame('Bersih-bersih', $second->input_manual);
    }

    /** The cascade removes children instead of leaving orphan rows. */
    public function test_deleting_a_checkpoint_cascades_to_items_and_images(): void
    {
        $checkpoint = CheckPoint::create([
            'user_id' => $this->user()->id,
            'type_check' => 'dikerjakan',
        ]);

        $item = $checkpoint->items()->create([
            'pekerjaan_cp_id' => '1',
            'approve_status' => 'proccess',
            'urutan' => 0,
        ]);
        $item->images()->create(['path' => 'dataX.jpg', 'urutan' => 0]);

        $checkpointId = $checkpoint->id;
        $checkpoint->delete();

        $this->assertDatabaseMissing('check_point_items', ['check_point_id' => $checkpointId]);
        $this->assertDatabaseMissing('check_point_images', ['check_point_item_id' => $item->id]);
    }
}
