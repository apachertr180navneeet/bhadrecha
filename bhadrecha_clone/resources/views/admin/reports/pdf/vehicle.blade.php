@extends('admin.reports.pdf.layout')
@section('content')
<table>
    <thead>
        <tr><th>#</th><th>Vehicle</th><th>Type</th><th class="text-center">Trips</th><th class="text-end">Fuel (L)</th><th class="text-end">Amount (₹)</th><th class="text-end">KM</th><th class="text-end">Avg KM/L</th></tr>
    </thead>
    <tbody>
        @foreach($vehicles as $vehicle)
        @php $avgKmL = $vehicle->total_fuel_qty > 0 ? round($vehicle->total_km / $vehicle->total_fuel_qty, 2) : 0; @endphp
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $vehicle->vehicle_number }}</td>
            <td>{{ $vehicle->vehicle_type ?? '-' }}</td>
            <td class="text-center">{{ $vehicle->total_trips }}</td>
            <td class="text-end">{{ number_format($vehicle->total_fuel_qty, 2) }}</td>
            <td class="text-end">{{ number_format($vehicle->total_fuel_amount, 2) }}</td>
            <td class="text-end">{{ number_format($vehicle->total_km, 2) }}</td>
            <td class="text-end">{{ $avgKmL > 0 ? $avgKmL : '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
