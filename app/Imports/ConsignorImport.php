<?php

namespace App\Imports;

use App\Models\Consignor;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Validators\Failure;

class ConsignorImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, WithEvents
{
    use Importable;

    protected $companyId;
    protected $branchId;
    protected $failures = [];
    protected $imported = 0;
    protected $skipped = 0;
    protected $headings = [];

    public function __construct($companyId, $branchId = null)
    {
        $this->companyId = $companyId;
        $this->branchId = $branchId;
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
        $name = $row['name'] ?? null;
        $phone = isset($row['phone']) ? (string) $row['phone'] : null;
        $vendorCode = isset($row['vendor_code']) ? (string) $row['vendor_code'] : null;

        if (!$phone || !$name) {
            $this->skipped++;
            return null;
        }

        $existing = Consignor::where('phone', $phone)->first();
        if ($existing) {
            $this->skipped++;
            return null;
        }

        if ($vendorCode && Consignor::where('vendor_code', $vendorCode)->exists()) {
            $this->skipped++;
            return null;
        }

        $this->imported++;
        return new Consignor([
            'company_id'  => $this->companyId,
            'branch_id'   => $this->branchId,
            'vendor_code' => $vendorCode,
            'name'        => $name,
            'phone'       => $phone,
            'email'       => $row['email'] ?? null,
            'gstin'       => isset($row['gstin']) ? (string) $row['gstin'] : null,
            'address'     => isset($row['address']) ? (string) $row['address'] : null,
            'city'        => isset($row['city']) ? (string) $row['city'] : null,
            'state'       => isset($row['state']) ? (string) $row['state'] : null,
            'pincode'     => isset($row['pincode']) ? (string) $row['pincode'] : null,
            'status'      => 'active',
        ]);
    }

    public function rules(): array
    {
        return [
            'name'  => 'required|max:255',
            'phone' => 'required|max:10',
            'email' => 'nullable|email|max:255',
            'gstin' => 'nullable|max:20',
            'vendor_code' => 'nullable|max:50',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        $this->failures = array_merge($this->failures, $failures);
    }

    public function getFailures(): array
    {
        return $this->failures;
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }

    public function getSkippedCount(): int
    {
        return $this->skipped;
    }

    public function getHeadings(): array
    {
        return $this->headings;
    }
}
