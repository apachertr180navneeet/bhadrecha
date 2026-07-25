<?php

namespace App\Imports;

use App\Models\FuelPump;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Validators\Failure;

class FuelPumpImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, WithEvents
{
    use Importable;

    protected $failures = [];
    protected $imported = 0;
    protected $skipped = 0;
    protected $headings = [];
    protected $fuelCompanyId;

    public function __construct($fuelCompanyId = null)
    {
        $this->fuelCompanyId = $fuelCompanyId;
    }

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
        $name = isset($row['name']) ? (string) $row['name'] : null;

        if (!$name) {
            $this->skipped++;
            return null;
        }

        if (FuelPump::where('name', $name)->exists()) {
            $this->skipped++;
            return null;
        }

        $this->imported++;

        return new FuelPump([
            'name'             => $name,
            'fuel_company_id'  => $this->fuelCompanyId,
            'number'           => $row['number'] ?? null,
            'address'          => $row['address'] ?? null,
            'owner_name'       => $row['owner_name'] ?? null,
            'owner_mobile'     => $row['owner_mobile'] ?? null,
            'status'           => 'active',
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|max:255',
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
