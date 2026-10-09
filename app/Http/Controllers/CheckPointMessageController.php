<?php

namespace App\Http\Controllers;

use App\Models\CheckPointItem;
use App\Models\CheckPointMessage;
use App\Models\User;
use App\Notifications\CheckPointReplyNotification;
use App\Services\CheckPointImageUploader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

/**
 * Two-way conversation on a single checkpoint item. One endpoint serves both
 * sides because the per-item thread is the same resource for both: the owner
 * (employee) replies to a reviewer's note, and DIREKSI / Manager CS reply back.
 */
class CheckPointMessageController extends Controller
{
    /** Jabatan codes that may speak from the management side of a thread. */
    private const MANAGEMENT_CODES = ['DIREKSI', 'MCS'];

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
        ]);

        $uploads = $this->uploads($request);
        $body = trim((string) ($data['body'] ?? ''));

        // A message with neither words nor proof is not a reply.
        if ($body === '' && $uploads === []) {
            throw ValidationException::withMessages([
                'body' => 'Tulis balasan atau lampirkan minimal satu foto.',
            ]);
        }

        $message = DB::transaction(function () use ($item, $user, $isManagement, $body, $uploads): CheckPointMessage {
            $message = $item->messages()->create([
                'user_id' => $user->id,
                'sender_role' => $isManagement
                    ? CheckPointMessage::ROLE_MANAGEMENT
                    : CheckPointMessage::ROLE_EMPLOYEE,
                'body' => $body === '' ? null : $body,
            ]);

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
            ], 201);
        }

        return back()->with('success', 'Balasan berhasil dikirim.');
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
}
