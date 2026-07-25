<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Letter - {{ $letterhead->letter_no }}</title>
    <style>
        @page {
            margin: 110px 40px 100px 40px;
        }
        header {
            position: fixed;
            top: -95px;
            left: 0px;
            right: 0px;
            height: 90px;
            border-bottom: 2px solid #0056b3;
            padding-bottom: 8px;
        }
        footer {
            position: fixed;
            bottom: -85px;
            left: 0px;
            right: 0px;
            height: 75px;
            border-top: 1px solid #ddd;
            padding-top: 5px;
            font-size: 9pt;
            color: #555;
            text-align: center;
        }
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Arial, sans-serif;
            font-size: 10.5pt;
            line-height: 1.6;
            color: #222;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: top;
        }
        .company-logo {
            max-height: 65px;
            max-width: 180px;
        }
        .company-name {
            font-size: 16pt;
            font-weight: bold;
            color: #0056b3;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .company-info {
            font-size: 8.5pt;
            color: #444;
            line-height: 1.3;
        }
        .ref-table {
            width: 100%;
            margin-top: 10px;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .ref-table td {
            font-size: 10pt;
        }
        .recipient-box {
            margin-bottom: 20px;
            line-height: 1.4;
        }
        .recipient-title {
            font-weight: bold;
            color: #333;
        }
        .subject-box {
            font-weight: bold;
            font-size: 11pt;
            margin: 20px 0;
            padding: 6px 10px;
            background-color: #f4f6f9;
            border-left: 4px solid #0056b3;
        }
        .letter-content {
            margin-top: 15px;
            margin-bottom: 30px;
            text-align: justify;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .signatory-table {
            width: 100%;
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .signature-img {
            max-height: 50px;
            max-width: 150px;
            display: block;
            margin-bottom: 5px;
        }
        .footer-disclaimer {
            font-size: 8pt;
            color: #777;
            margin-top: 3px;
            font-style: italic;
        }
    </style>
</head>
@php
    $logoPath = null;
    if (!empty($letterhead->company->logo)) {
        $cleanLogo = ltrim($letterhead->company->logo, '/');
        if (file_exists(public_path($cleanLogo))) {
            $logoPath = public_path($cleanLogo);
        } elseif (file_exists(public_path('uploads/' . $cleanLogo))) {
            $logoPath = public_path('uploads/' . $cleanLogo);
        }
    }

    $sigPath = null;
    if (!empty($letterhead->company->digital_signature)) {
        $cleanSig = ltrim($letterhead->company->digital_signature, '/');
        if (file_exists(public_path($cleanSig))) {
            $sigPath = public_path($cleanSig);
        } elseif (file_exists(public_path('uploads/' . $cleanSig))) {
            $sigPath = public_path('uploads/' . $cleanSig);
        }
    }
@endphp
<body>

    <header>
        <table class="header-table">
            <tr>
                <td style="width: 60%;">
                    @if($logoPath)
                        <img src="{{ $logoPath }}" class="company-logo" alt="Logo">
                    @else
                        <h1 class="company-name">{{ $letterhead->company->name ?? 'COMPANY NAME' }}</h1>
                    @endif
                </td>
                <td style="width: 40%; text-align: right;" class="company-info">
                    @if($logoPath)
                        <h2 class="company-name" style="font-size: 12pt; margin-bottom: 3px;">{{ $letterhead->company->name }}</h2>
                    @endif
                    @if(!empty($letterhead->company->address))
                        <div>{{ $letterhead->company->address }}@if(!empty($letterhead->company->state)), {{ $letterhead->company->state }}@endif</div>
                    @endif
                    @if(!empty($letterhead->company->phone))
                        <div><strong>Phone:</strong> {{ $letterhead->company->phone }}</div>
                    @endif
                    @if(!empty($letterhead->company->email))
                        <div><strong>Email:</strong> {{ $letterhead->company->email }}</div>
                    @endif
                    @if(!empty($letterhead->company->gst_number))
                        <div><strong>GSTIN:</strong> {{ $letterhead->company->gst_number }}</div>
                    @endif
                    @if(!empty($letterhead->company->pan_number))
                        <div><strong>PAN:</strong> {{ $letterhead->company->pan_number }}</div>
                    @endif
                </td>
            </tr>
        </table>
    </header>

    <footer>
        <div>
            <strong>{{ $letterhead->company->name ?? 'Company' }}</strong>
            @if(!empty($letterhead->company->address)) | {{ $letterhead->company->address }} @endif
            @if(!empty($letterhead->company->phone)) | Tel: {{ $letterhead->company->phone }} @endif
        </div>
        @if(!empty($letterhead->company->disclaimer))
            <div class="footer-disclaimer">{{ $letterhead->company->disclaimer }}</div>
        @endif
    </footer>

    <!-- Main Content -->
    <table class="ref-table">
        <tr>
            <td style="width: 55%;">
                <strong>Ref No:</strong> {{ $letterhead->letter_no }}
            </td>
            <td style="width: 45%; text-align: right;">
                <strong>Date & Time:</strong> {{ \Carbon\Carbon::parse($letterhead->letter_date)->format('d M, Y h:i A') }}
            </td>
        </tr>
    </table>

    <div class="recipient-box">
        <div class="recipient-title">To,</div>
        <div style="font-weight: bold;">{{ $letterhead->recipient_name }}</div>
        @if(!empty($letterhead->recipient_designation))
            <div>{{ $letterhead->recipient_designation }}</div>
        @endif
        @if(!empty($letterhead->recipient_company))
            <div><strong>{{ $letterhead->recipient_company }}</strong></div>
        @endif
        @if(!empty($letterhead->recipient_address))
            <div>{!! nl2br(e($letterhead->recipient_address)) !!}</div>
        @endif
        @if(!empty($letterhead->recipient_email))
            <div>Email: {{ $letterhead->recipient_email }}</div>
        @endif
    </div>

    <div class="subject-box">
        SUBJECT: {{ strtoupper($letterhead->subject) }}
    </div>

    <div class="letter-content">
{!! nl2br(e($letterhead->content)) !!}
    </div>

    <table class="signatory-table">
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 50%; text-align: right;">
                <div>Yours faithfully,</div>
                <div style="font-weight: bold; margin-top: 5px;">For {{ $letterhead->company->name ?? 'Company' }}</div>
                
                <div style="margin-top: 15px; min-height: 45px;">
                    @if($sigPath)
                        <img src="{{ $sigPath }}" class="signature-img" style="float: right;" alt="Signature">
                        <div style="clear: both;"></div>
                    @endif
                </div>

                @if(!empty($letterhead->signatory_name))
                    <div style="font-weight: bold; margin-top: 5px;">{{ $letterhead->signatory_name }}</div>
                @endif
                @if(!empty($letterhead->signatory_designation))
                    <div style="font-size: 9pt; color: #555;">{{ $letterhead->signatory_designation }}</div>
                @endif
                <div style="font-size: 8pt; color: #666; font-style: italic; margin-top: 4px;">
                    Signed Date & Time: {{ \Carbon\Carbon::parse($letterhead->letter_date)->format('d M, Y h:i A') }}
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
