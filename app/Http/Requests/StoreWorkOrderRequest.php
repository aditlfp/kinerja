<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'tanggal' => ['required', 'date', 'after_or_equal:today'],
            'deskripsi' => ['required', 'string', 'max:5000'],
        ];
    }
}
