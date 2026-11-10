<?php

namespace App\Services;

use App\Models\User;
use App\Models\WorkOrder;
use App\Notifications\WorkOrderNotification;
use Illuminate\Http\UploadedFile;

class WorkOrderService
{
    public function store(array $data, User $creator): WorkOrder
    {
        $images = $data['images'] ?? [];
        unset($data['images']);
        $workOrder = WorkOrder::create($data + ['created_by' => $creator->id]);
        foreach ($images as $index => $image) {
            /** @var UploadedFile $image */
            $workOrder->images()->create([
                'path' => $image->store('work-orders', 'public'),
                'sort_order' => $index,
            ]);
        }
        $user = User::findOrFail($data['user_id']);

        $user->notify(new WorkOrderNotification(
            workOrderId: (string) $workOrder->id,
            title: 'Pekerjaan Baru',
            message: 'Ada pekerjaan baru yang perlu kamu proses.',
        ));

        return $workOrder->setRelation('user', $user);
    }
}
