<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Sales Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            padding: 20px;
        }
        .company-name {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-title {
            text-align: center;
            font-size: 16px;
            margin-bottom: 20px;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px 10px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: 600;
        }
        .text-end {
            text-align: right;
        }
        .total-row td {
            font-weight: bold;
            border-top: 2px solid #333;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 11px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="company-name">{{ config('app.name') }}</div>
    <div class="report-title">Sales Report</div>
    @if(isset($dateFrom) && isset($dateTo))
        <div class="report-subtitle" style="text-align: center; font-size: 11px; margin-bottom: 15px; color: #666;">
            Period: {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Customer</th>
                <th>Service</th>
                <th>Store</th>
                <th>Branch</th>
                <th class="text-end">Amount</th>
                <th class="text-end">Discount</th>
                <th class="text-end">Final Amount</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalAmount = 0;
                $totalDiscount = 0;
                $totalFinal = 0;
            @endphp
            @forelse($appointments as $appointment)
                @php
                    $totalAmount += $appointment->total_amount ?? $appointment->amount ?? 0;
                    $totalDiscount += $appointment->discount ?? 0;
                    $totalFinal += $appointment->final_amount ?? 0;
                @endphp
                <tr>
                    <td>{{ $appointment->appointment_date ? $appointment->appointment_date->format('d-m-Y') : ($appointment->created_at ? $appointment->created_at->format('d-m-Y') : 'N/A') }}</td>
                    <td>{{ $appointment->customer->name ?? $appointment->customer_name ?? 'N/A' }}</td>
                    <td>
                        @if($appointment->appointmentServices && $appointment->appointmentServices->count() > 0)
                            @php
                                $sList = $appointment->appointmentServices->map(function($appSvc) {
                                    $sName = $appSvc->service->service_name ?? 'N/A';
                                    $mNames = $appSvc->staffMembers()->pluck('full_name')->filter()->implode(', ');
                                    if (empty($mNames) && $appSvc->staff) {
                                        $mNames = $appSvc->staff->full_name;
                                    }
                                    return !empty($mNames) ? "{$sName} ({$mNames})" : $sName;
                                })->implode(', ');
                            @endphp
                            {{ $sList }}
                        @else
                            @php
                                $sName = $appointment->service->service_name ?? $appointment->service_name ?? 'N/A';
                                $stf = $appointment->staff->full_name ?? $appointment->staff->name ?? null;
                            @endphp
                            {{ !empty($stf) ? "{$sName} ({$stf})" : $sName }}
                        @endif
                    </td>
                    <td>{{ $appointment->store->store_name ?? $appointment->store_name ?? 'N/A' }}</td>
                    <td>{{ $appointment->branch->name ?? 'N/A' }}</td>
                    <td class="text-end">₹{{ number_format($appointment->total_amount ?? $appointment->amount ?? 0, 2) }}</td>
                    <td class="text-end">₹{{ number_format($appointment->discount ?? 0, 2) }}</td>
                    <td class="text-end">₹{{ number_format($appointment->final_amount ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #888;">No sales records found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-end">Total</td>
                <td class="text-end">₹{{ number_format($totalAmount, 2) }}</td>
                <td class="text-end">₹{{ number_format($totalDiscount, 2) }}</td>
                <td class="text-end">₹{{ number_format($totalFinal, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Generated on {{ now()->format('d-m-Y h:i A') }} &mdash; {{ config('app.name') }}
    </div>
</body>
</html>
