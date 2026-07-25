@extends('admin.layouts.app')

@section('content')
@php
    $firstBulty = $invoice->bulties->first();
    $companyName = !empty($invoice->company_name) ? $invoice->company_name : ($invoice->company ? $invoice->company->name : '');
    $companyAddress = $invoice->company ? $invoice->company->address : '';
    $companyGst = $invoice->company ? $invoice->company->gst_number : '';
    $companyPan = $invoice->company && $invoice->company->pan_number ? $invoice->company->pan_number : '';
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
    $compSigUrl = $comp?->digital_signature_url;
    $compOwner = $comp && $comp->owner_name ? strtoupper($comp->owner_name) : '';
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

    // State & GST Type calculation
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

    $calculatedGst = floatval($invoice->total_gst) > 0 ? floatval($invoice->total_gst) : ($grandTollSum * ($gstRate / 100));
    $cgstVal = floatval($invoice->cgst_amount) > 0 ? floatval($invoice->cgst_amount) : ($calculatedGst / 2);
    $sgstVal = floatval($invoice->sgst_amount) > 0 ? floatval($invoice->sgst_amount) : ($calculatedGst / 2);
    $igstVal = floatval($invoice->igst_amount) > 0 ? floatval($invoice->igst_amount) : $calculatedGst;

    $grandTotal = $grandTollSum + $calculatedGst;
    $amountInWords = \App\Http\Controllers\Admin\Transport\BillingController::convertNumberToWords($grandTotal);
@endphp

