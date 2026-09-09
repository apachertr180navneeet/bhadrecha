<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Payslip - {{ $payroll->staff->full_name ?? 'N/A' }} - {{ date('F', mktime(0, 0, 0, $payroll->month, 1)) }} {{ $payroll->year }}</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 13px; color: #333; margin: 0; padding: 40px; }
        .header { text-align: center; border-bottom: 2px solid #1e293b; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 26px; color: #1e293b; text-transform: uppercase; letter-spacing: 2px; }
        .header p { margin: 5px 0 0; color: #64748b; font-size: 14px; }
        .company-details { text-align: center; margin-bottom: 10px; font-size: 12px; color: #64748b; }
        .payslip-title { text-align: center; font-size: 18px; font-weight: bold; color: #1e293b; margin-bottom: 25px; }
        .section { margin-bottom: 25px; }
        .section-title { font-size: 14px; font-weight: bold; color: #1e293b; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        table th, table td { padding: 8px 10px; text-align: left; border: 1px solid #ddd; }
        table th { background: #f1f5f9; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
        .employee-details td { border: none; padding: 4px 0; }
        .employee-details td:first-child { font-weight: 600; width: 150px; color: #64748b; }
        .amount { text-align: right; font-weight: 600; }
        .total-row td { font-weight: bold; font-size: 14px; background: #f8fafc; }
        .net-amount { font-size: 18px; color: #16a34a; font-weight: bold; }
        .signature { margin-top: 50px; padding-top: 20px; border-top: 1px solid #ddd; }
        .signature-line { margin-top: 50px; }
        .signature-line p { margin: 0; }
        .signature-line .line { display: inline-block; width: 200px; border-top: 1px solid #333; margin-top: 40px; }
        .footer { text-align: center; margin-top: 40px; font-size: 11px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name') }}</h1>
        <p>123 Infinity Street, Beauty City, BC 10001</p>
        <p>Phone: (555) 123-4567 | Email: info@infinitysalon.com</p>
    </div>

    <div class="payslip-title">PAYSLIP - {{ date('F', mktime(0, 0, 0, $payroll->month, 1)) }} {{ $payroll->year }}</div>

    <div class="section">
        <div class="section-title">Employee Details</div>
        <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
            <tr><td>Staff Name</td><td>{{ $payroll->staff->full_name ?? 'N/A' }}</td></tr>
            <tr><td>Email</td><td>{{ $payroll->staff->email ?? 'N/A' }}</td></tr>
            <tr><td>Phone</td><td>{{ $payroll->staff->phone ?? 'N/A' }}</td></tr>
            <tr><td>Pay Period</td><td>{{ date('F', mktime(0, 0, 0, $payroll->month, 1)) }} {{ $payroll->year }}</td></tr>
        </table>
                </div>
    </div>

    <div class="section">
        <div class="section-title">Salary Breakdown</div>
        <div class="table-responsive text-nowrap">
                    <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="amount">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Basic Salary</td>
                    <td class="amount">₹{{ number_format($payroll->basic_salary, 2) }}</td>
                </tr>

                <tr>
                    <td>Attendance Deductions</td>
                    <td class="amount" style="color:#dc2626;">- ₹{{ number_format($payroll->deductions ?? 0, 2) }}</td>
                </tr>
                @if(($payroll->advance_amount ?? 0) > 0)
                <tr>
                    <td>Salary Advance Deduction</td>
                    <td class="amount" style="color:#dc2626;">- ₹{{ number_format($payroll->advance_amount, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>Net Amount</td>
                    <td class="amount net-amount">₹{{ number_format($payroll->net_amount, 2) }}</td>
                </tr>
            </tbody>
        </table>
                </div>
    </div>

    <div class="signature">
        <div style="float: left; width: 45%;">
            <p><strong>Authorized Signature</strong></p>
            <div class="signature-line">
                <div class="line"></div>
                <p style="margin-top:5px; font-size:12px; color:#64748b;">Authorized By</p>
            </div>
        </div>
        <div style="float: right; width: 45%; text-align: right;">
            <p><strong>Employee Signature</strong></p>
            <div class="signature-line">
                <div class="line" style="float:right;"></div>
                <p style="margin-top:5px; font-size:12px; color:#64748b; clear:both;">Employee</p>
            </div>
        </div>
        <div style="clear:both;"></div>
    </div>

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>Generated on {{ now()->format('d M Y h:i A') }}</p>
    </div>
</body>
</html>
