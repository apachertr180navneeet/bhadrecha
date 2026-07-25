<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;

class BankBranchMasterTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['SBI', 'Main Branch', 'SBIN0001234', '123 Main St, City'],
        ];
    }

    public function headings(): array
    {
        return ['bank_code', 'branch_name', 'ifsc', 'address'];
    }
}
