<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class ApprovalNotificationService
{
    public function sendToApprovers(string $sourceCode, int $kerjasamaId, string $type, Notification $notification): void
    {
        $approvers = $this->approversFor($sourceCode);

        if ($approvers->isEmpty()) {
            return;
        }

        NotificationFacade::send(
            $this->withoutUnreadNotification($approvers, $type, $kerjasamaId),
            $notification
        );
    }

    private function approversFor(string $sourceCode): Collection
    {
        $targetCode = $this->targetCodeFor($sourceCode);

        if (!$targetCode) {
            return collect();
        }

        return User::whereHas('jabatan', function ($query) use ($targetCode) {
            $query->where('code_jabatan', $targetCode);
        })->get();
    }

    private function targetCodeFor(string $sourceCode): ?string
    {
        return match (strtoupper($sourceCode)) {
            'CO-CS' => 'SPV',
            'CO-SCR' => 'MARKETING',
            default => null,
        };
    }

    private function withoutUnreadNotification(Collection $recipients, string $type, int $kerjasamaId): Collection
    {
        return $recipients->filter(function (User $recipient) use ($type, $kerjasamaId) {
            return !$recipient->unreadNotifications()
                ->where('notification_type', $type)
                ->where('kerjasama_id', $kerjasamaId)
                ->exists();
        });
    }
}
