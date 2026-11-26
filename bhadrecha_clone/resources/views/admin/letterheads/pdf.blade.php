<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Letter - {{ $letterhead->letter_no }}</title>
    <style>
        @page {
            size: A4;
            margin: 80px 35px 55px 35px;
        }
        header {
            position: fixed;
            top: -68px;
            left: 0px;
            right: 0px;
            height: 58px;
            border-bottom: 2px solid #062E39;
            padding-bottom: 2px;
        }
        footer {
            position: fixed;
            bottom: -45px;
            left: 0px;
            right: 0px;
            height: 38px;
            border-top: 1px solid #ddd;
            padding-top: 2px;
            font-size: 7.5pt;
            color: #555;
            text-align: center;
        }
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.35;
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
            max-height: 50px;
            max-width: 150px;
        }
        .company-name {
            font-size: 13pt;
            font-weight: bold;
            color: #062E39 !important;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .company-info {
            font-size: 7.5pt;
            color: #444;
            line-height: 1.2;
        }
        .ref-table {
            width: 100%;
            margin-top: 0px;
            margin-bottom: 6px;
            border-collapse: collapse;
        }
        .ref-table td {
            font-size: 8.5pt;
        }
        .recipient-box {
            margin-bottom: 6px;
            line-height: 1.25;
            font-size: 8.5pt;
        }
        .recipient-title {
            font-weight: bold;
            color: #333;
        }
        .subject-box {
            font-weight: bold;
            font-size: 9pt;
            margin: 6px 0;
            padding: 4px 6px;
            background-color: #f4f6f9;
            border-left: 4px solid #062E39;
        }
        .letter-content {
            margin-top: 6px;
            margin-bottom: 8px;
            text-align: justify;
            white-space: pre-wrap;
            word-wrap: break-word;
            line-height: 1.35;
            font-size: 9pt;
        }
        .signatory-table {
            width: 100%;
            margin-top: 8px;
            page-break-inside: avoid;
        }
        .signature-img {
            max-height: 40px;
            max-width: 130px;
            display: block;
            margin: 0 0 2px auto;
        }
        .digital-signature-seal {
            display: inline-block;
            border: 1.5px solid #062E39;
            border-radius: 3px;
            padding: 3px 6px;
            background-color: #f8fafc;
            text-align: center;
            min-width: 130px;
            margin-bottom: 2px;
        }
        .footer-disclaimer {
            font-size: 7pt;
            color: #777;
            margin-top: 1px;
            font-style: italic;
        }
    </style>
