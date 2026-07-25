<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;

class BankMasterTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['State Bank of India', 'SBI'],
        ];
    }

    public function headings(): array
    {
        return ['name', 'code'];
    }
}
