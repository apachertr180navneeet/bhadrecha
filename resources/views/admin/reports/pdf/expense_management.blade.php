@extends('admin.reports.pdf.layout')
@section('content')
<table>
    <thead><tr><th class="text-end">Trip Expenses</th><th class="text-end">Advance</th><th class="text-end">Maintenance</th><th class="text-end">Total Fuel</th><th class="text-end">Grand Total</th></tr></thead>
    <tbody>
        <tr>
            <td class="text-end">₹ {{ number_format($totalTripExpenses, 0) }}</td>
            <td class="text-end">₹ {{ number_format($totalTripAdvance, 0) }}</td>
            <td class="text-end">₹ {{ number_format($totalMaintenanceExpenses, 0) }}</td>
            <td class="text-end">₹ {{ number_format($totalFuelAmt, 0) }} ({{ number_format($totalFuelQty, 2) }} L)</td>
            <td class="text-end fw-bold">₹ {{ number_format($grandTotal, 0) }}</td>
        </tr>
    </tbody>
</table>

<h4>Vehicle-wise Expense Summary</h4>
<table>
    <thead>
        <tr><th>#</th><th>Vehicle</th><th class="text-end">Fuel</th><th class="text-end">FastTag</th><th class="text-end">AdBlue</th><th class="text-end">Other Trip</th><th class="text-end">Advance</th><th class="text-end">Maint.</th><th class="text-end">Breakdown</th><th class="text-end">Spare Parts</th><th class="text-end">Total</th></tr>
    </thead>
    <tbody>
        @foreach($vehicles as $i => $v)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $v->vehicle_number }}</td>
            <td class="text-end">₹ {{ number_format($v->fuel_expense, 0) }}</td>
            <td class="text-end">₹ {{ number_format($v->fasttag_expense, 0) }}</td>
            <td class="text-end">₹ {{ number_format($v->adblue_expense, 0) }}</td>
            <td class="text-end">₹ {{ number_format($v->other_expense, 0) }}</td>
            <td class="text-end">₹ {{ number_format($v->advance_expense, 0) }}</td>
            <td class="text-end">₹ {{ number_format($v->maintenance_cost, 0) }}</td>
            <td class="text-end">₹ {{ number_format($v->breakdown_cost, 0) }}</td>
            <td class="text-end">₹ {{ number_format($v->spare_part_cost, 0) }}</td>
            <td class="text-end fw-bold">₹ {{ number_format($v->total_expense, 0) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
