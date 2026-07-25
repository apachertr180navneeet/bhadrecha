<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;

class PackagingTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['Box', 'Standard cardboard box', 'active'],
        ];
    }

    public function headings(): array
    {
        return ['name', 'description', 'status'];
    }
}
