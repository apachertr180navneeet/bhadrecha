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

class BiltyExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $bulties;

    public function __construct($bulties)
    {
        $this->bulties = $bulties;
    }

    public function headings(): array
    {
        return [
            'S.No',
            'LR / Bilty Number',
            'Date',
            'Consignor',
            'Consignee',
            'From Branch',
            'To Branch / Destination',
            'Vehicle Number',
            'Total Weight',
            'Bill Amount (₹)',
            'Received Amount (₹)',
            'Remaining Amount (₹)',
            'Status',
        ];
    }

    public function array(): array
    {
        $rows = [];
        $totalBill = 0.0;
        $totalReceived = 0.0;
        $totalRemaining = 0.0;

        foreach ($this->bulties as $index => $bulty) {
            $billAmt = (float)($bulty->total_amount ?: $bulty->freight_charges);
            $recvAmt = (float)($bulty->advance_amount ?? 0);
            $remAmt = max(0, round($billAmt - $recvAmt, 2));

            $totalBill += $billAmt;
            $totalReceived += $recvAmt;
            $totalRemaining += $remAmt;

            $fromBranch = $bulty->branch ? $bulty->branch->name : ($bulty->originCity ? $bulty->originCity->name : '-');
            $toBranch = $bulty->destinationCity ? $bulty->destinationCity->name : ($bulty->to_branch ? $bulty->to_branch->name : '-');

            $weightVal = 0;
            if (!empty($bulty->bultyDetail->challan_qty) && floatval($bulty->bultyDetail->challan_qty) > 0) {
                $weightVal = floatval($bulty->bultyDetail->challan_qty);
            } elseif ($bulty->bultyItems && $bulty->bultyItems->sum('weight') > 0) {
                $weightVal = floatval($bulty->bultyItems->sum('weight'));
            }

            $rows[] = [
                $index + 1,
                $bulty->lr_no ?: '-',
                $bulty->lr_date ? $bulty->lr_date->format('d-m-Y') : '-',
                $bulty->consignor ? $bulty->consignor->name : ($bulty->consignor_name ?: '-'),
                $bulty->consignee ? $bulty->consignee->name : ($bulty->consignee_name ?: '-'),
                $fromBranch,
                $toBranch,
                $bulty->vehicle ? $bulty->vehicle->vehicle_number : '-',
                $weightVal > 0 ? number_format($weightVal, 2, '.', '') : '-',
                number_format($billAmt, 2, '.', ''),
                number_format($recvAmt, 2, '.', ''),
                number_format($remAmt, 2, '.', ''),
                ucfirst($bulty->status ?? 'pending'),
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
            '',
            '',
            number_format($totalBill, 2, '.', ''),
            number_format($totalReceived, 2, '.', ''),
            number_format($totalRemaining, 2, '.', ''),
            '',
        ];

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->bulties) + 2;

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1A5276']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
            $lastRow => [
                'font' => ['bold' => true, 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE8F8F5']],
                'borders' => ['top' => ['borderStyle' => Border::BORDER_DOUBLE], 'bottom' => ['borderStyle' => Border::BORDER_THIN]],
            ],
        ];
    }
}
