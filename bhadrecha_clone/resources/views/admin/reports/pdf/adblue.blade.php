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
        <tr><th>Date</th><th>Vehicle</th><th>Company</th><th>Payment Type</th><th class="text-end">Qty (L)</th><th class="text-end">Rate</th><th class="text-end">Amount</th><th class="text-end">KM</th><th>LR No</th></tr>
    </thead>
    <tbody>
        @foreach($adblueDetails as $ad)
        <tr>
            <td>{{ $ad->date?->format('d-m-Y') ?? '-' }}</td>
            <td>{{ $ad->trip?->builty?->vehicle?->vehicle_number ?? '-' }}</td>
            <td>{{ $ad->adblueCompany?->name ?? '-' }}</td>
            <td>{{ ucfirst($ad->payment_type ?? '-') }}</td>
            <td class="text-end">{{ number_format($ad->quantity, 2) }}</td>
            <td class="text-end">₹ {{ number_format($ad->rate, 2) }}</td>
            <td class="text-end">₹ {{ number_format($ad->amount, 2) }}</td>
            <td class="text-end">{{ number_format($ad->km, 2) }}</td>
            <td>{{ $ad->trip?->builty?->lr_no ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
