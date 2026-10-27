<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCheckPointRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'exists:users,id'],
            'divisi_id' => ['nullable'],
            'work_order_id' => ['nullable', 'exists:work_orders,id'],
            'type_check' => ['nullable', 'string'],
        ];
    }
}
