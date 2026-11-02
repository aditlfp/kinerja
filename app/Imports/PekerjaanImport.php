<?php

namespace App\Imports;

use App\Models\PekerjaanCp;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PekerjaanImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $userId = $this->normalize($row['user_id'] ?? null);
        $divisiId = $this->normalize($row['divisi_id'] ?? null);
        $kerjasamaId = $this->normalize($row['kerjasama_id'] ?? null);
        $name = $this->normalize($row['name'] ?? null);
        $typeCheck = $this->normalize($row['type_check'] ?? null);
        $weightingValue = $this->normalize($row['weighting_value'] ?? null);
        return PekerjaanCp::updateOrCreate(
            [
                'user_id' => $userId,
                'divisi_id' => $divisiId,
                'name' => $name,
                'type_check' => $typeCheck,
            ],
            [
                'user_id' => $userId,
                'divisi_id' => $divisiId,
                'kerjasama_id' => $kerjasamaId,
                'name' => $name,
                'type_check' => $typeCheck,
                'weighting_value' => $weightingValue === null ? 0 : max(0, min(1000, (int) $weightingValue)),
            ]
        );
    }

    private function normalize($value)
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '' || $value === '-' || $value === 'Kosong') {
            return null;
        }

        return $value;
    }
}
