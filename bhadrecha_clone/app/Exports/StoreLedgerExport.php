<?php

namespace App\Exports;

use App\Models\Appointment;
use App\Models\Expense;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class StoreLedgerExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $user = auth()->user();
        $storeId = $user->store_id;
        $branchId = $user->branch_id;
        $isAdmin = $user->hasRole('Admin');

        $dateFrom = $this->request->get('date_from') ?: Carbon::today()->format('Y-m-d');
        $dateTo = $this->request->get('date_to') ?: Carbon::today()->format('Y-m-d');

        $selectedStoreId = ($storeId && !$isAdmin) ? $storeId : $this->request->get('store_id');
        $selectedBranchId = ($branchId && !$isAdmin) ? $branchId : $this->request->get('branch_id');

        // Fetch Sales
        $sales = Appointment::with(['customer', 'service', 'store', 'branch', 'appointmentServices.service', 'appointmentServices.staff'])
            ->when($selectedStoreId, fn($q) => $q->where('store_id', $selectedStoreId))
            ->when($selectedBranchId, fn($q) => $q->where('branch_id', $selectedBranchId))
            ->when($dateFrom, fn($q) => $q->whereDate('appointment_date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('appointment_date', '<=', $dateTo))
            ->where('status', 'completed')
            ->get()
            ->map(function ($item) {
                $pType = $item->payment_type ? ucfirst($item->payment_type) : 'N/A';
                if (strtolower($item->payment_type) === 'split') {
                    $parts = [];
                    if ((float)$item->cash_amount > 0) $parts[] = 'Cash: ₹' . number_format($item->cash_amount, 2);
                    if ((float)$item->upi_amount > 0) $parts[] = 'UPI: ₹' . number_format($item->upi_amount, 2);
                    if ((float)$item->card_amount > 0) $parts[] = 'Card: ₹' . number_format($item->card_amount, 2);
                    $pType = 'Split (' . implode(', ', $parts) . ')';
                }

                $serviceDetails = [];
                if ($item->appointmentServices && $item->appointmentServices->count() > 0) {
                    foreach ($item->appointmentServices as $appService) {
                        $sName = $appService->service->service_name ?? 'Service';
                        $staffMembers = $appService->staffMembers();
                        $stNames = $staffMembers->count() > 0 
                            ? $staffMembers->pluck('full_name')->implode(', ') 
                            : ($appService->staff->full_name ?? $appService->staff->name ?? '');
                        
                        $serviceDetails[] = !empty($stNames) ? "{$sName} ({$stNames})" : $sName;
                    }
                } else {
                    $sName = $item->service->service_name ?? 'Service';
                    $stName = $item->staff->full_name ?? $item->staff->name ?? '';
                    $serviceDetails[] = !empty($stName) ? "{$sName} ({$stName})" : $sName;
                }
                $servicesText = !empty($serviceDetails) ? implode(', ', $serviceDetails) : 'Service';
                $customerName = $item->customer->name ?? $item->customer->full_name ?? 'Customer';

                return (object)[
                    'type' => 'Sale',
                    'timestamp' => $item->appointment_date ? $item->appointment_date->timestamp : 0,
                    'date_formatted' => $item->appointment_date ? $item->appointment_date->format('d-m-Y h:i A') : ($item->created_at ? $item->created_at->format('d-m-Y h:i A') : 'N/A'),
                    'store_name' => $item->store->store_name ?? $item->store->name ?? 'N/A',
                    'branch_name' => $item->branch->name ?? 'N/A',
                    'ref_no' => $item->appointment_number ?? ('APT-' . $item->id),
                    'payment_type' => $pType,
                    'upi_reference' => $item->upi_reference,
                    'details' => $customerName . ' - ' . $servicesText,
                    'credit' => (float)$item->final_amount,
                    'debit' => 0.00,
                ];
            });

        // Fetch Expenses
        $expenses = Expense::with(['category', 'store', 'branch'])
            ->when($selectedStoreId, fn($q) => $q->where('store_id', $selectedStoreId))
            ->when($selectedBranchId, fn($q) => $q->where('branch_id', $selectedBranchId))
            ->when($dateFrom, fn($q) => $q->whereDate('date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('date', '<=', $dateTo))
            ->get()
            ->map(function ($item) {
                return (object)[
                    'type' => 'Expense',
                    'timestamp' => $item->date ? $item->date->timestamp : 0,
                    'date_formatted' => $item->date ? $item->date->format('d-m-Y') : 'N/A',
                    'store_name' => $item->store->store_name ?? $item->store->name ?? 'N/A',
                    'branch_name' => $item->branch->name ?? 'N/A',
                    'ref_no' => 'EXP-' . $item->id,
                    'payment_type' => 'Cash/Bank',
                    'upi_reference' => null,
                    'details' => ($item->category->name ?? 'Expense') . ($item->remarks ? ' (' . $item->remarks . ')' : ''),
                    'credit' => 0.00,
                    'debit' => (float)$item->amount,
                ];
            });

        return $sales->concat($expenses)->sortByDesc('timestamp')->values();
    }

    public function headings(): array
    {
        return [
            'Date & Time',
            'Store',
            'Branch',
            'Type',
            'Reference No.',
            'Details / Description',
            'Payment Mode',
            'UPI Reference / Remark',
            'Credit (Sale ₹)',
            'Debit (Expense ₹)',
        ];
    }

    public function map($entry): array
    {
        return [
            $entry->date_formatted,
            $entry->store_name,
            $entry->branch_name,
            $entry->type,
            $entry->ref_no,
            $entry->details,
            $entry->payment_type,
            $entry->upi_reference ?? '-',
            number_format($entry->credit, 2, '.', ''),
            number_format($entry->debit, 2, '.', ''),
        ];
    }
}
