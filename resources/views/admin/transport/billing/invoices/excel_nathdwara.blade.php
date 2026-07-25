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
        <td colspan="10" style="border: 2px solid #000; text-align: center; font-weight: bold; padding: 4px;">Transportation Freight Bill</td>
    </tr>
    <tr>
        <td style="border: 2px solid #000; font-weight: bold; padding: 4px; text-align: center;">SR NO</td>
        <td style="border: 2px solid #000; font-weight: bold; padding: 4px; text-align: center;">DESCRIPTION</td>
        <td style="border: 2px solid #000; font-weight: bold; padding: 4px; text-align: center;">BILL NO</td>
        <td style="border: 2px solid #000; font-weight: bold; padding: 4px; text-align: center;">MN NO</td>
        <td style="border: 2px solid #000; font-weight: bold; padding: 4px; text-align: center;">NUMBER OF DI</td>
        <td style="border: 2px solid #000; font-weight: bold; padding: 4px; text-align: center;">MT</td>
        <td style="border: 2px solid #000; font-weight: bold; padding: 4px; text-align: center;">BILLING AMOUNT</td>
        <td style="border: 2px solid #000; font-weight: bold; padding: 4px; text-align: center;">LESS SHORTAGE</td>
        <td style="border: 2px solid #000; font-weight: bold; padding: 4px; text-align: center;">LESS DAMAGE</td>
        <td style="border: 2px solid #000; font-weight: bold; padding: 4px; text-align: center;">NET TOTAL</td>
    </tr>
    <tr>
        <td style="border: 2px solid #000; text-align: center; padding: 4px; font-weight: bold;">1</td>
        <td style="border: 2px solid #000; text-align: center; padding: 4px; font-weight: bold;">{{ $desc }}</td>
        <td style="border: 2px solid #000; text-align: center; padding: 4px; font-weight: bold;">{{ $billNo }}</td>
        <td style="border: 2px solid #000; text-align: center; padding: 4px; font-weight: bold;">{{ $mnNo }}</td>
        <td style="border: 2px solid #000; text-align: center; padding: 4px; font-weight: bold;">{{ $bulties->count() }}</td>
        <td style="border: 2px solid #000; text-align: center; padding: 4px; font-weight: bold;">{{ $displayMt }}</td>
        <td style="border: 2px solid #000; text-align: center; padding: 4px; font-weight: bold;">{{ number_format($totalAmt, 2, '.', '') }}</td>
        <td style="border: 2px solid #000; text-align: center; padding: 4px; font-weight: bold;">{{ number_format($totalShortageAmt, 2, '.', '') }}</td>
        <td style="border: 2px solid #000; text-align: center; padding: 4px; font-weight: bold;">{{ number_format($totalDamageAmt, 2, '.', '') }}</td>
        <td style="border: 2px solid #000; text-align: center; padding: 4px; font-weight: bold;">{{ number_format($netTotalS2, 2, '.', '') }}</td>
    </tr>
    @if($gstType === 'IGST')
    <tr>
        <td colspan="7" style="border-right: 2px solid #000;"></td>
        <td colspan="2" style="border: 2px solid #000; padding: 4px; text-align: center; font-weight: bold;">I GST {{ $gstRate }}%</td>
        <td style="border: 2px solid #000; padding: 4px; font-weight: bold; text-align: center;">{{ number_format($igst, 4, '.', '') }}</td>
    </tr>
    @else
    <tr>
        <td colspan="7" style="border-right: 2px solid #000;"></td>
        <td colspan="2" style="border: 2px solid #000; padding: 4px; text-align: right; font-weight: bold;">C GST {{ $halfGst }}%</td>
        <td style="border: 2px solid #000; padding: 4px; font-weight: bold; text-align: center;">{{ number_format($cgst, 4, '.', '') }}</td>
    </tr>
    <tr>
        <td colspan="7" style="border-right: 2px solid #000;"></td>
        <td colspan="2" style="border: 2px solid #000; padding: 4px; text-align: right; font-weight: bold;">S GST {{ $halfGst }}%</td>
        <td style="border: 2px solid #000; padding: 4px; font-weight: bold; text-align: center;">{{ number_format($sgst, 4, '.', '') }}</td>
    </tr>
    @endif
    <tr>
        <td colspan="7" style="border-right: 2px solid #000;"></td>
        <td colspan="2" style="border: 2px solid #000; padding: 4px; text-align: center; font-weight: bold;">TOTAL GST</td>
        <td style="border: 2px solid #000; padding: 4px; font-weight: bold; text-align: center;">{{ number_format($totalGst, 3, '.', '') }}</td>
    </tr>
    <tr>
        <td colspan="7" style="border-right: 2px solid #000;"></td>
        <td colspan="2" style="border: 2px solid #000; padding: 4px; text-align: center; font-weight: bold;">GRAND TOTAL</td>
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
            <br/><br/><br/>
            <div style="font-weight: bold;">Authorized Signatory</div>
        </td>
    </tr>
