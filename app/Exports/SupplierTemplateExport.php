<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;

class SupplierTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['ABC Suppliers', '9876543210', 'abc@supplier.com', '27AABCU1234D1Z1', 'Ramesh', '123 Industrial Area', 'Mumbai', 'Maharashtra', '400001'],
        ];
    }

    public function headings(): array
    {
        return [
            'name',
            'phone',
            'email',
            'gstin',
            'contact_person',
            'address',
            'city',
            'state',
            'pincode',
        ];
    }
}
