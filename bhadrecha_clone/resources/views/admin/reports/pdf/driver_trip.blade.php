@extends('admin.reports.pdf.layout')
@section('content')
@php $currentDriver = null; $first = true; @endphp
<table>
    <thead>
        <tr><th>LR No</th><th>Vehicle</th><th class="text-end">Fuel (L)</th><th class="text-end">Fuel Amt</th><th class="text-end">FastTag</th><th class="text-end">AdBlue</th><th class="text-end">Other</th><th class="text-end">Advance</th></tr>
    </thead>
    <tbody>
@foreach($trips as $builty)
@php
    $trip = $builty->trip;
    $totalFuelQty = $trip?->total_fuel_quantity ?? 0;
    $totalFuelAmt = $trip?->total_fuel_amount ?? 0;
    $fasttagAmt = $trip?->total_fasttag_amount ?? 0;
    $adblueAmt = $trip?->total_adblue_amount ?? 0;
    $otherAmt = $trip?->total_other_amount ?? 0;
    $advanceAmt = $trip?->total_advance_amount ?? 0;
@endphp
@if($currentDriver !== $builty->driver_id)
@php $currentDriver = $builty->driver_id; @endphp
        <tr><td colspan="8" style="border:none;padding:5px 0 2px;font-weight:bold;border-bottom:1px solid #ddd;">Driver: {{ $builty->driver?->name ?? 'N/A' }}</td></tr>
@endif
        <tr>
            <td>{{ $builty->lr_no }}</td>
            <td>{{ $builty->vehicle?->vehicle_number ?? '-' }}</td>
            <td class="text-end">{{ number_format($totalFuelQty, 2) }}</td>
            <td class="text-end">{{ number_format($totalFuelAmt, 2) }}</td>
            <td class="text-end">{{ $trip ? number_format($fasttagAmt, 2) : '-' }}</td>
            <td class="text-end">{{ $trip ? number_format($adblueAmt, 2) : '-' }}</td>
            <td class="text-end">{{ $trip ? number_format($otherAmt, 2) : '-' }}</td>
            <td class="text-end">{{ $trip ? number_format($advanceAmt, 2) : '-' }}</td>
        </tr>
@endforeach
    </tbody>
</table>
@endsection
