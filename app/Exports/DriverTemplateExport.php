<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;

class DriverTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['DRV001', 'Suresh Sharma', '9876543213', 'DL1234567890', '2027-05-15', '123 Driver Nagar', 'Mumbai', 'Maharashtra', '9876543214'],
        ];
    }

    public function headings(): array
    {
        return ['driver_id', 'name', 'phone', 'license_number', 'license_expiry', 'address', 'city', 'state', 'emergency_contact'];
    }
}
