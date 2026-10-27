@extends('admin.layouts.app')

@section('title', 'Letterhead #' . $letterhead->letter_no)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row page-titles mx-0 mb-3 align-items-center">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4 class="text-primary font-weight-bold">
                    <i class="bx bx-envelope-open me-2"></i>Letterhead Details (#{{ $letterhead->letter_no }})
                </h4>
                <p class="mb-0">View letterhead document summary and management actions.</p>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <a href="{{ route('admin.letterheads.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm me-2">
                <i class="bx bx-arrow-back me-1"></i> Back to List
            </a>
            <a href="{{ route('admin.letterheads.edit', $letterhead->id) }}" class="btn btn-primary btn-sm shadow-sm me-2">
                <i class="bx bx-edit-alt me-1"></i> Edit
            </a>
            <button type="button" onclick="window.print()" class="btn btn-secondary btn-sm shadow-sm me-2">
                <i class="bx bx-printer me-1"></i> Print
            </button>
            <a href="{{ route('admin.letterheads.pdf', $letterhead->id) }}" target="_blank" class="btn btn-info btn-sm shadow-sm">
                <i class="bx bx-file me-1"></i> Open PDF
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title text-white mb-0"><i class="bx bx-detail me-2"></i>{{ $letterhead->subject }}</h5>
                    <span class="badge badge-light text-primary font-weight-bold">{{ \Carbon\Carbon::parse($letterhead->letter_date)->format('d M, Y') }}</span>
                </div>
                <div class="card-body p-4 bg-light">
                    <!-- A4 Sheet Preview Box -->
                    <div id="letter-print-area" class="bg-white p-4 shadow-sm border rounded" style="font-family: sans-serif; font-size: 14px;">
                        
                        <!-- Company Header -->
                        <div class="border-bottom pb-2 mb-2 d-flex justify-content-between align-items-start">
                            <div>
                                @if(!empty($letterhead->company->logo))
                                    @php
                                        $cleanLogo = ltrim($letterhead->company->logo, '/');
                                        $logoUrl = Str::startsWith($cleanLogo, 'http') ? $cleanLogo : asset(Str::startsWith($cleanLogo, 'uploads/') ? $cleanLogo : 'uploads/' . $cleanLogo);
                                    @endphp
                                    <img src="{{ $logoUrl }}" style="max-height: 55px; max-width: 170px;" alt="Company Logo">
                                @else
                                    <h3 class="font-weight-bold mb-0" style="color: #062E39 !important; font-size: 1.4rem;">{{ $letterhead->company->name ?? 'COMPANY NAME' }}</h3>
                                @endif
                            </div>
                            <div class="text-right small text-muted" style="line-height: 1.25;">
                                <h6 class="font-weight-bold mb-1" style="color: #062E39 !important; font-size: 14px;">{{ $letterhead->company->name }}</h6>
                                @if(!empty($letterhead->company->address))
                                    <div>{{ $letterhead->company->address }}@if(!empty($letterhead->company->state)), {{ $letterhead->company->state }}@endif</div>
                                @endif
                                @if(!empty($letterhead->company->phone))<div>Phone: {{ $letterhead->company->phone }}</div>@endif
                                @if(!empty($letterhead->company->email))<div>Email: {{ $letterhead->company->email }}</div>@endif
                                @if(!empty($letterhead->company->gst_number))<div class="font-weight-bold text-dark">GSTIN: {{ $letterhead->company->gst_number }}</div>@endif
                            </div>
                        </div>

                        <!-- Ref & Date -->
                        <div class="d-flex justify-content-between mb-2 font-weight-bold small text-secondary">
                            <div>Ref No: <span class="text-dark">{{ $letterhead->letter_no }}</span></div>
                            <div>Date & Time: <span class="text-dark">{{ \Carbon\Carbon::parse($letterhead->letter_date)->format('d M, Y h:i A') }}</span></div>
                        </div>

                        <!-- Recipient -->
                        <div class="mb-2 small" style="line-height: 1.3;">
                            <div class="font-weight-bold text-secondary">To,</div>
                            <div class="font-weight-bold text-dark" style="font-size: 14px;">{{ $letterhead->recipient_name }}</div>
                            @if($letterhead->recipient_designation)<div class="text-muted">{{ $letterhead->recipient_designation }}</div>@endif
                            @if($letterhead->recipient_company)<div class="font-weight-bold text-dark">{{ $letterhead->recipient_company }}</div>@endif
                            @if($letterhead->recipient_address)<div class="text-dark">{!! nl2br(e($letterhead->recipient_address)) !!}</div>@endif
                            @if($letterhead->recipient_email)<div class="text-info"><i class="fa fa-envelope mr-1"></i>{{ $letterhead->recipient_email }}</div>@endif
                        </div>

                        <!-- Subject -->
                        <div class="bg-light p-2 my-2 border-left border-primary font-weight-bold text-dark small text-uppercase" style="border-left-width: 4px !important;">
                            SUBJECT: <span>{{ $letterhead->subject }}</span>
                        </div>

                        <!-- Content -->
                        <div class="text-justify my-2 text-dark" style="white-space: pre-wrap; line-height: 1.45; min-height: 150px; font-size: 13px;">
{!! e($letterhead->content) !!}
                        </div>

                        <!-- Signatory -->
                        <div class="mt-2 pt-2 text-right float-right" style="width: 240px; page-break-inside: avoid;">
                            <div class="small">Yours faithfully,</div>
                            <div class="font-weight-bold small" style="color: #062E39 !important;">For {{ $letterhead->company->name ?? 'Company' }}</div>
                            
                            <div class="my-1 text-right" style="min-height: 42px;">
                                @php
                                    $sigUrl = $letterhead->company->digital_signature_url ?? null;
                                    if (!$sigUrl && !empty($letterhead->company->digital_signature)) {
                                        $cleanSig = ltrim($letterhead->company->digital_signature, '/');
                                        $sigUrl = Str::startsWith($cleanSig, 'http') ? $cleanSig : asset(Str::startsWith($cleanSig, 'uploads/') ? $cleanSig : 'uploads/' . $cleanSig);
                                    }
                                @endphp
                                @if($sigUrl)
                                    <img src="{{ $sigUrl }}" class="signature-img" style="max-height: 42px; max-width: 140px; display: inline-block; vertical-align: middle;" alt="Digital Signature">
                                @else
                                    <div class="digital-signature-seal d-inline-block text-center border p-1 rounded" style="border: 1.5px solid #062E39 !important; background-color: #f8fafc; min-width: 130px;">
                                        <div style="font-size: 7.5pt; color: #062E39; font-weight: bold; letter-spacing: 0.5px;">
                                            <i class="bx bx-check-shield text-success me-1"></i> DIGITALLY SIGNED
                                        </div>
                                        <div style="font-size: 7pt; color: #555; font-style: italic;">
                                            {{ $letterhead->signatory_name ?: ($letterhead->company->name ?? 'Authorized Signatory') }}
                                        </div>
                                    </div>
                                @endif
                            </div>

                            @if($letterhead->signatory_name)<div class="font-weight-bold text-dark small">{{ $letterhead->signatory_name }}</div>@endif
                            @if($letterhead->signatory_designation)<div class="small text-muted">{{ $letterhead->signatory_designation }}</div>@endif
                            <div class="small text-muted font-italic mt-1" style="font-size: 10px;">
                                Date & Time: {{ \Carbon\Carbon::parse($letterhead->letter_date)->format('d M, Y h:i A') }}
                            </div>
                        </div>

                        <div class="clearfix"></div>

                        <!-- Footer -->
                        <div class="border-top pt-2 mt-3 text-center text-muted small" style="page-break-inside: avoid;">
                            <div class="font-weight-bold text-dark" style="font-size: 11px;">
                                {{ $letterhead->company->name }} 
                                @if(!empty($letterhead->company->address)) | {{ $letterhead->company->address }} @endif 
                                @if(!empty($letterhead->company->phone)) | Tel: {{ $letterhead->company->phone }} @endif
                            </div>
                            @if(!empty($letterhead->company->disclaimer))
                                <div class="font-italic text-muted" style="font-size: 9.5px; line-height: 1.2;">{{ $letterhead->company->disclaimer }}</div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    html, body {
        width: 210mm !important;
        height: 297mm !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
        font-size: 11px !important;
        line-height: 1.3 !important;
        overflow: hidden !important;
    }
    body * {
        visibility: hidden !important;
    }
    #letter-print-area, #letter-print-area * {
        visibility: visible !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    #letter-print-area {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        max-width: 210mm !important;
        margin: 0 !important;
        padding: 8mm 12mm !important;
        box-shadow: none !important;
        border: none !important;
        background: #fff !important;
        page-break-after: avoid !important;
        page-break-inside: avoid !important;
    }
    .layout-navbar, .layout-menu, .content-footer, .btn, .page-titles, .card-header, .sidebar, nav, header {
        display: none !important;
    }
    @page {
        size: A4 portrait;
        margin: 6mm 10mm;
    }
    #letter-print-area .signature-img,
    #letter-print-area .digital-signature-seal {
        display: inline-block !important;
        visibility: visible !important;
    }
}
</style>
@endsection
