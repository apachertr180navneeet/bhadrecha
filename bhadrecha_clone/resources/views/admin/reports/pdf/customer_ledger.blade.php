@extends('admin.reports.pdf.layout')
@section('content')
@if($selectedConsignee)
<table>
    <tr><td><strong>Name:</strong> {{ $selectedConsignee->name }}</td><td><strong>Phone:</strong> {{ $selectedConsignee->phone ?? 'N/A' }}</td></tr>
    <tr><td><strong>GSTIN:</strong> {{ $selectedConsignee->gstin ?? 'N/A' }}</td><td><strong>City:</strong> {{ $selectedConsignee->city ?? 'N/A' }}</td></tr>
</table>
@endif
@if($summary)
<table>
    <thead><tr><th>Total LR</th><th class="text-end">Freight</th><th class="text-end">GST</th><th class="text-end">Other</th><th class="text-end">Total</th><th class="text-end">Paid</th><th class="text-end">Due</th></tr></thead>
    <tbody>
        <tr>
            <td class="text-center">{{ $summary->total_lr }}</td>
            <td class="text-end">₹ {{ number_format($summary->total_freight, 0) }}</td>
            <td class="text-end">₹ {{ number_format($summary->total_gst, 0) }}</td>
            <td class="text-end">₹ {{ number_format($summary->total_other, 0) }}</td>
            <td class="text-end">₹ {{ number_format($summary->total_amount, 0) }}</td>
            <td class="text-end">₹ {{ number_format($summary->total_advance, 0) }}</td>
            <td class="text-end">₹ {{ number_format($summary->total_remaining, 0) }}</td>
        </tr>
    </tbody>
</table>
@endif
<h4>Transactions</h4>
<table>
    <thead>
        <tr><th>LR No</th><th>Date</th><th>From → To</th><th>Vehicle</th><th class="text-end">Freight</th><th class="text-end">GST</th><th class="text-end">Other</th><th class="text-end">Total</th><th class="text-end">Paid</th><th class="text-end">Due</th></tr>
    </thead>
    <tbody>
        @foreach($transactions as $b)
        @php $due = $b->remaining_amount ?? ($b->total_amount - $b->advance_amount); @endphp
        <tr>
            <td>{{ $b->lr_no }}</td>
            <td>{{ $b->lr_date?->format('d-m-Y') ?? '-' }}</td>
            <td>{{ $b->originCity?->name ?? $b->from_city }} → {{ $b->destinationCity?->name ?? $b->to_city }}</td>
            <td>{{ $b->vehicle?->vehicle_number ?? '-' }}</td>
            <td class="text-end">₹ {{ number_format($b->freight_charges, 2) }}</td>
            <td class="text-end">₹ {{ number_format($b->gst_amount, 2) }}</td>
            <td class="text-end">₹ {{ number_format($b->other_charges, 2) }}</td>
            <td class="text-end">₹ {{ number_format($b->total_amount, 2) }}</td>
            <td class="text-end">₹ {{ number_format($b->advance_amount, 2) }}</td>
            <td class="text-end">₹ {{ number_format($due, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
