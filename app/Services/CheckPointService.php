<?php

namespace App\Services;

use App\Models\CheckPoint;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckPointService
{
    public function __construct(private readonly CheckPointSyncService $syncService) {}

    public function store(array $data, Request $request): CheckPoint
    {
        return DB::transaction(function () use ($data, $request): CheckPoint {
            $checkpoint = CheckPoint::create([
                'user_id' => $data['user_id'],
                'divisi_id' => $data['divisi_id'],
                'work_order_id' => $data['work_order_id'] ?? null,
                'type_check' => 'dikerjakan',
            ]);

            $this->syncService->syncFromRequest($checkpoint, $request);

            if (! empty($data['work_order_id'])) {
                WorkOrder::findOrFail($data['work_order_id'])->update(['has_complete' => 1]);
            }

            return $checkpoint;
        });
    }

    /**
     * @param  bool  $evaluationRowsOnly  Weekly window is closed: only rows with
     *                                    a Direksi evaluation note may change.
     */
    public function update(CheckPoint $checkpoint, Request $request, bool $evaluationRowsOnly = false): CheckPoint
    {
        return DB::transaction(function () use ($checkpoint, $request, $evaluationRowsOnly): CheckPoint {
            if ($evaluationRowsOnly) {
                $this->syncService->syncEvaluatedRows($checkpoint, $request);
            } else {
                $this->syncService->syncFromRequest($checkpoint, $request);
            }
            $checkpoint->type_check ??= 'dikerjakan';
            $checkpoint->save();
            return $checkpoint;
        });
    }
}
