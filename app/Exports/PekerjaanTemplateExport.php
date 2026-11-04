<?php

namespace App\Exports;

use App\Models\PekerjaanCp;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PekerjaanTemplateExport implements FromCollection, WithHeadings
{
    public function headings(): array
    {
        return [
            'id',
            'user_id',
            'divisi_id',
            'kerjasama_id',
            'name',
            'type_check',
            'weighting_value',
        ];
    }

    public function collection()
    {
        return PekerjaanCp::query()
            ->select([
                'id',
                'user_id',
                'divisi_id',
                'kerjasama_id',
                'name',
                'type_check',
                'weighting_value',
            ])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'user_id' => $item->user_id ?? 'Kosong',
                    'divisi_id' => $item->divisi_id ?? 'Kosong',
                    'kerjasama_id' => $item->kerjasama_id ?? 'Kosong',
                    'name' => $item->name ?? 'Kosong',
                    'type_check' => $item->type_check ?? 'Kosong',
                    'weighting_value' => $item->weighting_value ?? 0,
                ];
            });
    }
}
