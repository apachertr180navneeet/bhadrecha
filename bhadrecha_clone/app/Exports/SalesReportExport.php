<?php

namespace App\Exports;

use App\Models\Appointment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesReportExport implements FromCollection, WithHeadings, WithMapping
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

        $dateFrom = $this->request->get('date_from') ?: \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $this->request->get('date_to') ?: \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d');

        $selectedStoreId = ($storeId && !$isAdmin) ? $storeId : $this->request->get('store_id');
        $selectedBranchId = ($branchId && !$isAdmin) ? $branchId : $this->request->get('branch_id');

        return Appointment::with(['customer', 'service', 'store', 'branch', 'appointmentServices.service', 'appointmentServices.staff'])
            ->when($selectedStoreId, fn($q) => $q->where('store_id', $selectedStoreId))
            ->when($selectedBranchId, fn($q) => $q->where('branch_id', $selectedBranchId))
            ->when($dateFrom, fn($q) => $q->whereDate('appointment_date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('appointment_date', '<=', $dateTo))
            ->where('status', 'completed')
            ->orderBy('appointment_date', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Date',
            'Customer',
            'Service',
            'Amount',
            'Discount',
            'Final Amount',
            'Payment Status',
            'Store',
            'Branch',
        ];
    }

    public function map($appointment): array
    {
        $serviceDetails = [];
        if ($appointment->appointmentServices && $appointment->appointmentServices->count() > 0) {
            foreach ($appointment->appointmentServices as $appService) {
                $sName = $appService->service->service_name ?? 'N/A';
                $staffMembers = $appService->staffMembers();
                $stNames = $staffMembers->count() > 0 
                    ? $staffMembers->pluck('full_name')->implode(', ') 
                    : ($appService->staff->full_name ?? $appService->staff->name ?? '');
                
                $serviceDetails[] = !empty($stNames) ? "{$sName} ({$stNames})" : $sName;
            }
        } else {
            $sName = $appointment->service->service_name ?? $appointment->service_name ?? 'N/A';
            $stName = $appointment->staff->full_name ?? $appointment->staff->name ?? '';
            $serviceDetails[] = !empty($stName) ? "{$sName} ({$stName})" : $sName;
        }

        return [
            $appointment->appointment_date ? $appointment->appointment_date->format('d-m-Y') : ($appointment->created_at ? $appointment->created_at->format('d-m-Y') : 'N/A'),
            $appointment->customer->name ?? $appointment->customer_name ?? 'N/A',
            implode(', ', $serviceDetails),
            $appointment->total_amount ?? $appointment->amount ?? 0,
            $appointment->discount ?? 0,
            $appointment->final_amount ?? 0,
            ucfirst($appointment->payment_status),
            $appointment->store->store_name ?? $appointment->store_name ?? 'N/A',
            $appointment->branch->name ?? 'N/A',
        ];
    }
}
