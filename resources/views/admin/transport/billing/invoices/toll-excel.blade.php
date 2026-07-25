<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
@php
    $firstBulty = $invoice->bulties->first();
    $companyName = $invoice->company ? $invoice->company->name : '';
    $companyAddress = $invoice->company ? $invoice->company->address : '';
    $companyGst = $invoice->company ? $invoice->company->gst_number : '';
    $companyPan = $invoice->company ? $invoice->company->pan_number : '';
    $companyPhone = $invoice->company ? $invoice->company->phone : '';
    $companyHsn = !empty($invoice->custom_hsn_code) ? $invoice->custom_hsn_code : ($invoice->company && $invoice->company->hsn_code ? $invoice->company->hsn_code : '');

    $partyName = $invoice->consignor_name ?? ($invoice->consignor->name ?? '-');
    $partyAddress = !empty($invoice->billing_address) ? str_replace("\n", "<br>", $invoice->billing_address) : ($invoice->consignor ? str_replace("\n", "<br>", $invoice->consignor->address ?? '') : '');
    $partyGst = !empty($invoice->custom_gstn) ? $invoice->custom_gstn : ($invoice->consignor ? ($invoice->consignor->gst_no ?? '-') : '-');
    $partyState = !empty($invoice->custom_state) ? $invoice->custom_state : ($firstBulty && $firstBulty->destinationCity ? ($firstBulty->destinationCity->state ?? 'RAJASTHAN') : 'RAJASTHAN');
    $partyCity = !empty($invoice->custom_district) ? $invoice->custom_district : ($firstBulty && $firstBulty->destinationCity ? ($firstBulty->destinationCity->name ?? 'AHEMDABAD') : 'AHEMDABAD');
    $partyPan = !empty($invoice->custom_pan_no) ? $invoice->custom_pan_no : ($invoice->consignor && isset($invoice->consignor->pan_no) ? $invoice->consignor->pan_no : substr($partyGst, 2, 10));
    $placeOfSupply = !empty($invoice->custom_place_of_supply) ? $invoice->custom_place_of_supply : $partyState;
    $stateCode = !empty($invoice->custom_state_code) ? $invoice->custom_state_code : (substr($partyGst, 0, 2) ?: '');

    $comp = $invoice->company;
    $rcmPayableVal = $invoice->rcm_payable ?? 0;
    if ($rcmPayableVal == 1) {
        $compDeclaration = ($comp && !empty($comp->declaration))
            ? $comp->declaration
            : 'GST payable by recipient under Reverse Charge (RCM) on GTA services.';
    } else {
        $compDeclaration = ($comp && !empty($comp->declaration))
            ? $comp->declaration
            : 'Declaration:-I we Have take registration under the CGST Act. 2017 and have excercised the option to pay tax on service of GTA in relation to transport of goods supplied by us during thr Financial Year 2026-2027 under forward charge';
    }

    // State calculation
    $originState = $invoice->company && $invoice->company->state ? $invoice->company->state : ($firstBulty && $firstBulty->originCity ? ($firstBulty->originCity->state ?? 'RAJASTHAN') : 'RAJASTHAN');
    $isSameState = \App\Http\Controllers\Admin\Transport\BillingController::isSameGstState($originState, $placeOfSupply);
    $gstType = $invoice->gst_type ?? ($isSameState ? 'CGST_SGST' : 'IGST');

    $gstRate = $invoice->gstMaster ? floatval($invoice->gstMaster->percentage) : 18.00;

    // Use saved toll details if available, otherwise compute from fast tag details
    $useSaved = $invoice->tollDetails->isNotEmpty();

    if ($useSaved) {
        $tollLocations = $invoice->tollDetails->pluck('location')->unique()->values();
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
                    if ($ft->location) {
                        $tollLocations->push(strtoupper(trim($ft->location)));
                    }
                }
            }
        }
        $tollLocations = $tollLocations->unique()->values();

        $grandTollSum = 0;
        foreach ($invoice->bulties as $bulty) {
            if ($bulty->trip) {
                $grandTollSum += floatval($bulty->trip->fastTagDetails->sum('amount'));
            }
        }
    }

    $calculatedGst = $grandTollSum * ($gstRate / 100);
    $grandTotal = $grandTollSum + $calculatedGst;
    $amountInWords = \App\Http\Controllers\Admin\Transport\BillingController::convertNumberToWords($grandTotal);
