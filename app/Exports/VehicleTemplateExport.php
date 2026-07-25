<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;

class VehicleTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['MH01AB1234', 'Truck', 'Tata 407', 4.5, 'Rajesh Kumar', '9876543212', '2026-12-31', '2026-06-30', '2026-09-30', '2026-10-31'],
        ];
    }

    public function headings(): array
    {
        return ['vehicle_number', 'vehicle_type', 'make_model', 'capacity_tons', 'owner_name', 'owner_phone', 'insurance_expiry', 'fitness_expiry', 'permit_expiry', 'pollution_expiry'];
    }
}
