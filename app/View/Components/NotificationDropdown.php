<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class NotificationDropdown extends Component
{
    public function render(): View
    {
        $user = auth()->user();

        $notifications = $user
            ? $user->notifications()
            ->latest()
            ->take(10)
            ->get()
            : collect();

        $unreadCount = $user
            ? $user->unreadNotifications()->count()
            : 0;

        return view('components.notification-dropdown', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }
}