</table>

<br/><br/>

<table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 10px; background-color: #ffffff;">
    <tr>
        <td colspan="15" style="border: 2px solid #000; text-align: center; padding: 4px;">
            <h3 style="margin: 0; font-weight: bold; text-transform: uppercase;">{{ $compName }}.</h3>
            <div style="font-weight: bold; font-size: 11px;">Off Phone No {{ $compPh }}, Mobile No Res.Tel</div>
        </td>
    </tr>
    <tr>
        <td colspan="15" style="border: 2px solid #000; text-align: right; font-weight: bold; padding: 4px;">
            Service Tax Regn No : 
        </td>
    </tr>
    <tr>
        <td colspan="4" style="border: 2px solid #000; font-weight: bold; padding: 4px;">Bill No: {{ $billNo }}</td>
        <td colspan="7" style="border: 2px solid #000; text-align: center; font-weight: bold; padding: 4px;">ULTRATECH CEMENT LIMITED UNIT: BIRLA WHITE Rajashree Nagar Vill. Kharia Khangar-342606</td>
        <td colspan="4" style="border: 2px solid #000; font-weight: bold; padding: 4px;">Dt: {{ $billDate }}</td>
    </tr>
    <tr>
        <td colspan="15" style="border: 2px solid #000; text-align: center; font-weight: bold; padding: 4px;">Summary</td>
    </tr>
    <tr>
        <td colspan="5" style="border: 2px solid #000; font-weight: bold; padding: 4px;">Charge Type</td>
        <td colspan="5" style="border: 2px solid #000; text-align: center; font-weight: bold; padding: 4px;">QTY(MT)</td>
        <td colspan="5" style="border: 2px solid #000; text-align: center; font-weight: bold; padding: 4px;">Amount</td>
    </tr>
    <tr>
        <td colspan="5" style="border: 2px solid #000; padding: 4px;">ROAD</td>
        <td colspan="5" style="border: 2px solid #000; text-align: center; padding: 4px;">{{ number_format($totalQty, 3, '.', '') }}</td>
        <td colspan="5" style="border: 2px solid #000; text-align: center; padding: 4px;">{{ number_format($totalAmt, 2, '.', '') }}</td>
    </tr>
    
    <tr>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">Truck No</td>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">From Place</td>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">To Place</td>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">Consignment No</td>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">Date</td>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">Lane</td>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">QTY(MT)</td>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">Rate</td>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">Damage Qty</td>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">Damage Rate</td>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">Shortage Qty</td>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">Shortage Rate</td>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">Amt.</td>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">S.Tax</td>
        <td style="border: 1px solid #000; font-weight: bold; padding: 2px; text-align: center;">S.B.Cess</td>
    </tr>
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
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">{{ $bulty->vehicle ? $bulty->vehicle->vehicle_number : '-' }}</td>
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">{{ $fromCity }}</td>
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">{{ $toCity }}</td>
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">{{ $bulty->lr_no }}</td>
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">{{ $formattedLrDate }}</td>
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">ROAD</td>
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">{{ number_format($qtyMt, 3, '.', '') }}</td>
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">{{ number_format($rateMt, 2, '.', '') }}</td>
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">{{ number_format($damageQty, 3, '.', '') }}</td>
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">{{ $damageQty > 0 ? number_format($damageAmt/$damageQty, 2, '.', '') : '0.00' }}</td>
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">{{ number_format($shortageQty, 3, '.', '') }}</td>
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">{{ $shortageQty > 0 ? number_format($shortageAmt/$shortageQty, 2, '.', '') : '0.00' }}</td>
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">{{ number_format($amt, 2, '.', '') }}</td>
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">0.00</td>
            <td style="border: 1px solid #000; padding: 2px; text-align: center;">0.00</td>
        </tr>
    @endforeach
    
    <tr>
        <td colspan="12" style="border: 1px solid #000; text-align: right; font-weight: bold; padding: 2px;">Gross Total:</td>
        <td style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 2px;">{{ number_format($totalAmt, 2, '.', '') }}</td>
        <td colspan="2" style="border: 1px solid #000;"></td>
    </tr>
    <tr>
        <td colspan="12" style="border: 1px solid #000; text-align: right; font-weight: bold; padding: 2px;">Less :Shortage</td>
        <td style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 2px;">{{ number_format($totalShortageAmt, 2, '.', '') }}</td>
        <td colspan="2" style="border: 1px solid #000;"></td>
    </tr>
    <tr>
        <td colspan="12" style="border: 1px solid #000; text-align: right; font-weight: bold; padding: 2px;">Less: Damage</td>
        <td style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 2px;">{{ number_format($totalDamageAmt, 2, '.', '') }}</td>
        <td colspan="2" style="border: 1px solid #000;"></td>
    </tr>
    <tr>
        <td colspan="12" style="border: 1px solid #000; text-align: right; font-weight: bold; padding: 2px;">Net Total</td>
        <td style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 2px;">{{ number_format($netTotalS2, 2, '.', '') }}</td>
        <td colspan="2" style="border: 1px solid #000;"></td>
    </tr>
    <tr>
        <td colspan="12" style="border: 1px solid #000; text-align: right; font-weight: bold; padding: 2px;">Service Tax</td>
        <td style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 2px;">0.00</td>
        <td colspan="2" style="border: 1px solid #000;"></td>
    </tr>
    <tr>
        <td colspan="12" style="border: 1px solid #000; text-align: right; font-weight: bold; padding: 2px;">Swachh Bharat cess</td>
        <td style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 2px;">0.00</td>
        <td colspan="2" style="border: 1px solid #000;"></td>
    </tr>
    <tr>
        <td colspan="12" style="border: 1px solid #000; text-align: right; font-weight: bold; padding: 2px;">Total Value :</td>
        <td style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 2px;">{{ number_format($netTotalS2, 2, '.', '') }}</td>
        <td colspan="2" style="border: 1px solid #000;"></td>
    </tr>
    <tr>
        <td colspan="15" style="border: 1px solid #000; padding: 4px;">
            <div>In words : {{ \App\Http\Controllers\Admin\Transport\BillingController::convertNumberToWords($netTotalS2) }}</div>
            <br/>
            <div style="font-weight: bold; text-align: center;">DECLARATION</div>
            <div>We hereby declare that we have not availed GST credit paid on input or capital goods used for providing services under GST rules 2017.GST service file under RCM & GST liabilities is of service recipient.</div>
            <br/><br/>
            <div style="text-align: right; font-weight: bold;">For {{ $compName }}</div>
            <br/><br/>
            <div style="text-align: right;">Authorized Signatory</div>
        </td>
    </tr>
</table>

</body>
</html>
