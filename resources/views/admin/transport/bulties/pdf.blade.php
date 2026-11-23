<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Bilty - {{ $bulty->lr_no ?? 'LR-NO' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 6mm;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8.5pt;
            color: #000;
            background: #fff;
            line-height: 1.2;
            -webkit-print-color-adjust: exact;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        .bilty-wrapper {
            border: 1.5px solid #000;
            width: 100%;
            margin: 0 auto;
            table-layout: fixed;
        }
        .grid-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
    </style>
</head>
<body>

@php
    $company = $bulty->company ?? null;
    $sigPath = null;
    $sigBase64 = null;
    if ($company && !empty($company->digital_signature)) {
        $cleanSig = ltrim($company->digital_signature, '/\\');
        $possibleSigPaths = [
            public_path($cleanSig),
            public_path('uploads/' . $cleanSig),
            base_path('public/' . $cleanSig),
            base_path('public/uploads/' . $cleanSig),
            'c:/laragon/www/laravel_project/bhadrecha/public/' . $cleanSig,
        ];
        foreach ($possibleSigPaths as $p) {
            if (file_exists($p)) {
                $sigPath = $p;
                break;
            }
        }
        if ($sigPath && file_exists($sigPath)) {
            $sType = pathinfo($sigPath, PATHINFO_EXTENSION);
            $sigBase64 = 'data:image/' . ($sType === 'svg' ? 'svg+xml' : $sType) . ';base64,' . base64_encode(file_get_contents($sigPath));
        }
    }

    $logoPath = null;
    $logoBase64 = null;
    if ($company && !empty($company->logo)) {
        $cleanLogo = ltrim($company->logo, '/\\');
        $possiblePaths = [
            public_path($cleanLogo),
            public_path('uploads/' . $cleanLogo),
            base_path('public/' . $cleanLogo),
            base_path('public/uploads/' . $cleanLogo),
            'c:/laragon/www/laravel_project/bhadrecha/public/' . $cleanLogo,
        ];
        foreach ($possiblePaths as $p) {
            if (file_exists($p)) {
                $logoPath = $p;
                break;
            }
        }
    }
    if (!$logoPath) {
        $fallbackLogos = [
            public_path('assets/admin/img/logo.png'),
            base_path('public/assets/admin/img/logo.png'),
            'c:/laragon/www/laravel_project/bhadrecha/public/assets/admin/img/logo.png',
        ];
        foreach ($fallbackLogos as $p) {
            if (file_exists($p)) {
                $logoPath = $p;
                break;
            }
        }
    }
    if ($logoPath && file_exists($logoPath)) {
        $lType = pathinfo($logoPath, PATHINFO_EXTENSION);
        $logoBase64 = 'data:image/' . ($lType === 'svg' ? 'svg+xml' : $lType) . ';base64,' . base64_encode(file_get_contents($logoPath));
    }

    // Build LR Details rows for right side of middle table
    $lrRows = [
        ['label' => 'LR. No.', 'value' => $bulty->lr_no ?? '-'],
        ['label' => 'Date', 'value' => $bulty->lr_date ? date('d M Y', strtotime($bulty->lr_date)) : '-'],
        ['label' => 'Truck No.', 'value' => $bulty->vehicle->vehicle_number ?? '-'],
        ['label' => 'Fleet Owner', 'value' => strtoupper($company->name ?? 'BHADRECHA LOGISTICS')],
        ['label' => 'Driver Name', 'value' => strtoupper($bulty->driver->name ?? '-')],
        ['label' => 'Driver Ph. No.', 'value' => $bulty->driver->phone ? '+91-' . $bulty->driver->phone : '-'],
        ['label' => 'Order No.', 'value' => !empty(trim($bulty->order_number ?? '', ' :-')) ? $bulty->order_number : '-'],
        ['label' => 'Delivery No.', 'value' => !empty(trim($bulty->delivery_number ?? '', ' :-')) ? $bulty->delivery_number : '-'],
    ];

    if (!empty($bulty->delivery_date) || !empty($bulty->bultyDetail?->delivery_date)) {
        $lrRows[] = ['label' => 'Delivery Dt', 'value' => date('d M Y', strtotime($bulty->delivery_date ?? $bulty->bultyDetail->delivery_date))];
    }
    if (!empty($bulty->delivery_time) || !empty($bulty->bultyDetail?->delivery_time)) {
        $lrRows[] = ['label' => 'Delivery Time', 'value' => $bulty->delivery_time ?? $bulty->bultyDetail->delivery_time];
    }

    $formNo = !empty(trim($bulty->from_no ?? '', ' :-')) ? $bulty->from_no : '---';
    $invNo = !empty(trim($bulty->invoice_number ?? '', ' :-')) ? $bulty->invoice_number : '-';
    $invDt = $bulty->invoice_date ? date('d M Y', strtotime($bulty->invoice_date)) : '-';
    $ewayNo = !empty(trim($bulty->eway_bill_no ?? '', ' :-')) ? $bulty->eway_bill_no : '--';
    $genDt = $bulty->generation_date ? date('d M Y', strtotime($bulty->generation_date)) : '-';
    $expDt = $bulty->expiry_date ? date('d M Y', strtotime($bulty->expiry_date)) : '-';

    $lrRows[] = ['label' => 'Form no.', 'value' => $formNo];
    $lrRows[] = ['label' => 'Invoice No.', 'value' => $invNo];
    $lrRows[] = ['label' => 'Invoice Dt', 'value' => $invDt];
    $lrRows[] = ['label' => 'E-Way Bill No.', 'value' => $ewayNo];
    $lrRows[] = ['label' => 'Generation Dt', 'value' => $genDt];
    $lrRows[] = ['label' => 'Expiry Dt', 'value' => $expDt];

    $items = $bulty->bultyItems ?? collect();
    $totalArticles = 0;
    $totalWeight = 0;
    $totalFreight = 0;
    foreach ($items as $it) {
        $totalArticles += $it->articles ?? 0;
        $totalWeight += $it->weight ?? 0;
        $totalFreight += $it->amount ?? 0;
    }

    $advance = (float)($bulty->advance ?? ($bulty->advance_amount ?? 0));
    $remaining = max(0, $totalFreight - $advance);
    $biltyCommission = $bulty->bilty_commission ? number_format($bulty->bilty_commission, fmod($bulty->bilty_commission, 1) !== 0.0 ? 2 : 0, '.', '') : '1680';

    $totalLrRows = count($lrRows);
    $bodyRowCount = $totalLrRows - 4; // Rows between header and 3 totals rows
@endphp

<!-- MASTER WRAPPER TABLE -->
<table class="bilty-wrapper">
    <!-- 1. DISPUTE NOTE -->
    <tr>
        <td style="border-bottom: 1px solid #000; padding: 2px 0; text-align: center; font-size: 7.5pt;">
            Disputes are subject to Jodhpur Jurisdiction only
        </td>
    </tr>

    <!-- 2. HEADER -->
    <tr>
        <td style="padding: 4px 6px 6px 6px; vertical-align: top;">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 15%; text-align: left; vertical-align: middle; padding: 2px;">
                        @if($logoBase64)
                            <img src="{{ $logoBase64 }}" style="max-width: 80px; max-height: 65px; height: auto;" alt="Logo">
                        @endif
                    </td>
                    <td style="width: 55%; text-align: center; vertical-align: middle; padding: 0 4px;">
                        <div style="font-size: 20pt; font-weight: bold; letter-spacing: 0.5px; text-transform: uppercase; line-height: 1.1;">
                            {{ $company->name ?? 'BHADRECHA LOGISTICS' }}
                        </div>
                        <div style="font-size: 6.8pt; font-weight: bold; line-height: 1.35; margin-top: 5px; text-transform: uppercase;">
                            HEAD OFFICE: {{ $company->address ?? '23/B/121 C.H.B BEHIND ASHOK UDHYAN PAL ROAD, JODHPUR - 342001 (RAJASTHAN)' }}<br>
                            BRANCH OFFICE: {{ $bulty->branch?->address ?: ($company->branch_address ?? 'NEAR SARLANAGAR BYPASS ROAD, HARANAMPUR, MAIHAR(M.P)-485772') }}
                        </div>
                    </td>
                    <td style="width: 30%; text-align: right; vertical-align: top; font-size: 7.5pt; line-height: 1.35; padding: 0;">
                        <strong>MR. Harish Suthar : 9829894925</strong><br>
                        <strong>MR. Dinesh Suthar : 9314194925</strong><br>
                        Email id : {{ $company->email ?? 'bhadrechalogistics@gmail.com' }}<br>
                        GSTIN : {{ $company->gst_number ?? '08HBIPS8626G1Z0' }}<br>
                        PAN No. : {{ $company->pan_number ?? 'HBIPS8626G' }}<br>
                        Vendor Code : {{ $bulty->consignor?->vendor_code ?: '2904324' }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- 3. FROM / TO -->
    <tr>
        <td style="border-top: 1px solid #000; padding: 0;">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%; padding: 3px 6px; border-right: 1px solid #000; font-size: 8.5pt;">
                        <strong>FROM :</strong> {{ $bulty->originCity->name ?? '-' }}{{ isset($bulty->originCity->state) ? ', ' . $bulty->originCity->state : '' }}
                    </td>
                    <td style="width: 50%; padding: 3px 6px; font-size: 8.5pt;">
                        <strong>TO :</strong> {{ $bulty->destinationCity->name ?? '-' }}{{ isset($bulty->destinationCity->state) ? ', ' . $bulty->destinationCity->state : '' }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- 4. CONSIGNOR / CONSIGNEE -->
    <tr>
        <td style="border-top: 1px solid #000; padding: 0;">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%; padding: 4px 6px; border-right: 1px solid #000; vertical-align: top; font-size: 8pt; line-height: 1.3;">
                        <div style="font-weight: bold;">CONSIGNOR :</div>
                        <div style="font-weight: bold; text-transform: uppercase;">{{ $bulty->consignor->name ?? '-' }}</div>
                        <div>{!! nl2br(e($bulty->consignor->address ?? '-')) !!}</div>
                        @if(!empty($bulty->consignor->phone)) <div>Phone: {{ $bulty->consignor->phone }}</div> @endif
                        <div>GSTIN: {{ $bulty->consignor->gstin ?? ($bulty->consignor->gst_no ?? '-') }}</div>
                    </td>
                    <td style="width: 50%; padding: 4px 6px; vertical-align: top; font-size: 8pt; line-height: 1.3;">
                        <div style="font-weight: bold;">CONSIGNEE :</div>
                        <div style="font-weight: bold; text-transform: uppercase;">{{ $bulty->consignee->name ?? '-' }}</div>
                        <div>{!! nl2br(e($bulty->consignee->address ?? '-')) !!}</div>
                        @if(!empty($bulty->consignee->phone)) <div>Phone: {{ $bulty->consignee->phone }}</div> @endif
                        <div>GSTIN: {{ $bulty->consignee->gstin ?? ($bulty->consignee->gst_no ?? '-') }}</div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- 5. UNIFIED ITEMS & LR DETAILS GRID -->
    <tr>
        <td style="border-top: 1px solid #000; padding: 0; vertical-align: top;">
            <table class="grid-table">
                <colgroup>
                    <col style="width: 13%;">
                    <col style="width: 9%;">
                    <col style="width: 9%;">
                    <col style="width: 9%;">
                    <col style="width: 12%;">
                    <col style="width: 15%;">
                    <col style="width: 33%;">
                </colgroup>
                
                <!-- Row 0: Table Header -->
                <tr style="height: 23px;">
                    <th style="font-size: 8pt; font-weight: bold; text-align: center; border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 3px 2px;">ITEM</th>
                    <th style="font-size: 8pt; font-weight: bold; text-align: center; border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 3px 2px;">NO. OF<br>ARTICLES</th>
                    <th style="font-size: 8pt; font-weight: bold; text-align: center; border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 3px 2px;">TOTAL<br>WEIGHT</th>
                    <th style="font-size: 8pt; font-weight: bold; text-align: center; border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 3px 2px;">RATE</th>
                    <th style="font-size: 8pt; font-weight: bold; text-align: center; border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 3px 2px;">TOTAL FREIGHT</th>
                    <td style="font-size: 8pt; font-weight: bold; padding: 2px 5px; border-right: 1px solid #000; border-bottom: 1px solid #000; white-space: nowrap;">{{ $lrRows[0]['label'] }}</td>
                    <td style="font-size: 8pt; padding: 2px 5px; border-bottom: 1px solid #000; white-space: nowrap;">{{ $lrRows[0]['value'] }}</td>
                </tr>

                <!-- Rows 1 to N-4: Items & Spacer Rows -->
                @for($r = 1; $r <= $bodyRowCount; $r++)
                    @php
                        $itemIdx = $r - 1;
                        $currItem = $items->get($itemIdx);
                        $lr = $lrRows[$r];
                    @endphp
                    <tr style="height: 22px;">
                        @if($currItem)
                            <td style="font-size: 8pt; text-align: center; border-right: 1px solid #000; padding: 2px;">{{ strtoupper($currItem->item_name ?? '-') }}</td>
                            <td style="font-size: 8pt; text-align: center; border-right: 1px solid #000; padding: 2px;">{{ $currItem->articles ?? '-' }}</td>
                            <td style="font-size: 8pt; text-align: center; border-right: 1px solid #000; padding: 2px;">{{ $currItem->weight ? number_format($currItem->weight, fmod($currItem->weight, 1) !== 0.0 ? 2 : 0, '.', '') : '-' }}{{ $currItem->unit ?? '' }}</td>
                            <td style="font-size: 8pt; text-align: center; border-right: 1px solid #000; padding: 2px;">{{ $currItem->freight_per_mt ? number_format($currItem->freight_per_mt, fmod($currItem->freight_per_mt, 1) !== 0.0 ? 2 : 0, '.', '') : '-' }}</td>
                            <td style="font-size: 8pt; text-align: center; border-right: 1px solid #000; padding: 2px;">{{ $currItem->amount ? number_format($currItem->amount, fmod($currItem->amount, 1) !== 0.0 ? 2 : 0, '.', '') : '-' }}</td>
                        @else
                            <td style="border-right: 1px solid #000;"></td>
                            <td style="border-right: 1px solid #000;"></td>
                            <td style="border-right: 1px solid #000;"></td>
                            <td style="border-right: 1px solid #000;"></td>
                            <td style="border-right: 1px solid #000;"></td>
                        @endif
                        <td style="font-size: 8pt; font-weight: bold; padding: 2px 5px; border-right: 1px solid #000; border-bottom: 1px solid #000; white-space: nowrap;">{{ $lr['label'] }}</td>
                        <td style="font-size: 8pt; padding: 2px 5px; border-bottom: 1px solid #000; white-space: nowrap;">{{ $lr['value'] }}</td>
                    </tr>
                @endfor

                @php
                    $lr11 = $lrRows[$totalLrRows - 3]; // E-Way Bill No.
                    $lr12 = $lrRows[$totalLrRows - 2]; // Generation Dt
                    $lr13 = $lrRows[$totalLrRows - 1]; // Expiry Dt
                @endphp

                <!-- Row 12: Remarks + Total Freight -->
                <tr style="height: 22px;">
                    <td colspan="3" rowspan="2" style="font-size: 8pt; border-top: 1px solid #000; border-right: 1px solid #000; padding: 3px 5px; vertical-align: top;">
                        <strong>Remarks :</strong>{{ strtoupper($bulty->remark ?? '-') }}
                    </td>
                    <td style="font-size: 8pt; font-weight: bold; border-top: 1px solid #000; border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 2px 4px; text-align: left;">
                        Total Freight
                    </td>
                    <td style="font-size: 8pt; border-top: 1px solid #000; border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 2px 4px; text-align: left;">
                        {{ $totalFreight ? number_format($totalFreight, fmod($totalFreight, 1) !== 0.0 ? 2 : 0, '.', '') : '0' }}
                    </td>
                    <td style="font-size: 8pt; font-weight: bold; padding: 2px 5px; border-right: 1px solid #000; border-bottom: 1px solid #000; white-space: nowrap;">{{ $lr11['label'] }}</td>
                    <td style="font-size: 8pt; padding: 2px 5px; border-bottom: 1px solid #000; white-space: nowrap;">{{ $lr11['value'] }}</td>
                </tr>

                <!-- Row 13: Advance -->
                <tr style="height: 22px;">
                    <td style="font-size: 8pt; font-weight: bold; border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 2px 4px; text-align: left;">
                        Advance
                    </td>
                    <td style="font-size: 8pt; border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 2px 4px; text-align: left;">
                        {{ $advance ? number_format($advance, fmod($advance, 1) !== 0.0 ? 2 : 0, '.', '') : '0' }}
                    </td>
                    <td style="font-size: 8pt; font-weight: bold; padding: 2px 5px; border-right: 1px solid #000; border-bottom: 1px solid #000; white-space: nowrap;">{{ $lr12['label'] }}</td>
                    <td style="font-size: 8pt; padding: 2px 5px; border-bottom: 1px solid #000; white-space: nowrap;">{{ $lr12['value'] }}</td>
                </tr>

                <!-- Row 14: Commission + Remaining -->
                <tr style="height: 22px;">
                    <td colspan="3" style="font-size: 8pt; border-top: 1px solid #000; border-right: 1px solid #000; padding: 3px 5px; vertical-align: middle;">
                        <strong>Bilty Commission:</strong>{{ $biltyCommission }}
                    </td>
                    <td style="font-size: 8pt; font-weight: bold; border-right: 1px solid #000; padding: 2px 4px; text-align: left;">
                        Remaining
                    </td>
                    <td style="font-size: 8pt; border-right: 1px solid #000; padding: 2px 4px; text-align: left;">
                        {{ $remaining ? number_format($remaining, fmod($remaining, 1) !== 0.0 ? 2 : 0, '.', '') : '0' }}
                    </td>
                    <td style="font-size: 8pt; font-weight: bold; padding: 2px 5px; border-right: 1px solid #000; white-space: nowrap;">{{ $lr13['label'] }}</td>
                    <td style="font-size: 8pt; padding: 2px 5px; white-space: nowrap;">{{ $lr13['value'] }}</td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- 6. FOOTER NOTE -->
    <tr>
        <td style="border-top: 1px solid #000; padding: 4px 6px; font-size: 7.5pt; line-height: 1.35;">
            <strong>GST Applicable as per rules</strong><br>
            KINDLY RETURN THE BILTY WITH SEAL &amp; SIGN.<br>
            NOTE : FREIGHT WILL BE PAID ONLY IF THE GOODS RECEIVING RECEIPT SUBMITTED TO OFFICE WITHIN 15 DAYS.
        </td>
    </tr>

    <!-- 7. SIGNATURE & STAMP SECTION -->
    <tr>
        <td style="border-top: 1px solid #000; padding: 0;">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%; padding: 6px 8px; border-right: 1px solid #000; vertical-align: top;">
                        <div style="font-size: 8pt; font-weight: bold;">Material Received :</div>
                        <div style="height: 48px;"></div>
                        <div style="font-size: 7.5pt;">Receiving Date, Signature and Rubber Stamp.</div>
                    </td>
                    <td style="width: 50%; padding: 6px 8px; vertical-align: top; text-align: right;">
                        <div style="font-size: 8pt;">For <strong>{{ strtoupper($company->name ?? 'BHADRECHA LOGISTICS') }}</strong></div>
                        <table style="width: 100%; margin-top: 14px; border: none;">
                            <tr>
                                <td style="text-align: right; vertical-align: middle; border: none; padding-right: 12px;">
                                    @if($sigBase64)
                                        <img src="{{ $sigBase64 }}" alt="Digital Signature" style="max-height: 40px; max-width: 130px; object-fit: contain;">
                                    @else
                                        <div style="font-size: 10pt; font-weight: bold; text-transform: uppercase; line-height: 1.1;">
                                            {{ $company->owner_name ?? 'HARISH SUTHAR' }}
                                        </div>
                                    @endif
                                </td>
                                <td style="text-align: right; vertical-align: middle; border: none; font-size: 7pt; width: 75px; line-height: 1.3;">
                                    signed by<br>
                                    Date: {{ $bulty->created_at ? date('d/m/Y', strtotime($bulty->created_at)) : date('d/m/Y') }}<br>
                                    {{ $bulty->created_at ? date('H:i:s', strtotime($bulty->created_at)) : date('H:i:s') }}
                                </td>
                            </tr>
                        </table>
                        <div style="font-size: 8pt; margin-top: 6px;">Signature Of Booking Clerk</div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>
