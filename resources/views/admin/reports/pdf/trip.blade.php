@extends('admin.reports.pdf.layout')
@section('content')
<table>
    <thead>
        <tr><th>LR No</th><th>Trip Status</th><th class="text-end">Fuel (L)</th><th class="text-end">Fuel Amt</th><th class="text-end">FastTag</th><th class="text-end">AdBlue</th><th class="text-end">Other Exp</th><th class="text-end">Trip Advance</th></tr>
    </thead>
    <tbody>
        @foreach($trips as $builty)
        @php
            $trip = $builty->trip;
            $totalFuelQty = $trip?->fuelDetails->sum('quantity') ?? 0;
            $totalFuelAmt = $trip?->fuelDetails->sum('amount') ?? 0;
        @endphp
        <tr>
            <td>{{ $builty->lr_no }}</td>
            <td>{{ $trip ? ucfirst($trip->status) : '-' }}</td>
            <td class="text-end">{{ number_format($totalFuelQty, 2) }}</td>
            <td class="text-end">{{ number_format($totalFuelAmt, 2) }}</td>
            <td class="text-end">{{ $trip ? number_format($trip->fasttag_total_amount, 2) : '-' }}</td>
            <td class="text-end">{{ $trip ? number_format($trip->adblue_total_amount, 2) : '-' }}</td>
            <td class="text-end">{{ $trip ? number_format($trip->other_amount, 2) : '-' }}</td>
            <td class="text-end">{{ $trip ? number_format($trip->advance_total_amount, 2) : '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
