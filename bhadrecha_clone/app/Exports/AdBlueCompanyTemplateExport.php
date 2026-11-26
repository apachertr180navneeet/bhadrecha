<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;

class AdBlueCompanyTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['Sample AdBlue Co', 'active'],
        ];
    }

    public function headings(): array
    {
        return ['name', 'status'];
    }
}
