<?php

namespace App\Http\Controllers;

use App\Models\CheckPointItem;
use App\Models\CheckPointMessage;
use App\Models\CheckPointMessageAcknowledgement;
use App\Models\User;
use App\Notifications\CheckPointReplyNotification;
use App\Services\CheckPointImageUploader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Two-way conversation on a single checkpoint item. One endpoint serves both
 * sides because the per-item thread is the same resource for both: the owner
 * (employee) replies to a reviewer's note, and DIREKSI / Manager CS reply back.
 *
 * A management reply is not only shown to the employee, it is asked back from
 * them: `read` stamps the moment the thread was actually opened, and `confirm`
 * records the checklist the employee answers it with — which is what lets the
 * reviewer see, on their own message, that it was read and answered.
 */
class CheckPointMessageController extends Controller
{
    /** Jabatan codes that may speak from the management side of a thread. */
    private const MANAGEMENT_CODES = ['DIREKSI', 'MCS'];

    /** Bytes a voice note may take (2 minutes of Opus/AAC stays far below). */
    private const MAX_VOICE_KB = 6144;

    /**
     * What a browser recorder can hand us, mapped to the extension the file is
     * stored under. finfo reports an audio-only WebM as `video/webm` and a
     * Safari recording as `audio/mp4`/`video/mp4`, so all of them are listed.
     * The extension comes from here, never from the uploaded file name.
     */
    private const VOICE_TYPES = [
        'audio/webm' => 'webm',
        'video/webm' => 'webm',
        'audio/ogg' => 'ogg',
        'video/ogg' => 'ogg',
        'audio/opus' => 'ogg',
        'audio/mp4' => 'm4a',
        'video/mp4' => 'm4a',
        'audio/x-m4a' => 'm4a',
        'audio/aac' => 'aac',
        'audio/mpeg' => 'mp3',
        'audio/wav' => 'wav',
        'audio/wave' => 'wav',
        'audio/x-wav' => 'wav',
    ];

    public function __construct(
        private readonly CheckPointImageUploader $uploader,
    ) {}

    public function store(Request $request, CheckPointItem $item): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $checkPoint = $item->checkPoint;
        $isManagement = $this->isManagement($user);
        $isOwner = $checkPoint !== null && (int) $checkPoint->user_id === (int) $user->id;

        abort_unless($isManagement || $isOwner, 403);

        $data = $request->validate([
            'body' => ['nullable', 'string', 'max:2000'],
            'images' => ['nullable', 'array', 'max:' . CheckPointMessage::MAX_IMAGES],
            'images.*' => ['image'],
            'audio' => ['nullable', 'file', 'max:' . self::MAX_VOICE_KB],
            // The browser measures the recording and reports it with the blob;
            // a MediaRecorder file carries no duration the server can read.
            'audio_duration' => [
                'nullable',
                'integer',
                'min:1',
                'max:' . CheckPointMessage::MAX_AUDIO_SECONDS,
                'required_with:audio',
            ],
        ], [
            'audio.max' => 'Voice note terlalu besar (maks. ' . intdiv(self::MAX_VOICE_KB, 1024) . ' MB).',
            'audio_duration.max' => 'Voice note maksimal ' . CheckPointMessage::MAX_AUDIO_SECONDS . ' detik.',
            'audio_duration.required_with' => 'Durasi voice note tidak terbaca. Coba rekam ulang.',
        ]);

        $uploads = $this->uploads($request);
        $audio = $request->file('audio');
        $voiceExtension = $audio instanceof UploadedFile ? $this->voiceExtension($audio) : null;

        // Laravel's `mimetypes` rule cannot see an audio-only WebM through
        // finfo, so the container is checked here against the same map that
        // decides the stored extension.
        if ($audio instanceof UploadedFile && $voiceExtension === null) {
            throw ValidationException::withMessages([
                'audio' => 'Voice note harus berupa rekaman suara (webm, ogg, mp4, mp3).',
            ]);
        }

        $body = trim((string) ($data['body'] ?? ''));

