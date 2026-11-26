<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;

class CityTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['Mumbai', 'Maharashtra', 'active'],
        ];
    }

    public function headings(): array
    {
        return ['name', 'state', 'status'];
    }
}
