<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;

class UnitTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['Kg', 'Kilogram', 'active'],
        ];
    }

    public function headings(): array
    {
        return ['name', 'description', 'status'];
    }
}
