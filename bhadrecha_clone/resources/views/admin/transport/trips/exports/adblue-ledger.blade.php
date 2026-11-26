<table>
    <thead>
        <tr>
            <th colspan="9" style="text-align: center; font-size: 16px; font-weight: bold;">AdBlue Outstanding Ledger</th>
        </tr>
        <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Ref / Method</th>
            <th>Vehicle</th>
            <th>Company</th>
            <th>Qty (L)</th>
            <th>Debit (Purchase +)</th>
            <th>Credit (Paid -)</th>
            <th>Running Balance</th>
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
                <td>{{ $item['company'] }}</td>
                <td>{{ (isset($item['qty']) && $item['qty'] > 0) ? number_format($item['qty'], 2) : '-' }}</td>
                <td>{{ (isset($item['debit']) && $item['debit'] > 0) ? number_format($item['debit'], 2) : '-' }}</td>
                <td>{{ (isset($item['credit']) && $item['credit'] > 0) ? number_format($item['credit'], 2) : '-' }}</td>
                <td>{{ number_format($runBal, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
