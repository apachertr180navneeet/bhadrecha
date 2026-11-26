<?php

namespace App\Imports;

use App\Models\City;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Validators\Failure;

class CityImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, WithEvents
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
        $name = isset($row['name']) ? (string) $row['name'] : null;
        $state = isset($row['state']) ? (string) $row['state'] : null;

        if (!$name || !$state) {
            $this->skipped++;
            return null;
        }

        if (City::where('name', $name)->where('state', $state)->exists()) {
            $this->skipped++;
            return null;
        }

        $this->imported++;
        return new City([
            'name'   => $name,
            'state'  => $state,
            'status' => 'active',
        ]);
    }

    public function rules(): array
    {
        return [
            'name'  => 'required|max:255',
            'state' => 'required|max:255',
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
