<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;

class FastTagTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['2026-05-19 10:30:00', 'Toll payment', 'TXN001', 'Location Name', '75.00', '75.00'],
        ];
    }

    public function headings(): array
    {
        return [
            'transaction_time',
            'description',
            'transaction_id',
            'location',
            'one_way',
            'return',
        ];
    }
}
