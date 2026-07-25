@extends('admin.layouts.app')

@section('content')

<div class="container-fluid flex-grow-1 container-p-y hide-on-print">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Invoice Details: {{ $invoice->invoice_no }}</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.transport.invoices.index') }}">Invoice History</a></li>
                    <li class="breadcrumb-item active">{{ $invoice->invoice_no }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.transport.invoices.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back
            </a>
            <a href="{{ route('admin.transport.invoices.toll-print', $invoice->id) }}" class="btn btn-outline-primary" target="_blank">
                <i class="bx bx-printer me-1"></i> Toll Print View
            </a>
            <a href="{{ route('admin.transport.invoices.export-excel', $invoice->id) }}" class="btn btn-outline-success">
                <i class="bx bx-file me-1"></i> Excel Export
            </a>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="bx bx-printer me-1"></i> Print Invoice
            </button>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-9 col-12">
        <div class="card border-0 shadow-none p-0 m-0" style="background: transparent;">
            <div class="card-body p-4" style="background: #fff; color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 11px; line-height: 1.4;">
                @php
                    $freightTotal = $invoice->total_freight;
                    $gstTotal = $invoice->total_gst;
                    $otherTotal = $invoice->total_other;
                    $grandTotal = $invoice->total_amount;
                    $amountInWords = $invoice->amount_in_words;
                    $billNumber = $invoice->bill_number ?? $invoice->invoice_no;
                    $vendorCode = $invoice->vendor_code;
                    $invoiceCompany = $invoice->company;
                    $invoiceConsignor = (object) [
                        'name' => $invoice->consignor_name ?? ($invoice->consignor->name ?? '-'),
                        'address' => $invoice->billing_address ?? ($invoice->consignor->address ?? '-'),
                        'city' => $invoice->consignor->city ?? '',
                        'state' => $invoice->consignor->state ?? '',
                        'pincode' => $invoice->consignor->pincode ?? '',
                        'vendor_code' => $invoice->consignor->vendor_code ?? ''
                    ];
                    $bulties = $invoice->bulties;
                    $gstPercentage = $invoice->gstMaster ? floatval($invoice->gstMaster->percentage) : 0;
                    
                    $bankAccountNo = $invoiceCompany && $invoiceCompany->bank_account_no ? $invoiceCompany->bank_account_no : '';
                    $bankIfsc = $invoiceCompany && $invoiceCompany->bank_ifsc ? $invoiceCompany->bank_ifsc : '';
                    $bankHolder = $invoiceCompany && $invoiceCompany->bank_holder_name ? strtoupper($invoiceCompany->bank_holder_name) : '';
                    $grnNewPage = $invoice->grn_new_page ?? false;
                @endphp
                @if(($existingInvoice?->template_type ?? 'standard') === 'nathdwara')
                    @include('admin.transport.billing._nathdwara_invoice_preview', [
                        'invoiceCompany' => $invoiceCompany,
                        'existingInvoice' => $invoice,
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
                        'bankAccountNo' => $bankAccountNo,
                        'bankIfsc' => $bankIfsc,
                        'bankHolder' => $bankHolder,
                        'grnNewPage' => $grnNewPage,
                    ])
                @elseif(($existingInvoice?->template_type ?? 'standard') === 'gypsum')
                    @include('admin.transport.billing._gypsum_invoice_preview', [
                        'invoiceCompany' => $invoiceCompany,
                        'existingInvoice' => $invoice,
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
                        'bankAccountNo' => $bankAccountNo,
                        'bankIfsc' => $bankIfsc,
                        'bankHolder' => $bankHolder,
                        'grnNewPage' => $grnNewPage,
                    ])
                @else
                    @include('admin.transport.billing._invoice_preview', [
                        'invoiceCompany' => $invoiceCompany,
                        'existingInvoice' => $invoice,
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
                        'bankAccountNo' => $bankAccountNo,
                        'bankIfsc' => $bankIfsc,
                        'bankHolder' => $bankHolder,
                        'grnNewPage' => $grnNewPage,
                    ])
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('style')
<style>
    #preview-table th, #preview-table td, #preview-grn-table th, #preview-grn-table td {
        border: 1px solid #000 !important;
        padding: 2px 2px !important;
        text-align: center !important;
        vertical-align: middle !important;
        font-size: 8px !important;
        font-weight: normal;
        color: #000 !important;
        word-break: break-word;
    }
    #preview-table th, #preview-grn-table th {
        font-weight: bold !important;
    }

    @media print {
        @page {
            size: A4 landscape;
        }
        .hide-on-print, .main-header, .layout-navbar, .content-backdrop, .layout-menu-toggle, .layout-menu {
            display: none !important;
        }
        body, .layout-page, .content-wrapper, .container-xxl, .card, .card-body, .col-md-9, .col-12 {
            background: transparent !important;
            padding: 0 !important;
            margin: 0 !important;
            box-shadow: none !important;
            border: none !important;
            width: 100% !important;
            max-width: 100% !important;
            flex: 0 0 100% !important;
        }
        .row {
            width: 100% !important;
            margin: 0 !important;
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
@endsection
