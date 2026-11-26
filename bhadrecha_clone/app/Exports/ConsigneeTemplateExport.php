<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;

class ConsigneeTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['ABC Logistics', '9876543211', 'info@abclogistics.com', '27AABCU1234D1Z1', '456 Indl Area', 'Delhi', 'Delhi', '110001'],
        ];
    }

    public function headings(): array
    {
        return ['name', 'phone', 'email', 'gstin', 'address', 'city', 'state', 'pincode'];
    }
}
