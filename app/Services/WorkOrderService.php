<?php

namespace App\Services;

use App\Models\User;
use App\Models\WorkOrder;
use App\Notifications\WorkOrderNotification;

class WorkOrderService
{
    public function store(array $data, User $creator): WorkOrder
    {
        $workOrder = WorkOrder::create($data + ['created_by' => $creator->id]);
        $user = User::findOrFail($data['user_id']);

        $user->notify(new WorkOrderNotification(
            workOrderId: (string) $workOrder->id,
            title: 'Pekerjaan Baru',
            message: 'Ada pekerjaan baru yang perlu kamu proses.',
        ));

        return $workOrder->setRelation('user', $user);
    }
}
