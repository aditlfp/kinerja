<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NewsUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Sama dengan NewsRequest, kecuali gambar boleh dikosongkan karena edit
     * boleh dilakukan tanpa mengganti foto berita.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'image' => 'nullable|image|mimes:jpg,jpeg,png,svg,gif|max:2048',
            'tanggal_lihat' => 'required',
            'tanggal_tutup' => 'required',
            'tanggal_muncul' => 'required|array',
            'tanggal_muncul.*' => 'integer|min:1|max:31',
        ];
    }
}
