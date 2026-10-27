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
use Illuminate\Support\Facades\Storage;
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

    /** Pull a message back over HTTP, exactly as the thread button does. */
    private function retract(CheckPointMessage $message)
    {
        return $this->delete(
            route('checkpoint-item.messages.destroy', $message->id),
            [],
            ['Accept' => 'application/json'],
        );
    }

    /**
     * A recording as a browser uploads it: a bare blob named for its
     * container. Nothing in the fake looks like real audio, so this also
     * proves the stored extension comes from the validated type.
     */
    private function voice(int $kilobytes = 200, string $name = 'voice-note.webm', string $mime = 'audio/webm'): UploadedFile
    {
        return UploadedFile::fake()->create($name, $kilobytes, $mime);
    }

    private function messageFor(CheckPointItem $item, User $sender, string $role, array $attributes = []): CheckPointMessage
    {
        return $item->messages()->create(array_merge([
            'user_id' => $sender->id,
            'sender_role' => $role,
            'body' => 'Pesan uji.',
        ], $attributes));
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

    public function test_the_employee_edit_page_renders_the_thread_and_composer(): void
    {
        $owner = $this->employee();
        $item = $this->itemFor($owner, [
            'approve_status' => 'denied',
            'note' => 'Revisi navigasi mobile dan responsivitas tombol aksi.',
        ]);
        $item->messages()->create([
            'user_id' => $owner->id,
            'sender_role' => CheckPointMessage::ROLE_EMPLOYEE,
            'body' => 'Baik, tombol Log Out sudah ditambahkan.',
        ]);

        $this->actingAs($owner)
            ->get(route('checkpoint-user.edit', $item->check_point_id))
            ->assertOk()
            ->assertSee('Catatan Evaluasi')
            ->assertSee('Riwayat Percakapan')
            ->assertSee('Tulis Balasan / Klarifikasi')
            ->assertSee('Baik, tombol Log Out sudah ditambahkan.')
            ->assertSee('PERLU DIPERBAIKI')
            ->assertSee(route('checkpoint-item.messages.store', $item->id), false)
            ->assertSee('data-viewer-role="employee"', false)
            // The chat-screen dressing: the dotted backdrop behind the bubbles
            // and the down arrow that returns you to the newest message.
            ->assertSee('radial-gradient', false)
            ->assertSee('data-thread-jump', false);
    }

    public function test_the_employee_history_detail_renders_the_thread(): void
    {
        $owner = $this->employee();
        $item = $this->itemFor($owner, ['approve_status' => 'accept']);
        $item->messages()->create([
            'user_id' => $owner->id,
            'sender_role' => CheckPointMessage::ROLE_MANAGEMENT,
            'body' => 'Bukti sudah sesuai.',
        ]);

        $this->actingAs($owner)
            ->get(route('checkpoint-user.history.show', $item->check_point_id))
            ->assertOk()
            ->assertSee('Bukti sudah sesuai.')
            ->assertSee('Tulis Balasan / Klarifikasi')
            ->assertSee('DITINJAU')
            // The verdict badge carries the same artwork as the chip, not a
            // platform emoji: an accepted item reads green with the check icon.
            ->assertSee('Disetujui')
            ->assertSee('ri-checkbox-circle-fill', false);
    }

    public function test_the_checkpoint_detail_badges_use_the_same_solid_icons_as_the_modal(): void
    {
        $owner = $this->employee();
        $direksi = $this->direksiUser();
        $item = $this->itemFor($owner, ['approve_status' => 'accept']);

        $this->actingAs($direksi)
            ->get(route('direksi.cp.history.show', $item->check_point_id))
            ->assertOk()
            ->assertSee('Diterima')
            ->assertSee('ri-checkbox-circle-fill', false)
            ->assertDontSee('ri-close-circle-fill', false);

        $item->update(['approve_status' => 'denied']);

        $this->actingAs($direksi)
            ->get(route('direksi.cp.history.show', $item->check_point_id))
            ->assertOk()
            ->assertSee('Ditolak')
            ->assertSee('ri-close-circle-fill', false);
    }

    public function test_the_management_detail_renders_the_thread_as_management(): void
    {
        $owner = $this->employee();
        $direksi = $this->direksiUser();
        $item = $this->itemFor($owner, ['approve_status' => 'proccess']);
        $item->messages()->create([
            'user_id' => $owner->id,
            'sender_role' => CheckPointMessage::ROLE_EMPLOYEE,
            'body' => 'Mohon ditinjau kembali.',
        ]);

        $this->actingAs($direksi)
            ->get(route('direksi.cp.history.show', $item->check_point_id))
            ->assertOk()
            ->assertSee('Mohon ditinjau kembali.')
            ->assertSee('data-viewer-role="management"', false)
            ->assertSee('data-thread-pending', false);
    }

    public function test_a_legacy_note_renders_as_the_opening_message(): void
    {
        $owner = $this->employee();
        $item = $this->itemFor($owner, [
            'approve_status' => 'denied',
            'note' => 'Catatan lama tanpa percakapan.',
        ]);

        $this->actingAs($owner)
            ->get(route('checkpoint-user.history.show', $item->check_point_id))
            ->assertOk()
            ->assertSee('Catatan lama tanpa percakapan.')
            ->assertSee('Manajemen');
    }

    public function test_a_thread_without_any_exchange_shows_the_empty_state(): void
    {
        $owner = $this->employee();
        $item = $this->itemFor($owner, ['approve_status' => 'proccess']);

        $this->actingAs($owner)
            ->get(route('checkpoint-user.history.show', $item->check_point_id))
            ->assertOk()
            ->assertSee('Belum ada percakapan')
            ->assertSee('MENUNGGU TINJAUAN');
    }

    public function test_a_checkpoint_reply_notification_lands_on_the_thread(): void
    {
        $owner = $this->employee();
        $item = $this->itemFor($owner, ['approve_status' => 'denied', 'note' => 'Perbaiki.']);

        $this->actingAs($owner)
            ->reply($item, ['body' => 'Sudah diperbaiki.'])
            ->assertCreated();

        $owner->notify(new CheckPointReplyNotification(
            title: 'Catatan baru pada bukti pekerjaan',
            message: 'Atasan menanggapi.',
            checkPointId: (int) $item->check_point_id,
            itemId: (int) $item->id,
        ));

        $notification = $owner->notifications()->firstOrFail();

        $this->actingAs($owner)
            ->get(route('notifications.open', $notification->id))
            ->assertRedirect(route('checkpoint-user.history.show', $item->check_point_id));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_a_management_notification_lands_on_the_direksi_thread(): void
    {
        $owner = $this->employee();
        $direksi = $this->direksiUser();
        $item = $this->itemFor($owner, ['approve_status' => 'proccess']);

        $direksi->notify(new CheckPointReplyNotification(
            title: 'Balasan baru dari karyawan',
            message: 'Karyawan menanggapi bukti pekerjaan.',
            checkPointId: (int) $item->check_point_id,
            itemId: (int) $item->id,
        ));

        $notification = $direksi->notifications()->firstOrFail();

        $this->actingAs($direksi)
            ->get(route('notifications.open', $notification->id))
            ->assertRedirect(route('direksi.cp.history.show', $item->check_point_id));

        // The target must also be reachable by this user: the `direksi`
        // middleware reads the division's jabatan, so a wrong guess here
        // would log them out instead of showing the thread.
        $this->actingAs($direksi)
            ->get(route('direksi.cp.history.show', $item->check_point_id))
            ->assertOk()
            ->assertSee('data-viewer-role="management"', false);
    }

    public function test_a_mcs_notification_lands_on_the_mcs_thread(): void
    {
        $owner = $this->employee();
        $mcs = $this->manager('MCS');
        $item = $this->itemFor($owner, ['approve_status' => 'proccess']);

        $mcs->notify(new CheckPointReplyNotification(
            title: 'Balasan baru dari karyawan',
            message: 'Karyawan menanggapi bukti pekerjaan.',
            checkPointId: (int) $item->check_point_id,
            itemId: (int) $item->id,
        ));

        $notification = $mcs->notifications()->firstOrFail();

        $this->actingAs($mcs)
            ->get(route('notifications.open', $notification->id))
            ->assertRedirect(route('mcs.cp.history.show', $item->check_point_id));

        $this->actingAs($mcs)
            ->get(route('mcs.cp.history.show', $item->check_point_id))
            ->assertOk()
            ->assertSee('data-viewer-role="management"', false);
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

    // ----------------------------------------------------------------------
    // Tarik pesan. A retraction keeps the row — the thread must not lose its
    // shape — but wipes everything the message carried.
    // ----------------------------------------------------------------------

    public function test_a_sender_can_retract_their_own_message_and_it_stays_as_a_tombstone(): void
    {
        $owner = $this->employee();
        $item = $this->itemFor($owner, ['approve_status' => 'denied', 'note' => 'Perbaiki.']);
        $message = $this->messageFor($item, $owner, CheckPointMessage::ROLE_EMPLOYEE, [
            'body' => 'Maaf salah ketik, ini balasan keliru.',
        ]);

        $this->actingAs($owner)
            ->retract($message)
            ->assertOk()
            ->assertJsonPath('body', null)
            ->assertJsonPath('audio_path', null);

        $message->refresh();

        $this->assertNotNull($message->retracted_at, 'the row must survive as a tombstone');
        $this->assertNull($message->body, 'the text must be gone');
        $this->assertDatabaseHas('check_point_messages', [
            'id' => $message->id,
            'body' => null,
        ]);

        $this->actingAs($owner)
            ->get(route('checkpoint-user.history.show', $item->check_point_id))
            ->assertOk()
            ->assertSee('Pesan ini ditarik')
            ->assertDontSee('Maaf salah ketik, ini balasan keliru.');
    }

    public function test_retracting_a_message_wipes_its_photos_and_their_files(): void
    {
        $owner = $this->employee();
        $item = $this->itemFor($owner, ['approve_status' => 'denied', 'note' => 'Perbaiki.']);

        Notification::fake();

        $this->actingAs($owner)
            ->reply($item, ['body' => 'Lampiran salah kirim.', 'images' => $this->photos(2)])
            ->assertCreated();

        $message = CheckPointMessage::query()->where('check_point_item_id', $item->id)->firstOrFail();
        $paths = $message->images()->pluck('path')->all();

        $this->assertCount(2, $paths);

        foreach ($paths as $path) {
            $this->assertTrue(Storage::disk('public')->exists('images/' . $path));
        }

        $this->actingAs($owner)->retract($message)->assertOk();

        $this->assertDatabaseCount('check_point_message_images', 0);

        foreach ($paths as $path) {
            $this->assertFalse(
                Storage::disk('public')->exists('images/' . $path),
                'the attachment file must be deleted along with the message',
            );
        }
    }

    public function test_retracting_a_message_wipes_its_voice_note_and_its_file(): void
    {
        $owner = $this->employee();
        $item = $this->itemFor($owner, ['approve_status' => 'denied', 'note' => 'Perbaiki.']);

        Notification::fake();

        $this->actingAs($owner)
            ->reply($item, ['audio' => $this->voice(), 'audio_duration' => 12])
            ->assertCreated();

        $message = CheckPointMessage::query()->where('check_point_item_id', $item->id)->firstOrFail();
        $path = $message->audio_path;

        $this->assertTrue(Storage::disk('public')->exists('voices/' . $path));

        $this->actingAs($owner)->retract($message)->assertOk();

        $message->refresh();

        $this->assertNotNull($message->retracted_at);
        $this->assertNull($message->audio_path);
        $this->assertNull($message->audio_duration);
        $this->assertFalse(Storage::disk('public')->exists('voices/' . $path));
    }

    public function test_only_the_author_can_retract_a_message(): void
    {
        $owner = $this->employee();
        $other = $this->employee();
        $direksi = $this->direksiUser();
        $item = $this->itemFor($owner, ['approve_status' => 'proccess']);
        $message = $this->messageFor($item, $owner, CheckPointMessage::ROLE_EMPLOYEE, [
            'body' => 'Tidak boleh ditarik sembarangan.',
        ]);

        // Neither a colleague nor the reviewer who reads the thread may
        // delete somebody else's words.
        $this->actingAs($other)->retract($message)->assertForbidden();
        $this->actingAs($direksi)->retract($message)->assertForbidden();

        $message->refresh();

        $this->assertNull($message->retracted_at);
        $this->assertSame('Tidak boleh ditarik sembarangan.', $message->body);
    }

    public function test_a_reviewer_can_retract_their_own_message(): void
    {
        $owner = $this->employee();
        $direksi = $this->direksiUser();
        $item = $this->itemFor($owner, ['approve_status' => 'proccess']);
        $note = $this->messageFor($item, $direksi, CheckPointMessage::ROLE_MANAGEMENT, [
            'body' => 'Catatan salah, mohon diabaikan.',
        ]);

        $this->actingAs($direksi)->retract($note)->assertOk();

        $note->refresh();

        $this->assertNotNull($note->retracted_at);
        $this->assertNull($note->body);

        // The employee sees the tombstone too, not the withdrawn words.
        $this->actingAs($owner)
            ->get(route('checkpoint-user.history.show', $item->check_point_id))
            ->assertOk()
            ->assertSee('Pesan ini ditarik')
            ->assertDontSee('Catatan salah, mohon diabaikan.');
    }

    public function test_retracting_a_resubmission_reply_leaves_the_item_in_proccess(): void
    {
        $owner = $this->employee();
        $item = $this->itemFor($owner, ['approve_status' => 'denied', 'note' => 'Perbaiki.']);

        Notification::fake();

        $this->actingAs($owner)
            ->reply($item, ['body' => 'Sudah diperbaiki.'])
            ->assertCreated()
            ->assertJsonPath('approve_status', 'proccess');

        $message = CheckPointMessage::query()->where('check_point_item_id', $item->id)->firstOrFail();

        $this->actingAs($owner)->retract($message)->assertOk();

        $this->assertSame(
            'proccess',
            $item->fresh()->approve_status,
            'retracting must not pull the row back out of the reviewer queue',
        );
    }

    public function test_a_retracted_marker_hides_the_text_even_when_the_row_still_holds_it(): void
    {
        $owner = $this->employee();
        $item = $this->itemFor($owner, ['approve_status' => 'proccess']);
        $message = $this->messageFor($item, $owner, CheckPointMessage::ROLE_EMPLOYEE, [
            'body' => 'Isi yang tidak boleh tampil lagi.',
            'retracted_at' => now(),
        ]);

        $this->actingAs($owner)
            ->get(route('checkpoint-user.history.show', $item->check_point_id))
            ->assertOk()
            ->assertSee('Pesan ini ditarik')
            ->assertDontSee('Isi yang tidak boleh tampil lagi.')
            // Nothing left to retract, so the link is not offered twice.
            ->assertDontSee('data-thread-retract="' . $message->id . '"', false);
    }

    // ----------------------------------------------------------------------
    // Voice notes.
    // ----------------------------------------------------------------------

    public function test_a_voice_note_alone_is_a_valid_reply(): void
    {
        $owner = $this->employee();
        $item = $this->itemFor($owner, ['approve_status' => 'denied', 'note' => 'Perbaiki.']);

        Notification::fake();

        $this->actingAs($owner)
            ->reply($item, ['audio' => $this->voice(), 'audio_duration' => 42])
            ->assertCreated()
            ->assertJsonPath('audio_duration', 42);

        $message = CheckPointMessage::query()->where('check_point_item_id', $item->id)->firstOrFail();

        $this->assertNull($message->body, 'a recording needs no text');
        $this->assertSame(42, $message->audio_duration);
        $this->assertStringEndsWith('.webm', (string) $message->audio_path);
        $this->assertTrue(Storage::disk('public')->exists('voices/' . $message->audio_path));
        $this->assertDatabaseCount('check_point_message_images', 0);

        Storage::disk('public')->delete('voices/' . $message->audio_path);
    }

    public function test_a_voice_note_upload_must_be_a_recording(): void
    {
        $owner = $this->employee();
        $item = $this->itemFor($owner, ['approve_status' => 'denied', 'note' => 'Perbaiki.']);

        $this->actingAs($owner)
            ->reply($item, [
                'audio' => UploadedFile::fake()->create('catatan.txt', 10, 'text/plain'),
                'audio_duration' => 5,
            ])
            ->assertStatus(422);

        $this->assertDatabaseCount('check_point_messages', 0);
        $this->assertSame('denied', $item->fresh()->approve_status);
    }

    public function test_a_voice_note_longer_than_two_minutes_is_rejected(): void
    {
        $owner = $this->employee();
        $item = $this->itemFor($owner, ['approve_status' => 'denied', 'note' => 'Perbaiki.']);

        $this->actingAs($owner)
            ->reply($item, [
                'audio' => $this->voice(),
                'audio_duration' => CheckPointMessage::MAX_AUDIO_SECONDS + 1,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('audio_duration');

        $this->assertDatabaseCount('check_point_messages', 0);
    }

    public function test_a_voice_note_without_a_duration_is_rejected(): void
    {
        $owner = $this->employee();
        $item = $this->itemFor($owner, ['approve_status' => 'denied', 'note' => 'Perbaiki.']);

        // The server cannot read a length out of a MediaRecorder blob, so a
        // recording without a reported duration is refused rather than stored
        // with a guess.
        $this->actingAs($owner)
            ->reply($item, ['audio' => $this->voice()])
            ->assertStatus(422)
            ->assertJsonValidationErrors('audio_duration');

        $this->assertDatabaseCount('check_point_messages', 0);
    }

    public function test_a_voice_note_over_the_size_cap_is_rejected(): void
    {
        $owner = $this->employee();
        $item = $this->itemFor($owner, ['approve_status' => 'denied', 'note' => 'Perbaiki.']);

        $this->actingAs($owner)
            ->reply($item, [
                'audio' => $this->voice(6 * 1024 + 1),
                'audio_duration' => 30,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('audio');

        $this->assertDatabaseCount('check_point_messages', 0);
    }

    public function test_the_thread_plays_a_voice_note_and_offers_the_retract_link_only_on_your_own_message(): void
    {
        $owner = $this->employee();
        $reviewer = $this->manager('DIREKSI');
        $item = $this->itemFor($owner, ['approve_status' => 'proccess']);
        $theirNote = $this->messageFor($item, $reviewer, CheckPointMessage::ROLE_MANAGEMENT, [
            'body' => 'Mohon lampirkan bukti.',
        ]);
        $mine = $this->messageFor($item, $owner, CheckPointMessage::ROLE_EMPLOYEE, [
            'body' => 'Ini penjelasannya.',
            'audio_path' => 'voice-thread-test.webm',
            'audio_duration' => 65,
        ]);

        $this->actingAs($owner)
            ->get(route('checkpoint-user.history.show', $item->check_point_id))
            ->assertOk()
            ->assertSee('data-thread-retract="' . $mine->id . '"', false)
            ->assertDontSee('data-thread-retract="' . $theirNote->id . '"', false)
            ->assertSee(asset('storage/voices/voice-thread-test.webm'), false)
            ->assertSee('1:05');
    }

    public function test_a_reviewer_sees_the_retract_link_on_their_own_note(): void
    {
        $owner = $this->employee();
        $direksi = $this->direksiUser();
        $item = $this->itemFor($owner, ['approve_status' => 'proccess']);
        $reply = $this->messageFor($item, $owner, CheckPointMessage::ROLE_EMPLOYEE, [
            'body' => 'Mohon ditinjau kembali.',
        ]);
        $note = $this->messageFor($item, $direksi, CheckPointMessage::ROLE_MANAGEMENT, [
            'body' => 'Catatan untuk karyawan.',
        ]);

        $this->actingAs($direksi)
            ->get(route('direksi.cp.history.show', $item->check_point_id))
            ->assertOk()
            ->assertSee('data-thread-retract="' . $note->id . '"', false)
            ->assertDontSee('data-thread-retract="' . $reply->id . '"', false);
    }

    public function test_an_emoji_approval_opens_the_thread_without_asking_for_a_checklist(): void
    {
        $owner = $this->employee();
        $direksi = $this->direksiUser();
        $item = $this->itemFor($owner, ['approve_status' => 'proccess']);

        Notification::fake();

        $this->actingAs($direksi)
            ->patch(route('direksi.cp.history.approve', $item->check_point_id), [
                'index' => 0,
                'status' => 'accept',
                'emoji' => 'baik',
            ])
            ->assertRedirect();

        $item->refresh();

        $this->assertSame('accept', $item->approve_status);
        $this->assertSame('👍 Baik', $item->note);

        $this->assertDatabaseHas('check_point_messages', [
            'check_point_item_id' => $item->id,
            'user_id' => $direksi->id,
            'sender_role' => CheckPointMessage::ROLE_MANAGEMENT,
            'body' => '👍 Baik',
            'requires_confirmation' => false,
        ]);

        Notification::assertSentTo($owner, CheckPointReplyNotification::class);

        // The thread and the "Catatan Evaluasi" banner draw the chip icon in
        // place of the emoji character the note stores.
        $this->actingAs($direksi)
            ->get(route('direksi.cp.history.show', $item->check_point_id))
            ->assertOk()
            ->assertSee('ri-thumb-up-fill', false)
            ->assertDontSee('👍 Baik');
    }

    public function test_an_emoji_and_a_comment_are_combined_and_still_ask_for_confirmation(): void
    {
        $owner = $this->employee();
        $direksi = $this->direksiUser();
        $item = $this->itemFor($owner, ['approve_status' => 'proccess']);

        Notification::fake();

        $this->actingAs($direksi)
            ->patch(route('direksi.cp.history.approve', $item->check_point_id), [
                'index' => 0,
                'status' => 'accept',
                'emoji' => 'bagus',
                'note' => 'Terima kasih, lanjutkan.',
            ])
            ->assertRedirect();

        $item->refresh();

        $this->assertSame('😊 Bagus — Terima kasih, lanjutkan.', $item->note);

        $this->assertDatabaseHas('check_point_messages', [
            'check_point_item_id' => $item->id,
            'body' => '😊 Bagus — Terima kasih, lanjutkan.',
            'requires_confirmation' => true,
        ]);
    }

    public function test_an_approval_comment_without_an_emoji_is_stored_verbatim(): void
    {
        $owner = $this->employee();
        $direksi = $this->direksiUser();
        $item = $this->itemFor($owner, ['approve_status' => 'proccess']);

        Notification::fake();

        $this->actingAs($direksi)
            ->patch(route('direksi.cp.history.approve', $item->check_point_id), [
                'index' => 0,
                'status' => 'accept',
                'note' => 'Bagus, silakan dilanjutkan.',
            ])
            ->assertRedirect();

        $this->assertSame('Bagus, silakan dilanjutkan.', $item->fresh()->note);
        $this->assertDatabaseHas('check_point_messages', [
            'check_point_item_id' => $item->id,
            'body' => 'Bagus, silakan dilanjutkan.',
            'requires_confirmation' => true,
        ]);
    }

    public function test_an_unknown_emoji_key_is_rejected_and_leaves_the_verdict_alone(): void
    {
        $owner = $this->employee();
        $direksi = $this->direksiUser();
        $item = $this->itemFor($owner, ['approve_status' => 'proccess']);

        $this->actingAs($direksi)
            ->patch(route('direksi.cp.history.approve', $item->check_point_id), [
                'index' => 0,
                'status' => 'accept',
                'emoji' => 'emoji-palsu',
            ])
            ->assertSessionHasErrors('emoji');

        $this->assertSame('proccess', $item->fresh()->approve_status);
        $this->assertDatabaseCount('check_point_messages', 0);
    }

    public function test_a_revision_request_still_needs_a_note(): void
    {
        $owner = $this->employee();
        $direksi = $this->direksiUser();
        $item = $this->itemFor($owner, ['approve_status' => 'proccess']);

        $this->actingAs($direksi)
            ->patch(route('direksi.cp.history.approve', $item->check_point_id), [
                'index' => 0,
                'status' => 'denied',
                'emoji' => 'baik',
            ])
            ->assertSessionHasErrors('note');

        $this->assertSame('proccess', $item->fresh()->approve_status);
    }

    public function test_the_approval_modal_offers_the_quick_reactions(): void
    {
        $owner = $this->employee();
        $direksi = $this->direksiUser();
        $item = $this->itemFor($owner, ['approve_status' => 'proccess']);

        $this->actingAs($direksi)
            ->get(route('direksi.cp.history.show', $item->check_point_id))
            ->assertOk()
            ->assertSee('Setujui dengan emoji')
            ->assertSee('data-emoji-choice="baik"', false)
            ->assertSee('data-emoji-choice="disetujui"', false)
            // The chips draw plain solid icons from the app's own icon font,
            // not 3D emoji pictures.
            ->assertSee('ri-star-fill', false)
            ->assertSee('ri-checkbox-circle-fill', false)
            ->assertDontSee('images/emoji/fluent-3d/', false)
            ->assertSee('Excellent')
            ->assertSee('Minta Revisi');
    }
}
