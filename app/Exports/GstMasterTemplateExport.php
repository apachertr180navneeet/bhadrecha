<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;

class GstMasterTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['GST-5', 5, '5% GST on basic goods'],
        ];
    }

    public function headings(): array
    {
        return ['gst_rate', 'percentage', 'description'];
    }
}
