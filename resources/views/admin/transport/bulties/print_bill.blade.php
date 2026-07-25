@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y hide-on-print">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Print Bill: {{ $billNumber }}</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.transport.bulties.index') }}">Bilties</a></li>
                    <li class="breadcrumb-item active">{{ $billNumber }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
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
                @include('admin.transport.billing._invoice_preview')
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
<script>
    window.onload = function() {
        window.print();
    }
</script>
@endsection
