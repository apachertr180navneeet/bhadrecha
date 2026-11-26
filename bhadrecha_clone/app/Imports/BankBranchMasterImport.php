<?php

namespace App\Imports;

use App\Models\BankBranchMaster;
use App\Models\BankMaster;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Validators\Failure;

class BankBranchMasterImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, WithEvents
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
        $bankCode = isset($row['bank_code']) ? trim((string) $row['bank_code']) : null;
        $branchName = isset($row['branch_name']) ? trim((string) $row['branch_name']) : null;
        $ifsc = isset($row['ifsc']) ? trim((string) $row['ifsc']) : null;

        if (!$bankCode || !$branchName || !$ifsc) {
            $this->skipped++;
            return null;
        }

        $bank = BankMaster::where('code', $bankCode)->where('status', 'active')->first();
        if (!$bank) {
            $this->skipped++;
            return null;
        }

        if (BankBranchMaster::where('ifsc', $ifsc)->exists()) {
            $this->skipped++;
            return null;
        }

        $this->imported++;
        return new BankBranchMaster([
            'bank_id'     => $bank->id,
            'branch_name' => $branchName,
            'ifsc'        => $ifsc,
            'address'     => $row['address'] ?? null,
            'status'      => 'active',
        ]);
    }

    public function rules(): array
    {
        return [
            'bank_code'   => 'required|max:50',
            'branch_name' => 'required|max:255',
            'ifsc'        => 'required|max:20',
            'address'     => 'nullable|max:500',
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
