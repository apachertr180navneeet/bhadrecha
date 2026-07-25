<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoiceNo }} — Print</title>
    <meta name="description" content="Tax Invoice {{ $invoiceNo }} for {{ $invoiceCompany?->name ?? 'Company' }}">
    <!-- Bootstrap CSS for layout -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* ─── Reset & Base ─────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            background: #eef2f5;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #111827;
        }

        /* ─── Toolbar (hidden on print) ─────────────────────── */
        .print-toolbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 9999;
            background: #0f3d4a;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,.25);
            gap: 12px;
        }
        .print-toolbar .tb-title {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: .3px;
        }
        .print-toolbar .tb-sub {
            font-size: 11px;
            opacity: .7;
            margin-top: 2px;
        }
        .tb-actions { display: flex; gap: 10px; align-items: center; }
        .btn-tb {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 18px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: opacity .15s;
        }
        .btn-tb:hover { opacity: .88; }
        .btn-print  { background: #fd5523; color: #fff; }
        .btn-back   { background: rgba(255,255,255,.15); color: #fff; }
        .btn-list   { background: rgba(255,255,255,.10); color: #fff; }

        /* ─── Page wrapper ──────────────────────────────────── */
        .page-wrap {
            padding: 80px 24px 48px;
            display: flex;
            justify-content: center;
            min-height: 100vh;
        }

        /* ─── Status Badge ───────────────────────────────────── */
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .status-pending   { background: #fff3cd; color: #856404; }
        .status-paid      { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }

        /* ─── Print Styles ────────────────────────────────────── */
        @media print {
            @page {
                size: A4 landscape;
                margin: 0;
            }
            html, body {
                background: #fff !important;
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            .print-toolbar { display: none !important; }
            .page-wrap { padding: 0 !important; min-height: auto !important; display: block !important; }
            .invoice-preview-container {
                width: 100% !important;
                max-width: 100% !important;
                box-shadow: none !important;
            }
            #freight-preview-sheet, #grn-preview-sheet {
                width: 100% !important;
                box-sizing: border-box;
                page-break-inside: avoid;
            }
            #preview-page-break {
                display: block !important;
                page-break-before: always !important;
                break-before: page !important;
                height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                visibility: hidden !important;
            }
        }
    </style>
</head>
<body>

{{-- ─── Toolbar (hidden when printing) ─────────────────── --}}
<div class="print-toolbar" id="printToolbar">
    <div>
        <div class="tb-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-3px;margin-right:6px"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
            Invoice: <strong>{{ $invoiceNo }}</strong>
            @php
                $statusMap = ['pending'=>'status-pending','paid'=>'status-paid','cancelled'=>'status-cancelled'];
                $cls = $statusMap[$invoice->status ?? 'pending'] ?? 'status-pending';
            @endphp
            <span class="status-badge {{ $cls }}" style="margin-left:10px;">{{ ucfirst($invoice->status ?? 'pending') }}</span>
        </div>
        <div class="tb-sub">{{ $invoiceCompany?->name ?? 'Company' }} &mdash; {{ $invoice->invoice_date?->format('d M Y') ?? now()->format('d M Y') }}</div>
    </div>
    <div class="tb-actions">
        <a href="{{ route('admin.transport.billing.invoices') }}" class="btn-tb btn-list">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
            All Invoices
        </a>
        <a href="{{ route('admin.transport.billing.create', ['ids' => $bulties->pluck('id')->join(',')]) }}" class="btn-tb btn-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Edit View
        </a>
        <button class="btn-tb btn-print" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Print Invoice
        </button>
    </div>
</div>

{{-- ─── Invoice Page ─────────────────────────────────────── --}}
<div class="page-wrap">
    <div style="width: 297mm; max-width: 100%; margin: 0 auto; background: #fff; box-shadow: 0 16px 40px rgba(15,23,42,.1);">
        @php
            $freightTotal = $invoice->total_freight;
            $gstTotal = $invoice->total_gst;
            $otherTotal = $invoice->total_other;
            $grandTotal = $invoice->total_amount;
            $amountInWords = $invoice->amount_in_words;
            $billNumber = $invoice->bill_number ?? $invoice->invoice_no;
            $vendorCode = $invoice->vendor_code;
            $invoiceConsignor = (object) [
                'name' => $invoice->consignor_name ?? ($invoice->consignor->name ?? '-'),
                'address' => $invoice->billing_address ?? ($invoice->consignor->address ?? '-'),
                'city' => $invoice->consignor->city ?? '',
                'state' => $invoice->consignor->state ?? '',
                'pincode' => $invoice->consignor->pincode ?? '',
                'vendor_code' => $invoice->consignor->vendor_code ?? ''
            ];
            $gstPercentage = $invoice->gstMaster ? floatval($invoice->gstMaster->percentage) : 0;
            $grnNewPage = $invoice->grn_new_page ?? false;
        @endphp
        @include('admin.transport.billing._invoice_preview', [
            'existingInvoice' => $invoice,
            'invoiceCompany' => $invoiceCompany,
            'invoiceConsignor' => $invoiceConsignor,
            'billNumber' => $billNumber,
            'vendorCode' => $vendorCode,
            'bulties' => $bulties,
            'gstPercentage' => $gstPercentage,
            'freightTotal' => $freightTotal,
            'gstTotal' => $gstTotal,
            'otherTotal' => $otherTotal,
            'grandTotal' => $grandTotal,
            'amountInWords' => $amountInWords,
        ])
    </div>
</div>

<script>
    // Auto-trigger print dialog when page loads (only in non-toolbar scenarios)
    // Remove the comment below if you want auto-print
    // window.onload = function() { window.print(); };
</script>
</body>
</html>