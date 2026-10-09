<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Database pings for the checkpoint conversation: management hears about an
 * employee reply, the employee hears about a management note.
 */
class CheckPointReplyNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public ?int $checkPointId = null,
        public ?int $itemId = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'check_point_id' => $this->checkPointId,
            'item_id' => $this->itemId,
            'icon' => 'ri-chat-3-line',
        ];
    }
}
