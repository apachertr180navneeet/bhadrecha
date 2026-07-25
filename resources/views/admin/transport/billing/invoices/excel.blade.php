<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
@php
    $existingInvoice = $invoice;
    $bulties = $invoice->bulties;
    $invoiceCompany = $invoice->company;
@endphp
@php
    $visibleFields = $existingInvoice?->visible_fields ?? [];
    $isMaiharUnloading = ($existingInvoice?->template_type === 'maihar_unloading');

    $freightFields = $visibleFields;
    $grnFields = [];
    if ($existingInvoice?->grn_new_page ?? false) {
        $grnFields = $existingInvoice?->grn_fields ?? [];
    }

    $fieldLabels = [
        'lr_no' => 'LR NO.',
        'lr_date' => 'DISP. DATE',
        'from_city' => 'FROM',
        'to_city' => 'DESTINATION',
        'consignee_id' => 'CONSIGNEE',
        'vehicle_id' => 'VEHICLE NO.',
        'driver_id' => 'DRIVER',
        'payment_type' => 'PAYMENT TYPE',
        'gst_type' => 'GST TYPE',
        'declared_value' => 'DEC. VALUE',
        'freight_charges' => 'FREIGHT',
        'gst_amount' => 'GST AMOUNT',
        'other_charges' => 'OTHER CHARGES',
        'total_amount' => 'TOTAL AMOUNT',
        'advance_amount' => 'ADVANCE',
        'remaining_amount' => 'REMAINING',
        'bilty_commission' => 'COMMISSION',
        'order_number' => 'ORDER NO.',
        'delivery_number' => 'DILIVERY NO.',
        'invoice_number' => 'INVOICE NO.',
        'invoice_date' => 'INVOICE DATE',
        'eway_bill_no' => 'E-WAY BILL NO.',
        'generation_date' => 'GEN DATE',
        'expiry_date' => 'EXP DATE',
        'mode' => 'MODE',
        'damage_amount' => 'DAMAGE AMOUNT',
        'shortage_amount' => 'SHORTAGE AMOUNT',
        'e_lr_no' => 'E-LR NO.',
        'ul_date' => 'U/L DATE',
        'ul_rate' => 'U/L RATE',
        'ul_amount' => 'UL AMOUNT',
        'bag_ld' => 'BAG LOAD',
        'bag_ul' => 'BAG UNLOAD',
        'bag_short' => 'BAG SHORT',
        'rate_mt' => 'RATE M/T',
        'qty_mt' => 'QTY/MT',
        'description_services' => 'DESCRIPTION OF SERVICES',
        'posting_date' => 'POSTING DATE',
        'po_no' => 'PO NO.',
        'po_item' => 'PO ITEM',
        'mat_doc' => 'MAT DOC',
        'gate_entry_no' => 'GATE ENTRY NO.',
        'challan_no' => 'CHALAAN NO.',
        'challan_date' => 'CHALLAN DATE',
        'transporter_code' => 'TRANSPORTER CODE',
        'transporter_name' => 'TRANSPOSTER NAME',
        'gate_out_date' => 'GATE OUT DATE',
        'invoice_doc' => 'INVOICE DOC',
        'bilty_detail_invoice_date' => 'INVOICE DATE',
        'invoice_time' => 'INVOICE TIME',
        'grn_no' => 'GRN NO. (RECD. QTY)',
        'grn_date' => 'GRN DATE (RECD QTY)',
        'grn_time' => 'GRN TIME (RECD QTY)',
        'recd_qty' => 'RECD. QTY',
        'arrival_time' => 'ARRIVAL TIME',
        'shortage_grn_no' => 'GRN NO. (SHORTAGE)',
        'shortage_grn_date' => 'GRN DATE (SHORTAGE)',
        'short_qty' => 'SHORT QTY',
        'item_name' => 'ITEM NAME',
        'packaging_type' => 'PACKAGING TYPE',
        'articles' => 'ARTICLES',
        'weight' => 'WEIGHT',
        'unit' => 'UNIT',
        'freight_per_mt' => 'FREIGHT/MT',
        'freight_per_kg' => 'FREIGHT/MT',
        'item_amount' => 'ITEM AMOUNT',
        'pod_file' => 'POD FILE',
        'bill_no' => 'BILL NO.',
        'supplier_no' => 'SUPPLIER NO.',
        'material_name' => 'MATERIAL NAME',
        'material_no' => 'MATERIAL NO.',
        'depot_name' => 'DEPOT NAME',
        'billed_qty' => 'BILLED QTY',
        'mn_no' => 'MN NO',
        'no_of_lr' => 'NO. OF DI'
    ];

    $getFieldValuePhp = function($bulty, $fieldKey) use ($existingInvoice) {
        if (!$bulty) return '-';
        
        $detailFields = [
            'posting_date', 'po_no', 'po_item', 'mat_doc', 'gate_entry_no', 'challan_no', 'challan_date',
            'transporter_code', 'transporter_name', 'gate_out_date', 'invoice_doc', 'bilty_detail_invoice_date',
            'invoice_time', 'grn_no', 'grn_date', 'grn_time', 'recd_qty', 'arrival_time', 'shortage_grn_no',
            'shortage_grn_date', 'short_qty', 'ul_date', 'ul_rate', 'ul_amount', 'bag_ld', 'bag_ul', 'bag_short',
            'rate_mt', 'qty_mt', 'description_services', 'challan_qty', 'final_wgt', 'supplier_id',
            'bill_no', 'supplier_no', 'material_name', 'material_no', 'depot_name', 'billed_qty'
        ];
        
        $itemFields = [
            'item_name', 'packaging_type', 'articles', 'weight', 'unit', 'freight_per_mt', 'freight_per_kg', 'item_amount', 'pod_file'
        ];
        
        if (in_array($fieldKey, $detailFields)) {
            $detail = $bulty->bultyDetail;
            if (!$detail) return '-';
            
            if ($fieldKey === 'supplier_id') {
                return $detail->supplier ? $detail->supplier->name : ($detail->supplier_id ?? '-');
            }
            
            if ($fieldKey === 'ul_amount') {
                $weight = $bulty->bultyItems ? floatval($bulty->bultyItems->sum('weight')) : 0;
                $ulRate = floatval($detail->ul_rate ?? 0);
                return number_format($weight * $ulRate, 2, '.', '');
            }

            if ($fieldKey === 'qty_mt') {
                return $detail->qty_mt ?? '-';
            }
            
            $valKey = $fieldKey;
            if ($fieldKey === 'bilty_detail_invoice_date') $valKey = 'invoice_date';
            
            $val = $detail->{$valKey};
            if ($val === null || $val === '') return '-';
            
            if (str_contains($fieldKey, 'date') && $val) {
                try {
                    return date('d-m-Y', strtotime($val));
                } catch (\Exception $e) {
                    return $val;
                }
            }
            return $val;
        }
        
        if (in_array($fieldKey, $itemFields)) {
            $items = $bulty->bultyItems ?? collect();
            if ($items->isEmpty()) return '-';
            if ($fieldKey === 'item_name') {
                $names = $items->map(function($it) {
                    return $it->item ? $it->item->name : ($it->item_name ?? '');
                })->filter()->implode(', ');
                return $names ?: '-';
            }
            if ($fieldKey === 'packaging_type') {
                $types = $items->map(function($it) {
                    return $it->packaging ? $it->packaging->name : ($it->packaging_type ?? '');
                })->filter()->implode(', ');
                return $types ?: '-';
            }
            if ($fieldKey === 'unit') {
                $units = $items->map(function($it) {
                    return $it->unit ? $it->unit->name : ($it->unit ?? '');
                })->filter()->implode(', ');
                return $units ?: '-';
            }
            if ($fieldKey === 'pod_file') {
                return $items->contains(function($it) { return !empty($it->pod_file); }) ? 'Yes' : 'No';
            }
            if ($fieldKey === 'item_amount') {
                $sum = floatval($items->sum('amount')) - floatval($bulty->advance_amount);
                return $sum > 0 ? $sum : '-';
            }
            if ($fieldKey === 'weight') {
                $qtyMt = floatval($detail->qty_mt ?? 0);
                if ($qtyMt > 0) return $qtyMt;
                return $items->pluck('weight')->map(fn($w) => (float)$w)->sum() ?: '-';
            }
            if ($fieldKey === 'freight_per_mt' || $fieldKey === 'freight_per_kg') {
                $first = $items->first(function($it) { return $it->freight_per_mt !== null && $it->freight_per_mt !== ''; });
                return $first ? $first->freight_per_mt : '-';
            }
            
            $vals = $items->map(function($it) use ($fieldKey) {
                return $it->{$fieldKey};
            })->filter(function($val) {
                return $val !== null && $val !== '';
            })->implode(', ');
            
            return $vals ?: '-';
        }
        
        if ($fieldKey === 'from_city') return $bulty->originCity ? $bulty->originCity->name : '-';
        if ($fieldKey === 'to_city') return $bulty->destinationCity ? $bulty->destinationCity->name : '-';
        if ($fieldKey === 'consignee_id') return $bulty->consignee ? $bulty->consignee->name : '-';
        if ($fieldKey === 'vehicle_id') return $bulty->vehicle ? $bulty->vehicle->vehicle_number : '-';
        if ($fieldKey === 'driver_id') return $bulty->driver ? $bulty->driver->name : '-';
        
        if ($fieldKey === 'lr_date') {
            return $bulty->lr_date ? $bulty->lr_date->format('d-m-Y') : '-';
        }
        
        if ($fieldKey === 'bill_no') return $bulty->invoice_number ?? '-';
        if ($fieldKey === 'mn_no') return $bulty->invoice ? $bulty->invoice->mn_number : '-';
        if ($fieldKey === 'no_of_lr') return $existingInvoice ? $existingInvoice->no_of_lrs : $bulties->count();
        
        $isMaiharUnloading = ($existingInvoice?->template_type === 'maihar_unloading');

        if ($fieldKey === 'freight_charges') {
            if ($isMaiharUnloading) {
                $weight = $bulty->bultyItems ? floatval($bulty->bultyItems->sum('weight')) : 0;
                $ulRate = $bulty->bultyDetail ? floatval($bulty->bultyDetail->ul_rate) : 0;
                $freight = $weight * $ulRate;
            } else {
                $freight = floatval($bulty->freight_charges) - floatval($bulty->advance_amount);
            }

            $other = floatval($bulty->other_charges ?? 0);
            $damage = floatval($bulty->damage_amount ?? 0);
            $shortage = floatval($bulty->shortage_amount ?? 0);

            return $freight + $other - $damage - $shortage;
        }
        if ($fieldKey === 'gst_amount') {
            if ($isMaiharUnloading) {
                $weight = $bulty->bultyItems ? floatval($bulty->bultyItems->sum('weight')) : 0;
                $ulRate = $bulty->bultyDetail ? floatval($bulty->bultyDetail->ul_rate) : 0;
                $f = $weight * $ulRate;
            } else {
                $f = floatval($bulty->freight_charges) - floatval($bulty->advance_amount);
            }
            $gstRate = $existingInvoice?->gstMaster ? floatval($existingInvoice->gstMaster->percentage) : null;
            return $gstRate !== null ? ($f * ($gstRate / 100)) : floatval($bulty->gst_amount);
        }
        if ($fieldKey === 'total_amount') {
            if ($isMaiharUnloading) {
                $weight = $bulty->bultyItems ? floatval($bulty->bultyItems->sum('weight')) : 0;
                $ulRate = $bulty->bultyDetail ? floatval($bulty->bultyDetail->ul_rate) : 0;
                $freight = $weight * $ulRate;
            } else {
                $freight = floatval($bulty->freight_charges) - floatval($bulty->advance_amount);
            }

            $other = floatval($bulty->other_charges ?? 0);
            $damage = floatval($bulty->damage_amount ?? 0);
            $shortage = floatval($bulty->shortage_amount ?? 0);

            return $freight + $other - $damage - $shortage;
        }
        
        return $bulty->{$fieldKey} ?? '-';
    };

    $firstBulty = $bulties->first();
    $comp = $invoiceCompany ?? ($existingInvoice?->company ?? ($invoice?->company ?? ($firstBulty?->company ?? null)));
    $companyName = !empty($existingInvoice?->company_name) ? $existingInvoice->company_name : ($comp ? $comp->name : '');
    $companyAddress = $comp ? $comp->address : '';
    $companyGst = $comp ? $comp->gst_number : '';
    $companyPan = $comp && $comp->pan_number ? $comp->pan_number : '';
    $companyPhone = $comp ? $comp->phone : '';
    $companyOwner = $comp && $comp->owner_name ? strtoupper($comp->owner_name) : '';
    $companyHsn = !empty($existingInvoice?->custom_hsn_code) ? $existingInvoice->custom_hsn_code : ($comp && $comp->hsn_code ? $comp->hsn_code : '996511');

    $bankAccountNo = $comp && $comp->bank_account_no ? $comp->bank_account_no : '';
    $bankIfsc = $comp && $comp->bank_ifsc ? $comp->bank_ifsc : '';
    $bankHolder = $comp && $comp->bank_holder_name ? strtoupper($comp->bank_holder_name) : '';

    $partyName = $existingInvoice?->consignor_name ?? ($existingInvoice?->consignor->name ?? '-');
    $fallbackAddress = '<div class="fw-bold" style="font-size: 11px;">' . $partyName . '</div>' . ($existingInvoice?->consignor ? str_replace("\n", "<br>", $existingInvoice->consignor->address ?? '') : '');
    $partyAddress = !empty($existingInvoice?->billing_address) ? str_replace("\n", "<br>", $existingInvoice->billing_address) : $fallbackAddress;
    $partyGst = $existingInvoice?->consignor ? ($existingInvoice->consignor->gst_no ?? '-') : '-';
    $partyState = !empty($existingInvoice?->custom_place_of_supply) ? $existingInvoice->custom_place_of_supply : ($firstBulty && $firstBulty->destinationCity ? ($firstBulty->destinationCity->state ?? 'RAJASTHAN') : 'RAJASTHAN');

    // State calculation
    $originState = $existingInvoice?->company && $existingInvoice->company->state ? $existingInvoice->company->state : ($firstBulty && $firstBulty->originCity ? ($firstBulty->originCity->state ?? 'RAJASTHAN') : 'RAJASTHAN');
    $isSameState = \App\Http\Controllers\Admin\Transport\BillingController::isSameGstState($originState, $partyState);
    $gstType = $existingInvoice?->gst_type ?? ($isSameState ? 'CGST_SGST' : 'IGST');

    $gstRate = $existingInvoice?->gstMaster ? floatval($existingInvoice->gstMaster->percentage) : 0;
