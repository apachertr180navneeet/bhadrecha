<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
@php
    $firstBulty = $invoice->bulties->first();
    $comp = $invoice->company ?? ($firstBulty?->company ?? null);
    $companyName = !empty($invoice->company_name) ? $invoice->company_name : ($comp ? $comp->name : '');
    $companyAddress = $comp ? $comp->address : '';
    $companyGst = $comp ? $comp->gst_number : '';
    $companyPan = $comp && $comp->pan_number ? $comp->pan_number : '';
    $companyPhone = $comp ? $comp->phone : '';
    $companyHsn = !empty($invoice->custom_hsn_code) ? $invoice->custom_hsn_code : ($comp && $comp->hsn_code ? $comp->hsn_code : '996791');

    $partyName = $invoice->consignor_name ?? ($invoice->consignor->name ?? '-');
    $partyAddress = !empty($invoice->billing_address) ? $invoice->billing_address : ($invoice->consignor ? ($invoice->consignor->address ?? '') : '');
    $partyGst = !empty($invoice->custom_gstn) ? $invoice->custom_gstn : ($invoice->consignor ? ($invoice->consignor->gst_no ?? '-') : '-');
    $partyState = !empty($invoice->custom_state) ? $invoice->custom_state : ($firstBulty && $firstBulty->destinationCity ? ($firstBulty->destinationCity->state ?? 'RAJASTHAN') : 'RAJASTHAN');
    $partyCity = !empty($invoice->custom_district) ? $invoice->custom_district : ($firstBulty && $firstBulty->destinationCity ? ($firstBulty->destinationCity->name ?? 'AHEMDABAD') : 'AHEMDABAD');
    $partyPan = !empty($invoice->custom_pan_no) ? $invoice->custom_pan_no : ($invoice->consignor && isset($invoice->consignor->pan_no) ? $invoice->consignor->pan_no : substr($partyGst, 2, 10));
    $placeOfSupply = !empty($invoice->custom_place_of_supply) ? $invoice->custom_place_of_supply : $partyState;
    $stateCode = !empty($invoice->custom_state_code) ? $invoice->custom_state_code : (substr($partyGst, 0, 2) ?: '');

    $rcmPayableVal = $invoice->rcm_payable ?? 0;
    if ($rcmPayableVal == 1) {
        $compDeclaration = ($comp && !empty($comp->declaration))
            ? $comp->declaration
            : 'GST payable by recipient under Reverse Charge (RCM) on GTA services.';
    } else {
        $compDeclaration = ($comp && !empty($comp->declaration))
            ? $comp->declaration
            : 'Declaration:-I we Have take registration under the CGST Act. 2017 and have exercised the option to pay tax on service of GTA in relation to transport of goods supplied by us during the Financial Year 2026-2027 under forward charge';
    }

    // State calculation
    $originState = $invoice->company && $invoice->company->state ? $invoice->company->state : ($firstBulty && $firstBulty->originCity ? ($firstBulty->originCity->state ?? 'RAJASTHAN') : 'RAJASTHAN');
    $isSameState = \App\Http\Controllers\Admin\Transport\BillingController::isSameGstState($originState, $placeOfSupply);
    $gstType = !empty($invoice->gst_type) ? $invoice->gst_type : ($isSameState ? 'CGST_SGST' : 'IGST');

    $gstRate = $invoice->gstMaster ? floatval($invoice->gstMaster->percentage) : 18.00;

    // Use saved toll details if available, otherwise compute from fast tag details
    $useSaved = $invoice->tollDetails->isNotEmpty();

    if ($useSaved) {
        $tollLocations = $invoice->tollDetails->pluck('location')->filter()->unique()->values();
        $grandTollSum = 0;
        foreach ($invoice->bulties as $bulty) {
            $bultySavedDetails = $invoice->tollDetails->where('builty_id', $bulty->id);
            $grandTollSum += $bultySavedDetails->sum(function($d) { return floatval($d->one_way) + floatval($d->return_amount); });
        }
    } else {
        $tollLocations = collect();
        foreach ($invoice->bulties as $bulty) {
            if ($bulty->trip) {
                foreach ($bulty->trip->fastTagDetails as $ft) {
                    $loc = strtoupper(trim((string)($ft->location ?? '')));
                    if (!empty($loc)) {
                        $tollLocations->push($loc);
                    } elseif (floatval($ft->amount ?? 0) > 0 || floatval($ft->one_way ?? 0) > 0 || floatval($ft->return ?? 0) > 0) {
                        $tollLocations->push('TOLL PLAZA');
                    }
                }
            }
        }
        $tollLocations = $tollLocations->unique()->values();

        $grandTollSum = 0;
        foreach ($invoice->bulties as $bulty) {
            if ($bulty->trip) {
                $grandTollSum += floatval($bulty->trip->total_fasttag_amount);
            }
        }
    }

    $tollCount = max(3, $tollLocations->count());
    $totalColumns = 8 + ($tollCount * 2) + 3;

    $calculatedGst = round($grandTollSum * ($gstRate / 100), 2);
    $grandTotal = $grandTollSum + $calculatedGst;
    $amountInWords = \App\Http\Controllers\Admin\Transport\BillingController::convertNumberToWords($grandTotal);

    $metaCol1 = 2;
    $metaCol2 = max(4, floor(($totalColumns - 6) / 2));
    $metaCol3 = 2;
    $metaCol4 = $totalColumns - ($metaCol1 + $metaCol2 + $metaCol3);
