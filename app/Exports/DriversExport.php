<?php

namespace App\Exports;

use App\Models\Driver;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DriversExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Driver::all();
    }

    public function headings(): array
    {
        return ['Driver ID', 'Name', 'Phone', 'License Number', 'License Expiry', 'Address', 'City', 'State', 'Emergency Contact'];
    }

    public function map($driver): array
    {
        return [
            $driver->driver_id ?? '-',
            $driver->name,
            $driver->phone,
            $driver->license_number,
            $driver->license_expiry ? $driver->license_expiry->format('Y-m-d') : '-',
            $driver->address ?? '-',
            $driver->city ?? '-',
            $driver->state ?? '-',
            $driver->emergency_contact ?? '-',
        ];
    }
}
