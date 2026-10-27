<?php

namespace Tests\Feature;

use App\Models\CheckPoint;
use App\Models\CheckPointItem;
use App\Models\CheckPointMessage;
use App\Models\CheckPointMessageAcknowledgement;
use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\User;
use App\Notifications\CheckPointReplyNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * HTTP coverage for the two halves of "tanda respon atasan": the read stamp the
 * page sends when the thread is actually opened
 * (`POST checkpoint-item/{item}/messages/read`) and the confirmation checklist
 * the employee answers a management reply with
 * (`POST checkpoint-item/{item}/message/{message}/confirm`).
 *
 * Uses the real middleware stack and the real MySQL test database named in
 * phpunit.xml, like CheckPointMessageHttpTest next door.
 */
class CheckPointMessageAcknowledgementHttpTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Keep the suite hermetic without RefreshDatabase, which would roll back
        // the whole migrated schema. Acknowledgements first: they hang off the
        // messages that are deleted next.
        CheckPointMessageAcknowledgement::query()->delete();
        CheckPoint::query()->delete();
        CheckPointMessage::query()->delete();
        User::withTrashed()->where('email', 'like', 'cpma-test-%')->forceDelete();
        Divisi::query()->where('name', 'like', 'cpma-test-%')->delete();
        Jabatan::query()->where('type_jabatan', 'cpma-test')->delete();
    }

    private function jabatanRow(string $code): Jabatan
    {
        return Jabatan::create([
            'divisi_id' => Divisi::query()->value('id') ?? 1,
            'code_jabatan' => $code,
            'type_jabatan' => 'cpma-test',
            'name_jabatan' => 'cpma-test-' . $code . '-' . uniqid(),
        ]);
    }

    private function employee(): User
    {
        return $this->makeUser($this->jabatanRow('STAFF'));
    }

    private function manager(string $code = 'DIREKSI'): User
    {
        return $this->makeUser($this->jabatanRow($code));
    }

    /**
     * A reviewer the `direksi` middleware accepts: it reads the code from the
     * division's jabatan, not the user's own, so the Direksi pages need this
     * shape rather than a bare manager().
     */
    private function direksiUser(): User
    {
        $jabatan = $this->jabatanRow('DIREKSI');
        $divisi = Divisi::create([
            'name' => 'cpma-test-div-' . uniqid(),
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
            'name' => 'cpma-test-' . uniqid(),
            'nama_lengkap' => 'Karyawan Uji',
            'image' => 'default.png',
            'email' => 'cpma-test-' . uniqid() . '@example.com',
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

    /**
     * A management reply that asks to be confirmed — what the thread calls a
     * "respon atasan".
     */
    private function managementReply(CheckPointItem $item, User $sender, array $attributes = []): CheckPointMessage
    {
        return $item->messages()->create(array_merge([
            'user_id' => $sender->id,
            'sender_role' => CheckPointMessage::ROLE_MANAGEMENT,
            'requires_confirmation' => true,
            'body' => 'Tambahkah Menu baru dengan warna merah.',
        ], $attributes));
    }

    private function confirm(CheckPointItem $item, CheckPointMessage $message, array $payload)
    {
        return $this->post(
            route('checkpoint-item.messages.confirm', ['item' => $item->id, 'message' => $message->id]),
            $payload,
            ['Accept' => 'application/json'],
        );
    }

    private function reply(CheckPointItem $item, array $payload)
    {
        return $this->post(
            route('checkpoint-item.messages.store', $item->id),
            $payload,
            ['Accept' => 'application/json'],
        );
    }

    private function stampRead(CheckPointItem $item)
    {
        return $this->post(
            route('checkpoint-item.messages.read', $item->id),
            [],
            ['Accept' => 'application/json'],
        );
    }

    public function test_an_unanswered_management_reply_renders_the_checklist_card(): void
    {
        $owner = $this->employee();
        $manager = $this->manager();
        $item = $this->itemFor($owner);
        $message = $this->managementReply($item, $manager);

        $this->actingAs($owner)
            ->get(route('checkpoint-user.history.show', $item->check_point_id))
            ->assertOk()
            ->assertSee('data-thread-confirm-card', false)
            ->assertSee('Konfirmasi pesan atasan', false)
            ->assertSee('Telah Dipahami/Dimengerti')
            ->assertSee('Siap Dilaksanakan')
            ->assertSee('Lainnya')
            // The composer stays shut until the checklist is answered.
            ->assertSee('data-thread-locked', false)
            ->assertSee(route('checkpoint-item.messages.confirm', ['item' => $item->id, 'message' => $message->id]), false)
            ->assertSee(route('checkpoint-item.messages.read', $item->id), false);
    }

    public function test_a_reply_that_does_not_ask_for_confirmation_renders_no_card_and_no_lock(): void
    {
        $owner = $this->employee();
        $manager = $this->manager();
        $item = $this->itemFor($owner);
        // Written before this feature existed: shown, never asked back.
        $this->managementReply($item, $manager, [
            'requires_confirmation' => false,
            'body' => 'Catatan lama tanpa konfirmasi.',
        ]);

        $this->actingAs($owner)
            ->get(route('checkpoint-user.history.show', $item->check_point_id))
            ->assertOk()
            ->assertSee('Catatan lama tanpa konfirmasi.')
            // The card carries the confirm endpoint; the panel never states it.
            ->assertDontSee('data-confirm-endpoint', false)
            ->assertDontSee('data-thread-locked', false);
    }

    public function test_management_never_sees_the_checklist_card(): void
    {
        $owner = $this->employee();
        $direksi = $this->direksiUser();
        $item = $this->itemFor($owner);
        $this->managementReply($item, $direksi);

        $this->actingAs($direksi)
            ->get(route('direksi.cp.history.show', $item->check_point_id))
            ->assertOk()
            ->assertDontSee('data-confirm-endpoint', false)
            ->assertDontSee('data-thread-locked', false)
            // ...but does see that it is still unread.
            ->assertSee('Belum dibaca', false);
    }

    public function test_the_employee_answers_the_checklist_and_management_sees_it(): void
    {
        $owner = $this->employee();
        $manager = $this->direksiUser();
        $item = $this->itemFor($owner);
        $message = $this->managementReply($item, $manager);

        Notification::fake();

        $this->actingAs($owner)
            ->confirm($item, $message, ['choices' => ['paham', 'siap']])
            ->assertCreated()
            ->assertJsonPath('message_id', $message->id)
            ->assertJsonPath('choices', ['paham', 'siap'])
            ->assertJsonPath('choice_labels', ['Telah Dipahami/Dimengerti', 'Siap Dilaksanakan'])
            // The bubble is drawn from these, not from the plain body: the icons
            // come from the server so the page never keeps a second mapping.
            ->assertJsonPath('confirmation', true)
            ->assertJsonPath('choice_details.0.icon', 'ri-checkbox-circle-fill')
            ->assertJsonPath('choice_details.1.icon', 'ri-rocket-2-fill')
            ->assertJsonPath('pending', 0)
            ->assertJsonPath('approve_status', 'proccess');

        $acknowledgement = $message->fresh()->acknowledgement;

        $this->assertNotNull($acknowledgement?->confirmed_at);
        $this->assertNotNull($acknowledgement?->read_at, 'confirming also proves it was read');
        $this->assertSame(['paham', 'siap'], $acknowledgement->answers());

        // The answers travel back as a real thread message, so management is
        // notified through the existing reply pipeline. Its body is the plain
        // fallback only — a bullet, never an emoji, because the icons the page
        // shows cannot live inside an escaped string.
        $this->assertDatabaseHas('check_point_messages', [
            'check_point_item_id' => $item->id,
            'user_id' => $owner->id,
            'sender_role' => CheckPointMessage::ROLE_EMPLOYEE,
            'requires_confirmation' => false,
            'body' => "Konfirmasi pesan atasan:\n• Telah Dipahami/Dimengerti\n• Siap Dilaksanakan",
        ]);

        Notification::assertSentTo($manager, CheckPointReplyNotification::class);

        $this->actingAs($manager)
            ->get(route('direksi.cp.history.show', $item->check_point_id))
            ->assertOk()
            ->assertSee('Dikonfirmasi')
            ->assertSee('Telah Dipahami/Dimengerti')
            // The checklist reads as real Remix Icon glyphs, not emoji — the
            // stray ✅ in the composer's emoji picker is why the assertion names
            // the answer rather than the bare character.
            ->assertSee('ri-checkbox-circle-fill', false)
            ->assertSee('ri-rocket-2-fill', false)
            ->assertDontSee('✅ Telah Dipahami/Dimengerti')
            // The stored plain text is a fallback, never what the bubble shows.
            ->assertDontSee('Konfirmasi pesan atasan:')
            ->assertDontSee('data-confirm-endpoint', false);

        // The employee sees the same answer on their own page.
        $this->actingAs($owner)
            ->get(route('checkpoint-user.history.show', $item->check_point_id))
            ->assertOk()
            ->assertSee('ri-checkbox-circle-fill', false)
            ->assertDontSee('✅ Telah Dipahami/Dimengerti');
    }

    public function test_the_other_answer_carries_its_text_into_the_thread(): void
    {
        $owner = $this->employee();
        $manager = $this->manager();
        $item = $this->itemFor($owner);
        $message = $this->managementReply($item, $manager);

        Notification::fake();

        $this->actingAs($owner)
            ->confirm($item, $message, ['choices' => ['lainnya'], 'note' => 'Mohon dijelaskan warnanya.'])
            ->assertCreated()
            ->assertJsonPath('note', 'Mohon dijelaskan warnanya.');

        $this->assertDatabaseHas('check_point_messages', [
            'check_point_item_id' => $item->id,
            'body' => "Konfirmasi pesan atasan:\n• Lainnya\nCatatan: Mohon dijelaskan warnanya.",
        ]);

        // The free text carries its own icon in the bubble.
        $this->actingAs($owner)
            ->get(route('checkpoint-user.history.show', $item->check_point_id))
            ->assertOk()
            ->assertSee('ri-more-fill', false)
            ->assertSee('ri-chat-1-line', false)
            ->assertSee('Mohon dijelaskan warnanya.');
    }

    public function test_all_three_answers_can_be_ticked_at_once(): void
    {
        $owner = $this->employee();
        $manager = $this->manager();
        $item = $this->itemFor($owner);
        $message = $this->managementReply($item, $manager);

        Notification::fake();

        $this->actingAs($owner)
            ->confirm($item, $message, [
                'choices' => ['paham', 'siap', 'lainnya'],
                'note' => 'Dikerjakan setelah shift.',
            ])
            ->assertCreated()
            ->assertJsonPath('choices', ['paham', 'siap', 'lainnya']);
    }

    public function test_a_second_confirmation_does_not_answer_the_same_reply_twice(): void
    {
        $owner = $this->employee();
        $manager = $this->manager();
        $item = $this->itemFor($owner);
        $message = $this->managementReply($item, $manager);

        Notification::fake();

        $this->actingAs($owner)->confirm($item, $message, ['choices' => ['paham']])->assertCreated();

        $this->actingAs($owner)
            ->confirm($item, $message, ['choices' => ['siap']])
            ->assertOk()
            // The first answer stands.
            ->assertJsonPath('choices', ['paham']);

        $this->assertSame(
            2,
            $item->messages()->count(),
            'the management reply plus one confirmation, and nothing more',
        );
        $this->assertDatabaseCount('check_point_message_acknowledgements', 1);
    }

    public function test_a_confirmation_needs_at_least_one_answer(): void
    {
        $owner = $this->employee();
        $manager = $this->manager();
        $item = $this->itemFor($owner);
        $message = $this->managementReply($item, $manager);

        $this->actingAs($owner)
            ->confirm($item, $message, ['choices' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors('choices');

        $this->actingAs($owner)
            ->confirm($item, $message, [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('choices');

        $this->assertDatabaseCount('check_point_message_acknowledgements', 0);
        $this->assertDatabaseCount('check_point_messages', 1);
    }

    public function test_the_other_answer_insists_on_its_text(): void
    {
        $owner = $this->employee();
        $manager = $this->manager();
        $item = $this->itemFor($owner);
        $message = $this->managementReply($item, $manager);

        $this->actingAs($owner)
            ->confirm($item, $message, ['choices' => ['lainnya']])
            ->assertStatus(422)
            ->assertJsonValidationErrors('note');

        // Whitespace is not an answer either.
        $this->actingAs($owner)
            ->confirm($item, $message, ['choices' => ['lainnya'], 'note' => '   '])
            ->assertStatus(422)
            ->assertJsonValidationErrors('note');
    }

    public function test_the_other_answer_is_capped_in_length(): void
    {
        $owner = $this->employee();
        $manager = $this->manager();
        $item = $this->itemFor($owner);
        $message = $this->managementReply($item, $manager);

        $this->actingAs($owner)
            ->confirm($item, $message, [
                'choices' => ['lainnya'],
                'note' => str_repeat('a', CheckPointMessage::MAX_CONFIRM_NOTE + 1),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('note');
    }

    public function test_an_unknown_answer_is_rejected(): void
    {
        $owner = $this->employee();
        $manager = $this->manager();
        $item = $this->itemFor($owner);
        $message = $this->managementReply($item, $manager);

        $this->actingAs($owner)
            ->confirm($item, $message, ['choices' => ['mungkin']])
            ->assertStatus(422)
            ->assertJsonValidationErrors('choices.0');
    }

    public function test_a_non_owner_cannot_confirm(): void
    {
        $owner = $this->employee();
        $other = $this->employee();
        $manager = $this->manager();
        $item = $this->itemFor($owner);
        $message = $this->managementReply($item, $manager);

        $this->actingAs($other)
            ->confirm($item, $message, ['choices' => ['paham']])
            ->assertForbidden();

        $this->assertDatabaseCount('check_point_message_acknowledgements', 0);
    }

    public function test_management_cannot_confirm_its_own_reply(): void
    {
        $owner = $this->employee();
        $manager = $this->manager();
        $item = $this->itemFor($owner);
        $message = $this->managementReply($item, $manager);

        $this->actingAs($manager)
            ->confirm($item, $message, ['choices' => ['paham']])
            ->assertForbidden();
    }

    public function test_an_employee_cannot_confirm_their_own_message(): void
    {
        $owner = $this->employee();
        $item = $this->itemFor($owner);
        $own = $item->messages()->create([
            'user_id' => $owner->id,
            'sender_role' => CheckPointMessage::ROLE_EMPLOYEE,
            'body' => 'Balasan saya sendiri.',
        ]);

        $this->actingAs($owner)
            ->confirm($item, $own, ['choices' => ['paham']])
            ->assertForbidden();
    }

    public function test_a_reply_from_another_item_cannot_be_confirmed_through_this_one(): void
    {
        $owner = $this->employee();
        $manager = $this->manager();
        $item = $this->itemFor($owner);
        $otherItem = $this->itemFor($owner);
        $foreign = $this->managementReply($otherItem, $manager);

        $this->actingAs($owner)
            ->confirm($item, $foreign, ['choices' => ['paham']])
            ->assertForbidden();

        $this->assertDatabaseCount('check_point_message_acknowledgements', 0);
    }

    public function test_a_retracted_reply_cannot_be_confirmed(): void
    {
        $owner = $this->employee();
        $manager = $this->manager();
        $item = $this->itemFor($owner);
        $message = $this->managementReply($item, $manager, ['retracted_at' => now()]);

        $this->actingAs($owner)
            ->confirm($item, $message, ['choices' => ['paham']])
            ->assertStatus(422);
    }

    public function test_a_new_reply_has_to_wait_for_the_confirmation(): void
    {
        $owner = $this->employee();
        $manager = $this->manager();
        $item = $this->itemFor($owner);
        $message = $this->managementReply($item, $manager);

        Notification::fake();

        $this->actingAs($owner)
            ->reply($item, ['body' => 'Siap, saya kerjakan.'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('body');

        $this->assertDatabaseCount('check_point_messages', 1);

        // Management is never blocked: only the employee owes the answer. Their
        // second reply asks for a confirmation of its own, so the door only
        // reopens once both have been answered.
        $second = $this->actingAs($manager)
            ->reply($item, ['body' => 'Mohon segera dikerjakan.'])
            ->assertCreated()
            ->json('id');

        $this->actingAs($owner)->confirm($item, $message, ['choices' => ['siap']])->assertCreated();

        $this->actingAs($owner)
            ->reply($item, ['body' => 'Masih ada yang tertunda.'])
            ->assertStatus(422);

        $this->actingAs($owner)
            ->confirm($item, CheckPointMessage::findOrFail($second), ['choices' => ['paham']])
            ->assertCreated();

        $this->actingAs($owner)
            ->reply($item, ['body' => 'Sudah saya kerjakan.'])
            ->assertCreated();
    }

    public function test_the_read_ping_stamps_unread_management_replies_once(): void
    {
        $owner = $this->employee();
        $manager = $this->manager();
        $item = $this->itemFor($owner);
        $message = $this->managementReply($item, $manager);
        $legacy = $this->managementReply($item, $manager, [
            'requires_confirmation' => false,
            'body' => 'Catatan lama.',
        ]);

        $this->actingAs($owner)
            ->stampRead($item)
            ->assertOk()
            ->assertJsonCount(1, 'read')
            ->assertJsonStructure(['read' => [$message->id]]);

        $acknowledgement = $message->fresh()->acknowledgement;

        $this->assertNotNull($acknowledgement?->read_at);
        $this->assertNull($acknowledgement?->confirmed_at, 'reading is not confirming');

        // A message that never asked for a confirmation is left alone.
        $this->assertNull($legacy->fresh()->acknowledgement);

        $this->actingAs($owner)
            ->stampRead($item)
            ->assertOk()
            ->assertJsonCount(0, 'read');

        $this->assertDatabaseCount('check_point_message_acknowledgements', 1);
    }

    public function test_a_non_owner_cannot_stamp_read(): void
    {
        $owner = $this->employee();
        $other = $this->employee();
        $manager = $this->manager();
        $item = $this->itemFor($owner);
        $this->managementReply($item, $manager);

        $this->actingAs($other)
            ->stampRead($item)
            ->assertForbidden();

        $this->assertDatabaseCount('check_point_message_acknowledgements', 0);
    }

    public function test_the_employee_page_shows_a_read_reply_and_the_management_page_does_not_pretend(): void
    {
        $owner = $this->employee();
        $manager = $this->direksiUser();
        $item = $this->itemFor($owner);
        $read = $this->managementReply($item, $manager, ['body' => 'Pesan yang sudah dibuka.']);
        $this->managementReply($item, $manager, ['body' => 'Pesan yang belum dibuka.']);

        $read->acknowledgement()->create([
            'user_id' => $owner->id,
            'read_at' => now(),
        ]);

        $this->actingAs($owner)
            ->get(route('checkpoint-user.history.show', $item->check_point_id))
            ->assertOk()
            ->assertSee('Dibaca')
            ->assertSee('Belum dibaca', false);

        $this->actingAs($manager)
            ->get(route('direksi.cp.history.show', $item->check_point_id))
            ->assertOk()
            ->assertSee('Dibaca')
            ->assertSee('Belum dibaca', false);
    }

    public function test_the_management_page_renders_a_confirmed_reply_with_its_answers(): void
    {
        $owner = $this->employee();
        $manager = $this->direksiUser();
        $item = $this->itemFor($owner);
        $message = $this->managementReply($item, $manager);

        Notification::fake();

        $this->actingAs($owner)
            ->confirm($item, $message, ['choices' => ['paham', 'lainnya'], 'note' => 'Dijelaskan besok.'])
            ->assertCreated();

        $this->actingAs($manager)
            ->get(route('direksi.cp.history.show', $item->check_point_id))
            ->assertOk()
            ->assertSee('Dikonfirmasi')
            ->assertSee('Telah Dipahami/Dimengerti')
            ->assertSee('Dijelaskan besok.')
            ->assertDontSee('data-confirm-endpoint', false)
            ->assertDontSee('data-thread-locked', false);
    }
}