@endphp

<div style="background: #fff; color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 11px; line-height: 1.4;">
    <div id="toll-print-sheet" style="border: 1px solid #000; padding: 0;">
                    
                    <!-- TAX INVOICE HEADER -->
                    <div class="text-center fw-bold" style="border-bottom: 1px solid #000; padding: 4px; font-size: 14px; text-transform: uppercase;">
                        TAX INVOICE
                    </div>
                    
                    <div class="text-center" style="border-bottom: 1px solid #000; padding: 6px;">
                        <h2 class="m-0 fw-bold" style="font-size: 22px; letter-spacing: 1px; text-transform: uppercase;">{{ $invoice->company_name ?? $companyName }}</h2>
                        <div style="font-size: 11px; margin-top: 4px; font-weight: bold;">{{ $companyAddress }}</div>
                        <div style="font-size: 11px; margin-top: 2px; font-weight: bold;">
                            PAN NO. : {{ $companyPan }} &nbsp;&nbsp; GSTIN : {{ $companyGst }} &nbsp;&nbsp; STATE: RAJASTHAN CODE 08
                        </div>
                    </div>

                    <div class="text-center fw-bold" style="border-bottom: 1px solid #000; padding: 4px; font-size: 11px; text-transform: uppercase;">
                        WHEATHER IS PAYABLE UNDER REVERSE CHARGE MECHANISIM:-{{ $rcmPayableVal == 1 ? 'YES' : 'NO' }}
                    </div>

                    <div class="text-center fw-bold" style="background: #ccc; border-bottom: 1px solid #000; padding: 5px; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">
                        TRANSPORATION TOLL FREIGHT BILL
                    </div>

                    <!-- Supply and Vendor Codes Meta Info -->
                    <table class="w-100 table-meta" style="border-collapse: collapse; border-bottom: 1px solid #000;">
                        <tr>
                            <td style="width: 15%; font-weight: bold; border-right: 1px solid #000; padding: 4px;">PLACE OF SUPPLY</td>
                            <td style="width: 45%; font-weight: bold; border-right: 1px solid #000; padding: 4px; text-transform: uppercase;">{{ $placeOfSupply }}</td>
                            <td style="width: 25%; font-weight: bold; border-right: 1px solid #000; padding: 4px;">HSN/SAC CODE-</td>
                            <td style="width: 15%; font-weight: bold; padding: 4px; text-align: center;">{{ $companyHsn }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border-top: 1px solid #000; border-right: 1px solid #000; padding: 4px; vertical-align: top;">COMPANY NAME</td>
                            <td style="font-weight: bold; border-top: 1px solid #000; border-right: 1px solid #000; padding: 4px; text-transform: uppercase;">{{ $partyName }}</td>
                            <td style="font-weight: bold; border-top: 1px solid #000; border-right: 1px solid #000; padding: 4px;">DATE</td>
                            <td style="font-weight: bold; border-top: 1px solid #000; padding: 4px; text-align: center;">{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border-top: 1px solid #000; border-right: 1px solid #000; padding: 4px; vertical-align: top;">Address-</td>
                            <td style="border-top: 1px solid #000; border-right: 1px solid #000; padding: 4px; vertical-align: top; font-weight: bold;">
                                {!! $partyAddress !!}
                            </td>
                            <td style="font-weight: bold; border-top: 1px solid #000; border-right: 1px solid #000; padding: 4px;">STATE VENDOR CODE</td>
                            <td style="font-weight: bold; border-top: 1px solid #000; padding: 4px; text-align: center;">{{ $invoice->state_vendor_code ?? $invoice->consignor->vendor_code ?? '' }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border-top: 1px solid #000; border-right: 1px solid #000; padding: 4px;">DISTRICT | STATE &amp; CODE</td>
                            <td style="font-weight: bold; border-top: 1px solid #000; border-right: 1px solid #000; padding: 4px; text-transform: uppercase;">
                                {{ $partyCity }} | {{ $partyState }} &nbsp;&nbsp; CODE: {{ $stateCode }}
                            </td>
                            <td style="font-weight: bold; border-top: 1px solid #000; border-right: 1px solid #000; padding: 4px;">VENDOR CODE</td>
                            <td style="font-weight: bold; border-top: 1px solid #000; padding: 4px; text-align: center;">{{ $invoice->vendor_code ?? $invoice->consignor->vendor_code ?? '' }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border-top: 1px solid #000; border-right: 1px solid #000; padding: 4px;">GSTN | PAN NO</td>
                            <td style="font-weight: bold; border-top: 1px solid #000; border-right: 1px solid #000; padding: 4px; text-transform: uppercase;">
                                GSTN:- {{ $partyGst }}, PAN NO:- {{ $partyPan }}
                            </td>
                            <td style="font-weight: bold; border-top: 1px solid #000; border-right: 1px solid #000; padding: 4px;">VENDOR NAME</td>
                            <td style="font-weight: bold; border-top: 1px solid #000; padding: 4px; text-align: center; text-transform: uppercase;">{{ $invoice->vendor_name ?? $companyName }}</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="border-top: 1px solid #000; border-right: 1px solid #000;"></td>
                            <td style="font-weight: bold; border-top: 1px solid #000; border-right: 1px solid #000; padding: 4px;">BILL NO.</td>
                            <td style="font-weight: bold; border-top: 1px solid #000; padding: 4px; text-align: center; color: #d00; font-size: 12px;">{{ $invoice->bill_number ?? $invoice->invoice_no }}</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="border-top: 1px solid #000; border-right: 1px solid #000;"></td>
                            <td style="font-weight: bold; border-top: 1px solid #000; border-right: 1px solid #000; padding: 4px;">EPOD Status:-</td>
                            <td style="font-weight: bold; border-top: 1px solid #000; padding: 4px; text-align: center;">{{ $invoice->epod_status ?? 'N' }}</td>
                        </tr>
                    </table>

                    <!-- MAIN TOLL TABLE -->
                    <div class="table-responsive">
                        <table class="table-toll" style="width: 100%; border-collapse: collapse; text-align: center;">
                            <thead>
                                <tr style="background: #e6e6e6;">
                                    <th rowspan="2" style="width: 3%;">SR No.</th>
                                    <th rowspan="2" style="width: 8%;">DI NO</th>
                                    <th rowspan="2" style="width: 8%;">Dispatch Date</th>
                                    <th rowspan="2" style="width: 10%;">Truck No</th>
                                    <th rowspan="2" style="width: 10%;">LR No</th>
                                    <th rowspan="2" style="width: 4%;">Qty</th>
                                    <th rowspan="2" style="width: 10%;">Destination</th>
                                    <th rowspan="2" style="width: 10%;">Dedicated/Nondedicated</th>
                                    
                                    <!-- Dynamic Toll Locations Header -->
                                    @foreach($tollLocations as $location)
                                        <th colspan="2" style="border-bottom: 1px solid #000; font-size: 9px; text-transform: uppercase;">{{ $location }}</th>
                                    @endforeach
                                    
                                    <!-- Pad out to at least a few columns if locations are fewer than 3, just for visual width similar to image -->
                                    @for($i = $tollLocations->count(); $i < 3; $i++)
                                        <th colspan="2" style="border-bottom: 1px solid #000; font-size: 9px; min-height: 15px;">&nbsp;</th>
                                    @endfor
                                    
                                    <th rowspan="2" style="width: 8%;">TOTAL TOLL AMOUNT</th>
                                    <th rowspan="2" style="width: 10%;">Freight Bill No.</th>
                                    <th rowspan="2" style="width: 10%;">UTCL Billing Location</th>
                                </tr>
                                <tr style="background: #e6e6e6; font-size: 8px;">
                                    <!-- Sub-columns for dynamic locations -->
                                    @foreach($tollLocations as $location)
                                        <th style="font-size: 8px; font-weight: bold; width: 4%;">ONE WAY</th>
                                        <th style="font-size: 8px; font-weight: bold; width: 4%;">RETURN</th>
                                    @endforeach
                                    
                                    @for($i = $tollLocations->count(); $i < 3; $i++)
                                        <th style="font-size: 8px; width: 4%;">ONE WAY</th>
                                        <th style="font-size: 8px; width: 4%;">RETURN</th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->bulties as $index => $bulty)
                                    @php
                                        $bultyTollSum = 0;
                                        if ($useSaved) {
                                            $bultySavedDetails = $invoice->tollDetails->where('builty_id', $bulty->id);
                                        } else {
                                            $ftDetails = $bulty->trip ? $bulty->trip->fastTagDetails : collect();
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td style="font-weight: bold;">{{ $bulty->delivery_number ?? $bulty->order_number ?? '-' }}</td>
                                        <td>{{ $bulty->lr_date ? $bulty->lr_date->format('d/m/Y') : '-' }}</td>
                                        <td style="font-weight: bold;">{{ $bulty->vehicle->vehicle_number ?? '-' }}</td>
                                        <td>{{ $bulty->lr_no ?? '-' }}</td>
                                        <td>{{ $bulty->bultyDetail->challan_qty ?? ($bulty->bultyItems->sum('weight') ?? '-') }}</td>
                                        <td>{{ $bulty->destinationCity->name ?? '-' }}</td>
                                        <td style="text-transform: uppercase;">{{ $bulty->mode ?? 'DEDICATED' }}</td>
                                        
                                        <!-- Render dynamic toll locations values -->
                                        @foreach($tollLocations as $location)
                                            @php
                                                if ($useSaved) {
                                                    $match = $bultySavedDetails->firstWhere('location', $location);
                                                    $oneWay = $match && floatval($match->one_way) > 0 ? floatval($match->one_way) : null;
                                                    $returnVal = $match && floatval($match->return_amount) > 0 ? floatval($match->return_amount) : null;
                                                } else {
                                                    $match = $ftDetails->first(function($ft) use ($location) {
                                                        return strtoupper(trim($ft->location)) === $location;
                                                    });
                                                    $oneWay = $match && floatval($match->one_way) > 0 ? floatval($match->one_way) : null;
                                                    $returnVal = $match && floatval($match->return) > 0 ? floatval($match->return) : null;
                                                }
                                                
                                                if ($oneWay) $bultyTollSum += $oneWay;
                                                if ($returnVal) $bultyTollSum += $returnVal;
                                            @endphp
                                            <td style="font-weight: bold;">{{ $oneWay ? number_format($oneWay, 0) : '' }}</td>
                                            <td style="font-weight: bold;">{{ $returnVal ? number_format($returnVal, 0) : '' }}</td>
                                        @endforeach
                                        
                                        <!-- Empty columns to pad -->
                                        @for($i = $tollLocations->count(); $i < 3; $i++)
                                            <td></td>
                                            <td></td>
                                        @endfor
                                        
                                        <td style="font-weight: bold;">{{ number_format($bultyTollSum, 2) }}</td>
                                        <td style="text-transform: uppercase; font-weight: bold;">{{ $bulty->invoice ? $bulty->invoice->invoice_no : ($bulty->invoice_id ? '' : '-') }}</td>
                                        <td style="text-transform: uppercase; font-weight: bold;">{{ $partyCity }}</td>
                                    </tr>
                                @endforeach

                                <!-- Bottom Summary rows embedded inside the table layout matching the Excel design -->
                                @php
                                    $colSpanBeforeToll = 8 + (max(3, $tollLocations->count()) * 2);
                                @endphp
                                <tr>
                                    <td colspan="{{ $colSpanBeforeToll - 1 }}" style="border-right: 1px solid #000; border-bottom: none;"></td>
                                    <td style="font-weight: bold; background: #e6e6e6; border: 1px solid #000;">TOTAL</td>
                                    <td style="font-weight: bold; border: 1px solid #000; text-align: right; padding-right: 5px;">{{ number_format($grandTollSum, 2) }}</td>
                                    <td colspan="2" style="border-left: 1px solid #000; border-bottom: none;"></td>
                                </tr>
                                
                                @if($gstType === 'CGST_SGST')
                                    @php
                                        $halfGst = $calculatedGst / 2;
                                        $halfRate = $gstRate / 2;
                                    @endphp
                                    <tr>
                                        <td colspan="{{ $colSpanBeforeToll - 1 }}" style="border-right: 1px solid #000; border-top: none; border-bottom: none;"></td>
                                        <td style="font-weight: bold; border: 1px solid #000;">C GST {{ str_replace('.0', '', number_format($halfRate, 1)) }}%</td>
                                        <td style="font-weight: bold; border: 1px solid #000; text-align: right; padding-right: 5px;">{{ number_format($halfGst, 2) }}</td>
                                        <td colspan="2" style="border-left: 1px solid #000; border-top: none; border-bottom: none;"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="{{ $colSpanBeforeToll - 1 }}" style="border-right: 1px solid #000; border-top: none; border-bottom: none;"></td>
                                        <td style="font-weight: bold; border: 1px solid #000;">S GST {{ str_replace('.0', '', number_format($halfRate, 1)) }}%</td>
                                        <td style="font-weight: bold; border: 1px solid #000; text-align: right; padding-right: 5px;">{{ number_format($halfGst, 2) }}</td>
                                        <td colspan="2" style="border-left: 1px solid #000; border-top: none; border-bottom: none;"></td>
                                    </tr>
                                @else
                                    <tr>
                                        <td colspan="{{ $colSpanBeforeToll - 1 }}" style="border-right: 1px solid #000; border-top: none; border-bottom: none;"></td>
                                        <td style="font-weight: bold; border: 1px solid #000;">I GST {{ str_replace('.0', '', number_format($gstRate, 1)) }}%</td>
                                        <td style="font-weight: bold; border: 1px solid #000; text-align: right; padding-right: 5px;">{{ number_format($calculatedGst, 2) }}</td>
                                        <td colspan="2" style="border-left: 1px solid #000; border-top: none; border-bottom: none;"></td>
                                    </tr>
                                @endif
                                <tr>
                                    <td colspan="{{ $colSpanBeforeToll - 1 }}" style="border-right: 1px solid #000; border-top: none; border-bottom: none;"></td>
                                    <td style="font-weight: bold; border: 1px solid #000;">TOTAL GST</td>
                                    <td style="font-weight: bold; border: 1px solid #000; text-align: right; padding-right: 5px;">{{ number_format($calculatedGst, 2) }}</td>
                                    <td colspan="2" style="border-left: 1px solid #000; border-top: none; border-bottom: none;"></td>
                                </tr>
                                <tr>
                                    <td colspan="{{ $colSpanBeforeToll - 1 }}" style="border-right: 1px solid #000; border-top: none;"></td>
                                    <td style="font-weight: bold; background: #e6e6e6; border: 1px solid #000;">GRAND TOTAL</td>
                                    <td style="font-weight: bold; border: 1px solid #000; text-align: right; padding-right: 5px;">{{ number_format($grandTollSum + $calculatedGst, 2) }}</td>
                                    <td colspan="2" style="border-left: 1px solid #000; border-top: none;"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Bottom Terms & Declaration section -->
                    <div style="border-top: 1px solid #000; padding: 8px; font-weight: bold; font-size: 11px;">
                        Amount In word- &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #333; text-transform: uppercase;">{{ $amountInWords }}</span>
                    </div>

                    <div style="border-top: 1px solid #000; padding: 6px; font-weight: bold; font-size: 11px;">
                        Person Liable to Pay Gst: {{ $companyName }}
                    </div>

                    <div class="row g-0" style="border-top: 1px solid #000;">
                        <div class="col-8 p-2" style="border-right: 1px solid #000; font-size: 11px; font-weight: bold; line-height: 1.5; vertical-align: middle;">
                            Declaration:-{{ $compDeclaration }}
                        </div>
                        <div class="col-4 p-2 text-center d-flex flex-column justify-content-between" style="min-height: 90px;">
                            <div class="fw-bold" style="font-size: 11px; text-transform: uppercase;">For. {{ $companyName }}</div>
                            <div class="fw-bold" style="font-size: 11px; border-top: 1px solid #000; width: 80%; margin: 0 auto; padding-top: 4px;">Authorized Signatory</div>
                        </div>
                    </div>

                    <!-- Bottom footer -->
                    <div class="text-center fw-bold" style="background: #e6e6e6; border-top: 1px solid #000; padding: 5px; font-size: 11px;">
                        All Disputes are subjected to Jodhpur Jurisdiction only
                    </div>

    </div>
</div>
</body>
</html>
