@extends('admin.reports.pdf.layout')
@section('content')
<table>
    <tr><td><strong>Total LR:</strong> {{ $totalLR }}</td><td><strong>Revenue:</strong> ₹ {{ number_format($totalRevenue, 0) }}</td><td><strong>Advance:</strong> ₹ {{ number_format($totalAdvance, 0) }}</td><td><strong>Due:</strong> ₹ {{ number_format($totalDue, 0) }}</td></tr>
    <tr><td><strong>Vehicles:</strong> {{ $totalVehicles }}</td><td><strong>Drivers:</strong> {{ $totalDrivers }}</td><td><strong>Active Trips:</strong> {{ $activeTrips }}</td><td><strong>Month LR:</strong> {{ $monthLR }}</td></tr>
    <tr><td colspan="2"><strong>Fuel:</strong> {{ number_format($totalFuelQty, 2) }} L / ₹ {{ number_format($totalFuelAmt, 0) }}</td><td><strong>FastTag:</strong> ₹ {{ number_format($totalFastTag, 0) }}</td><td><strong>AdBlue:</strong> ₹ {{ number_format($totalAdBlue, 0) }} / <strong>Other:</strong> ₹ {{ number_format($totalOtherExp, 0) }}</td></tr>
</table>

<h4>Top 10 Vehicles by Trips</h4>
<table>
    <thead><tr><th>#</th><th>Vehicle</th><th class="text-center">Trips</th><th class="text-end">Freight</th><th class="text-end">Revenue</th></tr></thead>
    <tbody>
        @foreach($topVehicles as $i => $tv)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $tv->vehicle?->vehicle_number ?? 'Unknown' }}</td>
            <td class="text-center">{{ $tv->trip_count }}</td>
            <td class="text-end">₹ {{ number_format($tv->freight, 0) }}</td>
            <td class="text-end">₹ {{ number_format($tv->revenue, 0) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<h4>Recent Trips</h4>
<table>
    <thead><tr><th>LR No</th><th>Vehicle</th><th>Driver</th><th>Route</th><th>Status</th></tr></thead>
    <tbody>
        @foreach($recentTrips as $rt)
        <tr>
            <td>{{ $rt->lr_no }}</td>
            <td>{{ $rt->vehicle?->vehicle_number ?? '-' }}</td>
            <td>{{ $rt->driver?->name ?? '-' }}</td>
            <td>{{ $rt->originCity?->name ?? $rt->from_city }} → {{ $rt->destinationCity?->name ?? $rt->to_city }}</td>
            <td>{{ $rt->trip ? ucfirst($rt->trip->status) : 'No Trip' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
