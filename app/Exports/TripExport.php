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

class TripExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $trips;

    public function __construct($trips)
    {
        $this->trips = $trips;
    }

    public function headings(): array
    {
        return [
            'S.No',
            'Trip No',
            'LR / Bilty No',
            'Date',
            'Vehicle Number',
            'Driver Name',
            'Route (From -> To)',
            'Branch',
            'Total Freight (₹)',
            'FastTag (₹)',
            'Fuel (₹)',
            'AdBlue (₹)',
            'Other Expenses (₹)',
            'Advance (₹)',
            'Total Expenses (₹)',
            'Status',
        ];
    }

    public function array(): array
    {
        $rows = [];
        $sumFreight = 0.0;
        $sumFasttag = 0.0;
        $sumFuel = 0.0;
        $sumAdblue = 0.0;
        $sumOther = 0.0;
        $sumAdvance = 0.0;
        $sumTotalExpenses = 0.0;

        foreach ($this->trips as $index => $trip) {
            $builty = $trip->builty;
            $freight = $builty ? (float)($builty->total_amount ?: $builty->freight_charges) : 0.0;
            $fasttag = (float)($trip->fasttag_total_amount ?? 0);
            $fuel = (float)($trip->fuel_amount ?? 0);
            $adblue = (float)($trip->adblue_total_amount ?? 0);
            $other = (float)($trip->other_amount ?? 0);
            $advance = (float)($trip->advance_total_amount ?? 0);
            $totalExpenses = $fasttag + $fuel + $adblue + $other + $advance;

            $sumFreight += $freight;
            $sumFasttag += $fasttag;
            $sumFuel += $fuel;
            $sumAdblue += $adblue;
            $sumOther += $other;
            $sumAdvance += $advance;
            $sumTotalExpenses += $totalExpenses;

            $vehicleNo = $builty?->vehicle?->vehicle_number ?: ($trip->vehicle_id ?: '-');
            $driverName = $builty?->driver ? ($builty->driver->name ?? trim($builty->driver->first_name . ' ' . $builty->driver->last_name)) : '-';
            $route = ($builty?->originCity?->name ?? 'Origin') . ' -> ' . ($builty?->destinationCity?->name ?? 'Destination');
            $branchName = $builty?->branch?->name ?: '-';
            $tripDate = $builty?->lr_date ? $builty->lr_date->format('d-m-Y') : ($trip->created_at ? $trip->created_at->format('d-m-Y') : '-');

            $rows[] = [
                $index + 1,
                $trip->trip_no ?: ('TRIP-' . $trip->id),
                $builty?->lr_no ?: '-',
                $tripDate,
                $vehicleNo,
                $driverName,
                $route,
                $branchName,
                number_format($freight, 2, '.', ''),
                number_format($fasttag, 2, '.', ''),
                number_format($fuel, 2, '.', ''),
                number_format($adblue, 2, '.', ''),
                number_format($other, 2, '.', ''),
                number_format($advance, 2, '.', ''),
                number_format($totalExpenses, 2, '.', ''),
                ucfirst($trip->status ?: ($builty?->status ?? 'active')),
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
            number_format($sumFreight, 2, '.', ''),
            number_format($sumFasttag, 2, '.', ''),
            number_format($sumFuel, 2, '.', ''),
            number_format($sumAdblue, 2, '.', ''),
            number_format($sumOther, 2, '.', ''),
            number_format($sumAdvance, 2, '.', ''),
            number_format($sumTotalExpenses, 2, '.', ''),
            '',
        ];

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->trips) + 2;

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF117864']],
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
