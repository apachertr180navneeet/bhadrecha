@extends('admin.reports.pdf.layout')
@section('content')
<table>
    <thead><tr><th class="text-center">Total Bills</th><th class="text-end">Freight</th><th class="text-end">GST</th><th class="text-end">Other</th><th class="text-end">Total</th></tr></thead>
    <tbody>
        <tr>
            <td class="text-center fw-bold">{{ $totalBills }}</td>
            <td class="text-end">₹ {{ number_format($totalFreight, 0) }}</td>
            <td class="text-end">₹ {{ number_format($totalGst, 0) }}</td>
            <td class="text-end">₹ {{ number_format($totalOtherCharges, 0) }}</td>
            <td class="text-end fw-bold">₹ {{ number_format($totalAmount, 0) }}</td>
        </tr>
    </tbody>
</table>

@if($gstBreakdown->count() > 0)
<h4>GST Rate-wise Summary</h4>
<table>
    <thead><tr><th>GST Rate</th><th class="text-center">Bills</th><th class="text-end">Freight</th><th class="text-end">GST</th><th class="text-end">Other</th><th class="text-end">Total</th></tr></thead>
    <tbody>
        @foreach($gstBreakdown as $rate => $data)
        <tr>
            <td>{{ $rate }}</td>
            <td class="text-center">{{ $data['count'] }}</td>
            <td class="text-end">₹ {{ number_format($data['freight'], 0) }}</td>
            <td class="text-end">₹ {{ number_format($data['gst'], 0) }}</td>
            <td class="text-end">₹ {{ number_format($data['other'], 0) }}</td>
            <td class="text-end fw-bold">₹ {{ number_format($data['total'], 0) }}</td>
        </tr>
        @endforeach
        <tr class="summary-row">
            <td><strong>Total</strong></td>
            <td class="text-center">{{ $totalBills }}</td>
            <td class="text-end">₹ {{ number_format($totalFreight, 0) }}</td>
            <td class="text-end">₹ {{ number_format($totalGst, 0) }}</td>
            <td class="text-end">₹ {{ number_format($totalOtherCharges, 0) }}</td>
            <td class="text-end">₹ {{ number_format($totalAmount, 0) }}</td>
        </tr>
    </tbody>
</table>
@endif

<h4>Bill-wise Details</h4>
<table>
    <thead>
        <tr><th>#</th><th>LR No</th><th>Date</th><th>Consignor</th><th>Consignee</th><th>From → To</th><th>Vehicle</th><th class="text-end">Freight</th><th class="text-center">GST Rate</th><th class="text-end">GST</th><th class="text-end">Other</th><th class="text-end">Total</th></tr>
    </thead>
    <tbody>
        @foreach($bulties as $b)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $b->lr_no }}</td>
            <td>{{ $b->lr_date->format('d-m-Y') }}</td>
            <td>{{ $b->consignor?->name ?? '-' }}</td>
            <td>{{ $b->consignee?->name ?? '-' }}</td>
            <td>{{ $b->originCity?->name ?? '-' }} → {{ $b->destinationCity?->name ?? '-' }}</td>
            <td>{{ $b->vehicle?->vehicle_number ?? '-' }}</td>
            <td class="text-end">₹ {{ number_format($b->freight_charges, 0) }}</td>
            <td class="text-center">{{ $b->gstMaster?->gst_rate ?? 'N/A' }}</td>
            <td class="text-end">₹ {{ number_format($b->gst_amount, 0) }}</td>
            <td class="text-end">₹ {{ number_format($b->other_charges, 0) }}</td>
            <td class="text-end">₹ {{ number_format($b->total_amount, 0) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
