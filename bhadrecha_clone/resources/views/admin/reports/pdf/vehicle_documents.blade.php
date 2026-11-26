@extends('admin.reports.pdf.layout')
@section('content')
<table>
    <thead><tr><th class="text-center">Expired</th><th class="text-center">Expiring ≤7 days</th><th class="text-center">Expiring in {{ $thresholdDays }} days</th></tr></thead>
    <tbody>
        <tr>
            <td class="text-center fw-bold text-danger">{{ $totalExpired }}</td>
            <td class="text-center fw-bold text-warning">{{ $totalWarning }}</td>
            <td class="text-center fw-bold text-info">{{ $totalUpcoming }}</td>
        </tr>
    </tbody>
</table>

<table>
    <thead><tr><th>#</th><th>Vehicle</th><th>Company</th><th>Document</th><th>Expiry Date</th><th>Days Left</th></tr></thead>
    <tbody>
        @foreach($documents as $doc)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $doc['vehicle_number'] }}</td>
            <td>{{ $doc['company_name'] ?? 'N/A' }}</td>
            <td>{{ $doc['document'] }}</td>
            <td>{{ \Carbon\Carbon::parse($doc['expiry_date'])->format('d-m-Y') }}</td>
            <td class="text-center">{{ $doc['days_left'] <= 0 ? 'Expired' : $doc['days_left'] . ' days' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
