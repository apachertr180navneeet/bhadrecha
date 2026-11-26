<table>
    <thead>
        <tr>
            <th colspan="9" style="text-align: center; font-size: 16px; font-weight: bold;">Fuel Outstanding Ledger</th>
        </tr>
        <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Ref (LR / Method)</th>
            <th>Vehicle</th>
            <th>Pump / Company</th>
            <th>Qty (L)</th>
            <th>Rate</th>
            <th>Debit (We Owe +)</th>
            <th>Credit (Paid -)</th>
            <th>Running Balance</th>
            <th>Remark</th>
        </tr>
    </thead>
    <tbody>
        @php $runBal = 0; @endphp
        @foreach($ledgerItems as $item)
            @php $runBal += ($item['debit'] - $item['credit']); @endphp
            <tr>
                <td>{{ $item['date'] ? date('d-m-Y', strtotime($item['date'])) : '-' }}</td>
                <td>{{ $item['type'] }}</td>
                <td>{{ $item['ref_no'] }}</td>
                <td>{{ $item['vehicle'] }}</td>
                <td>{{ $item['pump'] }} ({{ $item['company'] }})</td>
                <td>{{ $item['qty'] !== null ? number_format($item['qty'], 2) : '-' }}</td>
                <td>{{ $item['rate'] !== null ? number_format($item['rate'], 2) : '-' }}</td>
                <td>{{ $item['debit'] > 0 ? number_format($item['debit'], 2) : '-' }}</td>
                <td>{{ $item['credit'] > 0 ? number_format($item['credit'], 2) : '-' }}</td>
                <td>{{ number_format($runBal, 2) }}</td>
                <td>{{ $item['remark'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
