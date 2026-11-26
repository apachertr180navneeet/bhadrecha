<?php

namespace App\Imports;

use App\Models\Driver;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Validators\Failure;

class DriverImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, WithEvents
{
    use Importable;

    protected $failures = [];
    protected $imported = 0;
    protected $skipped = 0;
    protected $headings = [];

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $this->headings = $event->getSheet()->getDelegate()->toArray()[0] ?? [];
            },
        ];
    }

    public function model(array $row)
    {
        $name = $row['name'] ?? null;
        $phone = isset($row['phone']) ? (string) $row['phone'] : null;
        $licenseNumber = isset($row['license_number']) ? (string) $row['license_number'] : null;
        $licenseExpiry = isset($row['license_expiry']) ? $row['license_expiry'] : null;

        if (!$phone || !$name || !$licenseNumber) {
            $this->skipped++;
            return null;
        }

        if (Driver::where('phone', $phone)->exists() || Driver::where('license_number', $licenseNumber)->exists()) {
            $this->skipped++;
            return null;
        }

        $this->imported++;
        return new Driver([
            'driver_id'         => isset($row['driver_id']) ? (string) $row['driver_id'] : null,
            'name'              => $name,
            'phone'             => $phone,
            'license_number'    => $licenseNumber,
            'license_expiry'    => $licenseExpiry,
            'address'           => isset($row['address']) ? (string) $row['address'] : null,
            'city'              => isset($row['city']) ? (string) $row['city'] : null,
            'state'             => isset($row['state']) ? (string) $row['state'] : null,
            'emergency_contact' => isset($row['emergency_contact']) ? (string) $row['emergency_contact'] : null,
            'status'            => 'active',
        ]);
    }

    public function rules(): array
    {
        return [
            'driver_id'         => 'nullable|max:50',
            'name'              => 'required|max:255',
            'phone'             => 'required|max:10',
            'license_number'    => 'required|max:50',
            'license_expiry'    => 'nullable',
            'address'           => 'nullable',
            'city'              => 'nullable|max:100',
            'state'             => 'nullable|max:100',
            'emergency_contact' => 'nullable|max:20',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        $this->failures = array_merge($this->failures, $failures);
    }

    public function getFailures(): array { return $this->failures; }
    public function getImportedCount(): int { return $this->imported; }
    public function getSkippedCount(): int { return $this->skipped; }
    public function getHeadings(): array { return $this->headings; }
}
