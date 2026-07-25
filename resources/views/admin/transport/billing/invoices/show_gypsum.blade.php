@extends('admin.layouts.app')

@section('content')

@php
    $bulties = collect();
    if(isset($invoice->bulties)) {
        $bulties = $invoice->bulties;
    }
    
    $comp = $invoice->company ?? ($bulties->first()?->company ?? null);
    $compName = !empty($invoice->company_name) ? $invoice->company_name : ($comp ? $comp->name : '');
    $compAdd = $comp ? $comp->address : '';
    $compGst = $comp ? $comp->gst_number : '';
    $compPan = $comp && $comp->pan_number ? $comp->pan_number : '';
    $compPh = $comp ? $comp->phone : '';
    $compOwner = $comp && $comp->owner_name ? strtoupper($comp->owner_name) : '';
    $bankAccountNo = $comp && $comp->bank_account_no ? $comp->bank_account_no : '';
    $bankIfsc = $comp && $comp->bank_ifsc ? $comp->bank_ifsc : '';
    $bankHolder = $comp && $comp->bank_holder_name ? strtoupper($comp->bank_holder_name) : '';

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
    $desc = "TRANSPORTATION OF GYPSUM";
    
    $totalQty = 0;
    $totalAmt = 0;
    $totalDamageQty = 0;
    $totalDamageAmt = 0;
    $totalShortageQty = 0;
    $totalShortageAmt = 0;
    
    $invoiceType = $invoice->invoice_type;
    
    foreach($bulties as $bulty) {
        $detail = $bulty->bultyDetail;
        $rateMt = $detail ? floatval($detail->rate_mt) : 0;
        
        $qtyMt = 0;
        $amt = 0;
        if ($invoiceType === 'toll') {
            $qtyMt = $detail ? floatval($detail->qty_mt) : 0;
            if ($bulty->trip && $bulty->trip->fastTagDetails) {
                $amt = $bulty->trip->fastTagDetails->sum('amount');
            }
            if ($qtyMt === 0 && $rateMt > 0 && $amt > 0) {
                $qtyMt = $amt / $rateMt;
            } elseif ($rateMt === 0 && $qtyMt > 0 && $amt > 0) {
                $rateMt = $amt / $qtyMt;
            }
        } else {
            $qtyMt = $detail ? floatval($detail->final_wgt) : 0;
            $amt = $qtyMt * $rateMt;
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
    
    $customRate = floatval($invoice->custom_rate ?? 0);
    $displayRate = $totalQty > 0 ? ($totalAmt / $totalQty) : 0;
    $displayTotalAmt = $totalAmt;

    if ($customRate > 0) {
        $displayRate = $customRate;
        $displayTotalAmt = $totalQty * $customRate;
    }

    $netTotalS2 = $displayTotalAmt - $totalDamageAmt - $totalShortageAmt;
    
    $originState = $invoice->company && $invoice->company->state ? $invoice->company->state : ($firstBulty && $firstBulty->originCity ? ($firstBulty->originCity->state ?? 'RAJASTHAN') : 'RAJASTHAN');
    $partyState = !empty($invoice->custom_place_of_supply) ? $invoice->custom_place_of_supply : (($firstBulty && ($firstBulty->destination_city ?? $firstBulty->destinationCity)) ? ($firstBulty->destination_city ?? $firstBulty->destinationCity)->state : 'RAJASTHAN');
    $isSameState = \App\Http\Controllers\Admin\Transport\BillingController::isSameGstState($originState, $partyState);
    $gstType = $invoice->gst_type ?? ($isSameState ? 'CGST_SGST' : 'IGST');
    $gstRate = $invoice->gstMaster ? floatval($invoice->gstMaster->percentage) : 5.0;
    $halfGst = $gstRate / 2;

    if ($gstType === 'IGST') {
        $igst = $invoice->igst_amount > 0 ? floatval($invoice->igst_amount) : ($displayTotalAmt * ($gstRate / 100));
        $cgst = 0;
        $sgst = 0;
        $totalGst = $igst;
    } else {
        $cgst = $invoice->cgst_amount > 0 ? floatval($invoice->cgst_amount) : ($displayTotalAmt * ($halfGst / 100));
        $sgst = $invoice->sgst_amount > 0 ? floatval($invoice->sgst_amount) : ($displayTotalAmt * ($halfGst / 100));
        $igst = 0;
        $totalGst = $cgst + $sgst;
    }
    $grandTotal = $displayTotalAmt;
    
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
            
            <div class="row g-0" style="border-bottom: 2px solid #000; font-weight: bold; font-size: 11px;">
                <div class="col-6 p-2" style="border-right: 2px solid #000;">
                    PO NO. - {{ $invoice->mn_number }}
                </div>
                <div class="col-6 p-2 text-end">
                    DETAILS: AS PER ANNEXURE ATTACHED
                </div>
            </div>

            <div class="text-center p-1 fw-bold" style="border-bottom: 2px solid #000;">Transportation Freight Bill</div>

            <!-- Table Sheet 1 -->
            <table class="w-100 text-center" style="border-collapse: collapse; border: none; font-size: 11px;">
                <thead>
                    <tr style="border-bottom: 2px solid #000; font-weight: bold;">
                        <td style="border-right: 2px solid #000; padding: 4px;">S.R No</td>
                        <td style="border-right: 2px solid #000; padding: 4px;">Description of Services</td>
                        <td style="border-right: 2px solid #000; padding: 4px;">Quantity</td>
                        <td style="border-right: 2px solid #000; padding: 4px;">Rate</td>
                        <td style="border-right: 2px solid #000; padding: 4px;">UoM</td>
                        <td style="padding: 4px;">Total Amount</td>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 2px solid #000; font-weight: bold;">
                        <td style="border-right: 2px solid #000; padding: 8px;">1</td>
                        <td style="border-right: 2px solid #000; padding: 8px;">{{ $invoice->description ?? 'TRANSPORTATION OF GYPSUM' }}</td>
                        <td style="border-right: 2px solid #000; padding: 8px;">{{ number_format($totalQty, 3, '.', '') }}</td>
                        <td style="border-right: 2px solid #000; padding: 8px;">{{ number_format($displayRate, 3, '.', '') }}</td>
                        <td style="border-right: 2px solid #000; padding: 8px;"></td>
                        <td style="padding: 8px;">{{ number_format($displayTotalAmt, 2, '.', '') }}</td>
                    </tr>
                    <!-- GST Rows -->
                    @if($gstType === 'IGST')
                    <tr>
                        <td colspan="4" style="border-right: 2px solid #000;"></td>
                        <td style="border-right: 2px solid #000; border-bottom: 2px solid #000; padding: 4px; text-align: center; font-weight: bold;">I GST {{ $gstRate }}%</td>
                        <td style="padding: 4px; border-bottom: 2px solid #000; font-weight: bold;">{{ number_format($igst, 4, '.', '') }}</td>
                    </tr>
                    @else
                    <tr>
                        <td colspan="4" style="border-right: 2px solid #000;"></td>
                        <td style="border-right: 2px solid #000; border-bottom: 1px solid #000; padding: 4px; text-align: right; font-weight: bold;">C GST {{ $halfGst }}%</td>
                        <td style="padding: 4px; border-bottom: 1px solid #000; font-weight: bold;">{{ number_format($cgst, 4, '.', '') }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" style="border-right: 2px solid #000;"></td>
                        <td style="border-right: 2px solid #000; border-bottom: 2px solid #000; padding: 4px; text-align: right; font-weight: bold;">S GST {{ $halfGst }}%</td>
                        <td style="padding: 4px; border-bottom: 2px solid #000; font-weight: bold;">{{ number_format($sgst, 4, '.', '') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="4" style="border-right: 2px solid #000;"></td>
                        <td style="border-right: 2px solid #000; border-bottom: 2px solid #000; padding: 4px; text-align: center; font-weight: bold;">TOTAL GST</td>
                        <td style="padding: 4px; border-bottom: 2px solid #000; font-weight: bold;">{{ number_format($totalGst, 3, '.', '') }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" style="border-right: 2px solid #000;"></td>
                        <td style="border-right: 2px solid #000; border-bottom: 2px solid #000; padding: 4px; text-align: center; font-weight: bold;">GRAND TOTAL</td>
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
        
        <!-- GYPSUM PAGE BREAK -->
        <div class="hide-on-print text-center my-4 text-muted" style="border-top: 2px dashed #888; padding-top: 8px; font-weight: bold;">
            --- PAGE BREAK ---
        </div>

        <!-- GYPSUM SHEET 2: GRN DETAILS -->
        <div style="border: 2px solid #000; padding: 0; page-break-before: always; break-before: page;">
            <div class="table-responsive">
                <table class="w-100 text-center" style="border-collapse: collapse; font-size: 9px; border: none;">
                    <thead>
                        <tr style="border-bottom: 2px solid #000;">
                            <td colspan="2" style="border-right: 2px solid #000;"></td>
                            <td colspan="14" class="text-center p-2" style="border-right: 2px solid #000;">
                                <h3 class="m-0 fw-bold" style="text-transform: uppercase;">{{ $compName }} GRN DETAILS</h3>
                            </td>
                            <td colspan="2"></td>
                        </tr>
                        <tr style="border-bottom: 2px solid #000; font-weight: bold; background-color: #ffff00 !important; -webkit-print-color-adjust: exact; color-adjust: exact;">
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px;">Posting Date</td>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px;">PO No</td>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px;">Mat Doc</td>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px;">Gate Entry No</td>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px;">Gate Out Date</td>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px;">Supplier</td>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px;">Supplier Name</td>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px;">Vehicle No</td>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px;">Challan No</td>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px;">Challan Date</td>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px;">LR No</td>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px;">Transporter</td>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px;">Transporter Name</td>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px;">PO Item</td>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px;">Material</td>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px;">Material Name</td>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px;">Challan Qty</td>
                            <td style="border-bottom: 1px solid #000; padding: 4px;">Final Wgt</td>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalChallanQty = 0;
                            $totalFinalWgt = 0;
                        @endphp
                        @foreach($bulties as $bulty)
                            @php
                                $detail = $bulty->bultyDetail;
                                $postingDate = $detail && $detail->posting_date ? \Carbon\Carbon::parse($detail->posting_date)->format('d/m/Y') : '-';
                                $poNo = $detail->po_no ?? '-';
                                $matDoc = $detail->mat_doc ?? '-';
                                $gateEntryNo = $detail->gate_entry_no ?? '-';
                                $gateOutDate = $detail && $detail->gate_out_date ? \Carbon\Carbon::parse($detail->gate_out_date)->format('d/m/Y') : '-';
                                $supplier = $detail->supplier_no ?? '-';
                                $supplierName = $detail && $detail->supplier ? $detail->supplier->name : '-';
                                $vehicleNo = $bulty->vehicle ? $bulty->vehicle->vehicle_number : '-';
                                $challanNo = $detail->challan_no ?? '-';
                                $challanDate = $detail && $detail->challan_date ? \Carbon\Carbon::parse($detail->challan_date)->format('d-m-Y') : '-';
                                $lrNo = $bulty->lr_no ?? '-';
                                $transporter = $detail->transporter_code ?? '-';
                                $transporterName = $detail->transporter_name ?? '-';
                                $poItem = $detail->po_item ?? '-';
                                $material = $detail->material_no ?? '-';
                                $materialName = $detail->material_name ?? '-';
                                $challanQty = $detail ? floatval($detail->challan_qty) : 0;
                                $finalWgt = $detail ? floatval($detail->final_wgt) : 0;
                                
                                $totalChallanQty += $challanQty;
                                $totalFinalWgt += $finalWgt;
                            @endphp
                            <tr>
                                <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 2px;">{{ $postingDate }}</td>
                                <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 2px;">{{ $poNo }}</td>
                                <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 2px;">{{ $matDoc }}</td>
                                <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 2px;">{{ $gateEntryNo }}</td>
                                <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 2px;">{{ $gateOutDate }}</td>
                                <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 2px;">{{ $supplier }}</td>
                                <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 2px;">{{ $supplierName }}</td>
                                <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 2px;">{{ $vehicleNo }}</td>
                                <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 2px;">{{ $challanNo }}</td>
                                <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 2px;">{{ $challanDate }}</td>
                                <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 2px;">{{ $lrNo }}</td>
                                <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 2px;">{{ $transporter }}</td>
                                <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 2px;">{{ $transporterName }}</td>
                                <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 2px;">{{ $poItem }}</td>
                                <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 2px;">{{ $material }}</td>
                                <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 2px;">{{ $materialName }}</td>
                                <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 2px;">{{ number_format($challanQty, 2, '.', '') }}</td>
                                <td style="border-bottom: 1px solid #000; padding: 2px;">{{ number_format($finalWgt, 2, '.', '') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="16" class="text-center fw-bold" style="border-right: 1px solid #000; padding: 4px; font-size: 11px;">TOTAL</td>
                            <td class="fw-bold" style="border-right: 1px solid #000; padding: 4px; font-size: 11px;">{{ number_format($totalChallanQty, 3, '.', '') }}</td>
                            <td class="fw-bold" style="padding: 4px; font-size: 11px;">{{ number_format($totalFinalWgt, 3, '.', '') }}</td>
                        </tr>
                    </tfoot>
                </table>
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
