<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>

@php
    $bulties = collect();
    if(isset($invoice->bulties)) {
        $bulties = $invoice->bulties;
    }
    
    $compName = $invoice->company ? $invoice->company->name : '';
    $compAdd = $invoice->company ? $invoice->company->address : '';
    $compGst = $invoice->company ? $invoice->company->gst_number : '';
    $compPan = $invoice->company && $invoice->company->pan_number ? $invoice->company->pan_number : '';
    $compPh = $invoice->company ? $invoice->company->phone : '';
    $compOwner = $invoice->company && $invoice->company->owner_name ? strtoupper($invoice->company->owner_name) : '';
    $bankAccountNo = $invoice->company && $invoice->company->bank_account_no ? $invoice->company->bank_account_no : '';
    $bankIfsc = $invoice->company && $invoice->company->bank_ifsc ? $invoice->company->bank_ifsc : '';
    $bankHolder = $invoice->company && $invoice->company->bank_holder_name ? strtoupper($invoice->company->bank_holder_name) : '';

    $comp = $invoice->company;
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

<table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 11px; background-color: #ffffff;">
    <tr>
        <td colspan="5" style="border: 2px solid #000; border-right: none; font-weight: bold; padding: 4px;">GSTN: {{ $compGst }}</td>
        <td colspan="5" style="border: 2px solid #000; border-left: none; font-weight: bold; text-align: right; padding: 4px;">PAN: {{ $compPan }} <br/> {{ $compOwner }} {{ $compPh }}</td>
    </tr>
    <tr>
        <td colspan="10" style="border: 2px solid #000; text-align: center; padding: 8px;">
            <h2 style="margin: 0; font-size: 22px; font-weight: bold; text-transform: uppercase;">{{ $compName }}</h2>
            <div style="font-size: 11px; margin-top: 4px; font-weight: bold;">{{ $compAdd }}</div>
        </td>
    </tr>
    <tr>
        <td colspan="6" style="border: 2px solid #000; padding: 4px; vertical-align: top; text-transform: uppercase;">
            {!! $partyAddress !!}
        </td>
        <td colspan="4" style="border: 2px solid #000; padding: 4px; vertical-align: top;">
            <strong>HSN/SAC CODE:</strong> {{ $partyHsn }}<br>
            <strong>Date:</strong> - {{ $billDate }}<br>
            <strong>Bill No:</strong> - {{ $billNo }}
            @if(!empty($invoice->state_vendor_code))
            <br><strong>State Vendor Code:</strong> - {{ $invoice->state_vendor_code }}
            @endif
            @if(!empty($invoice->vendor_code))
            <br><strong>Vendor Code:</strong> - {{ $invoice->vendor_code }}
            @endif
            @if(!empty($invoice->vendor_name))
            <br><strong>Vendor Name:</strong> - {{ $invoice->vendor_name }}
            @endif
            @if(!empty($invoice->epod_status))
            <br><strong>EPOD Status:</strong> - {{ $invoice->epod_status }}
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="10" style="border: 2px solid #000; font-weight: bold; text-transform: uppercase; padding: 4px;">WHETHER TAX IS PAYABLE UNDER REVERSE CHARGE MECHANISM : - {{ $rcmPayableVal == 1 ? 'YES' : 'NO' }}</td>
    </tr>
    <tr>
        <td colspan="5" style="border: 2px solid #000; font-weight: bold; padding: 4px;">PO NO. - {{ $invoice->mn_number }}</td>
        <td colspan="5" style="border: 2px solid #000; font-weight: bold; text-align: right; padding: 4px;">DETAILS: AS PER ANNEXURE ATTACHED</td>
    </tr>
    <tr>
        <td colspan="10" style="border: 2px solid #000; text-align: center; font-weight: bold; padding: 4px;">Transportation Freight Bill</td>
    </tr>
    <tr>
        <td style="border: 2px solid #000; font-weight: bold; padding: 4px; text-align: center;">S.R No</td>
        <td style="border: 2px solid #000; font-weight: bold; padding: 4px; text-align: center;">Description of Services</td>
        <td style="border: 2px solid #000; font-weight: bold; padding: 4px; text-align: center;">Quantity</td>
        <td style="border: 2px solid #000; font-weight: bold; padding: 4px; text-align: center;">Rate</td>
        <td style="border: 2px solid #000; font-weight: bold; padding: 4px; text-align: center;">UoM</td>
        <td style="border: 2px solid #000; font-weight: bold; padding: 4px; text-align: center;">Total Amount</td>
    </tr>
    <tr>
        <td style="border: 2px solid #000; text-align: center; padding: 4px; font-weight: bold;">1</td>
        <td style="border: 2px solid #000; text-align: center; padding: 4px; font-weight: bold;">{{ $invoice->description ?? 'TRANSPORTATION OF GYPSUM' }}</td>
        <td style="border: 2px solid #000; text-align: center; padding: 4px; font-weight: bold;">{{ $displayMt }}</td>
        <td style="border: 2px solid #000; text-align: center; padding: 4px; font-weight: bold;">{{ number_format($displayRate, 3, '.', '') }}</td>
        <td style="border: 2px solid #000; text-align: center; padding: 4px; font-weight: bold;"></td>
        <td style="border: 2px solid #000; text-align: center; padding: 4px; font-weight: bold;">{{ number_format($displayTotalAmt, 2, '.', '') }}</td>
    </tr>
    @if($gstType === 'IGST')
    <tr>
        <td colspan="4" style="border-right: 2px solid #000;"></td>
        <td style="border: 2px solid #000; padding: 4px; text-align: center; font-weight: bold;">I GST {{ $gstRate }}%</td>
        <td style="border: 2px solid #000; padding: 4px; font-weight: bold; text-align: center;">{{ number_format($igst, 4, '.', '') }}</td>
    </tr>
    @else
    <tr>
        <td colspan="4" style="border-right: 2px solid #000;"></td>
        <td style="border: 2px solid #000; padding: 4px; text-align: right; font-weight: bold;">C GST {{ $halfGst }}%</td>
        <td style="border: 2px solid #000; padding: 4px; font-weight: bold; text-align: center;">{{ number_format($cgst, 4, '.', '') }}</td>
    </tr>
    <tr>
        <td colspan="4" style="border-right: 2px solid #000;"></td>
        <td style="border: 2px solid #000; padding: 4px; text-align: right; font-weight: bold;">S GST {{ $halfGst }}%</td>
        <td style="border: 2px solid #000; padding: 4px; font-weight: bold; text-align: center;">{{ number_format($sgst, 4, '.', '') }}</td>
    </tr>
    @endif
    <tr>
        <td colspan="4" style="border-right: 2px solid #000;"></td>
        <td style="border: 2px solid #000; padding: 4px; text-align: center; font-weight: bold;">TOTAL GST</td>
        <td style="border: 2px solid #000; padding: 4px; font-weight: bold; text-align: center;">{{ number_format($totalGst, 3, '.', '') }}</td>
    </tr>
    <tr>
        <td colspan="4" style="border-right: 2px solid #000;"></td>
        <td style="border: 2px solid #000; padding: 4px; text-align: center; font-weight: bold;">GRAND TOTAL</td>
        <td style="border: 2px solid #000; padding: 4px; font-weight: bold; text-align: center;">{{ number_format($grandTotal, 2, '.', '') }}</td>
    </tr>
    <tr>
        <td colspan="10" style="border: 2px solid #000; padding: 4px; font-weight: bold;">
            AMOUNT IN WORD: {{ \App\Http\Controllers\Admin\Transport\BillingController::convertNumberToWords($grandTotal) }}
        </td>
    </tr>
    <tr>
        <td colspan="5" style="border: 2px solid #000; padding: 4px; vertical-align: top;">
            <div style="font-weight: bold; text-align: center; margin-bottom: 10px;">Declaration : {{ $compDeclaration }}</div>
            <br/>
            <table style="width: 100%; border-collapse: collapse;">
                <tr><td style="border: 2px solid #000; font-weight: bold; padding: 2px;">ACCOUNT NO.</td><td style="border: 2px solid #000; font-weight: bold; text-align: center; padding: 2px;">{{ $bankAccountNo }}</td></tr>
                <tr><td style="border: 2px solid #000; font-weight: bold; padding: 2px;">IFC CODE</td><td style="border: 2px solid #000; font-weight: bold; text-align: center; padding: 2px;">{{ $bankIfsc }}</td></tr>
                <tr><td style="border: 2px solid #000; font-weight: bold; padding: 2px;">HOLDER NAME</td><td style="border: 2px solid #000; font-weight: bold; text-align: center; padding: 2px;">{{ $bankHolder }}</td></tr>
            </table>
        </td>
        <td colspan="5" style="border: 2px solid #000; padding: 4px; text-align: right; vertical-align: top;">
            <div style="font-weight: bold;">For {{ $compName }}</div>
            <br/>
            @if(!empty($compOwner))
                <div style="font-size: 8px; color: #333; font-weight: bold; line-height: 1.2;">Digitally signed by {{ $compOwner }}</div>
            @endif
            <div style="font-size: 8px; color: #555; line-height: 1.2;">Date: {{ date('d-m-Y H:i:s') }}</div>
            <br/>
            <div style="font-weight: bold;">Authorized Signatory</div>
        </td>
    </tr>
