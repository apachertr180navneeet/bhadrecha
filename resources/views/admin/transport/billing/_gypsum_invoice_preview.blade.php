@php
    $firstBulty = $bulties->first();
    $companyName = $invoiceCompany ? $invoiceCompany->name : '';
    $companyAddress = $invoiceCompany ? $invoiceCompany->address : '';
    $companyGst = $invoiceCompany ? $invoiceCompany->gst_number : '';
    $companyPan = $invoiceCompany && $invoiceCompany->pan_number ? $invoiceCompany->pan_number : '';
    $companyPhone = $invoiceCompany ? $invoiceCompany->phone : '';
    $companyOwner = $invoiceCompany && $invoiceCompany->owner_name ? strtoupper($invoiceCompany->owner_name) : '';
    $companyHsn = !empty($existingInvoice?->custom_hsn_code) ? $existingInvoice->custom_hsn_code : ($invoiceCompany && $invoiceCompany->hsn_code ? $invoiceCompany->hsn_code : '996511');

    $bankAccountNo = $invoiceCompany && $invoiceCompany->bank_account_no ? $invoiceCompany->bank_account_no : '';
    $bankIfsc = $invoiceCompany && $invoiceCompany->bank_ifsc ? $invoiceCompany->bank_ifsc : '';
    $bankHolder = $invoiceCompany && $invoiceCompany->bank_holder_name ? strtoupper($invoiceCompany->bank_holder_name) : '';

    $comp = $invoiceCompany ?? ($existingInvoice?->company ?? null);
    $companySignatureUrl = $comp?->digital_signature_url;
    $rcmPayableVal = isset($rcmPayable) ? $rcmPayable : ($existingInvoice?->rcm_payable ?? 1);

    if ($rcmPayableVal == 1) {
        $companyDeclaration = ($comp && !empty($comp->declaration))
            ? $comp->declaration
            : 'GST payable by recipient under Reverse Charge (RCM) on GTA services.';
    } else {
        $companyDeclaration = ($comp && !empty($comp->declaration))
            ? $comp->declaration
            : 'Tax payable under Forward Charge.';
    }

    $partyName = $existingInvoice?->consignor_name ?? ($existingInvoice?->consignor->name ?? '-');
    $fallbackAddress = '<div class="fw-bold" style="font-size: 11px;">' . $partyName . '</div>' . ($existingInvoice?->consignor ? str_replace("\n", "<br>", $existingInvoice->consignor->address ?? '') : '');
    $partyAddress = !empty($existingInvoice?->billing_address) ? str_replace("\n", "<br>", $existingInvoice->billing_address) : $fallbackAddress;
    $partyGst = $existingInvoice?->consignor ? ($existingInvoice->consignor->gst_no ?? '-') : '-';
    $partyState = !empty($existingInvoice?->custom_place_of_supply) ? $existingInvoice->custom_place_of_supply : ($firstBulty && $firstBulty->destinationCity ? ($firstBulty->destinationCity->state ?? 'RAJASTHAN') : 'RAJASTHAN');

    // State calculation for CGST/SGST vs IGST
    $originState = $invoiceCompany && $invoiceCompany->state ? $invoiceCompany->state : ($firstBulty && $firstBulty->originCity ? ($firstBulty->originCity->state ?? 'RAJASTHAN') : 'RAJASTHAN');
    $isSameState = \App\Http\Controllers\Admin\Transport\BillingController::isSameGstState($originState, $partyState);
    $gstType = $existingInvoice?->gst_type ?? ($isSameState ? 'CGST_SGST' : 'IGST');

    $gstRate = $existingInvoice?->gstMaster ? floatval($existingInvoice->gstMaster->percentage) : 0;
    $halfGst = $gstRate / 2;

    $mnNumber = $existingInvoice?->mn_number ?? '-';
    $invoiceDate = $existingInvoice?->invoice_date ? $existingInvoice->invoice_date->format('d-m-Y') : now()->format('d-m-Y');
    
    // Calculate aggregates
    $totalMt = 0;
    $totalAmount = 0;
    $totalDamageQty = 0;
    $totalDamageAmount = 0;
    $totalShortageQty = 0;
    $totalShortageAmount = 0;

    foreach($bulties as $bulty) {
        $qty = 0;
        $damageQty = 0;
        $shortQty = 0;
        $rateMt = 0;
        if ($bulty->bultyDetail) {
            $qty = floatval($bulty->bultyDetail->final_wgt ?? 0);
            $damageQty = floatval($bulty->bultyDetail->damage_qty ?? 0);
            $shortQty = floatval($bulty->bultyDetail->short_qty ?? 0);
            $rateMt = floatval($bulty->bultyDetail->rate_mt ?? 0);
        }
        
        $totalMt += $qty;
        $totalDamageQty += $damageQty;
        $totalShortageQty += $shortQty;
        
        $totalDamageAmount += floatval($bulty->damage_amount);
        $totalShortageAmount += floatval($bulty->shortage_amount);
        $f = $qty * $rateMt;
        $totalAmount += $f;
    }

    $customRate = floatval($existingInvoice->custom_rate ?? 0);
    $displayRate = $totalMt > 0 ? ($totalAmount / $totalMt) : 0;
    $displayTotalAmount = $totalAmount;

    if ($customRate > 0) {
        $displayRate = $customRate;
        $displayTotalAmount = $totalMt * $customRate;
    }

    $netTotal = $displayTotalAmount - $totalShortageAmount - $totalDamageAmount;
    
    // Aggregate Bill No and MN No from Bulty details if available
    $bultyBillNos = collect($bulties)->map(function($b) { return $b->bultyDetail->bill_no ?? null; })->filter()->unique()->implode(', ');
    $bultyMnNos = collect($bulties)->map(function($b) { return $b->bultyDetail->mn_no ?? null; })->filter()->unique()->implode(', ');

    $rowBillNo = !empty($bultyBillNos) ? $bultyBillNos : $billNumber;
    $rowMnNo = !empty($bultyMnNos) ? $bultyMnNos : $mnNumber;

    $gstAmount = $displayTotalAmount * ($gstRate / 100);
    $halfGstAmount = $gstAmount / 2;
    $grandTotal = $displayTotalAmount + $gstAmount; 