@endphp
@php
    $colCount = max(count($freightFields) + 1, 3); // At least 3 for colspans
@endphp

<table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 11px; background-color: #ffffff;">
    <!-- Header 1 -->
    <tr>
        <td colspan="{{ floor($colCount / 3) }}" style="border: 2px solid #000; border-right: none; font-weight: bold; padding: 4px;">GSTIN: {{ $companyGst }}</td>
        <td colspan="{{ ceil($colCount / 3) }}" style="border: 2px solid #000; border-left: none; border-right: none; font-weight: bold; text-align: center; font-size: 13px; padding: 4px;">TAX INVOICE</td>
        <td colspan="{{ $colCount - floor($colCount / 3) - ceil($colCount / 3) }}" style="border: 2px solid #000; border-left: none; font-weight: bold; text-align: right; padding: 4px;">PAN: {{ $companyPan }}</td>
    </tr>
    
    <!-- Header 2 -->
    <tr>
        <td colspan="{{ $colCount }}" style="border: 2px solid #000; text-align: center; padding: 12px 8px;">
            <h2 style="margin: 0; font-size: 26px; font-weight: bold; text-transform: uppercase; color: #5a7b9d;">{{ $companyName }}</h2>
            <div style="font-size: 11px; margin-top: 4px; color: #333;">{{ $companyAddress }}</div>
        </td>
    </tr>

    <!-- Consignor and Details -->
    <tr>
        <td colspan="{{ ceil($colCount * 2 / 3) }}" rowspan="7" style="border: 2px solid #000; padding: 10px; vertical-align: top;">
            {!! $partyAddress !!}
        </td>
        <td colspan="{{ $colCount - ceil($colCount * 2 / 3) }}" style="border-top: 2px solid #000; border-right: 2px solid #000; padding: 4px 10px; vertical-align: top;">
            <strong>HSN/SAC CODE:</strong> {{ $companyHsn }}
        </td>
    </tr>
    <tr>
        <td colspan="{{ $colCount - ceil($colCount * 2 / 3) }}" style="border-right: 2px solid #000; padding: 4px 10px; vertical-align: top;">
            <strong>Date:</strong> - {{ $invoice->invoice_date ? $invoice->invoice_date->format('d-m-Y') : now()->format('d-m-Y') }}
        </td>
    </tr>
    <tr>
        <td colspan="{{ $colCount - ceil($colCount * 2 / 3) }}" style="border-right: 2px solid #000; padding: 4px 10px; vertical-align: top;">
            <strong>Bill No:</strong> - {{ $invoice->bill_number ?? $invoice->invoice_no }}
        </td>
    </tr>
    <tr>
        <td colspan="{{ $colCount - ceil($colCount * 2 / 3) }}" style="border-right: 2px solid #000; padding: 4px 10px; vertical-align: top;">
            <strong>State Vendor Code:</strong> - {{ $invoice->state_vendor_code ?? '-' }}
        </td>
    </tr>
    <tr>
        <td colspan="{{ $colCount - ceil($colCount * 2 / 3) }}" style="border-right: 2px solid #000; padding: 4px 10px; vertical-align: top;">
            <strong>Vendor Code:</strong> - {{ $invoice->vendor_code ?? '-' }}
        </td>
    </tr>
    <tr>
        <td colspan="{{ $colCount - ceil($colCount * 2 / 3) }}" style="border-right: 2px solid #000; padding: 4px 10px; vertical-align: top;">
            <strong>Vendor Name:</strong> - {{ $invoice->vendor_name ?? '-' }}
        </td>
    </tr>
    <tr>
        <td colspan="{{ $colCount - ceil($colCount * 2 / 3) }}" style="border-right: 2px solid #000; border-bottom: 2px solid #000; padding: 4px 10px; vertical-align: top;">
            <strong>EPOD Status:</strong> - {{ $invoice->epod_status ?? 'N' }}
        </td>
    </tr>

    <!-- RCM -->
    <tr>
        <td colspan="{{ $colCount }}" style="border: 2px solid #000; padding: 6px 12px; font-weight: bold; text-transform: uppercase;">
            WHETHER TAX IS PAYABLE UNDER REVERSE CHARGE MECHANISM : - {{ $invoice->rcm_payable == 1 ? 'YES' : 'NO' }}
        </td>
    </tr>

    <!-- Bill Type -->
    <tr>
        <td colspan="{{ $colCount }}" style="border: 2px solid #000; text-align: center; font-weight: bold; padding: 6px; font-size: 13px; background-color: #f9f9f9;">
            {{ $isMaiharUnloading ? 'Transportation Unloading Bill' : 'Transportation Freight Bill' }}
        </td>
    </tr>

    <!-- Table Headers -->
    <tr>
        <td style="border: 2px solid #000; font-weight: bold; text-align: center; text-transform: uppercase; padding: 8px 4px;">SR. NO.</td>
        @foreach($freightFields as $field)
        <td style="border: 2px solid #000; font-weight: bold; text-align: center; text-transform: uppercase; padding: 8px 4px;">{{ $fieldLabels[$field] ?? $field }}</td>
        @endforeach
    </tr>

    <!-- Table Data -->
    @php
        $totalFreight = 0;
        $totalGst = $invoice->total_gst ?? 0;
        $totalGrand = $invoice->total_amount ?? 0;
        $isMaiharUnloading = ($invoice?->template_type === 'maihar_unloading');
    @endphp
    @foreach($bulties as $index => $b)
        @php
            if ($isMaiharUnloading) {
                $weight = $b->bultyItems ? floatval($b->bultyItems->sum('weight')) : 0;
                $ulRate = $b->bultyDetail ? floatval($b->bultyDetail->ul_rate) : 0;
                $freight = $weight * $ulRate;
            } else {
                $freight = floatval($b->freight_charges ?? 0) - floatval($b->advance_amount ?? 0);
            }
            $totalFreight += $freight;
        @endphp
        <tr>
            <td style="border: 2px solid #000; text-align: center; padding: 4px;">{{ $index + 1 }}</td>
            @foreach($freightFields as $field)
            <td style="border: 2px solid #000; text-align: center; padding: 4px;">
                                        @php $val = $getFieldValuePhp($b, $field); @endphp
                                        @if(is_numeric($val) && strpos($field, 'amount') !== false)
                                            {{ number_format((float)$val, 2) }}
                                        @elseif(is_numeric($val) && in_array($field, ['weight','qty_mt','recd_qty','short_qty','billed_qty','challan_qty','final_wgt']))
                                            {{ number_format((float)$val, 3, '.', ',') }}
                                        @else
                                            {{ $val }}
                                        @endif
                                    </td>
            @endforeach
        </tr>
    @endforeach

    @php
        $damageTotal = $invoice->bulties ? $invoice->bulties->sum(fn($b) => floatval($b->damage_amount ?? 0)) : 0;
        $shortageTotal = $invoice->bulties ? $invoice->bulties->sum(fn($b) => floatval($b->shortage_amount ?? 0)) : 0;
        $netFreightTotal = $totalFreight - $damageTotal - $shortageTotal;
    @endphp
    <!-- Totals Rows -->
    <tr>
        <td colspan="{{ max($colCount - 2, 1) }}" style="border-left: 2px solid #000; border-right: none; border-bottom: none;"></td>
        <td style="border: 2px solid #000; border-bottom: 1px solid #000; text-align: right; font-weight: bold; padding: 4px;">Total Freight:</td>
        <td style="border: 2px solid #000; border-bottom: 1px solid #000; text-align: right; padding: 4px;">{{ number_format($netFreightTotal, 2) }}</td>
    </tr>
    @if($gstType === 'CGST_SGST')
        @php $halfGst = $gstRate / 2; @endphp
        <tr>
            <td colspan="{{ max($colCount - 2, 1) }}" style="border-left: 2px solid #000; border-right: none; border-top: none; border-bottom: none;"></td>
            <td style="border: 2px solid #000; border-top: 1px solid #000; border-bottom: 1px solid #000; text-align: right; font-weight: bold; padding: 4px;">CGST @ {{ number_format($halfGst, 1) }}%:</td>
            <td style="border: 2px solid #000; border-top: 1px solid #000; border-bottom: 1px solid #000; text-align: right; padding: 4px;">{{ number_format($totalGst / 2, 2) }}</td>
        </tr>
        <tr>
            <td colspan="{{ max($colCount - 2, 1) }}" style="border-left: 2px solid #000; border-right: none; border-top: none; border-bottom: none;"></td>
            <td style="border: 2px solid #000; border-top: 1px solid #000; border-bottom: 1px solid #000; text-align: right; font-weight: bold; padding: 4px;">SGST @ {{ number_format($halfGst, 1) }}%:</td>
            <td style="border: 2px solid #000; border-top: 1px solid #000; border-bottom: 1px solid #000; text-align: right; padding: 4px;">{{ number_format($totalGst / 2, 2) }}</td>
        </tr>
    @else
        <tr>
            <td colspan="{{ max($colCount - 2, 1) }}" style="border-left: 2px solid #000; border-right: none; border-top: none; border-bottom: none;"></td>
            <td style="border: 2px solid #000; border-top: 1px solid #000; border-bottom: 1px solid #000; text-align: right; font-weight: bold; padding: 4px;">IGST @ {{ number_format($gstRate, 1) }}%:</td>
            <td style="border: 2px solid #000; border-top: 1px solid #000; border-bottom: 1px solid #000; text-align: right; padding: 4px;">{{ number_format($totalGst, 2) }}</td>
        </tr>
    @endif
    <tr>
        <td colspan="{{ max($colCount - 2, 1) }}" style="border-left: 2px solid #000; border-right: none; border-top: none; border-bottom: none;"></td>
        <td style="border: 2px solid #000; border-top: 1px solid #000; border-bottom: 1px solid #000; text-align: right; font-weight: bold; padding: 4px;">Other Charges:</td>
        <td style="border: 2px solid #000; border-top: 1px solid #000; border-bottom: 1px solid #000; text-align: right; padding: 4px;">{{ number_format($invoice->bulties->sum('other_charges'), 2) }}</td>
    </tr>
    <tr>
        <td colspan="{{ max($colCount - 2, 1) }}" style="border-left: 2px solid #000; border-right: none; border-top: none; border-bottom: 2px solid #000;"></td>
        <td style="border: 2px solid #000; border-top: 1px solid #000; text-align: right; font-weight: bold; padding: 4px;">Grand Total:</td>
        <td style="border: 2px solid #000; border-top: 1px solid #000; text-align: right; font-weight: bold; padding: 4px;">{{ number_format($totalGrand, 2) }}</td>
    </tr>

    <!-- Amount in Words -->
    <tr>
        <td colspan="{{ $colCount }}" style="border: 2px solid #000; font-weight: bold; padding: 10px 12px;">
            AMOUNT IN WORD:<span style="text-transform: uppercase;">{{ $invoice->amount_in_words }}</span>
        </td>
    </tr>

    <!-- Declaration & Bank info / Signature -->
    @php
        $comp = $invoiceCompany ?? ($existingInvoice?->company ?? null);
        $rcmPayableVal = $existingInvoice?->rcm_payable ?? 1;
        if ($rcmPayableVal == 1) {
            $companyDeclaration = ($comp && !empty($comp->declaration))
                ? $comp->declaration
                : 'GST payable by recipient under Reverse Charge (RCM) on GTA services.';
        } else {
            $companyDeclaration = ($comp && !empty($comp->declaration))
                ? $comp->declaration
                : 'Tax payable under Forward Charge.';
        }
    @endphp
    <tr>
        <td colspan="{{ ceil($colCount * 2 / 3) }}" style="border-left: 2px solid #000; border-top: 2px solid #000; padding: 10px;">
            <div style="font-weight: bold;">Declaration : {{ $companyDeclaration }}</div>
        </td>
        <td colspan="{{ $colCount - ceil($colCount * 2 / 3) }}" style="border-right: 2px solid #000; border-top: 2px solid #000; text-align: right; padding: 10px;">
            <div style="font-weight: bold; text-align: right;">For <span style="text-transform: uppercase;">{{ $companyName }}</span></div>
        </td>
    </tr>
    <tr>
        <td colspan="{{ floor((ceil($colCount * 2 / 3)) / 2) }}" style="border-left: 2px solid #000; border: 1px solid #ddd; font-weight: bold; padding: 10px;">ACCOUNT NO.</td>
        <td colspan="{{ ceil((ceil($colCount * 2 / 3)) / 2) }}" style="border: 1px solid #ddd; font-weight: bold; padding: 10px;">{{ $bankAccountNo }}</td>
        <td colspan="{{ $colCount - ceil($colCount * 2 / 3) }}" style="border-right: 2px solid #000; text-align: right;"></td>
    </tr>
    <tr>
        <td colspan="{{ floor((ceil($colCount * 2 / 3)) / 2) }}" style="border-left: 2px solid #000; border: 1px solid #ddd; font-weight: bold; padding: 10px;">IFC CODE</td>
        <td colspan="{{ ceil((ceil($colCount * 2 / 3)) / 2) }}" style="border: 1px solid #ddd; font-weight: bold; padding: 10px;">{{ $bankIfsc }}</td>
        <td colspan="{{ $colCount - ceil($colCount * 2 / 3) }}" style="border-right: 2px solid #000; text-align: right; vertical-align: bottom;">
            <div style="font-size: 8px; color: #555; margin-bottom: 5px;">Print Date: {{ date('d-m-Y H:i:s') }}</div>
        </td>
    </tr>
    <tr>
        <td colspan="{{ floor((ceil($colCount * 2 / 3)) / 2) }}" style="border-bottom: 2px solid #000; border-left: 2px solid #000; border: 1px solid #ddd; font-weight: bold; padding: 10px;">HOLDER NAME</td>
        <td colspan="{{ ceil((ceil($colCount * 2 / 3)) / 2) }}" style="border-bottom: 2px solid #000; border: 1px solid #ddd; font-weight: bold; padding: 10px;">{{ $bankHolder }}</td>
        <td colspan="{{ $colCount - ceil($colCount * 2 / 3) }}" style="border-bottom: 2px solid #000; border-right: 2px solid #000; text-align: right; vertical-align: bottom; padding: 10px;">
            <div style="font-weight: bold; text-align: right;">
                @if(!empty($companyOwner))
                    <div style="font-size: 8px; color: #333; font-weight: bold; line-height: 1.2; text-align: center; display: inline-block;">Digitally signed by {{ $companyOwner }}</div><br/>
                @endif
                <div style="font-size: 8px; color: #555; line-height: 1.2; text-align: center; display: inline-block; margin-bottom: 4px;">Date: {{ date('d-m-Y H:i:s') }}</div><br/>
                <span style="border-top: 1px solid #000; padding-top: 3px; display: inline-block; width: 150px; text-align: center;">Authorized Signatory</span>
            </div>
        </td>
    </tr>

    @if($existingInvoice?->grn_new_page ?? false)
    @php
        $grnCount = max(count($grnFields) + 1, 1);
    @endphp
    <!-- Empty row for spacing -->
    <tr>
        <td colspan="{{ $colCount }}" style="height: 30px;"></td>
    </tr>
    
    <!-- GRN Header -->
    <tr>
        <td colspan="{{ $grnCount }}" style="border: 2px solid #000; text-align: center; font-weight: bold; padding: 6px; font-size: 13px; background-color: #f9f9f9;">
            GRN / Delivery Reference
        </td>
    </tr>

    <!-- GRN Table Headers -->
    <tr>
        <td style="border: 2px solid #000; font-weight: bold; text-align: center;">SR.</td>
        @foreach($grnFields as $field)
        <td style="border: 2px solid #000; font-weight: bold; text-align: center; text-transform: uppercase;">{{ $fieldLabels[$field] ?? $field }}</td>
        @endforeach
    </tr>

    <!-- GRN Table Data -->
    @foreach($bulties as $index => $b)
        <tr>
            <td style="border: 2px solid #000; text-align: center;">{{ $index + 1 }}</td>
            @foreach($grnFields as $field)
            <td style="border: 2px solid #000; text-align: center;">
                                        @php $val = $getFieldValuePhp($b, $field); @endphp
                                        @if(is_numeric($val) && strpos($field, 'amount') !== false)
                                            {{ number_format((float)$val, 2) }}
                                        @elseif(is_numeric($val) && in_array($field, ['weight','qty_mt','recd_qty','short_qty','billed_qty','challan_qty','final_wgt']))
                                            {{ number_format((float)$val, 3, '.', ',') }}
                                        @else
                                            {{ $val }}
                                        @endif
                                    </td>
            @endforeach
        </tr>
    @endforeach
    @endif
</table>
</body>
</html>