</table>

<br/><br/>

<table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 9px; background-color: #ffffff;">
    <tr>
        <td colspan="18" style="border: 2px solid #000; text-align: center; padding: 4px;">
            <h3 style="margin: 0; font-weight: bold; text-transform: uppercase;">{{ $compName }} GRN DETAILS</h3>
        </td>
    </tr>
    <tr style="background-color: #ffff00;">
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">Posting Date</td>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">PO No</td>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">Mat Doc</td>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">Gate Entry No</td>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">Gate Out Date</td>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">Supplier</td>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">Supplier Name</td>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">Vehicle No</td>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">Challan No</td>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">Challan Date</td>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">LR No</td>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">Transporter</td>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">Transporter Name</td>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">PO Item</td>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">Material</td>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">Material Name</td>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">Challan Qty</td>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">Final Wgt</td>
    </tr>
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
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">{{ $postingDate }}</td>
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">{{ $poNo }}</td>
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">{{ $matDoc }}</td>
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">{{ $gateEntryNo }}</td>
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">{{ $gateOutDate }}</td>
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">{{ $supplier }}</td>
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">{{ $supplierName }}</td>
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">{{ $vehicleNo }}</td>
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">{{ $challanNo }}</td>
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">{{ $challanDate }}</td>
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">{{ $lrNo }}</td>
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">{{ $transporter }}</td>
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">{{ $transporterName }}</td>
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">{{ $poItem }}</td>
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">{{ $material }}</td>
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">{{ $materialName }}</td>
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">{{ number_format($challanQty, 2, '.', '') }}</td>
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">{{ number_format($finalWgt, 2, '.', '') }}</td>
        </tr>
    @endforeach
    <tr>
        <td colspan="16" style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 4px; font-size: 11px;">TOTAL</td>
        <td style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 4px; font-size: 11px;">{{ number_format($totalChallanQty, 3, '.', '') }}</td>
        <td style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 4px; font-size: 11px;">{{ number_format($totalFinalWgt, 3, '.', '') }}</td>
    </tr>
</table>

</body>
</html>