<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAbsensiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $requiresImage = $this->user()->kerjasama_id != 1
            || ! in_array($this->user()->devisi_id, [2, 3, 7, 8, 12, 14, 18], true);

        return [
            'user_id' => ['required', 'exists:users,id'],
            'kerjasama_id' => ['required'],
            'shift_id' => ['required'],
            'perlengkapan' => ['required'],
            'keterangan' => ['required'],
            'absensi_type_masuk' => ['required'],
            'absensi_type_pulang' => ['nullable'],
            'lat_user' => ['required', 'numeric', 'between:-90,90'],
            'long_user' => ['required', 'numeric', 'between:-180,180'],
            'image' => [$requiresImage ? 'required' : 'nullable'],
            'deskripsi' => ['nullable'],
            'point_id' => ['nullable'],
            'subuh' => ['nullable'], 'dzuhur' => ['nullable'], 'asar' => ['nullable'],
            'magrib' => ['nullable'], 'isya' => ['nullable'],
            'msk_lat' => ['nullable', 'max:11'], 'msk_long' => ['nullable', 'max:11'],
            'sig_lat' => ['nullable', 'max:11'], 'sig_long' => ['nullable', 'max:11'],
            'plg_lat' => ['nullable', 'max:11'], 'plg_long' => ['nullable', 'max:11'],
            'masuk' => ['nullable'], 'tukar' => ['nullable'], 'lembur' => ['nullable'],
            'terus' => ['nullable'], 'tukar_id' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return ['required' => 'Kolom :attribute tidak boleh kosong.'];
    }
}
