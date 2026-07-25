@extends('admin.reports.pdf.layout')
@section('content')
<table>
    <thead>
        <tr><th>LR No</th><th>Date</th><th>Vehicle</th><th>Driver</th><th>Route</th><th class="text-end">Freight</th><th class="text-end">GST</th><th class="text-end">Other</th><th class="text-end">Total</th><th class="text-end">Bilty Advance</th><th class="text-end">Trip Advance</th><th>Trip Status</th><th class="text-end">Fuel Exp</th><th class="text-end">FastTag</th><th class="text-end">AdBlue</th><th class="text-end">Other Exp</th><th class="text-end">Net Profit</th></tr>
    </thead>
    <tbody>
        @foreach($trips as $builty)
        @php
            $trip = $builty->trip;
            $totalFuelAmt = $trip?->fuelDetails->sum('amount') ?? 0;
            $totalExpenses = $totalFuelAmt + ($trip?->fasttag_total_amount ?? 0) + ($trip?->adblue_total_amount ?? 0) + ($trip?->other_amount ?? 0) + ($trip?->advance_total_amount ?? 0);
            $netProfit = $builty->total_amount - $totalExpenses;
        @endphp
        <tr>
            <td>{{ $builty->lr_no }}</td>
            <td>{{ $builty->lr_date?->format('d-m-Y') ?? '-' }}</td>
            <td>{{ $builty->vehicle?->vehicle_number ?? '-' }}</td>
            <td>{{ $builty->driver?->name ?? '-' }}</td>
            <td>{{ $builty->originCity?->name ?? $builty->from_city }} → {{ $builty->destinationCity?->name ?? $builty->to_city }}</td>
            <td class="text-end">₹ {{ number_format($builty->freight_charges, 0) }}</td>
            <td class="text-end">₹ {{ number_format($builty->gst_amount, 0) }}</td>
            <td class="text-end">₹ {{ number_format($builty->other_charges, 0) }}</td>
            <td class="text-end">₹ {{ number_format($builty->total_amount, 0) }}</td>
            <td class="text-end">₹ {{ number_format($builty->advance_amount, 0) }}</td>
            <td class="text-end">₹ {{ number_format($trip?->advance_total_amount ?? 0, 0) }}</td>
            <td>{{ $trip ? ucfirst($trip->status) : '-' }}</td>
            <td class="text-end">₹ {{ number_format($totalFuelAmt, 0) }}</td>
            <td class="text-end">₹ {{ number_format($trip?->fasttag_total_amount ?? 0, 0) }}</td>
            <td class="text-end">₹ {{ number_format($trip?->adblue_total_amount ?? 0, 0) }}</td>
            <td class="text-end">₹ {{ number_format($trip?->other_amount ?? 0, 0) }}</td>
            <td class="text-end {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">₹ {{ number_format($netProfit, 0) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