        // A message with no words, no proof and no recording is not a reply.
        if ($body === '' && $uploads === [] && $audio === null) {
            throw ValidationException::withMessages([
                'body' => 'Tulis balasan, lampirkan minimal satu foto, atau rekam voice note.',
            ]);
        }

        // A management reply is owed a checklist before the employee moves on to
        // a new reply. Enforced here and not only in the composer, so the rule
        // holds for a page left open, for a replayed request, and for the
        // non-JavaScript POST fallback alike.
        if (! $isManagement) {
            $pending = $this->pendingConfirmations($item);

            if ($pending > 0) {
                throw ValidationException::withMessages([
                    'body' => 'Konfirmasi dulu ' . $pending . ' pesan atasan sebelum mengirim balasan baru.',
                ]);
            }
        }

        $message = DB::transaction(function () use ($item, $user, $isManagement, $body, $uploads, $audio, $voiceExtension, $data): CheckPointMessage {
            $message = $item->messages()->create([
                'user_id' => $user->id,
                'sender_role' => $isManagement
                    ? CheckPointMessage::ROLE_MANAGEMENT
                    : CheckPointMessage::ROLE_EMPLOYEE,
                // Every management reply asks to be confirmed; an employee
                // message never does — theirs is the answer, not the question.
                'requires_confirmation' => $isManagement,
                'body' => $body === '' ? null : $body,
            ]);

            if ($audio !== null && $voiceExtension !== null) {
                $message->forceFill([
                    'audio_path' => $this->storeVoice($audio, $voiceExtension),
                    'audio_duration' => (int) $data['audio_duration'],
                ])->save();
            }

            foreach ($uploads as $urutan => $file) {
                $message->images()->create([
                    'path' => $this->uploader->store($file),
                    'urutan' => $urutan,
                ]);
            }

            // Answering a rejected item resubmits it, so it returns to the
            // reviewer's queue. An accepted item stays accepted — a reply
            // there is clarification, not a new submission.
            if (! $isManagement && $item->approve_status === 'denied') {
                $item->approve_status = 'proccess';
                $item->save();
            }

            return $message;
        });

