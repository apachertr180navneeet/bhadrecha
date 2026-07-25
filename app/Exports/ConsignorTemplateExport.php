<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;

class ConsignorTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['VENDOR001', 'ABC Transport', '9876543210', 'abc@example.com', '27AABCU1234D1Z1', '123 Main St', 'Mumbai', 'Maharashtra', '400001'],
        ];
    }

    public function headings(): array
    {
        return [
            'vendor_code',
            'name',
            'phone',
            'email',
            'gstin',
            'address',
            'city',
            'state',
            'pincode',
        ];
    }
}
