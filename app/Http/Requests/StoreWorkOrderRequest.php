<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Closure;

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
            'images' => ['nullable', 'array', 'max:7'],
            'images.*' => [
                'file',
                'max:5120',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'mp4', 'mov', 'avi', 'webm', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
                    $extension = strtolower($value->getClientOriginalExtension());
                    if (!in_array($extension, $allowed, true)) {
                        $fail('Format file tidak didukung. Gunakan gambar, video, PDF, DOCX, atau XLSX.');
                    }
                },
            ],
        ];
    }
}
