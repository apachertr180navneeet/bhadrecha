<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;

class FuelPumpTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['Indian Oil Pump', '12345', '123 Main Street, City', 'Rajesh Kumar', '9876543210', 'active'],
        ];
    }

    public function headings(): array
    {
        return ['name', 'number', 'address', 'owner_name', 'owner_mobile', 'status'];
    }
}
