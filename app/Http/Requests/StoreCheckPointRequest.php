<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCheckPointRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'divisi_id' => ['required'],
            'work_order_id' => ['nullable', 'exists:work_orders,id'],
            'type_check' => ['nullable', 'string'],
            'work_mode' => ['nullable', 'in:individual,shared'],
            'participant_ids' => ['nullable', 'array'],
            'participant_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ];
    }
}
