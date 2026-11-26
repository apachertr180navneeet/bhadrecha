<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;

class FuelDetailTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['2026-05-19', '50', '105.00', '120', 'cash', 'Sample remark'],
        ];
    }

    public function headings(): array
    {
        return [
            'date',
            'quantity',
            'rate',
            'km',
            'payment_type',
            'remark',
        ];
    }
}
