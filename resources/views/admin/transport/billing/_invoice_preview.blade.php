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
    $companyName = !empty($existingInvoice?->company_name) ? $existingInvoice->company_name : ($invoiceCompany ? $invoiceCompany->name : '');
    $companyAddress = $invoiceCompany ? $invoiceCompany->address : '';
    $companyGst = $invoiceCompany ? $invoiceCompany->gst_number : '';
    $companyPan = $invoiceCompany && $invoiceCompany->pan_number ? $invoiceCompany->pan_number : '';
    $companyPhone = $invoiceCompany ? $invoiceCompany->phone : '';
    $companyOwner = $invoiceCompany && $invoiceCompany->owner_name ? strtoupper($invoiceCompany->owner_name) : '';
    $companyHsn = !empty($existingInvoice?->custom_hsn_code) ? $existingInvoice->custom_hsn_code : ($invoiceCompany && $invoiceCompany->hsn_code ? $invoiceCompany->hsn_code : '996511');

    $bankAccountNo = $invoiceCompany && $invoiceCompany->bank_account_no ? $invoiceCompany->bank_account_no : '';
    $bankIfsc = $invoiceCompany && $invoiceCompany->bank_ifsc ? $invoiceCompany->bank_ifsc : '';
    $bankHolder = $invoiceCompany && $invoiceCompany->bank_holder_name ? strtoupper($invoiceCompany->bank_holder_name) : '';

    $partyName = $existingInvoice?->consignor_name ?? ($existingInvoice?->consignor->name ?? '-');
    $fallbackAddress = '<div class="fw-bold" style="font-size: 11px;">' . $partyName . '</div>' . ($existingInvoice?->consignor ? str_replace("\n", "<br>", $existingInvoice->consignor->address ?? '') : '');
    $partyAddress = !empty($existingInvoice?->billing_address) ? str_replace("\n", "<br>", $existingInvoice->billing_address) : $fallbackAddress;
    $partyGst = $existingInvoice?->consignor ? ($existingInvoice->consignor->gst_no ?? '-') : '-';
    $partyState = !empty($existingInvoice?->custom_place_of_supply) ? $existingInvoice->custom_place_of_supply : ($firstBulty && $firstBulty->destinationCity ? ($firstBulty->destinationCity->state ?? 'RAJASTHAN') : 'RAJASTHAN');

    // State & GST Type calculation
    $originState = $invoiceCompany && $invoiceCompany->state ? $invoiceCompany->state : ($firstBulty && $firstBulty->originCity ? ($firstBulty->originCity->state ?? 'RAJASTHAN') : 'RAJASTHAN');
    $isSameState = \App\Http\Controllers\Admin\Transport\BillingController::isSameGstState($originState, $partyState);
    $gstType = $existingInvoice?->gst_type ?? ($isSameState ? 'CGST_SGST' : 'IGST');

    $gstRate = $existingInvoice?->gstMaster ? floatval($existingInvoice->gstMaster->percentage) : 0;
    $gstTotalVal = floatval($existingInvoice?->total_gst) > 0 ? floatval($existingInvoice->total_gst) : (floatval($gstTotal ?? 0) > 0 ? floatval($gstTotal) : ($freightTotal * ($gstRate / 100)));
    $cgstVal = ($gstType === 'CGST_SGST') ? (floatval($existingInvoice?->cgst_amount) > 0 ? floatval($existingInvoice->cgst_amount) : ($gstTotalVal / 2)) : 0;
    $sgstVal = ($gstType === 'CGST_SGST') ? (floatval($existingInvoice?->sgst_amount) > 0 ? floatval($existingInvoice->sgst_amount) : ($gstTotalVal / 2)) : 0;
    $igstVal = ($gstType === 'IGST') ? (floatval($existingInvoice?->igst_amount) > 0 ? floatval($existingInvoice->igst_amount) : $gstTotalVal) : 0;
    
    $effectiveGrandTotal = floatval($grandTotal ?? 0) > 0 ? floatval($grandTotal) : ($freightTotal + $otherTotal + $gstTotalVal);
    $effectiveAmountInWords = !empty($amountInWords) ? $amountInWords : \App\Http\Controllers\Admin\Transport\BillingController::convertNumberToWords($effectiveGrandTotal);

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
@endphp

