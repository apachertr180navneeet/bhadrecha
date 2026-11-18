<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CashLedgerExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $entries;
    protected $summary;

    public function __construct($entries, array $summary = [])
    {
        $this->entries = $entries;
        $this->summary = $summary;
    }

    public function headings(): array
    {
        return [
            'S.No',
            'Date',
            'Voucher / Ref No',
            'Account Name',
            'Branch',
            'Category',
            'Particulars / Description',
            'Debit (Expense) ₹',
            'Credit (Deposit) ₹',
            'Balance ₹',
            'Payment Mode',
            'Recorded By',
        ];
    }

    public function array(): array
    {
        $rows = [];
        $runningBalance = 0;
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($this->entries as $index => $item) {
            $amount = (float)$item->amount;
            $isCredit = strtolower($item->transaction_type) === 'credit';

            $debitVal = !$isCredit ? $amount : 0.0;
            $creditVal = $isCredit ? $amount : 0.0;

            $runningBalance += ($creditVal - $debitVal);
            $totalDebit += $debitVal;
            $totalCredit += $creditVal;

            $rows[] = [
                $index + 1,
                $item->date ? $item->date->format('d-m-Y') : '-',
                $item->voucher_no ?: '-',
                $item->account_name ?: 'Cash in Hand',
                $item->branch?->name ?: 'N/A',
                $item->category ?: '-',
                $item->description ?: '-',
                $debitVal > 0 ? number_format($debitVal, 2, '.', '') : '0.00',
                $creditVal > 0 ? number_format($creditVal, 2, '.', '') : '0.00',
                number_format($runningBalance, 2, '.', ''),
                $item->payment_mode ?: 'Cash',
                $item->creator ? ($item->creator->name ?? ($item->creator->first_name . ' ' . $item->creator->last_name)) : 'System',
            ];
        }

        // Summary Total Row
        $rows[] = [
            'TOTAL',
            '',
            '',
            '',
            '',
            '',
            '',
            number_format($totalDebit, 2, '.', ''),
            number_format($totalCredit, 2, '.', ''),
            number_format($runningBalance, 2, '.', ''),
            '',
            '',
        ];

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->entries) + 2;

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '062E39']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ],
            $lastRow => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E6E6E6']
                ],
            ]
        ];
    }
}
