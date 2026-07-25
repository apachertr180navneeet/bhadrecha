<?php

namespace App\Imports;

use App\Models\Vehicle;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Validators\Failure;

class VehicleImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, WithEvents
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
        $vehicleNumber = isset($row['vehicle_number']) ? (string) $row['vehicle_number'] : null;

        if (!$vehicleNumber) {
            $this->skipped++;
            return null;
        }

        if (Vehicle::where('vehicle_number', $vehicleNumber)->exists()) {
            $this->skipped++;
            return null;
        }

        $this->imported++;
        return new Vehicle([
            'vehicle_number'  => $vehicleNumber,
            'vehicle_type'    => isset($row['vehicle_type']) ? (string) $row['vehicle_type'] : null,
            'make_model'      => isset($row['make_model']) ? (string) $row['make_model'] : null,
            'capacity_tons'   => isset($row['capacity_tons']) ? (float) $row['capacity_tons'] : 0,
            'owner_name'      => isset($row['owner_name']) ? (string) $row['owner_name'] : null,
            'owner_phone'     => isset($row['owner_phone']) ? (string) $row['owner_phone'] : null,
            'insurance_expiry'=> isset($row['insurance_expiry']) ? $row['insurance_expiry'] : null,
            'fitness_expiry'  => isset($row['fitness_expiry']) ? $row['fitness_expiry'] : null,
            'permit_expiry'   => isset($row['permit_expiry']) ? $row['permit_expiry'] : null,
            'pollution_expiry'=> isset($row['pollution_expiry']) ? $row['pollution_expiry'] : null,
            'status'          => 'active',
        ]);
    }

    public function rules(): array
    {
        return [
            'vehicle_number' => 'required|max:20',
            'vehicle_type'   => 'nullable|max:50',
            'make_model'     => 'nullable|max:100',
            'capacity_tons'  => 'nullable|numeric',
            'owner_name'     => 'nullable|max:255',
            'owner_phone'    => 'nullable|max:20',
            'insurance_expiry'  => 'nullable',
            'fitness_expiry'    => 'nullable',
            'permit_expiry'     => 'nullable',
            'pollution_expiry'  => 'nullable',
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