</head>
@php
    $logoBase64 = null;
    if (!empty($letterhead->company->logo)) {
        $cleanLogo = ltrim($letterhead->company->logo, '/');
        $resolvedLogo = null;
        $logoCandidates = [
            public_path($cleanLogo),
            public_path('uploads/' . $cleanLogo),
            public_path('uploads/' . preg_replace('#^uploads/#', '', $cleanLogo)),
            storage_path('app/public/' . $cleanLogo),
            storage_path('app/public/' . preg_replace('#^storage/#', '', $cleanLogo)),
        ];
        foreach ($logoCandidates as $cand) {
            if (file_exists($cand) && is_file($cand)) {
                $resolvedLogo = $cand;
                break;
            }
        }
        if ($resolvedLogo && is_file($resolvedLogo)) {
            $ext = strtolower(pathinfo($resolvedLogo, PATHINFO_EXTENSION));
            $mime = ($ext === 'png') ? 'image/png' : (($ext === 'svg') ? 'image/svg+xml' : 'image/jpeg');
            $data = @file_get_contents($resolvedLogo);
            if ($data !== false) {
                $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode($data);
            }
        } elseif (\Illuminate\Support\Str::startsWith($cleanLogo, ['http://', 'https://'])) {
            $data = @file_get_contents($cleanLogo);
            if ($data !== false) {
                $ext = strtolower(pathinfo(parse_url($cleanLogo, PHP_URL_PATH), PATHINFO_EXTENSION)) ?: 'png';
                $mime = ($ext === 'png') ? 'image/png' : (($ext === 'svg') ? 'image/svg+xml' : 'image/jpeg');
                $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode($data);
            }
        }
    }

    $sigBase64 = null;
    if (!empty($letterhead->company->digital_signature)) {
        $cleanSig = ltrim($letterhead->company->digital_signature, '/');
        $resolvedSig = null;
        $sigCandidates = [
            public_path($cleanSig),
            public_path('uploads/' . $cleanSig),
            public_path('uploads/' . preg_replace('#^uploads/#', '', $cleanSig)),
            storage_path('app/public/' . $cleanSig),
            storage_path('app/public/' . preg_replace('#^storage/#', '', $cleanSig)),
        ];
        foreach ($sigCandidates as $cand) {
            if (file_exists($cand) && is_file($cand)) {
                $resolvedSig = $cand;
                break;
            }
        }
        if ($resolvedSig && is_file($resolvedSig)) {
            $ext = strtolower(pathinfo($resolvedSig, PATHINFO_EXTENSION));
            $mime = ($ext === 'png') ? 'image/png' : (($ext === 'svg') ? 'image/svg+xml' : 'image/jpeg');
            $data = @file_get_contents($resolvedSig);
            if ($data !== false) {
                $sigBase64 = 'data:' . $mime . ';base64,' . base64_encode($data);
            }
        } elseif (\Illuminate\Support\Str::startsWith($cleanSig, ['http://', 'https://'])) {
            $data = @file_get_contents($cleanSig);
            if ($data !== false) {
                $ext = strtolower(pathinfo(parse_url($cleanSig, PHP_URL_PATH), PATHINFO_EXTENSION)) ?: 'png';
                $mime = ($ext === 'png') ? 'image/png' : (($ext === 'svg') ? 'image/svg+xml' : 'image/jpeg');
                $sigBase64 = 'data:' . $mime . ';base64,' . base64_encode($data);
            }
        }
    }
@endphp
<body>

    <header>
        <table class="header-table">
            <tr>
                <td style="width: 58%;">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" class="company-logo" alt="Logo">
                    @else
                        <h1 class="company-name">{{ $letterhead->company->name ?? 'COMPANY NAME' }}</h1>
                    @endif
                </td>
                <td style="width: 42%; text-align: right;" class="company-info">
                    @if($logoBase64)
                        <h2 class="company-name" style="font-size: 11pt; margin-bottom: 2px;">{{ $letterhead->company->name }}</h2>
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
                <div style="font-weight: bold; margin-top: 3px; color: #062E39 !important;">For {{ $letterhead->company->name ?? 'Company' }}</div>
                
                <div style="margin-top: 4px; min-height: 40px; text-align: right;">
                    @if($sigBase64)
                        <img src="{{ $sigBase64 }}" class="signature-img" style="float: right;" alt="Signature">
                        <div style="clear: both;"></div>
                    @else
                        <div class="digital-signature-seal">
                            <div style="font-size: 7.5pt; color: #062E39; font-weight: bold; letter-spacing: 0.5px; text-transform: uppercase;">
                                DIGITALLY SIGNED
                            </div>
                            <div style="font-size: 6.5pt; color: #555; font-style: italic;">
                                {{ $letterhead->signatory_name ?: ($letterhead->company->name ?? 'Authorized Signatory') }}
                            </div>
                        </div>
                    @endif
                </div>

                @if(!empty($letterhead->signatory_name))
                    <div style="font-weight: bold; margin-top: 3px;">{{ $letterhead->signatory_name }}</div>
                @endif
                @if(!empty($letterhead->signatory_designation))
                    <div style="font-size: 8.5pt; color: #555;">{{ $letterhead->signatory_designation }}</div>
                @endif
                <div style="font-size: 7.5pt; color: #666; font-style: italic; margin-top: 3px;">
                    Signed Date & Time: {{ \Carbon\Carbon::parse($letterhead->letter_date)->format('d M, Y h:i A') }}
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
