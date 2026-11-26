@extends('admin.reports.pdf.layout')
@section('content')
<table>
    <thead>
        <tr><th>Vehicle</th><th>Type</th><th class="text-center">Trips</th><th class="text-end">KM</th><th class="text-end">Fuel (L)</th><th class="text-end">Avg KM/L</th><th class="text-end">Revenue</th><th class="text-end">Advance</th><th class="text-end">Fuel Cost</th><th class="text-end">FastTag</th><th class="text-end">AdBlue</th><th class="text-end">Other Exp</th><th class="text-end">Total Exp</th><th class="text-end">Net Rev</th></tr>
    </thead>
    <tbody>
        @foreach($vehicles as $vehicle)
        @php
            $avgKmL = $vehicle->total_fuel_qty > 0 ? round($vehicle->total_km / $vehicle->total_fuel_qty, 2) : 0;
            $totalExp = $vehicle->total_fuel_amount + $vehicle->total_fasttag + $vehicle->total_adblue + $vehicle->total_other_expense + $vehicle->total_advance;
            $netRevenue = $vehicle->total_revenue - $totalExp;
        @endphp
        <tr>
            <td>{{ $vehicle->vehicle_number }}</td>
            <td>{{ $vehicle->vehicle_type ?? '-' }}</td>
            <td class="text-center">{{ $vehicle->total_trips }}</td>
            <td class="text-end">{{ number_format($vehicle->total_km, 2) }}</td>
            <td class="text-end">{{ number_format($vehicle->total_fuel_qty, 2) }}</td>
            <td class="text-end">{{ $avgKmL > 0 ? $avgKmL : '-' }}</td>
            <td class="text-end">₹ {{ number_format($vehicle->total_revenue, 0) }}</td>
            <td class="text-end">₹ {{ number_format($vehicle->total_advance, 0) }}</td>
            <td class="text-end">₹ {{ number_format($vehicle->total_fuel_amount, 0) }}</td>
            <td class="text-end">₹ {{ number_format($vehicle->total_fasttag, 0) }}</td>
            <td class="text-end">₹ {{ number_format($vehicle->total_adblue, 0) }}</td>
            <td class="text-end">₹ {{ number_format($vehicle->total_other_expense, 0) }}</td>
            <td class="text-end">₹ {{ number_format($totalExp, 0) }}</td>
            <td class="text-end {{ $netRevenue >= 0 ? 'text-success' : 'text-danger' }}">₹ {{ number_format($netRevenue, 0) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