<div class="invoice-preview-container" style="background: #fff; color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 11px; line-height: 1.4;">
    <!-- SHEET 1: FREIGHT BILL -->
    <div id="freight-preview-sheet" style="border: 2px solid #000; padding: 0; box-sizing: border-box;">
        <!-- Top header details -->
        <div class="d-flex justify-content-between align-items-center position-relative" style="border-bottom: 2px solid #000; padding: 4px 8px; font-weight: bold; font-size: 10px;">
            <div>GSTIN: <span>{{ $companyGst }}</span></div>
            <div class="position-absolute top-50 start-50 translate-middle" style="font-size: 12px;">TAX INVOICE</div>
            <div>PAN: <span>{{ $companyPan }}</span></div>
        </div>
        
        <!-- Main Header -->
        <div class="text-center" style="border-bottom: 2px solid #000; padding: 8px;">
            <h2 class="m-0 fw-bold" style="font-size: 20px; letter-spacing: 1px; text-transform: uppercase;">{{ $companyName }}</h2>
            <div style="font-size: 10px; margin-top: 4px;">{{ $companyAddress }}</div>
        </div>

        <!-- Client details / Supply meta -->
        <div class="row g-0" style="border-bottom: 2px solid #000;">
            <div class="col-8 p-2" style="border-right: 2px solid #000; font-size: 10px;">
                <div>{!! $partyAddress !!}</div>
            </div>
            <div class="col-4 p-2" style="font-size: 10px;">
                <div><strong>HSN/SAC CODE:</strong> <span>{{ $companyHsn }}</span></div>
                <div class="mt-2"><strong>Date:</strong> - <span>{{ $existingInvoice->invoice_date ? $existingInvoice->invoice_date->format('d-m-Y') : now()->format('d-m-Y') }}</span></div>
                <div><strong>Bill No:</strong> - <span>{{ $billNumber }}</span></div>
                <div class="mt-1"><strong>State Vendor Code:</strong> - <span>{{ $existingInvoice->state_vendor_code ?? '-' }}</span></div>
                <div><strong>Vendor Code:</strong> - <span>{{ $vendorCode ?? '-' }}</span></div>
                <div><strong>Vendor Name:</strong> - <span>{{ $existingInvoice->vendor_name ?? '-' }}</span></div>
                <div><strong>EPOD Status:</strong> - <span>{{ $existingInvoice->epod_status ?? 'N' }}</span></div>
            </div>
        </div>

        <!-- RCM statement row -->
        <div style="border-bottom: 2px solid #000; padding: 4px 8px; font-weight: bold; font-size: 10px; text-transform: uppercase;">
            WHETHER TAX IS PAYABLE UNDER REVERSE CHARGE MECHANISM : - {{ $rcmPayableVal == 1 ? 'YES' : 'NO' }}
        </div>

        <!-- Subtitle tag -->
        <div class="text-center fw-bold" style="background: #f2f2f2; border-bottom: 2px solid #000; padding: 4px; font-size: 11px;">
            {{ $isMaiharUnloading ? 'Transportation Unloading Bill' : 'Transportation Freight Bill' }}
        </div>

        <!-- Main Table -->
        <div class="table-responsive">
            <table class="table table-bordered mb-0" id="preview-table" style="border-collapse: collapse; border: none; color: #000; width: 100%;">
                <thead style="background: #f2f2f2;">
                    <tr>
                        <th style="border: 1px solid #000 !important; padding: 2px 2px !important; font-weight: bold; text-align: center;">SR. NO.</th>
                        @foreach($freightFields as $fieldKey)
                            <th style="border: 1px solid #000 !important; padding: 2px 2px !important; font-weight: bold; text-align: center;">{{ $fieldLabels[$fieldKey] ?? strtoupper(str_replace('_', ' ', $fieldKey)) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($bulties as $idx => $bulty)
                        <tr>
                            <td style="border: 1px solid #000 !important; padding: 2px 2px !important; text-align: center;">{{ $idx + 1 }}</td>
                            @foreach($freightFields as $fieldKey)
                                <td style="border: 1px solid #000 !important; padding: 2px 2px !important; text-align: center;">
                                    @php $val = $getFieldValuePhp($bulty, $fieldKey); @endphp
                                    @if(is_numeric($val) && strpos($fieldKey, 'amount') !== false)
                                        {{ number_format((float)$val, 2) }}
                                    @elseif(is_numeric($val) && in_array($fieldKey, ['weight','qty_mt','recd_qty','short_qty','billed_qty','challan_qty','final_wgt']))
                                        {{ number_format((float)$val, 3, '.', ',') }}
                                    @else
                                        {{ $val }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- GST Table Split -->
        <div class="row g-0" style="border-top: 2px solid #000;">
            <div class="col-7" style="border-right: 2px solid #000;">
                <!-- Left side spacing -->
            </div>
            <div class="col-5">
                <table class="w-100" style="color: #000; font-size: 10px; border-collapse: collapse;">
                    <tr>
                        <td class="fw-bold p-1 text-end" style="border-bottom: 1px solid #000; border-left: 1px solid #000;">Total Freight:</td>
                        <td class="p-1 text-end" style="border-bottom: 1px solid #000; border-left: 1px solid #000;">{{ number_format($freightTotal, 2) }}</td>
                    </tr>
                    @if($gstType === 'CGST_SGST')
                        @php $halfGstRate = $gstRate / 2; @endphp
                        <tr>
                            <td class="fw-bold p-1 text-end" style="border-bottom: 1px solid #000; border-left: 1px solid #000;">CGST @ {{ number_format($halfGstRate, 1) }}%:</td>
                            <td class="p-1 text-end" style="border-bottom: 1px solid #000; border-left: 1px solid #000;">{{ number_format($cgstVal, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold p-1 text-end" style="border-bottom: 1px solid #000; border-left: 1px solid #000;">SGST @ {{ number_format($halfGstRate, 1) }}%:</td>
                            <td class="p-1 text-end" style="border-bottom: 1px solid #000; border-left: 1px solid #000;">{{ number_format($sgstVal, 2) }}</td>
                        </tr>
                    @else
                        <tr>
                            <td class="fw-bold p-1 text-end" style="border-bottom: 1px solid #000; border-left: 1px solid #000;">IGST @ {{ number_format($gstRate, 1) }}%:</td>
                            <td class="p-1 text-end" style="border-bottom: 1px solid #000; border-left: 1px solid #000;">{{ number_format($igstVal, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="fw-bold p-1 text-end" style="border-bottom: 1px solid #000; border-left: 1px solid #000;">Other Charges:</td>
                        <td class="p-1 text-end" style="border-bottom: 1px solid #000; border-left: 1px solid #000;">{{ number_format($otherTotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold p-1 text-end" style="border-bottom: 1px solid #000; border-left: 1px solid #000;">Grand Total:</td>
                        <td class="p-1 text-end fw-bold" style="border-bottom: 1px solid #000; border-left: 1px solid #000;">{{ number_format($effectiveGrandTotal, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Amount in Words -->
        <div class="p-2 fw-bold" style="border-top: 2px solid #000; border-bottom: 2px solid #000; font-size: 10px;">
            AMOUNT IN WORD: <span style="text-transform: uppercase;">{{ $effectiveAmountInWords }}</span>
        </div>

        <!-- Declaration & Bank info / Signature -->
        <div class="row g-0">
            <div class="col-7 p-2" style="border-right: 2px solid #000; font-size: 9.5px;">
                <div class="fw-bold">Declaration : {{ $companyDeclaration }}</div>
                <table class="table table-bordered table-sm mt-2 mb-0" style="color: #000; font-size: 9.5px; border-collapse: collapse; border: 1px solid #000;">
                    <tr>
                        <td class="fw-bold p-1" style="width: 30%; border: 1px solid #000;">ACCOUNT NO.</td>
                        <td class="p-1" style="border: 1px solid #000;">{{ $bankAccountNo }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold p-1" style="border: 1px solid #000;">IFC CODE</td>
                        <td class="p-1" style="border: 1px solid #000;">{{ $bankIfsc }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold p-1" style="border: 1px solid #000;">HOLDER NAME</td>
                        <td class="p-1" style="border: 1px solid #000;">{{ $bankHolder }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-5 p-2 d-flex flex-column justify-content-between text-end" style="min-height: 110px;">
                <div class="fw-bold" style="font-size: 10px;">For <span>{{ $companyName }}</span></div>
                @if(!empty($companySignatureUrl))
                    <div class="my-1 text-end">
                        <img src="{{ $companySignatureUrl }}" alt="Signature" style="max-height: 45px; max-width: 140px; object-fit: contain;">
                    </div>
                @else
                    <div style="font-size: 8px; color: #555; margin-bottom: 12px;">
                        Print Date: {{ date('d-m-Y H:i:s') }}
                    </div>
                @endif
                <div style="font-size: 10px;">
                    <span style="border-top: 1px solid #000; padding-top: 3px; display: inline-block; width: 150px; text-align: center; font-weight: bold;">Authorized Signatory</span>
                </div>
            </div>
        </div>
    </div>

    @if($grnNewPage ?? false)
    <!-- PAGE BREAK DIVIDER -->
    <div id="preview-page-break" class="text-center my-4 text-muted" style="border-top: 2px dashed #888; padding-top: 8px; font-weight: bold; page-break-before: always;">
        --- PAGE BREAK (GRN Details will print on next page) ---
    </div>

    <!-- SHEET 2: GRN DETAILS BILL -->
    <div id="grn-preview-sheet" style="border: 2px solid #000; padding: 0; box-sizing: border-box;">
        <!-- Subtitle tag -->
        <div class="text-center fw-bold" style="background: #f2f2f2; border-bottom: 2px solid #000; padding: 4px; font-size: 11px;">
            Transportation GRN Details
        </div>

        <!-- GRN Table -->
        <div class="table-responsive">
            <table class="table table-bordered mb-0" id="preview-grn-table" style="border-collapse: collapse; border: none; color: #000; width: 100%;">
                <thead style="background: #f2f2f2;">
                    <tr>
                        <th style="border: 1px solid #000 !important; padding: 2px 2px !important; font-weight: bold; text-align: center;">SR. NO.</th>
                        @foreach($grnFields as $fieldKey)
                            <th style="border: 1px solid #000 !important; padding: 2px 2px !important; font-weight: bold; text-align: center;">{{ $fieldLabels[$fieldKey] ?? strtoupper(str_replace('_', ' ', $fieldKey)) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($bulties as $idx => $bulty)
                        <tr>
                            <td style="border: 1px solid #000 !important; padding: 2px 2px !important; text-align: center;">{{ $idx + 1 }}</td>
                            @foreach($grnFields as $fieldKey)
                                <td style="border: 1px solid #000 !important; padding: 2px 2px !important; text-align: center;">
                                    @php $val = $getFieldValuePhp($bulty, $fieldKey); @endphp
                                    @if(is_numeric($val) && strpos($fieldKey, 'amount') !== false)
                                        {{ number_format((float)$val, 2) }}
                                    @elseif(is_numeric($val) && in_array($fieldKey, ['weight','qty_mt','recd_qty','short_qty','billed_qty','challan_qty','final_wgt']))
                                        {{ number_format((float)$val, 3, '.', ',') }}
                                    @else
                                        {{ $val }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>