@endphp

<div class="invoice-preview-container" style="background: #fff; color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 11px; line-height: 1.4;">
    
    <!-- SHEET 1: MAIN FREIGHT BILL -->
    <div id="freight-preview-sheet" style="border: 2px solid #000; padding: 0; box-sizing: border-box;">
        
        <!-- Header Top -->
        <div class="d-flex justify-content-between align-items-center" style="border-bottom: 2px solid #000; padding: 4px 8px; font-weight: bold; font-size: 10px;">
            <div>GSTIN:<span>{{ $companyGst }}</span></div>
            <div>
                <div>PAN:<span>{{ $companyPan }}</span></div>
            </div>
        </div>
        
        <!-- Company Name -->
        <div class="text-center" style="border-bottom: 2px solid #000; padding: 8px;">
            <h2 class="m-0 fw-bold" style="font-size: 20px; letter-spacing: 1px; text-transform: uppercase;">{{ $companyName }}</h2>
        </div>
        
        <!-- Address -->
        <div class="text-center" style="border-bottom: 2px solid #000; padding: 4px; font-weight: bold;">
            {{ $companyAddress }}
        </div>
        
        <!-- Client details -->
        <div class="row g-0" style="border-bottom: 2px solid #000;">
            <div class="col-8 p-2" style="border-right: 2px solid #000; font-size: 10px;">
                <div>{!! $partyAddress !!}</div>
            </div>
            <div class="col-4 p-2" style="font-size: 10px;">
                <div><strong>HSN/SAC CODE:</strong> <span>{{ $companyHsn }}</span></div>
            </div>
        </div>
        
        <!-- State & Date -->
        <div class="row g-0" style="border-bottom: 2px solid #000; font-size: 10px;">
            <div class="col-8 p-2" style="border-right: 2px solid #000;">
                <strong>State Name:-</strong> {{ $partyState }}
            </div>
            <div class="col-4 p-2">
                <strong>Date:</strong> - {{ $invoiceDate }}
            </div>
        </div>
        
        <!-- GSTIN -->
        <div style="border-bottom: 2px solid #000; padding: 4px 8px; font-size: 10px; font-weight: bold;">
            GSTIN- {{ $partyGst }}
        </div>
        
        <!-- Place of supply & Bill no -->
        <div class="row g-0" style="border-bottom: 2px solid #000; font-size: 10px; font-weight: bold;">
            <div class="col-8 p-2" style="border-right: 2px solid #000;">
                PLACE OF SUPPLY - {{ $partyStateStr }}
            </div>
            <div class="col-4 p-2">
                <div>Bill No: - {{ $billNumber }}</div>
                @if(!empty($existingInvoice?->state_vendor_code))
                <div class="mt-1"><strong>State Vendor Code:</strong> - <span>{{ $existingInvoice->state_vendor_code }}</span></div>
                @endif
                @if(!empty($existingInvoice?->vendor_code) || !empty($vendorCode))
                <div><strong>Vendor Code:</strong> - <span>{{ $vendorCode ?? $existingInvoice->vendor_code }}</span></div>
                @endif
                @if(!empty($existingInvoice?->vendor_name))
                <div><strong>Vendor Name:</strong> - <span>{{ $existingInvoice->vendor_name }}</span></div>
                @endif
                @if(!empty($existingInvoice?->epod_status))
                <div><strong>EPOD Status:</strong> - <span>{{ $existingInvoice->epod_status }}</span></div>
                @endif
            </div>
        </div>
        
        <!-- RCM -->
        <div style="border-bottom: 2px solid #000; padding: 4px 8px; font-size: 10px; font-weight: bold;">
            WHETHER TAX IS PAYABLE UNDER REVERSE CHARGE MECHANISM : - {{ $rcmPayableVal == 1 ? 'YES' : 'NO' }}
        </div>
        
        <!-- Title -->
        <div class="row g-0" style="border-bottom: 2px solid #000; font-weight: bold; font-size: 10px;">
            <div class="col-6 p-2" style="border-right: 2px solid #000;">
                PO NO. - {{ $existingInvoice->mn_number ?? '-' }}
            </div>
            <div class="col-6 p-2 text-end">
                DETAILS: AS PER ANNEXURE ATTACHED
            </div>
        </div>
        
        <div class="text-center" style="border-bottom: 2px solid #000; padding: 4px; font-weight: bold;">
            Transportation Freight Bill
        </div>
        
        <!-- Main Table -->
        <table style="width: 100%; border-collapse: collapse; text-align: center; font-size: 10px; font-weight: bold;">
            <thead>
                <tr style="border-bottom: 2px solid #000;">
                    <th style="border-right: 1px solid #000; padding: 4px;">S.R No</th>
                    <th style="border-right: 1px solid #000; padding: 4px;">Description of Services</th>
                    <th style="border-right: 1px solid #000; padding: 4px;">Quantity</th>
                    <th style="border-right: 1px solid #000; padding: 4px;">Rate</th>
                    <th style="border-right: 1px solid #000; padding: 4px;">UoM</th>
                    <th style="padding: 4px;">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom: 2px solid #000;">
                    <td style="border-right: 1px solid #000; padding: 8px;">1</td>
                    <td style="border-right: 1px solid #000; padding: 8px;">{{ $existingInvoice->description ?? 'TRANSPORTATION OF GYPSUM' }}</td>
                    <td style="border-right: 1px solid #000; padding: 8px;">{{ number_format($totalMt, 3, '.', '') }}</td>
                    <td style="border-right: 1px solid #000; padding: 8px;">{{ number_format($displayRate, 3, '.', '') }}</td>
                    <td style="border-right: 1px solid #000; padding: 8px;"></td>
                    <td style="padding: 8px;">{{ number_format($displayTotalAmount, 2, '.', '') }}</td>
                </tr>
                
                @if($gstType === 'CGST_SGST')
                <tr style="border-bottom: 1px solid #000;">
                    <td colspan="4" style="border-right: 1px solid #000;"></td>
                    <td style="border-right: 1px solid #000; padding: 4px; text-align: left;">C GST {{ $halfGst }}%</td>
                    <td style="padding: 4px;">{{ number_format($halfGstAmount, 4, '.', '') }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #000;">
                    <td colspan="4" style="border-right: 1px solid #000;"></td>
                    <td style="border-right: 1px solid #000; padding: 4px; text-align: left;">S GST {{ $halfGst }}%</td>
                    <td style="padding: 4px;">{{ number_format($halfGstAmount, 4, '.', '') }}</td>
                </tr>
                @else
                <tr style="border-bottom: 1px solid #000;">
                    <td colspan="4" style="border-right: 1px solid #000;"></td>
                    <td style="border-right: 1px solid #000; padding: 4px; text-align: left;">I GST {{ $gstRate }}%</td>
                    <td style="padding: 4px;">{{ number_format($gstAmount, 4, '.', '') }}</td>
                </tr>
                @endif
                <tr style="border-bottom: 2px solid #000;">
                    <td colspan="4" style="border-right: 1px solid #000;"></td>
                    <td style="border-right: 1px solid #000; padding: 4px; text-align: left;">TOTAL GST</td>
                    <td style="padding: 4px;">{{ number_format($gstAmount, 3, '.', '') }}</td>
                </tr>
                
                <tr style="border-bottom: 2px solid #000;">
                    <td colspan="7" style="border-right: 1px solid #000;"></td>
                    <td colspan="2" style="border-right: 1px solid #000; padding: 4px; text-align: center;">GRAND TOTAL</td>
                    <td style="padding: 4px;">{{ number_format($grandTotal, 2, '.', '') }}</td>
                </tr>
            </tbody>
        </table>
        
        <!-- Amount in words -->
        <div style="border-bottom: 2px solid #000; padding: 4px 8px; font-weight: bold; font-size: 10px;">
            AMOUNT IN WORD: {{ $amountInWords ?? ucwords((new NumberFormatter("en_IN", NumberFormatter::SPELLOUT))->format($grandTotal)) . ' Only' }}
        </div>
        
        <!-- Footer Declaration & Bank -->
        <div class="row g-0" style="min-height: 100px;">
            <div class="col-8" style="border-right: 2px solid #000; padding: 0;">
                <div style="padding: 12px 8px; font-weight: bold; font-size: 11px; text-align: center;">
                    Declaration : {{ $companyDeclaration }}
                </div>
                <table style="width: 100%; border-collapse: collapse; text-align: center; font-size: 10px; font-weight: bold; margin-top: auto;">
                    <tr>
                        <td style="border-top: 1px solid #000; border-right: 1px solid #000; padding: 4px;">ACCOUNT NO.</td>
                        <td style="border-top: 1px solid #000; padding: 4px;">{{ $bankAccountNo }}</td>
                    </tr>
                    <tr>
                        <td style="border-top: 1px solid #000; border-right: 1px solid #000; padding: 4px;">IFC CODE</td>
                        <td style="border-top: 1px solid #000; padding: 4px;">{{ $bankIfsc }}</td>
                    </tr>
                    <tr>
                        <td style="border-top: 1px solid #000; border-right: 1px solid #000; padding: 4px; border-bottom: 0;">HOLDER NAME</td>
                        <td style="border-top: 1px solid #000; padding: 4px; border-bottom: 0;">{{ $bankHolder }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-4 d-flex flex-column justify-content-between text-center" style="padding: 0;">
                <div style="padding: 4px; font-weight: bold; border-bottom: 1px solid #000;">For {{ $companyName }}</div>
                <div style="padding: 8px 4px; text-align: center;">
                    @if(!empty($companySignatureUrl))
                        <img src="{{ $companySignatureUrl }}" alt="Signature" style="max-height: 45px; max-width: 140px; object-fit: contain;">
                    @else
                        <div style="height: 45px;"></div>
                    @endif
                </div>
                <div style="padding: 4px; font-weight: bold; border-top: 1px solid #000;">Authorized Signatory</div>
            </div>
        </div>
        
    </div>

    <!-- Page Break -->
    <div id="preview-page-break" style="page-break-before: always; margin: 20px 0;"></div>

    <!-- SHEET 2: SUMMARY SHEET -->
    <div id="summary-preview-sheet" style="border: 2px solid #000; padding: 0; box-sizing: border-box;">
        
        <div class="text-center" style="border-bottom: 2px solid #000; padding: 8px;">
            <h2 class="m-0 fw-bold" style="font-size: 16px; letter-spacing: 1px;">{{ $companyName }} .</h2>
            <div style="font-size: 10px; font-weight: bold;">JODHPUR</div>
            <div style="font-size: 10px; font-weight: bold;">Off Phone No {{ $companyPhone }}</div>
        </div>
        <div class="text-end" style="border-bottom: 2px solid #000; padding: 4px 8px; font-weight: bold;">
            Service Tax Regn No :
        </div>
        <div class="d-flex" style="border-bottom: 2px solid #000; padding: 0; font-weight: bold;">
            <div style="border-right: 2px solid #000; padding: 4px 8px; width: 25%;">Bill No: {{ $mnNumber !== '-' ? $mnNumber : $billNumber }}</div>
            <div style="border-right: 2px solid #000; padding: 4px 8px; width: 60%;">{{ strip_tags($partyName) }}</div>
            <div style="padding: 4px 8px; width: 15%;">Dt: {{ $invoiceDate }}</div>
        </div>
        
        <div class="text-center" style="border-bottom: 1px solid #000; padding: 4px; font-weight: bold;">Summary</div>
        
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 10px; border-bottom: 2px solid #000;">
            <thead>
                <tr style="border-bottom: 1px solid #000;">
                    <th style="padding: 4px; border-right: 1px solid #000;">Charge Type</th>
                    <th style="padding: 4px; border-right: 1px solid #000; text-align: right;">QTY(MT)</th>
                    <th style="padding: 4px; text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding: 4px; border-right: 1px solid #000;">ROAD</td>
                    <td style="padding: 4px; border-right: 1px solid #000; text-align: right;">{{ number_format($totalMt, 3, '.', '') }}</td>
                    <td style="padding: 4px; text-align: right;">{{ number_format($totalAmount, 2, '.', '') }}</td>
                </tr>
                <tr><td colspan="3" style="padding: 8px;"></td></tr>
            </tbody>
        </table>
        
        <!-- Truck Details Table -->
        <table style="width: 100%; border-collapse: collapse; text-align: center; font-size: 9px; font-weight: bold;">
            <thead>
                <tr style="border-bottom: 1px solid #000;">
                    <th style="padding: 4px; border-right: 1px solid #000;">Truck No</th>
                    <th style="padding: 4px; border-right: 1px solid #000;">From Place</th>
                    <th style="padding: 4px; border-right: 1px solid #000;">To Place</th>
                    <th style="padding: 4px; border-right: 1px solid #000;">Consignment No</th>
                    <th style="padding: 4px; border-right: 1px solid #000;">Date</th>
                    <th style="padding: 4px; border-right: 1px solid #000;">Lane</th>
                    <th style="padding: 4px; border-right: 1px solid #000;">QTY(MT)</th>
                    <th style="padding: 4px; border-right: 1px solid #000;">Rate</th>
                    <th style="padding: 4px; border-right: 1px solid #000;">Damage<br>Qty</th>
                    <th style="padding: 4px; border-right: 1px solid #000;">Damage<br>Rate</th>
                    <th style="padding: 4px; border-right: 1px solid #000;">Shortage<br>Qty</th>
                    <th style="padding: 4px; border-right: 1px solid #000;">Shortage<br>Rate</th>
                    <th style="padding: 4px; border-right: 1px solid #000;">Amt.</th>
                    <th style="padding: 4px; border-right: 1px solid #000;">S.Tax</th>
                    <th style="padding: 4px;">S.B.Cess</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bulties as $bulty)
                @php
                    $qty = 0;
                    $damageQty = 0;
                    $shortQty = 0;
                    if ($bulty->bultyDetail) {
                        $qty = floatval($bulty->bultyDetail->billed_qty ?? $bulty->bultyDetail->qty_mt ?? $bulty->bultyDetail->challan_qty ?? 0);
                        $damageQty = floatval($bulty->bultyDetail->damage_qty ?? 0);
                        $shortQty = floatval($bulty->bultyDetail->short_qty ?? 0);
                    }
                    $f = floatval($bulty->freight_charges) - floatval($bulty->advance_amount);
                    $rate = $qty > 0 ? ($f / $qty) : 0;
                    $bultyLrDate = $bulty->lr_date ? $bulty->lr_date->format('d.m.Y') : '-';
                @endphp
                <tr style="border-bottom: 1px solid #000;">
                    <td style="padding: 4px; border-right: 1px solid #000;">{{ $bulty->vehicle ? $bulty->vehicle->vehicle_number : '-' }}</td>
                    <td style="padding: 4px; border-right: 1px solid #000;">{{ $bulty->originCity->name ?? '-' }}</td>
                    <td style="padding: 4px; border-right: 1px solid #000;">{{ $bulty->destinationCity->name ?? '-' }}</td>
                    <td style="padding: 4px; border-right: 1px solid #000;">{{ $bulty->lr_no }}</td>
                    <td style="padding: 4px; border-right: 1px solid #000;">{{ $bultyLrDate }}</td>
                    <td style="padding: 4px; border-right: 1px solid #000;">ROAD</td>
                    <td style="padding: 4px; border-right: 1px solid #000; text-align: right;">{{ number_format($qty, 3, '.', '') }}</td>
                    <td style="padding: 4px; border-right: 1px solid #000; text-align: right;">{{ number_format($rate, 2, '.', ',') }}</td>
                    <td style="padding: 4px; border-right: 1px solid #000; text-align: right;">{{ number_format($damageQty, 3, '.', '') }}</td>
                    <td style="padding: 4px; border-right: 1px solid #000; text-align: right;">{{ number_format(floatval($bulty->damage_amount), 2, '.', '') }}</td>
                    <td style="padding: 4px; border-right: 1px solid #000; text-align: right;">{{ number_format($shortQty, 3, '.', '') }}</td>
                    <td style="padding: 4px; border-right: 1px solid #000; text-align: right;">{{ number_format(floatval($bulty->shortage_amount), 2, '.', '') }}</td>
                    <td style="padding: 4px; border-right: 1px solid #000; text-align: right;">{{ number_format($f, 2, '.', ',') }}</td>
                    <td style="padding: 4px; border-right: 1px solid #000; text-align: right;">0.00</td>
                    <td style="padding: 4px; text-align: right;">0.00</td>
                </tr>
                @endforeach
                
                <!-- Totals footer -->
                <tr style="border-bottom: 1px solid #000;">
                    <td colspan="12" style="padding: 4px; border-right: 1px solid #000; text-align: right;">Gross Total:</td>
                    <td colspan="3" style="padding: 4px; text-align: right; border-bottom: 1px solid #000;">{{ number_format($totalAmount, 2, '.', ',') }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #000;">
                    <td colspan="12" style="padding: 4px; border-right: 1px solid #000; text-align: right;">Less :Shortage</td>
                    <td colspan="3" style="padding: 4px; text-align: right; border-bottom: 1px solid #000;">{{ number_format($totalShortageAmount, 2, '.', ',') }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #000;">
                    <td colspan="12" style="padding: 4px; border-right: 1px solid #000; text-align: right;">Less :Damage</td>
                    <td colspan="3" style="padding: 4px; text-align: right; border-bottom: 1px solid #000;">{{ number_format($totalDamageAmount, 2, '.', ',') }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #000;">
                    <td colspan="12" style="padding: 4px; border-right: 1px solid #000; text-align: right;">Net Total</td>
                    <td colspan="3" style="padding: 4px; text-align: right; border-bottom: 1px solid #000;">{{ number_format($netTotal, 2, '.', ',') }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #000;">
                    <td colspan="12" style="padding: 4px; border-right: 1px solid #000; text-align: right;">Service Tax</td>
                    <td colspan="3" style="padding: 4px; text-align: right; border-bottom: 1px solid #000;">0.00</td>
                </tr>
                <tr style="border-bottom: 1px solid #000;">
                    <td colspan="12" style="padding: 4px; border-right: 1px solid #000; text-align: right;">Swachh Bharat cess</td>
                    <td colspan="3" style="padding: 4px; text-align: right; border-bottom: 1px solid #000;">0.00</td>
                </tr>
                <tr style="border-bottom: 1px solid #000;">
                    <td colspan="12" style="padding: 4px; border-right: 1px solid #000; text-align: right;">Total Value :</td>
                    <td colspan="3" style="padding: 4px; text-align: right;">{{ number_format($netTotal, 2, '.', ',') }}</td>
                </tr>
            </tbody>
        </table>
        
        <div style="padding: 4px 8px; font-size: 10px;">
            In words : {{ $amountInWords ?? ucwords((new NumberFormatter("en_IN", NumberFormatter::SPELLOUT))->format($netTotal)) . ' Only' }}
        </div>

    </div>

</div>
