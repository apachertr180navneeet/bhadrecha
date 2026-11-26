<?php

namespace App\Imports;

use App\Models\GstMaster;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Validators\Failure;

class GstMasterImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, WithEvents
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
        $gstRate = isset($row['gst_rate']) ? (string) $row['gst_rate'] : null;
        $percentage = isset($row['percentage']) ? (float) $row['percentage'] : null;

        if (!$gstRate || $percentage === null) {
            $this->skipped++;
            return null;
        }

        if (GstMaster::where('gst_rate', $gstRate)->exists()) {
            $this->skipped++;
            return null;
        }

        $this->imported++;
        return new GstMaster([
            'gst_rate'    => $gstRate,
            'percentage'  => $percentage,
            'description' => $row['description'] ?? null,
            'status'      => 'active',
        ]);
    }

    public function rules(): array
    {
        return [
            'gst_rate'    => 'required|max:50',
            'percentage'  => 'required|numeric|min:0|max:100',
            'description' => 'nullable|max:500',
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
