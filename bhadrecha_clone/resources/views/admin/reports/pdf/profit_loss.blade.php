@extends('admin.reports.pdf.layout')
@section('content')
<table>
    <thead><tr><th class="text-end">Total Income</th><th class="text-end">Total Expenses</th><th class="text-end">Net Profit/Loss</th><th class="text-end">Margin</th></tr></thead>
    <tbody>
        <tr>
            <td class="text-end fw-bold text-success">₹ {{ number_format($summary['total_income'], 0) }}</td>
            <td class="text-end fw-bold text-danger">₹ {{ number_format($summary['total_expenses'], 0) }}</td>
            <td class="text-end fw-bold {{ $summary['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">₹ {{ number_format($summary['net_profit'], 0) }}</td>
            <td class="text-end fw-bold">{{ $summary['total_income'] > 0 ? number_format(($summary['net_profit'] / $summary['total_income']) * 100, 1) : 0 }}%</td>
        </tr>
    </tbody>
</table>

<h4>Income Breakdown</h4>
<table>
    <tr><td>Freight Charges</td><td class="text-end">₹ {{ number_format($summary['total_income'], 0) }}</td></tr>
</table>

<h4>Expense Breakdown</h4>
<table>
    <tr><td>Fuel</td><td class="text-end">₹ {{ number_format($summary['fuel_expense'], 0) }}</td></tr>
    <tr><td>FastTag (Toll)</td><td class="text-end">₹ {{ number_format($summary['fasttag_expense'], 0) }}</td></tr>
    <tr><td>AdBlue</td><td class="text-end">₹ {{ number_format($summary['adblue_expense'], 0) }}</td></tr>
    <tr><td>Other Trip Expenses</td><td class="text-end">₹ {{ number_format($summary['other_trip_expense'], 0) }}</td></tr>
    <tr><td>Trip Advance</td><td class="text-end">₹ {{ number_format($summary['total_trip_advance'], 0) }}</td></tr>
    <tr><td>Bilty Commission</td><td class="text-end">₹ {{ number_format($summary['total_commission'], 0) }}</td></tr>
    <tr class="summary-row"><td><strong>Total Expenses</strong></td><td class="text-end"><strong>₹ {{ number_format($summary['total_expenses'], 0) }}</strong></td></tr>
</table>
@endsection