<div class="container-fluid flex-grow-1 container-p-y hide-on-print">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Toll Print View: {{ $invoice->invoice_no }}</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.transport.invoices.index') }}">Invoice History</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.transport.invoices.bill-generate', $invoice->id) }}">Bill Generate</a></li>
                    <li class="breadcrumb-item active">Toll Print View</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.transport.invoices.bill-generate', $invoice->id) }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back to Bill Generate
            </a>
            <form id="save-toll-form" method="POST" action="{{ route('admin.transport.invoices.save-toll', $invoice->id) }}" class="d-inline">
                @csrf
                <input type="hidden" name="bill_number" id="hidden_bill_number" value="">
                <input type="hidden" name="company_name" id="hidden_company_name" value="">
                <input type="hidden" name="billing_address" id="hidden_billing_address" value="">
                <input type="hidden" name="custom_place_of_supply" id="hidden_custom_place_of_supply" value="">
                <input type="hidden" name="custom_district" id="hidden_custom_district" value="">
                <input type="hidden" name="custom_state" id="hidden_custom_state" value="">
                <input type="hidden" name="custom_state_code" id="hidden_custom_state_code" value="">
                <input type="hidden" name="custom_gstn" id="hidden_custom_gstn" value="">
                <input type="hidden" name="custom_pan_no" id="hidden_custom_pan_no" value="">
                <input type="hidden" name="consignor_name" id="hidden_consignor_name" value="">
                @foreach($invoice->bulties as $bulty)
                    <input type="hidden" name="bulty_modes[{{ $bulty->id }}]" id="hidden_bulty_mode_{{ $bulty->id }}" value="">
                @endforeach
                <button type="button" class="btn btn-success" onclick="
                    function gv(id){var e=document.getElementById(id);return e?e.innerText.trim():'';}
                    function gh(id){var e=document.getElementById(id);return e?e.innerHTML.trim():'';}
                    document.getElementById('hidden_bill_number').value = gv('bill_number_cell');
                    document.getElementById('hidden_company_name').value = gv('company_name_cell');
                    document.getElementById('hidden_billing_address').value = gh('billing_address_cell');
                    document.getElementById('hidden_custom_place_of_supply').value = gv('custom_place_of_supply_cell');
                    document.getElementById('hidden_custom_district').value = gv('custom_district_cell');
                    document.getElementById('hidden_custom_state').value = gv('custom_state_cell');
                    document.getElementById('hidden_custom_state_code').value = gv('custom_state_code_cell');
                    document.getElementById('hidden_custom_gstn').value = gv('custom_gstn_cell');
                    document.getElementById('hidden_custom_pan_no').value = gv('custom_pan_no_cell');
                    document.getElementById('hidden_consignor_name').value = gv('consignor_name_cell');
                    document.querySelectorAll('.bulty-mode-select').forEach(function(sel) {
                        var bultyId = sel.getAttribute('data-bulty-id');
                        var hidden = document.getElementById('hidden_bulty_mode_' + bultyId);
                        if (hidden) hidden.value = sel.value;
                    });
                    document.getElementById('save-toll-form').submit();
                ">
                    <i class="bx bx-save me-1"></i> Save
                </button>
            </form>
            <a href="{{ route('admin.transport.invoices.export-excel', $invoice->id) }}" class="btn btn-outline-success">
                <i class="bx bx-file me-1"></i> Excel Export
            </a>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="bx bx-printer me-1"></i> Print Toll Bill
            </button>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12" style="max-width: 1200px;">
        <div class="card border-0 shadow-none p-0 m-0" style="background: transparent;">
            <div class="card-body p-0" style="background: #fff; color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 11px; line-height: 1.4;">
                <div id="toll-print-sheet" style="border: 1px solid #000; padding: 0;">
                    
                    <!-- TAX INVOICE HEADER -->
                    <div class="text-center fw-bold" style="border-bottom: 1px solid #000; padding: 4px; font-size: 14px; text-transform: uppercase;">
                        TAX INVOICE
                    </div>
                    
                    <div class="text-center" style="border-bottom: 1px solid #000; padding: 6px;">
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
                            <td id="custom_place_of_supply_cell" contenteditable="true" style="width: 45%; font-weight: bold; border-right: 1px solid #000; padding: 4px; text-transform: uppercase; outline: none;" title="Click to edit place of supply">{{ $placeOfSupply }}</td>
                            <td style="width: 25%; font-weight: bold; border-right: 1px solid #000; padding: 4px;">HSN/SAC CODE-</td>
                            <td id="custom_hsn_cell" style="width: 15%; font-weight: bold; padding: 4px; text-align: center;">{{ $invoice->custom_hsn_code ?? $invoice->custom_hsn_code ?? '' }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border-top: 1px solid #000; border-right: 1px solid #000; padding: 4px; vertical-align: top;">COMPANY NAME</td>
                            <td id="consignor_name_cell" contenteditable="true" style="font-weight: bold; border-top: 1px solid #000; border-right: 1px solid #000; padding: 4px; text-transform: uppercase; outline: none;" title="Click to edit company name">{{ $partyName }}</td>
                            <td style="font-weight: bold; border-top: 1px solid #000; border-right: 1px solid #000; padding: 4px;">DATE</td>
                            <td style="font-weight: bold; border-top: 1px solid #000; padding: 4px; text-align: center;">{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border-top: 1px solid #000; border-right: 1px solid #000; padding: 4px; vertical-align: top;">Address-</td>
                            <td id="billing_address_cell" contenteditable="true" style="border-top: 1px solid #000; border-right: 1px solid #000; padding: 4px; vertical-align: top; font-weight: bold; outline: none;" title="Click to edit address">
                                {!! $partyAddress !!}
                            </td>
                            <td style="font-weight: bold; border-top: 1px solid #000; border-right: 1px solid #000; padding: 4px;">STATE VENDOR CODE</td>
                            <td id="state_vendor_code_cell" style="font-weight: bold; border-top: 1px solid #000; padding: 4px; text-align: center;">{{ $invoice->state_vendor_code ?? $invoice->state_vendor_code ?? '' }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border-top: 1px solid #000; border-right: 1px solid #000; padding: 4px;">DISTRICT | STATE&CODE</td>
                            <td style="font-weight: bold; border-top: 1px solid #000; border-right: 1px solid #000; padding: 4px; text-transform: uppercase;">
                                <span id="custom_district_cell" contenteditable="true" style="outline: none;" title="Click to edit district">{{ $partyCity }}</span>
                                | <span id="custom_state_cell" contenteditable="true" style="outline: none;" title="Click to edit state">{{ $partyState }}</span>
                                &nbsp;&nbsp; CODE: <span id="custom_state_code_cell" contenteditable="true" style="outline: none;" title="Click to edit state code">{{ $stateCode }}</span>
                            </td>
                            <td style="font-weight: bold; border-top: 1px solid #000; border-right: 1px solid #000; padding: 4px;">VENDOR CODE</td>
                            <td id="vendor_code_cell" style="font-weight: bold; border-top: 1px solid #000; padding: 4px; text-align: center;">{{ $invoice->vendor_code ?? $invoice->vendor_code ?? '' }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border-top: 1px solid #000; border-right: 1px solid #000; padding: 4px;">GSTN | PAN NO</td>
                            <td style="font-weight: bold; border-top: 1px solid #000; border-right: 1px solid #000; padding: 4px; text-transform: uppercase;">
                                GSTN:- <span id="custom_gstn_cell" contenteditable="true" style="outline: none;" title="Click to edit GSTN">{{ $partyGst }}</span>, PAN NO:- <span id="custom_pan_no_cell" contenteditable="true" style="outline: none;" title="Click to edit PAN number">{{ $partyPan }}</span>
                            </td>
                            <td style="font-weight: bold; border-top: 1px solid #000; border-right: 1px solid #000; padding: 4px;">VENDOR NAME</td>
                            <td id="vendor_name_cell" style="font-weight: bold; border-top: 1px solid #000; padding: 4px; text-align: center; text-transform: uppercase;">{{ $invoice->vendor_name ?? $invoice->vendor_name ?? '' }}</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="border-top: 1px solid #000; border-right: 1px solid #000;"></td>
                            <td style="font-weight: bold; border-top: 1px solid #000; border-right: 1px solid #000; padding: 4px;">BILL NO.</td>
                            <td id="bill_number_cell" contenteditable="true" style="font-weight: bold; border-top: 1px solid #000; padding: 4px; text-align: center; color: #d00; font-size: 12px; outline: none;" title="Click to edit bill number">{{ $invoice->bill_number ?? '' }}</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="border-top: 1px solid #000; border-right: 1px solid #000;"></td>
                            <td style="font-weight: bold; border-top: 1px solid #000; border-right: 1px solid #000; padding: 4px;">EPOD Status:-</td>
                            <td id="epod_status_cell" style="font-weight: bold; border-top: 1px solid #000; padding: 4px; text-align: center;">{{ $invoice->epod_status ?? 'N' }}</td>
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
                                        <td>{{ (!empty($bulty->bultyDetail->challan_qty) && floatval($bulty->bultyDetail->challan_qty) > 0) ? floatval($bulty->bultyDetail->challan_qty) : ($bulty->bultyItems->sum('articles') > 0 ? $bulty->bultyItems->sum('articles') : '-') }}</td>
                                        <td>{{ $bulty->destinationCity->name ?? '-' }}</td>
                                        <td style="text-transform: uppercase;">
                                            <select class="bulty-mode-select" data-bulty-id="{{ $bulty->id }}" style="font-weight: bold; font-size: inherit; border: 1px solid #000; padding: 1px 2px; text-transform: uppercase; background: #fff; color: #000; width: 100%;">
                                                <option value="DEDICATED" {{ ($bulty->mode ?? 'DEDICATED') === 'DEDICATED' ? 'selected' : '' }}>DEDICATED</option>
                                                <option value="NON-DEDICATED" {{ ($bulty->mode ?? '') === 'NON-DEDICATED' ? 'selected' : '' }}>NON-DEDICATED</option>
                                            </select>
                                            <span class="print-mode-text" style="display: none;">{{ $bulty->mode ?? 'DEDICATED' }}</span>
                                        </td>
                                        
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
                                        <td style="text-transform: uppercase; font-weight: bold;" class="utcl-billing-location">{{ $partyCity }}</td>
                                    </tr>
                                @endforeach

                                <!-- Bottom Summary rows embedded inside the table layout matching the Excel design -->
                                @php
                                    $colSpanBeforeToll = 8 + (max(3, $tollLocations->count()) * 2);
                                @endphp
                                <tr>
                                    <td colspan="{{ $colSpanBeforeToll - 1 }}" style="border-right: 1px solid #000; border-bottom: none;"></td>
                                    <td style="font-weight: bold; background: #e6e6e6; border: 1px solid #000; font-size: 11px;">TOTAL</td>
                                    <td style="font-weight: bold; border: 1px solid #000; text-align: right; padding-right: 5px; font-size: 11px;">{{ number_format($grandTollSum, 2) }}</td>
                                    <td colspan="2" style="border-left: 1px solid #000; border-bottom: none;"></td>
                                </tr>
                                
                                @if($gstType === 'CGST_SGST')
                                    @php $halfRate = $gstRate / 2; @endphp
                                    <tr>
                                        <td colspan="{{ $colSpanBeforeToll - 1 }}" style="border-right: 1px solid #000; border-top: none; border-bottom: none;"></td>
                                        <td style="font-weight: bold; border: 1px solid #000; font-size: 11px;">C GST {{ str_replace('.0', '', number_format($halfRate, 1)) }}%</td>
                                        <td style="font-weight: bold; border: 1px solid #000; text-align: right; padding-right: 5px; font-size: 11px;">{{ number_format($cgstVal, 2) }}</td>
                                        <td colspan="2" style="border-left: 1px solid #000; border-top: none; border-bottom: none;"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="{{ $colSpanBeforeToll - 1 }}" style="border-right: 1px solid #000; border-top: none; border-bottom: none;"></td>
                                        <td style="font-weight: bold; border: 1px solid #000; font-size: 11px;">S GST {{ str_replace('.0', '', number_format($halfRate, 1)) }}%</td>
                                        <td style="font-weight: bold; border: 1px solid #000; text-align: right; padding-right: 5px; font-size: 11px;">{{ number_format($sgstVal, 2) }}</td>
                                        <td colspan="2" style="border-left: 1px solid #000; border-top: none; border-bottom: none;"></td>
                                    </tr>
                                @else
                                    <tr>
                                        <td colspan="{{ $colSpanBeforeToll - 1 }}" style="border-right: 1px solid #000; border-top: none; border-bottom: none;"></td>
                                        <td style="font-weight: bold; border: 1px solid #000; font-size: 11px;">I GST {{ str_replace('.0', '', number_format($gstRate, 1)) }}%</td>
                                        <td style="font-weight: bold; border: 1px solid #000; text-align: right; padding-right: 5px; font-size: 11px;">{{ number_format($igstVal, 2) }}</td>
                                        <td colspan="2" style="border-left: 1px solid #000; border-top: none; border-bottom: none;"></td>
                                    </tr>
                                @endif
                                <tr>
                                    <td colspan="{{ $colSpanBeforeToll - 1 }}" style="border-right: 1px solid #000; border-top: none; border-bottom: none;"></td>
                                    <td style="font-weight: bold; border: 1px solid #000; font-size: 11px;">TOTAL GST</td>
                                    <td style="font-weight: bold; border: 1px solid #000; text-align: right; padding-right: 5px; font-size: 11px;">{{ number_format($calculatedGst, 2) }}</td>
                                    <td colspan="2" style="border-left: 1px solid #000; border-top: none; border-bottom: none;"></td>
                                </tr>
                                <tr>
                                    <td colspan="{{ $colSpanBeforeToll - 1 }}" style="border-right: 1px solid #000; border-top: none;"></td>
                                    <td style="font-weight: bold; background: #e6e6e6; border: 1px solid #000; font-size: 11px;">GRAND TOTAL</td>
                                    <td style="font-weight: bold; border: 1px solid #000; text-align: right; padding-right: 5px; font-size: 11px;">{{ number_format($grandTollSum + $calculatedGst, 2) }}</td>
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
                            @if(!empty($compSigUrl))
                                <div class="text-center my-1">
                                    <img src="{{ $compSigUrl }}" alt="Signature" style="max-height: 45px; max-width: 140px; object-fit: contain;">
                                </div>
                            @else
                                <div style="height: 25px;"></div>
                            @endif
                            @if(!empty($compOwner))
                                <div style="font-size: 8px; color: #333; font-weight: bold; line-height: 1.2;">Digitally signed by {{ $compOwner }}</div>
                            @endif
                            <div style="font-size: 8px; color: #555; line-height: 1.2;">Date: {{ date('d-m-Y H:i:s') }}</div>
                            <div class="fw-bold" style="font-size: 11px; border-top: 1px solid #000; width: 80%; margin: 4px auto 0; padding-top: 4px;">Authorized Signatory</div>
                        </div>
                    </div>

                    <!-- Bottom footer -->
                    <div class="text-center fw-bold" style="background: #e6e6e6; border-top: 1px solid #000; padding: 5px; font-size: 11px;">
                        All Disputes are subjected to Jodhpur Jurisdiction only
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('style')
<style>
    .table-meta td {
        border: 1px solid #000;
        padding: 5px 6px;
        font-size: 11px;
    }
    
    .table-toll th, .table-toll td {
        border: 1px solid #000 !important;
        padding: 4px 3px !important;
        font-size: 10px !important;
        vertical-align: middle !important;
        color: #000 !important;
    }
    
    .table-toll th {
        font-weight: bold !important;
    }

    #toll-print-sheet .card:hover {
        transform: none !important;
        box-shadow: none !important;
    }
    [contenteditable="true"] {
        cursor: text;
        border: 1px dashed transparent;
        transition: border-color 0.2s, background-color 0.2s;
    }
    [contenteditable="true"]:hover {
        border-color: #999;
        background-color: #fffde7;
    }
    [contenteditable="true"]:focus {
        border-color: #1976d2;
        background-color: #fffde7;
        outline: none;
    }

    @media print {
        .bulty-mode-select { display: none !important; }
        .print-mode-text { display: inline !important; }
        .hide-on-print, 
        .main-header, 
        .layout-navbar, 
        .content-backdrop, 
        .layout-menu-toggle, 
        .layout-menu,
        .layout-overlay,
        .content-footer,
        aside,
        nav {
            display: none !important;
        }
        html, body {
            width: 100% !important;
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            overflow: visible !important;
        }
        .layout-wrapper,
        .layout-container,
        .layout-page,
        .content-wrapper,
        .container-fluid,
        .container-xxl {
            width: 100% !important;
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
            background: transparent !important;
            overflow: visible !important;
            min-height: 0 !important;
            height: auto !important;
        }
        .layout-page {
            padding-left: 0 !important;
        }
        .card, .card-body {
            background: transparent !important;
            padding: 0 !important;
            margin: 0 !important;
            box-shadow: none !important;
            border: none !important;
        }
        .row {
            margin: 0 !important;
        }
        .row.justify-content-center {
            display: block !important;
        }
        .col-12 {
            max-width: 100% !important;
            width: 100% !important;
            padding: 0 !important;
            flex: none !important;
        }
        #toll-print-sheet {
            width: 100% !important;
            min-height: calc(100vh - 6mm) !important;
            box-sizing: border-box;
            border: 1px solid #000 !important;
        }
        #toll-print-sheet * {
            color: #000 !important;
        }
        /* Header section - full width */
        #toll-print-sheet h2 {
            font-size: 20px !important;
        }
        /* Meta table fills width */
        .table-meta {
            width: 100% !important;
        }
        .table-meta td {
            padding: 4px 6px !important;
            font-size: 11px !important;
        }
        /* Toll table - full width, rows stretch */
        .table-responsive {
            overflow: visible !important;
            display: block !important;
        }
        .table-toll {
            width: 100% !important;
        }
        .table-toll th, .table-toll td {
            border: 1px solid #000 !important;
            padding: 4px 3px !important;
            font-size: 10px !important;
            vertical-align: middle !important;
        }
        /* Bottom sections */
        .col-8.p-2, .col-4.p-2 {
            font-size: 10px !important;
        }
        .col-4.p-2 {
            min-height: 70px !important;
        }
        @page {
            size: A4 landscape;
            margin: 3mm;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var districtCell = document.getElementById('custom_district_cell');
    if (districtCell) {
        districtCell.addEventListener('input', function() {
            var val = this.innerText.trim();
            document.querySelectorAll('.utcl-billing-location').forEach(function(td) {
                td.textContent = val;
            });
        });
    }
});
</script>
@endsection