        $this->notifyCounterpart($message, $item, $isManagement);

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $message->id,
                'sender_role' => $message->sender_role,
                'body' => $message->body,
                'approve_status' => $item->approve_status,
                'images' => $message->images->pluck('path')->all(),
                'audio_path' => $message->audio_path,
                'audio_duration' => $message->audio_duration,
            ], 201);
        }

        return back()->with('success', 'Balasan berhasil dikirim.');
    }

    /**
     * Record that the employee has opened the thread: every management reply on
     * this item that still lacks a read stamp gets one.
     *
     * The browser only calls this once the thread is genuinely on screen (see
     * the view), because a history page stacks many items and marking them all
     * read on page load would prove nothing. Idempotent: a second call stamps
     * nothing and reports nothing.
     */
    public function read(Request $request, CheckPointItem $item): JsonResponse
    {
        $user = $request->user();

        abort_unless($this->ownsItem($item, $user), 403);

        $now = now();
        $stamped = [];

        foreach ($this->unreadManagementMessages($item)->get() as $message) {
            CheckPointMessageAcknowledgement::updateOrCreate(
                ['check_point_message_id' => $message->id],
                ['user_id' => $user->id, 'read_at' => $now],
            );

            $stamped[$message->id] = $now->locale('id')->translatedFormat('H.i');
        }

        return response()->json(['read' => $stamped]);
    }

    /**
     * Answer one management reply with the confirmation checklist — the "telah
     * dipahami" / "siap dilaksanakan" / "lainnya" answers, one, two or all three
     * at once.
     *
     * The answers travel back to management twice on purpose: as a normal thread
     * message (so it notifies and reads like every other reply) and as the status
     * stamped on the message being answered (so the reviewer sees "Dikonfirmasi"
     * on their own note).
     */
    public function confirm(Request $request, CheckPointItem $item, CheckPointMessage $message): JsonResponse
    {
        $user = $request->user();

        // Only the employee who owns the checkpoint confirms: the request is
        // that the subordinate proves they read the superior's reply. Management
        // never confirms its own note.
        abort_unless($this->ownsItem($item, $user), 403);
        abort_unless((int) $message->check_point_item_id === (int) $item->id, 403);
        abort_unless($message->isFromManagement(), 403);
        abort_if($message->isRetracted(), 422, 'Pesan ini sudah ditarik.');

        $data = $request->validate([
            'choices' => ['required', 'array', 'min:1'],
            'choices.*' => ['string', 'in:' . implode(',', array_keys(CheckPointMessage::CONFIRM_CHOICES))],
            // "Lainnya" is the only answer that carries words, so its box is the
            // only one that insists on them.
            'note' => [
                Rule::requiredIf(fn (): bool => in_array('lainnya', (array) $request->input('choices', []), true)),
                'nullable',
                'string',
                'max:' . CheckPointMessage::MAX_CONFIRM_NOTE,
            ],
        ], [
            'choices.required' => 'Pilih minimal satu jawaban konfirmasi.',
            'choices.min' => 'Pilih minimal satu jawaban konfirmasi.',
            'choices.*.in' => 'Pilihan konfirmasi tidak dikenal.',
            'note.required' => 'Isi jawaban untuk pilihan "Lainnya".',
            'note.max' => 'Jawaban "Lainnya" maksimal ' . CheckPointMessage::MAX_CONFIRM_NOTE . ' karakter.',
        ]);

        $choices = array_values(array_unique($data['choices']));
        $note = trim((string) ($data['note'] ?? ''));
        $note = $note === '' ? null : $note;

        $existing = $message->acknowledgement;

        // A double tap on "Kirim Konfirmasi" must not answer the same note twice:
        // the first answer stands, and the client is handed it back unchanged.
        if ($existing?->isConfirmed()) {
            return response()->json($this->confirmationPayload($item, $message, $existing));
        }

        $readAt = $existing?->read_at ?? now();

        [$acknowledgement, $confirmation] = DB::transaction(function () use ($item, $user, $message, $choices, $note, $readAt): array {
            $confirmation = $item->messages()->create([
                'user_id' => $user->id,
                'sender_role' => CheckPointMessage::ROLE_EMPLOYEE,
                'requires_confirmation' => false,
                'body' => $this->confirmationBody($choices, $note),
            ]);

            $acknowledgement = CheckPointMessageAcknowledgement::updateOrCreate(
                ['check_point_message_id' => $message->id],
                [
                    'confirmation_message_id' => $confirmation->id,
                    'user_id' => $user->id,
                    // Answering means it has been read, whether or not the read
                    // ping won the race.
                    'read_at' => $readAt,
                    'confirmed_at' => now(),
                    'choices' => $choices,
                    'note' => $note,
                ],
            );

            return [$acknowledgement, $confirmation];
        });

        $this->notifyConfirmation($confirmation, $item);

        return response()->json($this->confirmationPayload($item, $message, $acknowledgement), 201);
    }

    /**
     * Pull a message back ("tarik pesan"). Only the person who wrote it may do
     * so — not the other side, and not management on someone else's behalf —
     * and they must still have access to the thread.
     *
     * The row survives as a tombstone: the thread keeps its shape and its order,
     * but the body, the photos and the voice note are gone for everyone. The
     * item's approve_status is left alone, so retracting a reply cannot be used
     * to pull a resubmitted row out of the reviewer's queue.
     */
    public function destroy(Request $request, CheckPointMessage $message): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $item = $message->item;
        $isManagement = $this->isManagement($user);

        abort_unless($isManagement || ($item !== null && $this->ownsItem($item, $user)), 403);
        abort_unless((int) $message->user_id === (int) $user->id, 403);

        if (! $message->isRetracted()) {
            DB::transaction(function () use ($message): void {
                $message->images()->get()->each(function ($image): void {
                    Storage::disk('public')->delete('images/' . $image->path);
                    $image->delete();
                });

                if (filled($message->audio_path)) {
                    Storage::disk('public')->delete(
                        CheckPointMessage::VOICE_DIRECTORY . '/' . $message->audio_path,
                    );
                }

                $message->forceFill([
                    'body' => null,
                    'audio_path' => null,
                    'audio_duration' => null,
                    'retracted_at' => now(),
                ])->save();
            });
        }

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $message->id,
                'body' => null,
                'audio_path' => null,
                'retracted_at' => $message->retracted_at?->toIso8601String(),
                'approve_status' => $item?->approve_status,
            ]);
        }

        return back()->with('success', 'Pesan berhasil ditarik.');
    }

    /** The employee whose checkpoint this item belongs to, and nobody else. */
    private function ownsItem(CheckPointItem $item, ?User $user): bool
    {
        return $user !== null
            && $item->checkPoint !== null
            && (int) $item->checkPoint->user_id === (int) $user->id;
    }

    /**
     * Management replies on this item that still lack a read stamp.
     *
     * Typed as the relation rather than a plain Builder: `messages()` hands back
     * a HasMany whose `where` calls return the relation itself, and PHP would
     * refuse the narrower claim.
     */
    private function unreadManagementMessages(CheckPointItem $item): HasMany
    {
        return $item->messages()
            ->where('sender_role', CheckPointMessage::ROLE_MANAGEMENT)
            ->where('requires_confirmation', true)
            ->whereNull('retracted_at')
            ->whereDoesntHave('acknowledgement', fn (Builder $query) => $query->whereNotNull('read_at'));
    }

    /**
     * How many management replies on this item still owe the employee's
     * checklist. A message that was retracted stops counting: there is nothing
     * left in it to read or confirm.
     */
    private function pendingConfirmations(CheckPointItem $item): int
    {
        return $item->messages()
            ->where('sender_role', CheckPointMessage::ROLE_MANAGEMENT)
            ->where('requires_confirmation', true)
            ->whereNull('retracted_at')
            ->whereDoesntHave('acknowledgement', fn (Builder $query) => $query->whereNotNull('confirmed_at'))
            ->count();
    }

    /**
     * The reply text that carries the checklist back into the thread. It is the
     * plain-text fallback — a notification preview, a page with JavaScript off,
     * a search — so it uses a bullet rather than an emoji: the icons the bubble
     * really shows are Remix Icon glyphs, and those cannot live inside a string
     * the view escapes. The answers themselves are read from the stored
     * `choices`, which is what the thread renders.
     *
     * @param  array<int, string>  $choices
     */
    private function confirmationBody(array $choices, ?string $note): string
    {
        $lines = ['Konfirmasi pesan atasan:'];

        foreach (CheckPointMessage::confirmChoiceLabels($choices) as $label) {
            $lines[] = '• ' . $label;
        }

        if ($note !== null) {
            $lines[] = 'Catatan: ' . $note;
        }

        return implode("\n", $lines);
    }

    /**
     * Everything the page needs to stamp the answered message and to append the
     * confirmation bubble without a reload — shaped like the reply payload so
     * the same renderer can draw it.
     *
     * @return array<string, mixed>
     */
    private function confirmationPayload(CheckPointItem $item, CheckPointMessage $message, CheckPointMessageAcknowledgement $acknowledgement): array
    {
        $confirmation = $acknowledgement->confirmation;
        $answers = $acknowledgement->answers();

        return [
            'message_id' => $message->id,
            'id' => $confirmation?->id,
            'sender_role' => CheckPointMessage::ROLE_EMPLOYEE,
            'body' => $confirmation?->body,
            'images' => [],
            'audio_path' => null,
            'audio_duration' => null,
            // Marks the reply as a checklist answer, so the thread renders it as
            // an icon list instead of the plain-text body it also carries.
            'confirmation' => true,
            'choices' => $answers,
            'choice_labels' => CheckPointMessage::confirmChoiceLabels($answers),
            'choice_details' => CheckPointMessage::confirmChoiceDetails($answers),
            'note_icon' => CheckPointMessage::CONFIRM_NOTE_ICON,
            'note' => $acknowledgement->note,
            'read_at_label' => $acknowledgement->read_at?->locale('id')->translatedFormat('H.i'),
            'confirmed_at_label' => $acknowledgement->confirmed_at?->locale('id')->translatedFormat('H.i'),
            'pending' => $this->pendingConfirmations($item),
            'approve_status' => $item->approve_status,
        ];
    }

    /**
     * The confirmation goes to the same reviewers an employee reply would, under
     * its own heading: this is the acknowledgement they asked to see, not just
     * another reply to read.
     */
    private function notifyConfirmation(CheckPointMessage $message, CheckPointItem $item): void
    {
        $label = $item->pekerjaanCp?->name ?? $item->input_manual ?? 'Pekerjaan';

        Notification::send($this->managementRecipients($message->user_id), new CheckPointReplyNotification(
            title: 'Konfirmasi dari karyawan',
            message: 'Karyawan mengonfirmasi catatan pada "' . $label . '".',
            checkPointId: (int) $item->check_point_id,
            itemId: (int) $item->id,
        ));
    }

    /**
     * Mirror of the Direksi checkpoint check: a user is management when their
     * `jabatan` (or the jabatan of their division) carries a management code.
     */
    private function isManagement(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $code = $user->jabatan?->code_jabatan ?? $user->divisi?->jabatan?->code_jabatan;

        return in_array($code, self::MANAGEMENT_CODES, true);
    }

    /**
     * Recipients of an employee reply: everyone who reviews checkpoints.
     * The sender is skipped so nobody notifies themselves.
     *
     * @return Collection<int, User>
     */
    private function managementRecipients(?int $excludeUserId)
    {
        return User::query()
            ->when($excludeUserId, fn (Builder $query) => $query->whereKeyNot($excludeUserId))
            ->where(function (Builder $query): void {
                $query
                    ->whereHas('jabatan', fn (Builder $jabatan) => $jabatan->whereIn('code_jabatan', self::MANAGEMENT_CODES))
                    ->orWhereHas('divisi.jabatan', fn (Builder $jabatan) => $jabatan->whereIn('code_jabatan', self::MANAGEMENT_CODES));
            })
            ->get();
    }

    private function notifyCounterpart(CheckPointMessage $message, CheckPointItem $item, bool $fromManagement): void
    {
        $label = $item->pekerjaanCp?->name ?? $item->input_manual ?? 'Pekerjaan';

        if ($fromManagement) {
            $item->checkPoint?->user?->notify(new CheckPointReplyNotification(
                title: 'Catatan baru pada bukti pekerjaan',
                message: 'Atasan menanggapi "' . $label . '".',
                checkPointId: (int) $item->check_point_id,
                itemId: (int) $item->id,
            ));

            return;
        }

        Notification::send($this->managementRecipients($message->user_id), new CheckPointReplyNotification(
            title: 'Balasan baru dari karyawan',
            message: 'Karyawan membalas catatan pada "' . $label . '".',
            checkPointId: (int) $item->check_point_id,
            itemId: (int) $item->id,
        ));
    }

    /**
     * Attachments arrive either as a list (`images[]`) or, for a single-file
     * input, as one bare UploadedFile.
     *
     * @return array<int, UploadedFile>
     */
    private function uploads(Request $request): array
    {
        $files = $request->file('images', []);

        if ($files instanceof UploadedFile) {
            $files = [$files];
        }

        return array_values(array_filter(
            (array) $files,
            fn ($file) => $file instanceof UploadedFile,
        ));
    }

    /**
     * The extension a recording should be stored under, or null when the file
     * is not one of the containers a browser recorder produces. Both the
     * sniffed and the declared type are considered, because finfo reports an
     * audio-only WebM as `video/webm` and some Ogg streams as `application/ogg`.
     */
    private function voiceExtension(UploadedFile $file): ?string
    {
        foreach ([$file->getMimeType(), $file->getClientMimeType()] as $mime) {
            $extension = self::VOICE_TYPES[strtolower((string) $mime)] ?? null;

            if ($extension !== null) {
                return $extension;
            }
        }

        return null;
    }

    /**
     * Voice notes are stored as-is: they need no downscaling, and the browser
     * already picked the codec. The name is generated, so the extension comes
     * from the validated container and never from the uploaded file name.
     */
    private function storeVoice(UploadedFile $file, string $extension): string
    {
        $name = 'voice' . md5(uniqid((string) mt_rand(), true)) . '.' . $extension;

        $file->storeAs(CheckPointMessage::VOICE_DIRECTORY, $name, 'public');

        return $name;
    }
}
