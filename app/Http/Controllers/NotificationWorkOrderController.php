<?php

namespace App\Http\Controllers;

use App\Models\CheckPoint;
use App\Models\Checklist;
use App\Models\WorkOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationWorkOrderController extends Controller
{
    public function open(
        Request $request,
        DatabaseNotification $notification
    ): RedirectResponse {
        // Pastikan notification memang milik user yang sedang login
        abort_unless(
            $notification->notifiable_id === $request->user()->id,
            403
        );

        // Tandai sudah dibaca
        $notification->markAsRead();

        // Conversation pings carry the checkpoint they belong to.
        if ($checkPointId = $notification->data['check_point_id'] ?? null) {
            return redirect()->to($this->threadUrl($request, (int) $checkPointId));
        }

        $workOrderId = $notification->data['work_order_id'];

        $workOrder = WorkOrder::findOrFail($workOrderId);

        $workOrder->update(["has_read" => 1]);

        // $data = $notification->data;
        if ($workOrder->has_complete != 1) {
            return redirect()->route('checkpoint-user.create', [
                'work_order' => $workOrder->id,
            ]);
        } else {
            $id = CheckPoint::where('work_order_id', $workOrder->id)->first();
            return redirect()->route('checkpoint-user.edit', [
                'checkpoint_user' => $id,
                'tanggal' => $workOrder->tanggal
            ]);
        }
    }

    /**
     * The screen that renders the conversation for this user. No single
     * "checkpoint detail" route serves everybody, so pick the one whose own
     * middleware will accept them: `only:MCS` reads the user's jabatan, while
     * the `direksi` middleware reads the jabatan of the user's division.
     */
    private function threadUrl(Request $request, int $checkPointId): string
    {
        $user = $request->user();

        if ($user?->jabatan?->code_jabatan === 'MCS') {
            return route('mcs.cp.history.show', $checkPointId);
        }

        if ($user?->divisi?->jabatan?->code_jabatan === 'DIREKSI') {
            return route('direksi.cp.history.show', $checkPointId);
        }

        // Everyone else is the owner, who reads their own history detail.
        return route('checkpoint-user.history.show', $checkPointId);
    }
}
