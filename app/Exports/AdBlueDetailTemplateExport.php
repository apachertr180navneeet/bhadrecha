<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;

class AdBlueDetailTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['2026-05-21', '20', '150.00', '3000.00', '120', 'cash'],
        ];
    }

    public function headings(): array
    {
        return [
            'date',
            'quantity',
            'rate',
            'amount',
            'km',
            'payment_type',
        ];
    }
}