@endphp

<table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 10px;">
    <!-- Top Header -->
    <tr>
        <th colspan="{{ $totalColumns }}" style="border: 1px solid #000; text-align: center; font-weight: bold; font-size: 13px; padding: 5px;">TAX INVOICE</th>
    </tr>
    <tr>
        <th colspan="{{ $totalColumns }}" style="border: 1px solid #000; text-align: center; font-weight: bold; font-size: 15px; padding: 4px;">{{ $invoice->company_name ?? $companyName }}</th>
    </tr>
    <tr>
        <td colspan="{{ $totalColumns }}" style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 3px;">{{ $companyAddress }}</td>
    </tr>
    <tr>
        <td colspan="{{ $totalColumns }}" style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 3px;">PAN NO. : {{ $companyPan }} &nbsp;&nbsp;&nbsp; GSTIN : {{ $companyGst }} &nbsp;&nbsp;&nbsp; STATE: RAJASTHAN CODE 08</td>
    </tr>
    <tr>
        <td colspan="{{ $totalColumns }}" style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 3px;">WHETHER IS PAYABLE UNDER REVERSE CHARGE MECHANISM: {{ $rcmPayableVal == 1 ? 'YES' : 'NO' }}</td>
    </tr>
    <tr>
        <th colspan="{{ $totalColumns }}" style="border: 1px solid #000; text-align: center; font-weight: bold; font-size: 12px; background-color: #d9d9d9; padding: 5px;">TRANSPORTATION TOLL FREIGHT BILL</th>
    </tr>

    <!-- Meta Information -->
    <tr>
        <td colspan="{{ $metaCol1 }}" style="border: 1px solid #000; font-weight: bold; padding: 3px;">PLACE OF SUPPLY</td>
        <td colspan="{{ $metaCol2 }}" style="border: 1px solid #000; font-weight: bold; padding: 3px;">{{ strtoupper($placeOfSupply) }}</td>
        <td colspan="{{ $metaCol3 }}" style="border: 1px solid #000; font-weight: bold; padding: 3px;">HSN/SAC CODE</td>
        <td colspan="{{ $metaCol4 }}" style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 3px;">{{ $companyHsn }}</td>
    </tr>
    <tr>
        <td colspan="{{ $metaCol1 }}" style="border: 1px solid #000; font-weight: bold; padding: 3px;">COMPANY NAME</td>
        <td colspan="{{ $metaCol2 }}" style="border: 1px solid #000; font-weight: bold; padding: 3px;">{{ strtoupper($partyName) }}</td>
        <td colspan="{{ $metaCol3 }}" style="border: 1px solid #000; font-weight: bold; padding: 3px;">DATE</td>
        <td colspan="{{ $metaCol4 }}" style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 3px;">{{ $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : date('d/m/Y') }}</td>
    </tr>
    <tr>
        <td colspan="{{ $metaCol1 }}" style="border: 1px solid #000; font-weight: bold; padding: 3px;">COMPANY ADDRESS</td>
        <td colspan="{{ $metaCol2 }}" style="border: 1px solid #000; font-weight: bold; padding: 3px;">{{ preg_replace('/\s+/', ' ', trim($partyAddress)) }}</td>
        <td colspan="{{ $metaCol3 }}" style="border: 1px solid #000; font-weight: bold; padding: 3px;">STATE VENDOR CODE</td>
        <td colspan="{{ $metaCol4 }}" style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 3px;">{{ $invoice->state_vendor_code ?? ($invoice->consignor->vendor_code ?? '') }}</td>
    </tr>
    <tr>
        <td colspan="{{ $metaCol1 }}" style="border: 1px solid #000; font-weight: bold; padding: 3px;">GSTIN / PAN</td>
        <td colspan="{{ $metaCol2 }}" style="border: 1px solid #000; font-weight: bold; padding: 3px;">GSTIN: {{ $partyGst }} &nbsp;|&nbsp; PAN: {{ $partyPan }}</td>
        <td colspan="{{ $metaCol3 }}" style="border: 1px solid #000; font-weight: bold; padding: 3px;">VENDOR CODE</td>
        <td colspan="{{ $metaCol4 }}" style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 3px;">{{ $invoice->vendor_code ?? ($invoice->consignor->vendor_code ?? '') }}</td>
    </tr>
    <tr>
        <td colspan="{{ $metaCol1 }}" style="border: 1px solid #000; font-weight: bold; padding: 3px;">VENDOR NAME</td>
        <td colspan="{{ $metaCol2 }}" style="border: 1px solid #000; font-weight: bold; padding: 3px;">{{ strtoupper($invoice->vendor_name ?? $companyName) }}</td>
        <td colspan="{{ $metaCol3 }}" style="border: 1px solid #000; font-weight: bold; padding: 3px;">BILL NO.</td>
        <td colspan="{{ $metaCol4 }}" style="border: 1px solid #000; text-align: center; font-weight: bold; color: #cc0000; padding: 3px;">{{ $invoice->bill_number ?? $invoice->invoice_no }}</td>
    </tr>
    <tr>
        <td colspan="{{ $metaCol1 }}" style="border: 1px solid #000; font-weight: bold; padding: 3px;">NO OF LR</td>
        <td colspan="{{ $metaCol2 }}" style="border: 1px solid #000; font-weight: bold; padding: 3px;">{{ $invoice->no_of_lrs ?? ($invoice->bulties ? $invoice->bulties->count() : '-') }}</td>
        <td colspan="{{ $metaCol3 }}" style="border: 1px solid #000; font-weight: bold; padding: 3px;">EPOD STATUS</td>
        <td colspan="{{ $metaCol4 }}" style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 3px;">{{ $invoice->epod_status ?? 'N' }}</td>
    </tr>

    <!-- Spacer Row -->
    <tr>
        <td colspan="{{ $totalColumns }}" style="height: 8px;"></td>
    </tr>

    <!-- Table Main Headers -->
    <tr style="background-color: #e6e6e6;">
        <th rowspan="2" style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 4px;">SR No.</th>
        <th rowspan="2" style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 4px;">DI NO</th>
        <th rowspan="2" style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 4px;">Dispatch Date</th>
        <th rowspan="2" style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 4px;">Truck No</th>
        <th rowspan="2" style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 4px;">LR No</th>
        <th rowspan="2" style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 4px;">Qty</th>
        <th rowspan="2" style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 4px;">Destination</th>
        <th rowspan="2" style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 4px;">Dedicated/Nondedicated</th>
        @foreach($tollLocations as $loc)
            <th colspan="2" style="border: 1px solid #000; text-align: center; font-weight: bold; font-size: 9px; padding: 4px;">{{ strtoupper($loc) }}</th>
        @endforeach
        @for($i = $tollLocations->count(); $i < 3; $i++)
            <th colspan="2" style="border: 1px solid #000; text-align: center; font-weight: bold; font-size: 9px; padding: 4px;">TOLL PLAZA {{ $i + 1 }}</th>
        @endfor
        <th rowspan="2" style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 4px;">TOTAL TOLL AMOUNT</th>
        <th rowspan="2" style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 4px;">Freight Bill No.</th>
        <th rowspan="2" style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 4px;">UTCL Billing Location</th>
    </tr>
    <tr style="background-color: #e6e6e6;">
        @foreach($tollLocations as $loc)
            <th style="border: 1px solid #000; text-align: center; font-weight: bold; font-size: 8px; padding: 3px;">ONE WAY</th>
            <th style="border: 1px solid #000; text-align: center; font-weight: bold; font-size: 8px; padding: 3px;">RETURN</th>
        @endforeach
        @for($i = $tollLocations->count(); $i < 3; $i++)
            <th style="border: 1px solid #000; text-align: center; font-weight: bold; font-size: 8px; padding: 3px;">ONE WAY</th>
            <th style="border: 1px solid #000; text-align: center; font-weight: bold; font-size: 8px; padding: 3px;">RETURN</th>
        @endfor
    </tr>

    <!-- Table Data Rows -->
    @foreach($invoice->bulties as $index => $bulty)
        @php
            $bultyTollSum = 0;
            if ($useSaved) {
                $bultySavedDetails = $invoice->tollDetails->where('builty_id', $bulty->id);
            } else {
                $ftDetails = $bulty->trip ? $bulty->trip->fastTagDetails : collect();
            }

            $qtyVal = (!empty($bulty->bultyDetail->challan_qty) && floatval($bulty->bultyDetail->challan_qty) > 0)
                ? floatval($bulty->bultyDetail->challan_qty)
                : (($bulty->bultyItems && $bulty->bultyItems->sum('weight') > 0) ? floatval($bulty->bultyItems->sum('weight')) : 0);
        @endphp
        <tr>
            <td style="border: 1px solid #000; text-align: center; padding: 3px;">{{ $index + 1 }}</td>
            <td style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 3px;">{{ $bulty->delivery_number ?? ($bulty->order_number ?? '-') }}</td>
            <td style="border: 1px solid #000; text-align: center; padding: 3px;">{{ $bulty->lr_date ? $bulty->lr_date->format('d/m/Y') : '-' }}</td>
            <td style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 3px;">{{ $bulty->vehicle->vehicle_number ?? '-' }}</td>
            <td style="border: 1px solid #000; text-align: center; padding: 3px;">{{ $bulty->lr_no ?? '-' }}</td>
            <td style="border: 1px solid #000; text-align: right; padding: 3px;">{{ $qtyVal > 0 ? number_format($qtyVal, 2, '.', '') : '-' }}</td>
            <td style="border: 1px solid #000; text-align: left; padding: 3px;">{{ $bulty->destinationCity->name ?? '-' }}</td>
            <td style="border: 1px solid #000; text-align: center; padding: 3px;">{{ strtoupper($bulty->mode ?? 'DEDICATED') }}</td>

            @foreach($tollLocations as $loc)
                @php
                    if ($useSaved) {
                        $match = $bultySavedDetails->firstWhere('location', $loc);
                        $oneWay = $match && floatval($match->one_way) > 0 ? floatval($match->one_way) : 0;
                        $returnVal = $match && floatval($match->return_amount) > 0 ? floatval($match->return_amount) : 0;
                    } else {
                        $matched = $ftDetails->filter(function($ft) use ($loc) {
                            $fLoc = strtoupper(trim((string)($ft->location ?? '')));
                            if ($loc === 'TOLL PLAZA') {
                                return empty($fLoc) || $fLoc === 'TOLL PLAZA';
                            }
                            return $fLoc === $loc;
                        });
                        $oneWaySum = (float) $matched->sum('one_way');
                        $returnSum = (float) $matched->sum('return');
                        $amountSum = (float) $matched->sum('amount');
                        if ($oneWaySum <= 0 && $returnSum <= 0 && $amountSum > 0) {
                            $oneWaySum = $amountSum;
                        }
                        $oneWay = $oneWaySum > 0 ? $oneWaySum : 0;
                        $returnVal = $returnSum > 0 ? $returnSum : 0;
                    }
                    $bultyTollSum += ($oneWay + $returnVal);
                @endphp
                <td style="border: 1px solid #000; text-align: right; font-weight: bold; padding: 3px;">{{ $oneWay > 0 ? number_format($oneWay, 2, '.', '') : '' }}</td>
                <td style="border: 1px solid #000; text-align: right; font-weight: bold; padding: 3px;">{{ $returnVal > 0 ? number_format($returnVal, 2, '.', '') : '' }}</td>
            @endforeach

            @for($i = $tollLocations->count(); $i < 3; $i++)
                <td style="border: 1px solid #000; text-align: center; padding: 3px;"></td>
                <td style="border: 1px solid #000; text-align: center; padding: 3px;"></td>
            @endfor

            <td style="border: 1px solid #000; text-align: right; font-weight: bold; padding: 3px;">{{ number_format($bultyTollSum, 2, '.', '') }}</td>
            <td style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 3px;">{{ $bulty->invoice ? $bulty->invoice->invoice_no : '-' }}</td>
            <td style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 3px;">{{ strtoupper($partyCity) }}</td>
        </tr>
    @endforeach

    <!-- Summary Section -->
    @php
        $summaryColSpan = 8 + ($tollCount * 2) - 1;
    @endphp
    <tr>
        <td colspan="{{ $summaryColSpan }}" style="border-right: 1px solid #000; border-bottom: 1px solid #000;"></td>
        <td style="border: 1px solid #000; text-align: right; font-weight: bold; background-color: #e6e6e6; padding: 4px;">TOTAL</td>
        <td style="border: 1px solid #000; text-align: right; font-weight: bold; padding: 4px;">{{ number_format($grandTollSum, 2, '.', '') }}</td>
        <td colspan="2" style="border-left: 1px solid #000; border-bottom: 1px solid #000;"></td>
    </tr>

    @if($gstType === 'CGST_SGST')
        @php
            $halfGst = round($calculatedGst / 2, 2);
            $halfRate = $gstRate / 2;
        @endphp
        <tr>
            <td colspan="{{ $summaryColSpan }}" style="border-right: 1px solid #000;"></td>
            <td style="border: 1px solid #000; text-align: right; font-weight: bold; padding: 4px;">C GST {{ str_replace('.0', '', number_format($halfRate, 1)) }}%</td>
            <td style="border: 1px solid #000; text-align: right; font-weight: bold; padding: 4px;">{{ number_format($halfGst, 2, '.', '') }}</td>
            <td colspan="2" style="border-left: 1px solid #000;"></td>
        </tr>
        <tr>
            <td colspan="{{ $summaryColSpan }}" style="border-right: 1px solid #000;"></td>
            <td style="border: 1px solid #000; text-align: right; font-weight: bold; padding: 4px;">S GST {{ str_replace('.0', '', number_format($halfRate, 1)) }}%</td>
            <td style="border: 1px solid #000; text-align: right; font-weight: bold; padding: 4px;">{{ number_format($halfGst, 2, '.', '') }}</td>
            <td colspan="2" style="border-left: 1px solid #000;"></td>
        </tr>
    @else
        <tr>
            <td colspan="{{ $summaryColSpan }}" style="border-right: 1px solid #000;"></td>
            <td style="border: 1px solid #000; text-align: right; font-weight: bold; padding: 4px;">I GST {{ str_replace('.0', '', number_format($gstRate, 1)) }}%</td>
            <td style="border: 1px solid #000; text-align: right; font-weight: bold; padding: 4px;">{{ number_format($calculatedGst, 2, '.', '') }}</td>
            <td colspan="2" style="border-left: 1px solid #000;"></td>
        </tr>
    @endif

    <tr>
        <td colspan="{{ $summaryColSpan }}" style="border-right: 1px solid #000;"></td>
        <td style="border: 1px solid #000; text-align: right; font-weight: bold; padding: 4px;">TOTAL GST</td>
        <td style="border: 1px solid #000; text-align: right; font-weight: bold; padding: 4px;">{{ number_format($calculatedGst, 2, '.', '') }}</td>
        <td colspan="2" style="border-left: 1px solid #000;"></td>
    </tr>
    <tr>
        <td colspan="{{ $summaryColSpan }}" style="border-right: 1px solid #000; border-bottom: 1px solid #000;"></td>
        <td style="border: 1px solid #000; text-align: right; font-weight: bold; background-color: #e6e6e6; padding: 4px;">GRAND TOTAL</td>
        <td style="border: 1px solid #000; text-align: right; font-weight: bold; padding: 4px;">{{ number_format($grandTotal, 2, '.', '') }}</td>
        <td colspan="2" style="border-left: 1px solid #000; border-bottom: 1px solid #000;"></td>
    </tr>

    <!-- Footer Information -->
    <tr>
        <td colspan="{{ $totalColumns }}" style="border: 1px solid #000; font-weight: bold; padding: 4px;">
            Amount In Words: <span style="text-transform: uppercase;">{{ $amountInWords }}</span>
        </td>
    </tr>
    <tr>
        <td colspan="{{ $totalColumns }}" style="border: 1px solid #000; font-weight: bold; padding: 4px;">
            Person Liable to Pay GST: {{ $companyName }}
        </td>
    </tr>
    <tr>
        <td colspan="{{ $totalColumns - 4 }}" style="border: 1px solid #000; font-weight: bold; vertical-align: top; padding: 5px;">
            {{ $compDeclaration }}
        </td>
        <td colspan="4" style="border: 1px solid #000; text-align: center; vertical-align: bottom; font-weight: bold; padding: 10px 4px 4px 4px;">
            For {{ strtoupper($companyName) }}<br><br><br>
            Authorized Signatory
        </td>
    </tr>
    <tr>
        <td colspan="{{ $totalColumns }}" style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #e6e6e6; padding: 4px;">
            All Disputes are subjected to Jodhpur Jurisdiction only
        </td>
    </tr>
</table>
</body>
</html>
