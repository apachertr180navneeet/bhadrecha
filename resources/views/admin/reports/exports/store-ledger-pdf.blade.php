<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Store Ledger Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #333;
            padding: 15px;
        }
        .company-name {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .report-title {
            text-align: center;
            font-size: 14px;
            margin-bottom: 15px;
            color: #555;
        }
        .summary-box {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .summary-box td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            background: #f9f9f9;
        }
        .summary-title {
            font-size: 9px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 4px;
        }
        .summary-value {
            font-size: 14px;
            font-weight: bold;
        }
        table.ledger-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.ledger-table th, table.ledger-table td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }
        table.ledger-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-end {
            text-align: right;
        }
        .text-success {
            color: #28a745;
        }
        .text-danger {
            color: #dc3545;
        }
        .text-primary {
            color: #007bff;
        }
        .total-row td {
            font-weight: bold;
            background: #f8f9fa;
            border-top: 2px solid #333;
        }
        .footer {
            text-align: center;
            margin-top: 25px;
            font-size: 10px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="company-name">{{ config('app.name', 'Saloon ERP') }}</div>
    <div class="report-title">Store Ledger Report (Sales & Expenses)</div>
    @if(isset($dateFrom) && isset($dateTo))
        <div style="text-align: center; font-size: 11px; margin-bottom: 15px; color: #666;">
            Period: {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
        </div>
    @endif

    <table class="summary-box">
        <tr>
            <td>
                <div class="summary-title">Total Sales (Credit)</div>
                <div class="summary-value text-success">₹{{ number_format($totalSales, 2) }}</div>
            </td>
            <td>
                <div class="summary-title">Cash Sales</div>
                <div class="summary-value text-success">₹{{ number_format($totalCashSales ?? 0, 2) }}</div>
                <div style="font-size: 8px; color: #666; margin-top: 2px;">Count: {{ $totalCashCount ?? 0 }}</div>
            </td>
            <td>
                <div class="summary-title">UPI Sales</div>
                <div class="summary-value text-primary">₹{{ number_format($totalUpiSales ?? 0, 2) }}</div>
                <div style="font-size: 8px; color: #666; margin-top: 2px;">Count: {{ $totalUpiCount ?? 0 }}</div>
            </td>
            <td>
                <div class="summary-title">Total Expenses (Debit)</div>
                <div class="summary-value text-danger">₹{{ number_format($totalExpenses, 2) }}</div>
            </td>
            <td>
                <div class="summary-title">Net Ledger Balance</div>
                <div class="summary-value {{ $netBalance >= 0 ? 'text-primary' : 'text-danger' }}">₹{{ number_format($netBalance, 2) }}</div>
            </td>
        </tr>
    </table>

    <table class="ledger-table">
        <thead>
            <tr>
                <th>Date & Time</th>
                <th>Store</th>
                <th>Branch</th>
                <th>Type</th>
                <th>Ref No.</th>
                <th>Details / Description</th>
                <th>Payment Mode</th>
                <th class="text-end">Credit (Sale ₹)</th>
                <th class="text-end">Debit (Expense ₹)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($combinedLedger as $entry)
                <tr>
                    <td>{{ $entry->date ? \Carbon\Carbon::parse($entry->date)->format('d-m-Y h:i A') : 'N/A' }}</td>
                    <td>{{ $entry->store_name }}</td>
                    <td>{{ $entry->branch_name ?? 'N/A' }}</td>
                    <td>{{ $entry->type }}</td>
                    <td>{{ $entry->ref_no }}</td>
                    <td>{{ $entry->details }}</td>
                    <td>
                        {{ $entry->payment_type }}
                        @if(!empty($entry->upi_reference))
                            <div style="font-size: 9px; color: #555; margin-top: 2px;">Ref: {{ $entry->upi_reference }}</div>
                        @endif
                    </td>
                    <td class="text-end text-success">
                        {{ $entry->credit > 0 ? '₹' . number_format($entry->credit, 2) : '-' }}
                    </td>
                    <td class="text-end text-danger">
                        {{ $entry->debit > 0 ? '₹' . number_format($entry->debit, 2) : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: #888;">No transactions found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="7" class="text-end">Totals:</td>
                <td class="text-end text-success">₹{{ number_format($totalSales, 2) }}</td>
                <td class="text-end text-danger">₹{{ number_format($totalExpenses, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="7" class="text-end">Net Balance:</td>
                <td colspan="2" class="text-end {{ $netBalance >= 0 ? 'text-primary' : 'text-danger' }}">₹{{ number_format($netBalance, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Generated on {{ now()->format('d-m-Y h:i A') }} &mdash; {{ config('app.name', 'Saloon ERP') }}
    </div>
</body>
</html>
