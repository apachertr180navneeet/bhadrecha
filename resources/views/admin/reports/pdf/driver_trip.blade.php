@extends('admin.reports.pdf.layout')
@section('content')
@php $currentDriver = null; $first = true; @endphp
<table>
    <thead>
        <tr><th>LR No</th><th>Vehicle</th><th class="text-end">Fuel (L)</th><th class="text-end">Fuel Amt</th><th class="text-end">FastTag</th><th class="text-end">AdBlue</th><th class="text-end">Other</th><th class="text-end">Advance</th></tr>
    </thead>
    <tbody>
@foreach($trips as $builty)
@php $trip = $builty->trip; @endphp
@if($currentDriver !== $builty->driver_id)
@php $currentDriver = $builty->driver_id; @endphp
        <tr><td colspan="8" style="border:none;padding:5px 0 2px;font-weight:bold;border-bottom:1px solid #ddd;">Driver: {{ $builty->driver?->name ?? 'N/A' }}</td></tr>
@endif
        <tr>
            <td>{{ $builty->lr_no }}</td>
            <td>{{ $builty->vehicle?->vehicle_number ?? '-' }}</td>
            <td class="text-end">{{ number_format($trip?->fuelDetails->sum('quantity') ?? 0, 2) }}</td>
            <td class="text-end">{{ number_format($trip?->fuelDetails->sum('amount') ?? 0, 2) }}</td>
            <td class="text-end">{{ $trip ? number_format($trip->fasttag_total_amount, 2) : '-' }}</td>
            <td class="text-end">{{ $trip ? number_format($trip->adblue_total_amount, 2) : '-' }}</td>
            <td class="text-end">{{ $trip ? number_format($trip->other_amount, 2) : '-' }}</td>
            <td class="text-end">{{ $trip ? number_format($trip->advance_total_amount, 2) : '-' }}</td>
        </tr>
@endforeach
    </tbody>
</table>
@endsection
