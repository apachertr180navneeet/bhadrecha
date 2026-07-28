<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;

class AdvanceDetailTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['2026-06-18', '1000.00', 'cash', 'Sample remark'],
        ];
    }

    public function headings(): array
    {
        return [
            'date',
            'advance_amount',
            'payment_type',
            'remark',
        ];
    }
}
