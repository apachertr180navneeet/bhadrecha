@php
    $visibleFields = $existingInvoice?->visible_fields ?? [];

    $freightFields = $visibleFields;
    $grnFields = [];
    if ($existingInvoice?->grn_new_page ?? false) {
        $grnFields = $existingInvoice?->grn_fields ?? [];
    }

    $fieldLabels = [
        'lr_no' => 'LR NO.',
        'lr_date' => 'LR DATE',
        'from_city' => 'FROM',
        'to_city' => 'TO',
        'consignee_id' => 'CONSIGNEE',
        'vehicle_id' => 'TRUCK NUMBER',
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
        'delivery_number' => 'DELIVERY NO.',
        'invoice_number' => 'INVOICE NO.',
        'invoice_date' => 'INVOICE DATE',
        'eway_bill_no' => 'E-WAY BILL NO.',
        'generation_date' => 'GEN DATE',
        'expiry_date' => 'EXP DATE',
        'mode' => 'MODE',
        'damage_amount' => 'DAMAGE AMOUNT',
        'shortage_amount' => 'SHORTAGE AMOUNT',
        'e_lr_no' => 'E-LR NO.',
        'ul_date' => 'UL DATE',
        'ul_rate' => 'UL RATE',
        'ul_amount' => 'UL AMOUNT',
        'bag_ld' => 'BAGS LD',
        'bag_ul' => 'BAGS UL',
        'bag_short' => 'BAGS SHORT',
        'rate_mt' => 'RATE/MT',
        'qty_mt' => 'MT',
        'description_services' => 'DESCRIPTION',
        'posting_date' => 'POSTING DATE',
        'po_no' => 'PO NO.',
        'po_item' => 'PO ITEM',
        'mat_doc' => 'MAT DOC',
        'gate_entry_no' => 'GATE ENTRY NO.',
        'challan_no' => 'CHALLAN NO.',
        'challan_date' => 'CHALLAN DATE',
        'transporter_code' => 'TRANSPORTER CODE',
        'transporter_name' => 'TRANSPORTER NAME',
        'gate_out_date' => 'GATE OUT DATE',
        'invoice_doc' => 'INVOICE DOC',
        'bilty_detail_invoice_date' => 'INVOICE DATE',
        'invoice_time' => 'INVOICE TIME',
        'grn_no' => 'GRN NO.',
        'grn_date' => 'GRN DATE',
        'grn_time' => 'GRN TIME',
        'recd_qty' => 'RECD QTY',
        'arrival_time' => 'ARRIVAL TIME',
        'shortage_grn_no' => 'SHORTAGE GRN NO.',
        'shortage_grn_date' => 'SHORTAGE GRN DATE',
        'short_qty' => 'SHORT QTY',
        'item_name' => 'ITEM NAME',
        'packaging_type' => 'PACKAGING TYPE',
        'articles' => 'ARTICLES',
        'weight' => 'WEIGHT',
        'unit' => 'UNIT',
        'freight_per_kg' => 'FREIGHT/KG',
        'item_amount' => 'ITEM AMOUNT',
        'pod_file' => 'POD FILE',
        'bill_no' => 'BILL NO.',
        'supplier_no' => 'SUPPLIER NO.',
        'material_name' => 'MATERIAL NAME',
        'material_no' => 'MATERIAL NO.',
        'depot_name' => 'DEPOT NAME',
        'billed_qty' => 'BILLED QTY',
        'mn_no' => 'MN NO.',
        'no_of_lr' => 'NO OF LR'
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
            'item_name', 'packaging_type', 'articles', 'weight', 'unit', 'freight_per_kg', 'item_amount', 'pod_file'
        ];
        
        if (in_array($fieldKey, $detailFields)) {
            $detail = $bulty->bultyDetail;
            if (!$detail) return '-';
            
            if ($fieldKey === 'supplier_id') {
                return $detail->supplier ? $detail->supplier->name : ($detail->supplier_id ?? '-');
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
        
        if ($fieldKey === 'freight_charges') {
            return floatval($bulty->freight_charges) - floatval($bulty->advance_amount);
        }
        if ($fieldKey === 'gst_amount') {
            $f = floatval($bulty->freight_charges) - floatval($bulty->advance_amount);
            $gstRate = $existingInvoice?->gstMaster ? floatval($existingInvoice->gstMaster->percentage) : null;
            return $gstRate !== null ? ($f * ($gstRate / 100)) : floatval($bulty->gst_amount);
        }
        if ($fieldKey === 'total_amount') {
            $f = floatval($bulty->freight_charges) - floatval($bulty->advance_amount);
            $o = floatval($bulty->other_charges);
            $gstRate = $existingInvoice?->gstMaster ? floatval($existingInvoice->gstMaster->percentage) : null;
            $g = $gstRate !== null ? ($f * ($gstRate / 100)) : floatval($bulty->gst_amount);
            return $f + $o + $g;
        }
        
        return $bulty->{$fieldKey} ?? '-';
    };

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

    $partyName = $existingInvoice?->consignor_name ?? ($existingInvoice?->consignor->name ?? '-');
    $fallbackAddress = '<div class="fw-bold" style="font-size: 11px;">' . $partyName . '</div>' . ($existingInvoice?->consignor ? str_replace("\n", "<br>", $existingInvoice->consignor->address ?? '') : '');
    $partyAddress = !empty($existingInvoice?->billing_address) ? str_replace("\n", "<br>", $existingInvoice->billing_address) : $fallbackAddress;
    $partyGst = $existingInvoice?->consignor ? ($existingInvoice->consignor->gst_no ?? '-') : '-';
    $partyState = !empty($existingInvoice?->custom_place_of_supply) ? $existingInvoice->custom_place_of_supply : ($firstBulty && $firstBulty->destinationCity ? ($firstBulty->destinationCity->state ?? 'RAJASTHAN') : 'RAJASTHAN');

    // State calculation
    $originState = $invoiceCompany && $invoiceCompany->state ? $invoiceCompany->state : ($firstBulty && $firstBulty->originCity ? ($firstBulty->originCity->state ?? 'RAJASTHAN') : 'RAJASTHAN');
    $isSameState = \App\Http\Controllers\Admin\Transport\BillingController::isSameGstState($originState, $partyState);
    $gstType = $existingInvoice?->gst_type ?? ($isSameState ? 'CGST_SGST' : 'IGST');

    $gstRate = $existingInvoice?->gstMaster ? floatval($existingInvoice->gstMaster->percentage) : 0;
@endphp