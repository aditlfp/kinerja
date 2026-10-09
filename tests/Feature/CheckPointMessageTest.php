<?php

namespace Tests\Feature;

use App\Models\CheckPoint;
use App\Models\CheckPointItem;
use App\Models\CheckPointMessage;
use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\PekerjaanCp;
use App\Models\User;
use Tests\TestCase;

/**
 * Foundation coverage for the two-way checkpoint conversation:
 * `check_point_messages` and `check_point_message_images`.
 *
 * These tests use the real MySQL test database configured in phpunit.xml.
 */
class CheckPointMessageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();

        // Keep the suite hermetic without RefreshDatabase, which would roll
        // back the whole migrated schema.
        CheckPoint::query()->delete();
        CheckPointMessage::query()->delete();
        PekerjaanCp::query()->delete();
        User::query()->where('email', 'like', 'cpm-test-%')->delete();
    }

    private function user(): User
    {
        return User::create([
            'kerjasama_id' => 1,
            'devisi_id' => Divisi::query()->value('id') ?? 1,
            'jabatan_id' => Jabatan::query()->value('id') ?? 1,
            'name' => 'cpm-test-' . uniqid(),
            'nama_lengkap' => 'Karyawan Uji',
            'image' => 'default.png',
            'email' => 'cpm-test-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    private function itemFor(User $owner): CheckPointItem
    {
        $checkpoint = CheckPoint::create([
            'user_id' => $owner->id,
            'divisi_id' => 1,
            'type_check' => 'dikerjakan',
        ]);

        return $checkpoint->items()->create([
            'pekerjaan_cp_id' => '1',
            'deskripsi' => 'Bukti Pekerjaan 1',
            'approve_status' => 'proccess',
            'urutan' => 0,
        ]);
    }

    public function test_a_message_belongs_to_its_item_and_sender(): void
    {
        $owner = $this->user();
        $item = $this->itemFor($owner);

        $message = $item->messages()->create([
            'user_id' => $owner->id,
            'sender_role' => CheckPointMessage::ROLE_MANAGEMENT,
            'body' => 'Mohon ditambahkan tombol Log Out.',
        ]);

        $this->assertTrue($message->isFromManagement());
        $this->assertSame($item->id, $message->item->id);
        $this->assertSame($owner->id, $message->user->id);
        $this->assertSame(1, $item->fresh()->messages->count());
    }

    public function test_employee_messages_are_not_flagged_as_management(): void
    {
        $owner = $this->user();
        $item = $this->itemFor($owner);

        $message = $item->messages()->create([
            'user_id' => $owner->id,
            'sender_role' => CheckPointMessage::ROLE_EMPLOYEE,
            'body' => 'Baik, sudah ditambahkan.',
        ]);

        $this->assertFalse($message->isFromManagement());
    }

    public function test_attachments_are_returned_in_urutan_order(): void
    {
        $owner = $this->user();
        $item = $this->itemFor($owner);

        $message = $item->messages()->create([
            'user_id' => $owner->id,
            'sender_role' => CheckPointMessage::ROLE_EMPLOYEE,
            'body' => 'Lampiran bukti perbaikan.',
        ]);

        $message->images()->create(['path' => 'second.png', 'urutan' => 1]);
        $message->images()->create(['path' => 'first.png', 'urutan' => 0]);

        $this->assertSame(
            ['first.png', 'second.png'],
            $message->fresh()->images->pluck('path')->all(),
        );
    }

    public function test_a_message_allows_at_most_five_attachments(): void
    {
        $this->assertSame(5, CheckPointMessage::MAX_IMAGES);
    }

    public function test_deleting_an_item_cascades_to_its_messages_and_attachments(): void
    {
        $owner = $this->user();
        $item = $this->itemFor($owner);

        $message = $item->messages()->create([
            'user_id' => $owner->id,
            'sender_role' => CheckPointMessage::ROLE_EMPLOYEE,
            'body' => 'Balasan.',
        ]);
        $image = $message->images()->create(['path' => 'bukti.png', 'urutan' => 0]);

        $itemId = $item->id;
        $item->delete();

        $this->assertDatabaseMissing('check_point_messages', ['check_point_item_id' => $itemId]);
        $this->assertDatabaseMissing('check_point_message_images', ['id' => $image->id]);
    }

    public function test_deleting_a_checkpoint_cascades_through_items_to_messages(): void
    {
        $owner = $this->user();
        $item = $this->itemFor($owner);

        $message = $item->messages()->create([
            'user_id' => $owner->id,
            'sender_role' => CheckPointMessage::ROLE_MANAGEMENT,
            'body' => 'Catatan.',
        ]);

        $checkpointId = $item->check_point_id;
        CheckPoint::findOrFail($checkpointId)->delete();

        $this->assertDatabaseMissing('check_point_messages', ['id' => $message->id]);
    }

    /**
     * Users are soft-deleted, so a deactivated sender must still be shown as
     * the author of their message instead of rendering as somebody removed.
     */
    public function test_a_soft_deleted_sender_still_resolves_on_the_message(): void
    {
        $owner = $this->user();
        $sender = $this->user();
        $item = $this->itemFor($owner);

        $message = $item->messages()->create([
            'user_id' => $sender->id,
            'sender_role' => CheckPointMessage::ROLE_MANAGEMENT,
            'body' => 'Catatan evaluasi.',
        ]);

        $sender->delete();

        $fresh = $message->fresh();

        $this->assertSame($sender->id, $fresh->user_id);
        $this->assertSame($sender->id, $fresh->user?->id);
    }

    /**
     * A hard-deleted sender must not take the audit trail with them; the
     * message stays and simply loses its author (FK ON DELETE SET NULL).
     */
    public function test_a_hard_deleted_sender_leaves_the_message_with_a_null_user(): void
    {
        $owner = $this->user();
        $sender = $this->user();
        $item = $this->itemFor($owner);

        $message = $item->messages()->create([
            'user_id' => $sender->id,
            'sender_role' => CheckPointMessage::ROLE_MANAGEMENT,
            'body' => 'Catatan evaluasi.',
        ]);

        $sender->forceDelete();

        $fresh = $message->fresh();

        $this->assertNotNull($fresh);
        $this->assertNull($fresh->user_id);
        $this->assertNull($fresh->user);
    }
}
