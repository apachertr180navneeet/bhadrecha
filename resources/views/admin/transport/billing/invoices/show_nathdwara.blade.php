@extends('admin.layouts.app')

@section('content')

@php
    $bulties = collect();
    if(isset($invoice->bulties)) {
        $bulties = $invoice->bulties;
    }
    
    $compName = !empty($invoice->company_name) ? $invoice->company_name : ($invoice->company ? $invoice->company->name : '');
    $compAdd = $invoice->company ? $invoice->company->address : '';
    $compGst = $invoice->company ? $invoice->company->gst_number : '';
    $compPan = $invoice->company && $invoice->company->pan_number ? $invoice->company->pan_number : '';
    $compPh = $invoice->company ? $invoice->company->phone : '';
    $compOwner = $invoice->company && $invoice->company->owner_name ? strtoupper($invoice->company->owner_name) : '';
    $bankAccountNo = $invoice->company && $invoice->company->bank_account_no ? $invoice->company->bank_account_no : '';
    $bankIfsc = $invoice->company && $invoice->company->bank_ifsc ? $invoice->company->bank_ifsc : '';
    $bankHolder = $invoice->company && $invoice->company->bank_holder_name ? strtoupper($invoice->company->bank_holder_name) : '';

    $comp = $invoice->company;
    $compSigUrl = $comp?->digital_signature_url;
    $rcmPayableVal = $invoice->rcm_payable ?? 1;
    if ($rcmPayableVal == 1) {
        $compDeclaration = ($comp && !empty($comp->declaration))
            ? $comp->declaration
            : 'GST payable by recipient under Reverse Charge (RCM) on GTA services.';
    } else {
        $compDeclaration = ($comp && !empty($comp->declaration))
            ? $comp->declaration
            : 'Tax payable under Forward Charge.';
    }
    
    $firstBulty = $bulties->first();
    $partyName = $invoice->consignor_name ?? ($invoice->consignor->name ?? '-');
    $fallbackAddress = '<div class="fw-bold" style="font-size: 11px;">' . strtoupper($partyName) . '</div>' . ($invoice->consignor ? str_replace("\n", "<br>", strtoupper($invoice->consignor->address ?? '')) : '');
    $partyAddress = !empty($invoice->billing_address) ? str_replace("\n", "<br>", strtoupper($invoice->billing_address)) : $fallbackAddress;
    
    $partyGst = $firstBulty && $firstBulty->consignor ? $firstBulty->consignor->gst_no : '08AAACL6442L1ZA';
    $partyState = ($firstBulty && ($firstBulty->destination_city ?? $firstBulty->destinationCity)) ? strtoupper(($firstBulty->destination_city ?? $firstBulty->destinationCity)->state) : 'RAJASTHAN';
    $partyHsn = $invoice->custom_hsn_code ? $invoice->custom_hsn_code : ($invoice->company ? $invoice->company->hsn_code : '996511');
    
    $billDate = $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d-m-Y') : date('d-m-Y');
    $billNo = $invoice->invoice_no;
    $mnNo = $invoice->mn_number ?: '-';
    $desc = "WALL PUTTY TRANSPORATION";
    
    $totalQty = 0;
    $totalAmt = 0;
    $totalDamageQty = 0;
    $totalDamageAmt = 0;
    $totalShortageQty = 0;
    $totalShortageAmt = 0;
    
    $invoiceType = $invoice->invoice_type;
    
    foreach($bulties as $bulty) {
        $detail = $bulty->bultyDetail;
        $qtyMt = $detail ? floatval(($detail->billed_qty ?? 0) != 0 ? $detail->billed_qty : (($detail->qty_mt ?? 0) != 0 ? $detail->qty_mt : ($detail->challan_qty ?? 0))) : 0;
        $rateMt = $detail ? floatval($detail->rate_mt) : 0;
        
        $amt = 0;
        if ($invoiceType === 'toll') {
            if ($bulty->trip && $bulty->trip->fastTagDetails) {
                $amt = $bulty->trip->fastTagDetails->sum('amount');
            }
        } else {
            $amt = floatval($bulty->freight_charges) - floatval($bulty->advance_amount);
        }
        
        if ($qtyMt === 0 && $rateMt > 0 && $amt > 0) {
            $qtyMt = $amt / $rateMt;
        } elseif ($rateMt === 0 && $qtyMt > 0 && $amt > 0) {
            $rateMt = $amt / $qtyMt;
        }
        
        $damageAmt = floatval($bulty->damage_amount);
        $shortageAmt = floatval($bulty->shortage_amount);
        
        $damageQty = floatval($bulty->damage_qty) ?: ($rateMt > 0 ? ($damageAmt / $rateMt) : 0);
        $shortageQty = floatval($bulty->short_qty) ?: ($rateMt > 0 ? ($shortageAmt / $rateMt) : 0);
        
        $totalQty += $qtyMt;
        $totalAmt += $amt;
        $totalDamageQty += $damageQty;
        $totalDamageAmt += $damageAmt;
        $totalShortageQty += $shortageQty;
        $totalShortageAmt += $shortageAmt;
    }
    
    $netTotalS2 = $totalAmt - $totalDamageAmt - $totalShortageAmt;
    
    $originState = $invoice->company && $invoice->company->state ? $invoice->company->state : ($firstBulty && $firstBulty->originCity ? ($firstBulty->originCity->state ?? 'RAJASTHAN') : 'RAJASTHAN');
    $partyState = !empty($invoice->custom_place_of_supply) ? $invoice->custom_place_of_supply : (($firstBulty && ($firstBulty->destination_city ?? $firstBulty->destinationCity)) ? ($firstBulty->destination_city ?? $firstBulty->destinationCity)->state : 'RAJASTHAN');
    $isSameState = \App\Http\Controllers\Admin\Transport\BillingController::isSameGstState($originState, $partyState);
    $gstType = $invoice->gst_type ?? ($isSameState ? 'CGST_SGST' : 'IGST');
    $gstRate = $invoice->gstMaster ? floatval($invoice->gstMaster->percentage) : 5.0;
    $halfGst = $gstRate / 2;

    if ($gstType === 'IGST') {
        $igst = $invoice->igst_amount > 0 ? floatval($invoice->igst_amount) : ($totalAmt * ($gstRate / 100));
        $cgst = 0;
        $sgst = 0;
        $totalGst = $igst;
    } else {
        $cgst = $invoice->cgst_amount > 0 ? floatval($invoice->cgst_amount) : ($totalAmt * ($halfGst / 100));
        $sgst = $invoice->sgst_amount > 0 ? floatval($invoice->sgst_amount) : ($totalAmt * ($halfGst / 100));
        $igst = 0;
        $totalGst = $cgst + $sgst;
    }
    $grandTotal = $netTotalS2;
    
    $displayMt = number_format($totalQty, 3, '.', '');
