<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;

class OtherAmountDetailTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['Loading Charges', '500.00', '2026-05-21', 'Paid at origin'],
        ];
    }

    public function headings(): array
    {
        return [
            'title',
            'amount',
            'date',
            'remark',
        ];
    }
}
