<?php

namespace App\Exports;

use App\Models\Vehicle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VehiclesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Vehicle::all();
    }

    public function headings(): array
    {
        return ['Vehicle Number', 'Type', 'Make/Model', 'Capacity (Tons)', 'Owner Name', 'Owner Phone', 'Insurance Expiry', 'Fitness Expiry', 'Permit Expiry', 'Pollution Expiry'];
    }

    public function map($vehicle): array
    {
        return [
            $vehicle->vehicle_number,
            $vehicle->vehicle_type ?? '-',
            $vehicle->make_model ?? '-',
            $vehicle->capacity_tons,
            $vehicle->owner_name ?? '-',
            $vehicle->owner_phone ?? '-',
            $vehicle->insurance_expiry ? $vehicle->insurance_expiry->format('Y-m-d') : '-',
            $vehicle->fitness_expiry ? $vehicle->fitness_expiry->format('Y-m-d') : '-',
            $vehicle->permit_expiry ? $vehicle->permit_expiry->format('Y-m-d') : '-',
            $vehicle->pollution_expiry ? $vehicle->pollution_expiry->format('Y-m-d') : '-',
        ];
    }
}
