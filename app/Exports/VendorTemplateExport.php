<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;

class VendorTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['VEN001', 'ABC Traders', '9876543210', 'abc@vendor.com', '27AABCU1234D1Z1', 'Rajesh', '9988776655', '123 Industrial Area', 'Mumbai', 'Maharashtra', '400001', 'Net 30'],
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
            'contact_person',
            'contact_person_phone',
            'address',
            'city',
            'state',
            'pincode',
            'payment_terms',
        ];
    }
}
