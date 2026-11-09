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
            $checkpoint = null;
            if ($request->input('work_mode') === 'shared' && (int) $data['divisi_id'] === 3) {
                $checkpoint = CheckPoint::where('user_id', $data['user_id'])
                    ->whereDate('created_at', now()->toDateString())
                    ->whereHas('participants')
                    ->latest('id')
                    ->first();
            }
            $checkpoint ??= CheckPoint::create([
                'user_id' => $data['user_id'],
                'divisi_id' => $data['divisi_id'],
                'work_order_id' => $data['work_order_id'] ?? null,
                'type_check' => 'dikerjakan',
            ]);

            $this->syncService->syncFromRequest($checkpoint, $request);

            if ($request->input('work_mode') === 'shared' && (int) $data['divisi_id'] === 3) {
                $participants = collect($request->input('participant_ids', []))->push((int) $data['user_id'])->unique();
                $valid = \App\Models\User::query()
                    ->whereIn('id', $participants)
                    ->where(function ($query): void {
                        $query->where('kerjasama_id', 1)
                            ->orWhereHas('divisi', fn ($divisi) => $divisi->where('name', 'SPV-W'));
                    })
                    ->where('name', '!=', 'admin')
                    ->pluck('id');
                $checkpoint->participants()->sync($valid);
            }

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
