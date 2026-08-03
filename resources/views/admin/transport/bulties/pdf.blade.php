<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bilty - {{ $bulty->lr_no ?? 'LR-NO' }}</title>
    <style>
        @page {
            size: A4;
            margin: 8mm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        table {
            border-collapse: collapse;
        }
        .bilty-wrapper {
            border: 2px solid #000;
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
            table-layout: fixed;
        }
        .dispute-note {
            font-size: 8px;
            font-style: italic;
            text-align: center;
            letter-spacing: 0.3px;
            padding: 3px 0;
        }
        .company-name {
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 2.5px;
            text-transform: uppercase;
        }
        .address-text {
            font-size: 7.5px;
            line-height: 1.4;
            text-transform: uppercase;
        }
        .contact-text {
            font-size: 8px;
            line-height: 1.3;
        }
        .logo-img {
            width: 80px;
            height: auto;
        }
        .section-label {
            font-weight: 700;
            font-size: 10px;
        }
        .party-name {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10px;
            margin-top: 2px;
        }
        .party-details {
            font-size: 9px;
            line-height: 1.4;
        }
        .item-table {
            width: 100%;
        }
        .item-table th {
            font-weight: 700;
            font-size: 9px;
            text-align: center;
            padding: 4px 2px;
            border-bottom: 1px solid #000;
            border-right: 1px solid #000;
        }
        .item-table td {
            font-size: 9px;
            text-align: center;
            padding: 4px 2px;
            border-bottom: none;
            border-right: 1px solid #000;
        }
        .item-table tr > th:last-child,
        .item-table tr > td:last-child {
            border-right: none;
        }
        
        .lr-table {
            width: 100%;
        }
        .lr-table td {
            font-size: 9px;
            padding: 3px 4px;
            border-bottom: 1px solid #000;
        }
        .lr-table tr:last-child td {
            border-bottom: none;
        }
        .lr-label {
            font-weight: 700;
            width: 44%;
            border-right: 1px solid #000;
        }
        .lr-value {
            width: 56%;
        }
        .footer-note {
            font-size: 8px;
            line-height: 1.4;
        }
    </style>
</head>
<body>

<?php 
    $company = $bulty->company ?? null; 
    $sigPath = null;
    if ($company && !empty($company->digital_signature)) {
        $cleanSig = ltrim($company->digital_signature, '/');
        if (file_exists(public_path($cleanSig))) {
            $sigPath = public_path($cleanSig);
        } elseif (file_exists(public_path('uploads/' . $cleanSig))) {
            $sigPath = public_path('uploads/' . $cleanSig);
        } elseif (\Illuminate\Support\Str::startsWith($cleanSig, ['http://', 'https://'])) {
            $sigPath = $cleanSig;
        }
    }
?>

<!-- MASTER WRAPPER TABLE -->
<table class="bilty-wrapper">
    <!-- 1. DISPUTE NOTE -->
    <tr>
        <td style="border-bottom: 2px solid #000; padding: 0; vertical-align: top;">
            <div class="dispute-note">
                Disputes are subject to Jodhpur Jurisdiction only
            </div>
        </td>
    </tr>

    <!-- 2. HEADER -->
    <tr>
        <td style="padding: 0; vertical-align: top;">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 15%; text-align: center; padding: 5px;">
                        <img src="{{ $company && $company->logo && file_exists(public_path($company->logo)) ? public_path($company->logo) : public_path('assets/admin/img/logo.png') }}" alt="Logo" class="logo-img">
                    </td>
                    <td style="width: 55%; text-align: center; padding: 5px; vertical-align: middle;">
                        <div class="company-name">{{ strtoupper($company->name ?? '') }}</div>
                    </td>
                    <td style="width: 30%; text-align: right; padding: 5px 10px 5px 5px; vertical-align: top;" class="contact-text">
                        @if($company)
                            @if($company->phone) <strong>{{ $company->phone }}</strong><br> @endif
                            @if($company->email) Email id : {{ $company->email }}<br> @endif
                            @if($company->gst_number) GSTIN : {{ $company->gst_number }}<br> @endif
                            @if($company->pan_number) PAN No. : {{ $company->pan_number }} @endif
                        @endif
                        <br>
                        Vendor Code : 2122109<br>
                        2805688
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="address-text" style="text-align: center; padding-bottom: 8px;">
                        <strong>HEAD OFFICE:</strong> {{ $company->address ?? '' }}
                        <br>
                        <strong>BRANCH OFFICE:</strong> {{ $company->branch_address ?? '' }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- 3. FROM / TO -->
    <tr>
        <td style="border-top: 1px solid #000; padding: 0; vertical-align: top;">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%; padding: 4px 8px; border-right: 1px solid #000;">
                        <span class="section-label">FROM :</span> {{ $bulty->originCity->name ?? '-' }}{{ isset($bulty->originCity->state) ? ', ' . $bulty->originCity->state : '' }}
                    </td>
                    <td style="width: 50%; padding: 4px 8px;">
                        <span class="section-label">TO :</span> {{ $bulty->destinationCity->name ?? '-' }}{{ isset($bulty->destinationCity->state) ? ', ' . $bulty->destinationCity->state : '' }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- 4. CONSIGNOR / CONSIGNEE -->
    <tr>
        <td style="border-top: 1px solid #000; padding: 0; vertical-align: top;">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%; padding: 4px 8px; border-right: 1px solid #000; vertical-align: top;" class="party-details">
                        <div class="section-label">CONSIGNOR :</div>
                        <div class="party-name">{{ strtoupper($bulty->consignor->name ?? '-') }}</div>
                        <div>{{ $bulty->consignor->address ?? '-' }}</div>
                        @if(!empty($bulty->consignor->phone)) <div>Phone: {{ $bulty->consignor->phone }}</div> @endif
                        @if(!empty($bulty->consignor->gstin)) <div>GSTIN: {{ $bulty->consignor->gstin }}</div> @elseif(!empty($bulty->consignor->gst_no)) <div>GSTIN: {{ $bulty->consignor->gst_no }}</div> @endif
                    </td>
                    <td style="width: 50%; padding: 4px 8px; vertical-align: top;" class="party-details">
                        <div class="section-label">CONSIGNEE :</div>
                        <div class="party-name">{{ strtoupper($bulty->consignee->name ?? '-') }}</div>
                        <div>{{ $bulty->consignee->address ?? '-' }}</div>
                        @if(!empty($bulty->consignee->phone)) <div>Phone: {{ $bulty->consignee->phone }}</div> @endif
                        @if(!empty($bulty->consignee->gstin)) <div>GSTIN: {{ $bulty->consignee->gstin }}</div> @elseif(!empty($bulty->consignee->gst_no)) <div>GSTIN: {{ $bulty->consignee->gst_no }}</div> @endif
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- 5. ITEMS & LR DETAILS -->
    <tr>
        <td style="border-top: 1px solid #000; padding: 0; vertical-align: top;">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 58%; padding: 0; vertical-align: top; border-right: 1px solid #000;">
                        <table class="item-table">
                            <thead>
                                <tr>
                                    <th style="width:20%;">ITEM</th>
                                    <th style="width:20%;">NO. OF<br>ARTICLES</th>
                                    <th style="width:20%;">TOTAL<br>WEIGHT</th>
                                    <th style="width:20%;">RATE</th>
                                    <th style="width:20%;">TOTAL FREIGHT</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $items = $bulty->bultyItems ?? collect();
                                    $totalArticles = 0;
                                    $totalWeight = 0;
                                    $totalFreight = 0;
                                @endphp
                                @forelse($items as $item)
                                    @php
                                        $totalArticles += $item->articles ?? 0;
                                        $totalWeight += $item->weight ?? 0;
                                        $totalFreight += $item->amount ?? 0;
                                    @endphp
                                    <tr>
                                        <td>{{ strtoupper($item->item_name ?? '-') }}</td>
                                        <td>{{ $item->articles ?? '-' }}</td>
                                        <td>{{ $item->weight ? number_format($item->weight, 2) : '-' }} {{ $item->unit ?? '' }}</td>
                                        <td>{{ $item->freight_per_mt ? number_format($item->freight_per_mt, 2) : '-' }}</td>
                                        <td>{{ $item->amount ? number_format($item->amount, 2) : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                    </tr>
                                @endforelse
                                
                                {{-- Fixed-height spacer row ensures vertical column lines are drawn and pushes footer down --}}
                                <tr>
                                    <td style="height: 100mm;"></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" rowspan="2" style="text-align: left; padding: 4px; vertical-align: top; border-top: 1px solid #000;">
                                        <strong>Remarks :</strong> {{ strtoupper($bulty->remark ?? '-') }}
                                    </td>
                                    <td style="text-align: left; padding: 2px 4px; font-weight: bold; border-top: 1px solid #000; border-bottom: 1px solid #000;">Total Freight</td>
                                    <td style="text-align: left; padding: 2px 4px; border-top: 1px solid #000; border-bottom: 1px solid #000;">{{ $totalFreight ? number_format($totalFreight, 2) : '0' }}</td>
                                </tr>
                                <tr>
                                    <td style="text-align: left; padding: 2px 4px; font-weight: bold; border-bottom: 1px solid #000;">Advance</td>
                                    <td style="text-align: left; padding: 2px 4px; border-bottom: 1px solid #000;">{{ $bulty->advance ?? '0' }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" style="text-align: left; padding: 4px; vertical-align: bottom; border-top: 1px solid #000;">
                                        <strong>Bilty Commission:</strong> {{ $bulty->bilty_commission ?? '0' }}
                                    </td>
                                    <td style="text-align: left; padding: 2px 4px; font-weight: bold;">Remaining</td>
                                    <td style="text-align: left; padding: 2px 4px;">{{ ($totalFreight ?? 0) - ($bulty->advance ?? 0) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </td>
                    <td style="width: 42%; padding: 0; vertical-align: top;">
                        <table class="lr-table">
                            <tr><td class="lr-label">LR. No.</td><td class="lr-value">{{ $bulty->lr_no ?? '-' }}</td></tr>
                            <tr><td class="lr-label">Date</td><td class="lr-value">{{ $bulty->lr_date ? date('d M Y', strtotime($bulty->lr_date)) : '-' }}</td></tr>
                            <tr><td class="lr-label">Truck No.</td><td class="lr-value">{{ $bulty->vehicle->vehicle_number ?? '-' }}</td></tr>
                            <tr><td class="lr-label">Fleet Owner</td><td class="lr-value">{{ strtoupper($company->name ?? '-') }}</td></tr>
                            <tr><td class="lr-label">Driver Name</td><td class="lr-value text-uppercase">{{ $bulty->driver->name ?? '-' }}</td></tr>
                            <tr><td class="lr-label">Driver Ph. No.</td><td class="lr-value">{{ $bulty->driver->phone ? '+91-' . $bulty->driver->phone : '-' }}</td></tr>
                            <tr><td class="lr-label">Order No.</td><td class="lr-value">{{ $bulty->order_number ?? '-' }}</td></tr>
                            <tr><td class="lr-label">Delivery No.</td><td class="lr-value">{{ $bulty->delivery_number ?? '-' }}</td></tr>
                            <tr><td class="lr-label">Form no.</td><td class="lr-value">{{ $bulty->from_no ?? '-' }}</td></tr>
                            <tr><td class="lr-label">Invoice No.</td><td class="lr-value">{{ $bulty->invoice_number ?? '-' }}</td></tr>
                            <tr><td class="lr-label">Invoice Dt</td><td class="lr-value">{{ $bulty->invoice_date ? date('d M Y', strtotime($bulty->invoice_date)) : '-' }}</td></tr>
                            <tr><td class="lr-label">E-Way Bill No.</td><td class="lr-value">{{ $bulty->eway_bill_no ?? '-' }}</td></tr>
                            <tr><td class="lr-label">Generation Dt</td><td class="lr-value">{{ $bulty->generation_date ? date('d M Y', strtotime($bulty->generation_date)) : '-' }}</td></tr>
                            <tr><td class="lr-label">Expiry Dt</td><td class="lr-value">{{ $bulty->expiry_date ? date('d M Y', strtotime($bulty->expiry_date)) : '-' }}</td></tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- 6. FOOTER NOTE -->
    <tr>
        <td style="border-top: 1px solid #000; padding: 0; vertical-align: top;">
            <table style="width: 100%;">
                <tr>
                    <td class="footer-note" style="padding: 4px 8px;">
                        <strong>GST Applicable as per rules</strong><br>
                        KINDLY RETURN THE BILTY WITH SEAL &amp; SIGN.<br>
                        NOTE : FREIGHT WILL BE PAID ONLY IF THE GOODS RECEIVING RECEIPT SUBMITTED TO OFFICE WITHIN 15 DAYS.
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- 7. SIGNATURE SECTION -->
    <tr>
        <td style="border-top: 1px solid #000; padding: 0; vertical-align: bottom;">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%; padding: 8px; border-right: 1px solid #000; vertical-align: top;">
                        <strong>Material Received :</strong><br>
                        <div style="font-size:8px; margin-top: 40px;">Receiving Date, Signature and Rubber Stamp.</div>
                    </td>
                    <td style="width: 50%; padding: 8px; vertical-align: top; text-align: right;">
                        <div>For <strong>{{ strtoupper($company->name ?? '') }}</strong></div>
                        
                        <table style="width: 100%; margin-top: 10px; border: none;">
                            <tr>
                                <td style="text-align: right; vertical-align: middle; border: none; font-weight: 800; font-size: 11px; padding-right: 10px;">
                                    @if($sigPath)
                                        <img src="{{ $sigPath }}" alt="Digital Signature" style="max-height: 45px; max-width: 140px; object-fit: contain;">
                                    @endif
                                </td>
                                <td style="text-align: right; vertical-align: middle; border: none; font-size: 7.5px; width: 80px;">
                                    signed by<br>
                                    Date: {{ $bulty->created_at ? date('d/m/Y', strtotime($bulty->created_at)) : '-' }}<br>
                                    {{ $bulty->created_at ? date('H:i:s', strtotime($bulty->created_at)) : '-' }}
                                </td>
                            </tr>
                        </table>
                        
                        <div style="font-size:9px; margin-top: 5px;">Signature Of Booking Clerk</div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>
