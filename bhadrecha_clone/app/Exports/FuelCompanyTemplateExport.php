<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;

class FuelCompanyTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['Indian Oil', 'active'],
        ];
    }

    public function headings(): array
    {
        return ['name', 'status'];
    }
}
