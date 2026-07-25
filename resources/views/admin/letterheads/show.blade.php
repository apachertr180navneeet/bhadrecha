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
                    <div class="bg-white p-4 shadow-sm border rounded" style="font-family: sans-serif; font-size: 14px;">
                        
                        <!-- Company Header -->
                        <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-start">
                            <div>
                                @if(!empty($letterhead->company->logo))
                                    @php
                                        $cleanLogo = ltrim($letterhead->company->logo, '/');
                                        $logoUrl = Str::startsWith($cleanLogo, 'http') ? $cleanLogo : asset(Str::startsWith($cleanLogo, 'uploads/') ? $cleanLogo : 'uploads/' . $cleanLogo);
                                    @endphp
                                    <img src="{{ $logoUrl }}" style="max-height: 60px; max-width: 180px;" alt="Company Logo">
                                @else
                                    <h3 class="text-primary font-weight-bold mb-0">{{ $letterhead->company->name ?? 'COMPANY NAME' }}</h3>
                                @endif
                            </div>
                            <div class="text-right small text-muted">
                                <h6 class="font-weight-bold text-dark mb-1">{{ $letterhead->company->name }}</h6>
                                @if(!empty($letterhead->company->address))
                                    <div>{{ $letterhead->company->address }}@if(!empty($letterhead->company->state)), {{ $letterhead->company->state }}@endif</div>
                                @endif
                                @if(!empty($letterhead->company->phone))<div>Phone: {{ $letterhead->company->phone }}</div>@endif
                                @if(!empty($letterhead->company->email))<div>Email: {{ $letterhead->company->email }}</div>@endif
                                @if(!empty($letterhead->company->gst_number))<div class="font-weight-bold text-dark">GSTIN: {{ $letterhead->company->gst_number }}</div>@endif
                            </div>
                        </div>

                        <!-- Ref & Date -->
                        <div class="d-flex justify-content-between mb-3 font-weight-bold small text-secondary">
                            <div>Ref No: <span class="text-dark">{{ $letterhead->letter_no }}</span></div>
                            <div>Date & Time: <span class="text-dark">{{ \Carbon\Carbon::parse($letterhead->letter_date)->format('d M, Y h:i A') }}</span></div>
                        </div>

                        <!-- Recipient -->
                        <div class="mb-3 small">
                            <div class="font-weight-bold text-secondary">To,</div>
                            <div class="font-weight-bold text-dark" style="font-size: 15px;">{{ $letterhead->recipient_name }}</div>
                            @if($letterhead->recipient_designation)<div class="text-muted">{{ $letterhead->recipient_designation }}</div>@endif
                            @if($letterhead->recipient_company)<div class="font-weight-bold text-dark">{{ $letterhead->recipient_company }}</div>@endif
                            @if($letterhead->recipient_address)<div class="text-dark">{!! nl2br(e($letterhead->recipient_address)) !!}</div>@endif
                            @if($letterhead->recipient_email)<div class="text-info"><i class="fa fa-envelope mr-1"></i>{{ $letterhead->recipient_email }}</div>@endif
                        </div>

                        <!-- Subject -->
                        <div class="bg-light p-2 my-3 border-left border-primary font-weight-bold text-dark small text-uppercase">
                            SUBJECT: <span>{{ $letterhead->subject }}</span>
                        </div>

                        <!-- Content -->
                        <div class="text-justify my-4 text-dark" style="white-space: pre-wrap; line-height: 1.6; min-height: 200px;">
{!! e($letterhead->content) !!}
                        </div>

                        <!-- Signatory -->
                        <div class="mt-4 pt-3 text-right float-right" style="width: 240px;">
                            <div class="small">Yours faithfully,</div>
                            <div class="font-weight-bold small text-dark">For {{ $letterhead->company->name ?? 'Company' }}</div>
                            
                            <div class="my-2 text-right" style="min-height: 45px;">
                                @if(!empty($letterhead->company->digital_signature))
                                    @php
                                        $cleanSig = ltrim($letterhead->company->digital_signature, '/');
                                        $sigUrl = Str::startsWith($cleanSig, 'http') ? $cleanSig : asset(Str::startsWith($cleanSig, 'uploads/') ? $cleanSig : 'uploads/' . $cleanSig);
                                    @endphp
                                    <img src="{{ $sigUrl }}" style="max-height: 45px; max-width: 150px;" alt="Signature">
                                @endif
                            </div>

                            @if($letterhead->signatory_name)<div class="font-weight-bold text-dark small">{{ $letterhead->signatory_name }}</div>@endif
                            @if($letterhead->signatory_designation)<div class="small text-muted">{{ $letterhead->signatory_designation }}</div>@endif
                            <div class="small text-muted font-italic mt-1" style="font-size: 11px;">
                                Date & Time: {{ \Carbon\Carbon::parse($letterhead->letter_date)->format('d M, Y h:i A') }}
                            </div>
                        </div>

                        <div class="clearfix"></div>

                        <!-- Footer -->
                        <div class="border-top pt-2 mt-4 text-center text-muted small">
                            <div class="font-weight-bold text-dark">
                                {{ $letterhead->company->name }} 
                                @if(!empty($letterhead->company->address)) | {{ $letterhead->company->address }} @endif 
                                @if(!empty($letterhead->company->phone)) | Tel: {{ $letterhead->company->phone }} @endif
                            </div>
                            @if(!empty($letterhead->company->disclaimer))
                                <div class="font-italic text-muted" style="font-size: 10px;">{{ $letterhead->company->disclaimer }}</div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
