<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PekerjaanCpRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required',
            'devisi_id' => 'required',
            'kerjasama_id' => 'required',
            'type_check' => 'required|in:harian,mingguan,bulanan,isidental',
            'name' => 'required|string|max:255',
            'weighting_value' => 'nullable|integer|min:0|max:1000',
        ];
    }
}