@endphp

<div class="container-fluid flex-grow-1 container-p-y hide-on-print">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Invoice Details: {{ $invoice->invoice_no }}</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.transport.invoices.index') }}">Invoice History</a></li>
                    <li class="breadcrumb-item active">{{ $invoice->invoice_no }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.transport.invoices.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back
            </a>
            <a href="{{ route('admin.transport.invoices.export-excel', $invoice->id) }}" class="btn btn-outline-success">
                <i class="bx bx-file me-1"></i> Excel Export
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bx bx-printer me-1"></i> Print Invoice
            </button>
        </div>
    </div>
</div>

<div class="print-container">
    <div class="card-body p-4" style="background: #fff; color: #000; font-family: 'Times New Roman', Times, serif; font-size: 12px; line-height: 1.4;">
        
        <!-- NATHDWARA SHEET 1: SUMMARY -->
        <div style="border: 2px solid #000; padding: 0; margin-bottom: 20px;">
            <!-- Top header -->
            <div class="d-flex justify-content-between p-2" style="border-bottom: 2px solid #000; font-weight: bold;">
                <div>GSTN: {{ $compGst }}</div>
                <div class="text-end">
                    <div>PAN: {{ $compPan }}</div>
                    {{--  <div>{{ $compOwner }} {{ $compPh }}</div>  --}}
                </div>
            </div>

            <!-- Company Header -->
            <div class="text-center p-2" style="border-bottom: 2px solid #000;">
                <h2 class="m-0 fw-bold" style="font-size: 22px; letter-spacing: 1px; text-transform: uppercase;">{{ $compName }}</h2>
            </div>
            
            <!-- Company Address -->
            <div class="text-center p-1 fw-bold" style="border-bottom: 2px solid #000; font-size: 11px;">{{ $compAdd }}</div>

            <!-- Client Details -->
            <div class="row g-0" style="border-bottom: 2px solid #000;">
                <div class="col-8 p-2" style="border-right: 2px solid #000; font-size: 11px; text-transform: uppercase;">
                    <div>{!! $partyAddress !!}</div>
                </div>
                <div class="col-4 p-2" style="font-size: 11px;">
                    <div><strong>HSN/SAC CODE:</strong> <span>{{ $partyHsn }}</span></div>
                    <div class="mt-2"><strong>Date:</strong> - <span>{{ $billDate }}</span></div>
                    <div><strong>Bill No:</strong> - <span>{{ $billNo }}</span></div>
                    @if(!empty($invoice->state_vendor_code))
                    <div class="mt-1"><strong>State Vendor Code:</strong> - <span>{{ $invoice->state_vendor_code }}</span></div>
                    @endif
                    @if(!empty($invoice->vendor_code))
                    <div><strong>Vendor Code:</strong> - <span>{{ $invoice->vendor_code }}</span></div>
                    @endif
                    @if(!empty($invoice->vendor_name))
                    <div><strong>Vendor Name:</strong> - <span>{{ $invoice->vendor_name }}</span></div>
                    @endif
                    @if(!empty($invoice->epod_status))
                    <div><strong>EPOD Status:</strong> - <span>{{ $invoice->epod_status }}</span></div>
                    @endif
                </div>
            </div>
            <div class="p-1 fw-bold" style="border-bottom: 2px solid #000; text-transform: uppercase;">WHETHER TAX IS PAYABLE UNDER REVERSE CHARGE MECHANISM : - {{ $rcmPayableVal == 1 ? 'YES' : 'NO' }}</div>

            <div class="text-center p-1 fw-bold" style="border-bottom: 2px solid #000;">Transportation Freight Bill</div>

            <!-- Table Sheet 1 -->
            <table class="w-100 text-center" style="border-collapse: collapse; border: none; font-size: 11px;">
                <thead>
                    <tr style="border-bottom: 2px solid #000; font-weight: bold;">
                        <td style="border-right: 2px solid #000; padding: 4px;">SR NO</td>
                        <td style="border-right: 2px solid #000; padding: 4px;">DESCRIPTION</td>
                        <td style="border-right: 2px solid #000; padding: 4px;">BILL NO</td>
                        <td style="border-right: 2px solid #000; padding: 4px;">MN NO</td>
                        <td style="border-right: 2px solid #000; padding: 4px;">NUMBER OF DI</td>
                        <td style="border-right: 2px solid #000; padding: 4px;">MT</td>
                        <td style="border-right: 2px solid #000; padding: 4px;">BILLING AMOUNT</td>
                        <td style="border-right: 2px solid #000; padding: 4px;">LESS SHORTAGE</td>
                        <td style="border-right: 2px solid #000; padding: 4px;">LESS DAMAGE</td>
                        <td style="padding: 4px;">NET TOTAL</td>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 2px solid #000; font-weight: bold;">
                        <td style="border-right: 2px solid #000; padding: 8px;">1</td>
                        <td style="border-right: 2px solid #000; padding: 8px;">{{ $desc }}</td>
                        <td style="border-right: 2px solid #000; padding: 8px;">{{ $billNo }}</td>
                        <td style="border-right: 2px solid #000; padding: 8px;">{{ $mnNo }}</td>
                        <td style="border-right: 2px solid #000; padding: 8px;">{{ $bulties->count() }}</td>
                        <td style="border-right: 2px solid #000; padding: 8px;">{{ $displayMt }}</td>
                        <td style="border-right: 2px solid #000; padding: 8px;">{{ number_format($totalAmt, 2, '.', '') }}</td>
                        <td style="border-right: 2px solid #000; padding: 8px;">{{ number_format($totalShortageAmt, 2, '.', '') }}</td>
                        <td style="border-right: 2px solid #000; padding: 8px;">{{ number_format($totalDamageAmt, 2, '.', '') }}</td>
                        <td style="padding: 8px;">{{ number_format($netTotalS2, 2, '.', '') }}</td>
                    </tr>
                    <!-- GST Rows -->
                    @if($gstType === 'IGST')
                    <tr>
                        <td colspan="7" style="border-right: 2px solid #000;"></td>
                        <td colspan="2" style="border-right: 2px solid #000; border-bottom: 2px solid #000; padding: 4px; text-align: center; font-weight: bold;">I GST {{ $gstRate }}%</td>
                        <td style="padding: 4px; border-bottom: 2px solid #000; font-weight: bold;">{{ number_format($igst, 4, '.', '') }}</td>
                    </tr>
                    @else
                    <tr>
                        <td colspan="7" style="border-right: 2px solid #000;"></td>
                        <td colspan="2" style="border-right: 2px solid #000; border-bottom: 1px solid #000; padding: 4px; text-align: right; font-weight: bold;">C GST {{ $halfGst }}%</td>
                        <td style="padding: 4px; border-bottom: 1px solid #000; font-weight: bold;">{{ number_format($cgst, 4, '.', '') }}</td>
                    </tr>
                    <tr>
                        <td colspan="7" style="border-right: 2px solid #000;"></td>
                        <td colspan="2" style="border-right: 2px solid #000; border-bottom: 2px solid #000; padding: 4px; text-align: right; font-weight: bold;">S GST {{ $halfGst }}%</td>
                        <td style="padding: 4px; border-bottom: 2px solid #000; font-weight: bold;">{{ number_format($sgst, 4, '.', '') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="7" style="border-right: 2px solid #000;"></td>
                        <td colspan="2" style="border-right: 2px solid #000; border-bottom: 2px solid #000; padding: 4px; text-align: center; font-weight: bold;">TOTAL GST</td>
                        <td style="padding: 4px; border-bottom: 2px solid #000; font-weight: bold;">{{ number_format($totalGst, 3, '.', '') }}</td>
                    </tr>
                    <tr>
                        <td colspan="7" style="border-right: 2px solid #000;"></td>
                        <td colspan="2" style="border-right: 2px solid #000; border-bottom: 2px solid #000; padding: 4px; text-align: center; font-weight: bold;">GRAND TOTAL</td>
                        <td style="padding: 4px; border-bottom: 2px solid #000; font-weight: bold;">{{ number_format($grandTotal, 2, '.', '') }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Amount in Words -->
            <div class="p-2 fw-bold" style="border-bottom: 2px solid #000;">
                AMOUNT IN WORD: <span>{{ \App\Http\Controllers\Admin\Transport\BillingController::convertNumberToWords($grandTotal) }}</span>
            </div>

            <!-- Footer Block -->
            <div class="row g-0">
                <div class="col-7 p-2" style="border-right: 2px solid #000;">
                    <div class="fw-bold text-center mt-4 mb-4">Declaration : {{ $compDeclaration }}</div>
                    <table class="w-100" style="border-collapse: collapse;">
                        <tr>
                            <td class="fw-bold p-1" style="border: 2px solid #000; width: 40%;">ACCOUNT NO.</td>
                            <td class="p-1 text-center fw-bold" style="border: 2px solid #000;">{{ $bankAccountNo }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold p-1" style="border: 2px solid #000;">IFC CODE</td>
                            <td class="p-1 text-center fw-bold" style="border: 2px solid #000;">{{ $bankIfsc }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold p-1" style="border: 2px solid #000;">HOLDER NAME</td>
                            <td class="p-1 text-center fw-bold" style="border: 2px solid #000;">{{ $bankHolder }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-5 p-2 d-flex flex-column text-end position-relative">
                    <div class="fw-bold">For <span>{{ $compName }}</span></div>
                    <div style="padding: 4px 0;" class="text-end">
                        @if(!empty($compSigUrl))
                            <img src="{{ $compSigUrl }}" alt="Signature" style="max-height: 45px; max-width: 140px; object-fit: contain;">
                        @else
                            <div style="height: 30px;"></div>
                        @endif
                        @if(!empty($compOwner))
                            <div style="font-size: 8px; color: #333; font-weight: bold; line-height: 1.2;">Digitally signed by {{ $compOwner }}</div>
                        @endif
                        <div style="font-size: 8px; color: #555; line-height: 1.2;">Date: {{ date('d-m-Y H:i:s') }}</div>
                    </div>
                    <div style="margin-top: auto;" class="fw-bold">Authorized Signatory</div>
                </div>
            </div>
        </div>

        <!-- NATHDWARA PAGE BREAK -->
        <div class="text-center my-4 text-muted page-break" style="border-top: 2px dashed #888; padding-top: 8px; font-weight: bold; page-break-after: always; break-after: page;">
            --- PAGE BREAK ---
        </div>

        <!-- NATHDWARA SHEET 2: ANNEXURE -->
        <div style="border: 2px solid #000; padding: 0;">
            <div class="text-center p-2" style="border-bottom: 2px solid #000;">
                <h3 class="m-0 fw-bold" style="text-transform: uppercase;">{{ $compName }}.</h3>
                <div class="fw-bold" style="font-size: 11px;">Off Phone No {{ $compPh }}, Mobile No Res.Tel</div>
            </div>
            
            <div class="text-end p-1 fw-bold" style="border-bottom: 2px solid #000;">
                Service Tax Regn No : 
            </div>

            <div class="row g-0" style="border-bottom: 2px solid #000; font-weight: bold;">
                <div class="col-3 p-1" style="border-right: 2px solid #000;">Bill No: <span>{{ $billNo }}</span></div>
                <div class="col-6 p-1 text-center" style="border-right: 2px solid #000;">ULTRATECH CEMENT LIMITED UNIT: BIRLA WHITE Rajashree Nagar Vill. Kharia Khangar-342606</div>
                <div class="col-3 p-1">Dt: <span>{{ $billDate }}</span></div>
            </div>

            <div class="text-center fw-bold p-1" style="border-bottom: 2px solid #000;">Summary</div>

            <table class="w-100" style="border-collapse: collapse; border-bottom: 2px solid #000;">
                <tr style="border-bottom: 2px solid #000;">
                    <td class="p-1 fw-bold" style="border-right: 2px solid #000; width: 50%;">Charge Type</td>
                    <td class="p-1 fw-bold text-center" style="border-right: 2px solid #000; width: 25%;">QTY(MT)</td>
                    <td class="p-1 fw-bold text-center" style="width: 25%;">Amount</td>
                </tr>
                <tr>
                    <td class="p-1" style="border-right: 2px solid #000;">ROAD</td>
                    <td class="p-1 text-center" style="border-right: 2px solid #000;">{{ number_format($totalQty, 3, '.', '') }}</td>
                    <td class="p-1 text-center">{{ number_format($totalAmt, 2, '.', '') }}</td>
                </tr>
            </table>

            <!-- Detailed Table -->
            <table class="w-100 text-center" style="border-collapse: collapse; font-size: 10px;">
                <thead>
                    <tr style="border-bottom: 2px solid #000; font-weight: bold;">
                        <td style="border-right: 1px solid #000; padding: 2px;">Truck No</td>
                        <td style="border-right: 1px solid #000; padding: 2px;">From Place</td>
                        <td style="border-right: 1px solid #000; padding: 2px;">To Place</td>
                        <td style="border-right: 1px solid #000; padding: 2px;">Consignment No</td>
                        <td style="border-right: 1px solid #000; padding: 2px;">Date</td>
                        <td style="border-right: 1px solid #000; padding: 2px;">Lane</td>
                        <td style="border-right: 1px solid #000; padding: 2px;">QTY(MT)</td>
                        <td style="border-right: 1px solid #000; padding: 2px;">Rate</td>
                        <td style="border-right: 1px solid #000; padding: 2px;">Damage<br>Qty</td>
                        <td style="border-right: 1px solid #000; padding: 2px;">Damage<br>Rate</td>
                        <td style="border-right: 1px solid #000; padding: 2px;">Shortage<br>Qty</td>
                        <td style="border-right: 1px solid #000; padding: 2px;">Shortage<br>Rate</td>
                        <td style="border-right: 1px solid #000; padding: 2px;">Amt.</td>
                        <td style="border-right: 1px solid #000; padding: 2px;">S.Tax</td>
                        <td style="padding: 2px;">S.B.Cess</td>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bulties as $bulty)
                        @php
                            $fromCity = ($bulty->origin_city ?? $bulty->originCity) ? ($bulty->origin_city->name ?? $bulty->originCity->name) : '-';
                            $toCity = ($bulty->destination_city ?? $bulty->destinationCity) ? ($bulty->destination_city->name ?? $bulty->destinationCity->name) : '-';
                            $formattedLrDate = '-';
                            try {
                                if($bulty->lr_date) {
                                    $formattedLrDate = \Carbon\Carbon::parse($bulty->lr_date)->format('d.m.Y');
                                }
                            } catch(\Exception $e) {}

                            $detail = $bulty->bultyDetail;
                            $qtyMt = $detail ? floatval(($detail->billed_qty ?? 0) != 0 ? $detail->billed_qty : (($detail->qty_mt ?? 0) != 0 ? $detail->qty_mt : ($detail->challan_qty ?? 0))) : 0;
                            $rateMt = $detail ? floatval($detail->rate_mt) : 0;
                            
                            $amt = 0;
                            if ($invoiceType === 'toll') {
                                if ($bulty->trip && $bulty->trip->fastTagDetails) {
                                    $amt = $bulty->trip->fastTagDetails->sum('amount');
                                }
                            } else {
                                $amt = floatval($bulty->freight_charges) - floatval($bulty->advance_amount);
                            }
                            
                            if ($qtyMt === 0 && $rateMt > 0 && $amt > 0) {
                                $qtyMt = $amt / $rateMt;
                            } elseif ($rateMt === 0 && $qtyMt > 0 && $amt > 0) {
                                $rateMt = $amt / $qtyMt;
                            }

                            $damageAmt = floatval($bulty->damage_amount);
                            $shortageAmt = floatval($bulty->shortage_amount);
                            
                            $damageQty = floatval($bulty->damage_qty) ?: ($rateMt > 0 ? ($damageAmt / $rateMt) : 0);
                            $shortageQty = floatval($bulty->short_qty) ?: ($rateMt > 0 ? ($shortageAmt / $rateMt) : 0);
                        @endphp
                        <tr>
                            <td style="border-right: 1px solid #000; padding: 2px;">{{ $bulty->vehicle ? $bulty->vehicle->vehicle_number : '-' }}</td>
                            <td style="border-right: 1px solid #000; padding: 2px;">{{ $fromCity }}</td>
                            <td style="border-right: 1px solid #000; padding: 2px;">{{ $toCity }}</td>
                            <td style="border-right: 1px solid #000; padding: 2px;">{{ $bulty->lr_no }}</td>
                            <td style="border-right: 1px solid #000; padding: 2px;">{{ $formattedLrDate }}</td>
                            <td style="border-right: 1px solid #000; padding: 2px;">ROAD</td>
                            <td style="border-right: 1px solid #000; padding: 2px;">{{ number_format($qtyMt, 3, '.', '') }}</td>
                            <td style="border-right: 1px solid #000; padding: 2px;">{{ number_format($rateMt, 2, '.', '') }}</td>
                            <td style="border-right: 1px solid #000; padding: 2px;">{{ number_format($damageQty, 3, '.', '') }}</td>
                            <td style="border-right: 1px solid #000; padding: 2px;">{{ $damageQty > 0 ? number_format($damageAmt/$damageQty, 2, '.', '') : '0.00' }}</td>
                            <td style="border-right: 1px solid #000; padding: 2px;">{{ number_format($shortageQty, 3, '.', '') }}</td>
                            <td style="border-right: 1px solid #000; padding: 2px;">{{ $shortageQty > 0 ? number_format($shortageAmt/$shortageQty, 2, '.', '') : '0.00' }}</td>
                            <td style="border-right: 1px solid #000; padding: 2px;">{{ number_format($amt, 2, '.', '') }}</td>
                            <td style="border-right: 1px solid #000; padding: 2px;">0.00</td>
                            <td style="padding: 2px;">0.00</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="12" class="text-end fw-bold" style="border-right: 1px solid #000; border-top: 2px solid #000; padding: 2px;">Gross Total:</td>
                        <td class="text-center fw-bold" style="border-right: 1px solid #000; border-top: 2px solid #000; padding: 2px;">{{ number_format($totalAmt, 2, '.', '') }}</td>
                        <td colspan="2" style="border-top: 2px solid #000;"></td>
                    </tr>
                    <tr>
                        <td colspan="12" class="text-end fw-bold" style="border-right: 1px solid #000; border-top: 1px solid #000; padding: 2px;">Less :Shortage</td>
                        <td class="text-center fw-bold" style="border-right: 1px solid #000; border-top: 1px solid #000; padding: 2px;">{{ number_format($totalShortageAmt, 2, '.', '') }}</td>
                        <td colspan="2" style="border-top: 1px solid #000;"></td>
                    </tr>
                    <tr>
                        <td colspan="12" class="text-end fw-bold" style="border-right: 1px solid #000; border-top: 1px solid #000; padding: 2px;">Less: Damage</td>
                        <td class="text-center fw-bold" style="border-right: 1px solid #000; border-top: 1px solid #000; padding: 2px;">{{ number_format($totalDamageAmt, 2, '.', '') }}</td>
                        <td colspan="2" style="border-top: 1px solid #000;"></td>
                    </tr>
                    <tr>
                        <td colspan="12" class="text-end fw-bold" style="border-right: 1px solid #000; border-top: 1px solid #000; padding: 2px;">Net Total</td>
                        <td class="text-center fw-bold" style="border-right: 1px solid #000; border-top: 1px solid #000; padding: 2px;">{{ number_format($netTotalS2, 2, '.', '') }}</td>
                        <td colspan="2" style="border-top: 1px solid #000;"></td>
                    </tr>
                    <tr>
                        <td colspan="12" class="text-end fw-bold" style="border-right: 1px solid #000; border-top: 1px solid #000; padding: 2px;">Service Tax</td>
                        <td class="text-center fw-bold" style="border-right: 1px solid #000; border-top: 1px solid #000; padding: 2px;">0.00</td>
                        <td colspan="2" style="border-top: 1px solid #000;"></td>
                    </tr>
                    <tr>
                        <td colspan="12" class="text-end fw-bold" style="border-right: 1px solid #000; border-top: 1px solid #000; padding: 2px;">Swachh Bharat cess</td>
                        <td class="text-center fw-bold" style="border-right: 1px solid #000; border-top: 1px solid #000; padding: 2px;">0.00</td>
                        <td colspan="2" style="border-top: 1px solid #000;"></td>
                    </tr>
                    <tr>
                        <td colspan="12" class="text-end fw-bold" style="border-right: 1px solid #000; border-top: 1px solid #000; padding: 2px;">Total Value :</td>
                        <td class="text-center fw-bold" style="border-right: 1px solid #000; border-top: 1px solid #000; border-bottom: 2px solid #000; padding: 2px;">{{ number_format($netTotalS2, 2, '.', '') }}</td>
                        <td colspan="2" style="border-top: 1px solid #000; border-bottom: 2px solid #000;"></td>
                    </tr>
                </tfoot>
            </table>

            <div class="p-2" style="font-size: 10px;">
                <div>In words : <span>{{ \App\Http\Controllers\Admin\Transport\BillingController::convertNumberToWords($netTotalS2) }}</span></div>
                <div class="text-center mt-2 fw-bold">DECLARATION</div>
                <div>{{ $compDeclaration }}</div>
                
                <div class="text-end mt-3 mb-1">
                    <div class="fw-bold" style="font-size: 12px;">For <span>{{ $compName }}</span></div>
                    <div style="padding: 4px 0;" class="text-end">
                        @if(!empty($compSigUrl))
                            <img src="{{ $compSigUrl }}" alt="Signature" style="max-height: 45px; max-width: 140px; object-fit: contain;">
                        @else
                            <div style="height: 30px;"></div>
                        @endif
                        @if(!empty($compOwner))
                            <div style="font-size: 8px; color: #333; font-weight: bold; line-height: 1.2;">Digitally signed by {{ $compOwner }}</div>
                        @endif
                        <div style="font-size: 8px; color: #555; line-height: 1.2;">Date: {{ date('d-m-Y H:i:s') }}</div>
                    </div>
                    <div style="font-size: 10px; font-weight: bold;">Authorized Signatory</div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    @media print {
        body {
            background-color: #fff;
        }
        .hide-on-print {
            display: none !important;
        }
        .layout-menu, .layout-navbar, .footer {
            display: none !important;
        }
        .layout-page {
            padding: 0 !important;
        }
        .container-p-y {
            padding: 0 !important;
        }
        .print-container {
            width: 100%;
            margin: 0;
            padding: 0;
        }
        .page-break {
            page-break-after: always;
            break-after: page;
        }
    }
</style>

@endsection
