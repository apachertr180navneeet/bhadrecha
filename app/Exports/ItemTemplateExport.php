<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;

class ItemTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['Steel Rods', 'TMT steel reinforcement bars', 'active'],
        ];
    }

    public function headings(): array
    {
        return ['name', 'description', 'status'];
    }
}
