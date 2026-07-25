@extends('admin.reports.pdf.layout')
@section('content')
@if($summary)
<table>
    <thead><tr><th class="text-end">Total Qty (L)</th><th class="text-end">Total Amount</th><th class="text-end">Total KM</th></tr></thead>
    <tbody>
        <tr>
            <td class="text-end fw-bold">{{ number_format($summary->total_qty, 2) }}</td>
            <td class="text-end fw-bold">₹ {{ number_format($summary->total_amount, 2) }}</td>
            <td class="text-end fw-bold">{{ number_format($summary->total_km, 2) }}</td>
        </tr>
    </tbody>
</table>
@endif
<table>
    <thead>
        <tr><th>Date</th><th>Vehicle</th><th>Pump</th><th>Company</th><th class="text-end">Qty (L)</th><th class="text-end">Rate</th><th class="text-end">Amount</th><th class="text-end">KM</th><th>LR No</th></tr>
    </thead>
    <tbody>
        @foreach($fuelDetails as $fd)
        <tr>
            <td>{{ $fd->date?->format('d-m-Y') ?? '-' }}</td>
            <td>{{ $fd->trip?->builty?->vehicle?->vehicle_number ?? '-' }}</td>
            <td>{{ $fd->fuelPump?->name ?? '-' }}</td>
            <td>{{ $fd->fuelCompany?->name ?? '-' }}</td>
            <td class="text-end">{{ number_format($fd->quantity, 2) }}</td>
            <td class="text-end">₹ {{ number_format($fd->rate, 2) }}</td>
            <td class="text-end">₹ {{ number_format($fd->amount, 2) }}</td>
            <td class="text-end">{{ number_format($fd->km, 2) }}</td>
            <td>{{ $fd->trip?->builty?->lr_no ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
