<?php

namespace Tests\Feature;

use App\Models\CheckPoint;
use App\Models\CheckPointItem;
use App\Models\CheckPointMessage;
use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\User;
use App\Notifications\CheckPointReplyNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * HTTP coverage for the checkpoint conversation endpoint
 * (`POST checkpoint-item/{item}/messages`) and the approve-modal seeding that
 * opens the thread.
 *
 * Unlike the model tests these run the real middleware stack, so route model
 * binding and the `auth` middleware are exercised too. The real MySQL test
 * database from phpunit.xml is used.
 */
class CheckPointMessageHttpTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Keep the suite hermetic without RefreshDatabase, which would roll
        // back the whole migrated schema. Order matters: users and checkpoints
        // hold foreign keys into divisis/jabatans.
        CheckPoint::query()->delete();
        CheckPointMessage::query()->delete();
        User::withTrashed()->where('email', 'like', 'cpmh-test-%')->forceDelete();
        Divisi::query()->where('name', 'like', 'cpmh-test-%')->delete();
        Jabatan::query()->where('type_jabatan', 'cpmh-test')->delete();
    }

    private function jabatanRow(string $code): Jabatan
    {
        return Jabatan::create([
            'divisi_id' => Divisi::query()->value('id') ?? 1,
            'code_jabatan' => $code,
            'type_jabatan' => 'cpmh-test',
            'name_jabatan' => 'cpmh-test-' . $code . '-' . uniqid(),
        ]);
    }

    /** A plain employee: their own jabatan carries no management code. */
    private function employee(): User
    {
        return $this->makeUser($this->jabatanRow('STAFF'));
    }

    /** A DIREKSI / MCS reviewer, detected through their own jabatan. */
    private function manager(string $code): User
    {
        return $this->makeUser($this->jabatanRow($code));
    }

    /**
     * A user the `direksi` middleware accepts: it reads the code from the
     * division's jabatan, not the user's own.
     */
    private function direksiUser(): User
    {
        $jabatan = $this->jabatanRow('DIREKSI');
        $divisi = Divisi::create([
            'name' => 'cpmh-test-div-' . uniqid(),
            'jabatan_id' => $jabatan->id,
        ]);

        return $this->makeUser($jabatan, $divisi);
    }

    private function makeUser(Jabatan $jabatan, ?Divisi $divisi = null): User
    {
        return User::create([
            'kerjasama_id' => 1,
            'devisi_id' => $divisi?->id ?? 1,
            'jabatan_id' => $jabatan->id,
            'name' => 'cpmh-test-' . uniqid(),
            'nama_lengkap' => 'Karyawan Uji',
            'image' => 'default.png',
            'email' => 'cpmh-test-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    private function itemFor(User $owner, array $attributes = []): CheckPointItem
    {
        $checkpoint = CheckPoint::create([
            'user_id' => $owner->id,
            'divisi_id' => 1,
            'type_check' => 'dikerjakan',
        ]);

        return $checkpoint->items()->create(array_merge([
            'pekerjaan_cp_id' => '1',
            'deskripsi' => 'Bukti Pekerjaan 1',
            'approve_status' => 'proccess',
            'urutan' => 0,
        ], $attributes));
    }

    /** @return array<int, UploadedFile> */
    private function photos(int $count): array
    {
        $photos = [];

        for ($i = 0; $i < $count; $i++) {
            $photos[] = UploadedFile::fake()->create("bukti-{$i}.jpg", 5, 'image/jpeg');
        }

        return $photos;
    }

    private function reply(CheckPointItem $item, array $payload)
    {
        return $this->post(
            route('checkpoint-item.messages.store', $item->id),
            $payload,
            ['Accept' => 'application/json'],
        );
    }

    public function test_employee_reply_on_a_denied_item_flips_it_back_to_proccess(): void
    {
        $owner = $this->employee();
        $item = $this->itemFor($owner, [
            'approve_status' => 'denied',
            'note' => 'Mohon tambahkan tombol Log Out pada navigasi mobile.',
        ]);

        Notification::fake();

        $this->actingAs($owner)
            ->reply($item, ['body' => 'Baik, tombol Log Out sudah ditambahkan.'])
            ->assertCreated()
            ->assertJsonPath('sender_role', CheckPointMessage::ROLE_EMPLOYEE)
            ->assertJsonPath('approve_status', 'proccess');

        $this->assertSame('proccess', $item->fresh()->approve_status);

        $this->assertDatabaseHas('check_point_messages', [
            'check_point_item_id' => $item->id,
            'user_id' => $owner->id,
            'sender_role' => CheckPointMessage::ROLE_EMPLOYEE,
            'body' => 'Baik, tombol Log Out sudah ditambahkan.',
        ]);
    }

    public function test_employee_reply_on_an_accepted_item_keeps_it_accepted(): void
    {
        $owner = $this->employee();
        $item = $this->itemFor($owner, ['approve_status' => 'accept']);

        Notification::fake();

        $this->actingAs($owner)
            ->reply($item, ['body' => 'Terima kasih, ini catatan tambahan saja.'])
            ->assertCreated()
            ->assertJsonPath('approve_status', 'accept');

        $this->assertSame('accept', $item->fresh()->approve_status);
    }

    public function test_management_reply_is_recorded_and_notifies_the_owner(): void
    {
        $owner = $this->employee();
        $manager = $this->manager('DIREKSI');
        $item = $this->itemFor($owner, ['approve_status' => 'proccess']);

        Notification::fake();

        $this->actingAs($manager)
            ->reply($item, ['body' => 'Mohon lampirkan bukti setelah perbaikan.'])
            ->assertCreated()
            ->assertJsonPath('sender_role', CheckPointMessage::ROLE_MANAGEMENT);

        $this->assertDatabaseHas('check_point_messages', [
            'check_point_item_id' => $item->id,
            'user_id' => $manager->id,
            'sender_role' => CheckPointMessage::ROLE_MANAGEMENT,
        ]);

        Notification::assertSentTo($owner, CheckPointReplyNotification::class);
        Notification::assertNotSentTo($manager, CheckPointReplyNotification::class);
    }

    public function test_an_mcs_reviewer_can_reply(): void
    {
        $owner = $this->employee();
        $mcs = $this->manager('MCS');
        $item = $this->itemFor($owner, ['approve_status' => 'proccess']);

        Notification::fake();

        $this->actingAs($mcs)
            ->reply($item, ['body' => 'Catatan dari Manager CS.'])
            ->assertCreated()
            ->assertJsonPath('sender_role', CheckPointMessage::ROLE_MANAGEMENT);
    }

    public function test_an_employee_reply_notifies_the_reviewers(): void
    {
        $owner = $this->employee();
        $manager = $this->manager('DIREKSI');
        $item = $this->itemFor($owner, ['approve_status' => 'denied', 'note' => 'Perbaiki.']);

        Notification::fake();

        $this->actingAs($owner)
            ->reply($item, ['body' => 'Sudah diperbaiki.'])
            ->assertCreated();

        Notification::assertSentTo($manager, CheckPointReplyNotification::class);
        Notification::assertNotSentTo($owner, CheckPointReplyNotification::class);
    }

    public function test_a_non_owner_employee_cannot_reply(): void
    {
        $owner = $this->employee();
        $other = $this->employee();
        $item = $this->itemFor($owner, ['approve_status' => 'proccess']);

        Notification::fake();

        $this->actingAs($other)
            ->reply($item, ['body' => 'Saya bukan pemiliknya.'])
            ->assertForbidden();

        $this->assertDatabaseCount('check_point_messages', 0);
        Notification::assertNothingSent();
    }

    public function test_a_post_without_body_or_images_is_rejected(): void
    {
        $owner = $this->employee();
        $item = $this->itemFor($owner, ['approve_status' => 'denied', 'note' => 'Perbaiki.']);

        // No `body` key and no attachments at all.
        $this->actingAs($owner)
            ->reply($item, [])
            ->assertStatus(422);

        // A body that is only whitespace is not a reply either.
        $this->actingAs($owner)
            ->reply($item, ['body' => '   '])
            ->assertStatus(422);

        $this->assertDatabaseCount('check_point_messages', 0);
        $this->assertSame('denied', $item->fresh()->approve_status);
    }

    public function test_more_than_five_attachments_are_rejected(): void
    {
        $owner = $this->employee();
        $item = $this->itemFor($owner, ['approve_status' => 'denied', 'note' => 'Perbaiki.']);

        $this->actingAs($owner)
            ->reply($item, [
                'body' => 'Lihat lampiran.',
                'images' => $this->photos(CheckPointMessage::MAX_IMAGES + 1),
            ])
            ->assertStatus(422);

        $this->assertDatabaseCount('check_point_messages', 0);
        $this->assertDatabaseCount('check_point_message_images', 0);
    }

    public function test_attachments_are_stored_on_the_message(): void
    {
        $owner = $this->employee();
        $item = $this->itemFor($owner, ['approve_status' => 'denied', 'note' => 'Perbaiki.']);

        Notification::fake();

        $this->actingAs($owner)
            ->reply($item, [
                'body' => 'Lampiran bukti perbaikan.',
                'images' => $this->photos(2),
            ])
            ->assertCreated()
            ->assertJsonCount(2, 'images');

        $message = CheckPointMessage::query()->where('check_point_item_id', $item->id)->firstOrFail();

        $this->assertDatabaseCount('check_point_message_images', 2);
        $this->assertSame(
            2,
            $message->images()->count(),
            'the attachments must belong to the message, not the evidence photos',
        );
        $this->assertDatabaseCount('check_point_images', 0);
    }

    public function test_a_filled_verdict_note_opens_the_thread_as_a_management_message(): void
    {
        $owner = $this->employee();
        $direksi = $this->direksiUser();
        $item = $this->itemFor($owner, ['approve_status' => 'proccess']);

        Notification::fake();

        $this->actingAs($direksi)
            ->patch(route('direksi.cp.history.approve', $item->check_point_id), [
                'index' => 0,
                'status' => 'denied',
                'note' => 'Revisi navigasi mobile dan pengujian responsivitas tombol aksi.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('check_point_messages', [
            'check_point_item_id' => $item->id,
            'user_id' => $direksi->id,
            'sender_role' => CheckPointMessage::ROLE_MANAGEMENT,
            'body' => 'Revisi navigasi mobile dan pengujian responsivitas tombol aksi.',
        ]);

        Notification::assertSentTo($owner, CheckPointReplyNotification::class);
    }

    public function test_a_verdict_without_a_note_does_not_open_the_thread(): void
    {
        $owner = $this->employee();
        $direksi = $this->direksiUser();
        $item = $this->itemFor($owner, ['approve_status' => 'proccess']);

        Notification::fake();

        $this->actingAs($direksi)
            ->patch(route('direksi.cp.history.approve', $item->check_point_id), [
                'index' => 0,
                'status' => 'accept',
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('check_point_messages', 0);
    }

    public function test_the_notification_is_written_to_the_database_channel(): void
    {
        $owner = $this->employee();
        $manager = $this->manager('DIREKSI');
        $item = $this->itemFor($owner, ['approve_status' => 'denied', 'note' => 'Perbaiki.']);

        // No Notification::fake() here: this asserts the database channel
        // actually persists the ping the dropdown renders.
        $this->actingAs($owner)
            ->reply($item, ['body' => 'Sudah diperbaiki.'])
            ->assertCreated();

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $manager->id,
        ]);

        $notification = $manager->notifications()->firstOrFail();

        $this->assertSame('Balasan baru dari karyawan', $notification->data['title']);
        $this->assertSame($item->id, $notification->data['item_id']);
    }
}
