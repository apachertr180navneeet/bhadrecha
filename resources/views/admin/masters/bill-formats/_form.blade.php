@php
    $selectedFields = $format ? ($format->visible_fields ?? []) : old('visible_fields', []);
    $selectedOrder = $format ? ($format->field_order ?? []) : old('field_order', []);
    $fieldGroups = [
        'Basic Information' => [
            'lr_no' => 'Bilty Number',
            'lr_date' => 'LR Date',
            'payment_type' => 'Payment Type',
            'from_city' => 'Origin City',
            'to_city' => 'Destination City',
        ],
        'Parties Details' => [
            'consignor_id' => 'Consignor',
            'consignee_id' => 'Consignee',
        ],
        'Items / Goods Details' => [
            'item_name' => 'Item Name',
            'packaging_type' => 'Packaging Type',
            'articles' => 'No of Articles',
            'weight' => 'Total Weight',
            'unit' => 'Unit',
            'freight_per_mt' => 'Freight Per Mt',
            'item_amount' => 'Amount',
        ],
        'Vehicle & Driver Details' => [
            'vehicle_id' => 'Vehicle',
            'driver_id' => 'Driver',
            'truck_owner_name' => 'Truck Owner Name',
            'pan_no' => 'PAN No.',
            'dl_no' => 'DL No.',
            'mobile_no' => 'Mobile No.',
        ],
        'References & Documents' => [
            'order_number' => 'Order Number',
            'delivery_number' => 'Delivery Number',
            'from_no' => 'From No.',
            'invoice_number' => 'Invoice Number',
            'invoice_date' => 'Invoice Date',
            'eway_bill_no' => 'E-Way Bill No',
            'generation_date' => 'Generation Date',
            'expiry_date' => 'Expiry Date',
            'e_lr_no' => 'E-LR No',
            'mode' => 'Transit Mode',
            'mn_no' => 'MN No.',
            'bill_no' => 'Bill No.',
            'no_of_lr' => 'No. of LR',
        ],
        'Other Entry' => [
            'posting_date' => 'Posting Date',
            'supplier_id' => 'Supplier',
            'supplier_no' => 'Supplier No.',
            'depot_name' => 'Depot Name',
            'po_no' => 'PO No',
            'po_item' => 'PO Item',
            'mat_doc' => 'Mat Doc',
            'gate_entry_no' => 'Gate Entry No.',
            'challan_no' => 'Challan No',
            'challan_date' => 'Challan Date',
            'transporter_code' => 'Transporter Code',
            'transporter_name' => 'Transporter Name',
            'gate_out_date' => 'Gate Out Date',
            'invoice_doc' => 'Invoice Doc',
            'invoice_time' => 'Invoice Time',
            'grn_no' => 'GRN No',
            'grn_date' => 'GRN Date',
            'grn_time' => 'GRN Time',
            'challan_qty' => 'Challan Qty',
            'final_wgt' => 'Final Wgt',
            'recd_qty' => 'Received Qty',
            'material_name' => 'Material Name',
            'material_no' => 'Material No.',
            'billed_qty' => 'Billed Qty.',
            'arrival_time' => 'Arrival Time',
            'ul_date' => 'Unloading Date',
            'ul_rate' => 'Unloading Rate',
            'shortage_grn_no' => 'Shortage GRN No',
            'shortage_grn_date' => 'Shortage GRN Date',
            'short_qty' => 'Short Qty',
            'bag_ld' => 'Bags Loaded',
            'bag_ul' => 'Bags Unloaded',
            'bag_short' => 'Bags Short',
            'rate_mt' => 'Rate/MT',
            'qty_mt' => 'Qty/MT',
            'description_services' => 'Description of Services',
        ],
        'Charges & Payment' => [
            'freight_charges' => 'Freight Charges',
            'other_charges' => 'Other Charges',
            'bilty_commission' => 'Bilty Commission',
            'advance_amount' => 'Advance Amount',
            'damage_amount' => 'Damage Amount',
            'shortage_amount' => 'Shortage Amount',
            'remark' => 'Remark',
            'total_amount' => 'Total Amount',
            'remaining_amount' => 'Remaining Amount',
        ]
    ];
    $allFields = [];
    foreach ($fieldGroups as $group => $fields) {
        foreach ($fields as $key => $label) {
            $allFields[$key] = $label;
        }
    }
    
    $selectedGrnFields = $format ? ($format->grn_fields ?? []) : old('grn_fields', []);
    $selectedGrnOrder = $format ? ($format->grn_field_order ?? []) : old('grn_field_order', []);
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Company <span class="text-danger">*</span></label>
        <select name="company_id" class="form-select" required>
            <option value="">Select Company</option>
            @foreach($companies as $company)
                <option value="{{ $company->id }}" {{ ($format?->company_id ?? old('company_id')) == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Format Name <span class="text-danger">*</span></label>
        <input type="text" name="format_name" class="form-control" value="{{ $format->format_name ?? old('format_name') }}" required placeholder="e.g. JK, Nathdwara, Birla White">
    </div>
    <div class="col-md-6">
        <label class="form-label">Template Type <span class="text-danger">*</span></label>
        <select name="template_type" class="form-select" required>
            <option value="standard" {{ ($format?->template_type ?? old('template_type')) == 'standard' ? 'selected' : '' }}>Standard (Dynamic Columns)</option>
            <option value="nathdwara" {{ ($format?->template_type ?? old('template_type')) == 'nathdwara' ? 'selected' : '' }}>Nathdwara (Fixed Format)</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Depot (optional)</label>
        <select name="depot_id" class="form-select">
            <option value="">All Depots</option>
            @if($format && $format->depot_id)
                <option value="{{ $format->depot_id }}" selected>{{ $format->depot?->name }}</option>
            @endif
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Party / Consignor (optional)</label>
        <select name="party_id" class="form-select">
            <option value="">All Parties</option>
            @if($format && $format->party_id)
                <option value="{{ $format->party_id }}" selected>{{ $format->party?->name }}</option>
            @endif
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">GST Rate (optional)</label>
        <select name="gst_master_id" class="form-select">
            <option value="">No GST</option>
            @foreach($gstMasters as $gst)
                <option value="{{ $gst->id }}" {{ ($format?->gst_master_id ?? old('gst_master_id')) == $gst->id ? 'selected' : '' }}>{{ $gst->gst_rate }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 d-flex align-items-center pt-4">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" value="1" name="grn_new_page" id="grn_new_page" {{ ($format?->grn_new_page ?? old('grn_new_page')) ? 'checked' : '' }}>
            <label class="form-check-label" for="grn_new_page">Print GRN on new page</label>
        </div>
    </div>
</div>

<hr>

<ul class="nav nav-tabs mb-3" id="formatTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="freight-tab" data-bs-toggle="tab" data-bs-target="#freight-fields" type="button" role="tab">Freight Table Columns</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="grn-tab" data-bs-toggle="tab" data-bs-target="#grn-fields" type="button" role="tab">GRN Table Columns</button>
    </li>
</ul>

<div class="tab-content" id="formatTabsContent">
    <div class="tab-pane fade show active" id="freight-fields" role="tabpanel">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h6 class="fw-bold mb-0">Select Fields to Show on Freight Invoice</h6>
                <p class="text-muted small mb-0 mt-1">Check the fields you want visible in the freight table.</p>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger" id="reset-freight-btn"><i class="bx bx-reset me-1"></i> Reset Fields</button>
        </div>
        <div class="field-checkbox-grid">
            @foreach($fieldGroups as $groupName => $fields)
                <h6 class="mt-3 mb-2 text-primary border-bottom pb-1">{{ $groupName }}</h6>
                <div class="row g-2 mb-3">
                    @foreach($fields as $fieldKey => $fieldLabel)
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input field-checkbox" type="checkbox"
                                    name="visible_fields[]" value="{{ $fieldKey }}"
                                    id="field_{{ $fieldKey }}"
                                    {{ in_array($fieldKey, $selectedFields) ? 'checked' : '' }}>
                                <label class="form-check-label" for="field_{{ $fieldKey }}">
                                    {{ $fieldLabel }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
        <input type="hidden" name="field_order" id="field_order_input" value="{{ json_encode($selectedOrder) }}">
    </div>
    
    <div class="tab-pane fade" id="grn-fields" role="tabpanel">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h6 class="fw-bold mb-0">Select Fields to Show on GRN Invoice</h6>
                <p class="text-muted small mb-0 mt-1">Check the fields you want visible in the GRN table.</p>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger" id="reset-grn-btn"><i class="bx bx-reset me-1"></i> Reset Fields</button>
        </div>
        <div class="field-checkbox-grid">
            @foreach($fieldGroups as $groupName => $fields)
                <h6 class="mt-3 mb-2 text-primary border-bottom pb-1">{{ $groupName }}</h6>
                <div class="row g-2 mb-3">
                    @foreach($fields as $fieldKey => $fieldLabel)
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input grn-field-checkbox" type="checkbox"
                                    name="grn_fields[]" value="{{ $fieldKey }}"
                                    id="grn_field_{{ $fieldKey }}"
                                    {{ in_array($fieldKey, $selectedGrnFields) ? 'checked' : '' }}>
                                <label class="form-check-label" for="grn_field_{{ $fieldKey }}">
                                    {{ $fieldLabel }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
        <input type="hidden" name="grn_field_order" id="grn_field_order_input" value="{{ json_encode($selectedGrnOrder) }}">
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let selectedOrder = {!! json_encode($selectedOrder) !!} || [];
    const orderInput = document.getElementById('field_order_input');

    document.querySelectorAll('.field-checkbox').forEach(cb => {
        if (cb.checked && !selectedOrder.includes(cb.value)) {
            selectedOrder.push(cb.value);
        }
    });
    orderInput.value = JSON.stringify(selectedOrder);

    document.querySelectorAll('.field-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            if (this.checked) {
                if (!selectedOrder.includes(this.value)) {
                    selectedOrder.push(this.value);
                }
            } else {
                selectedOrder = selectedOrder.filter(val => val !== this.value);
            }
            orderInput.value = JSON.stringify(selectedOrder);
        });
    });
    
    let selectedGrnOrder = {!! json_encode($selectedGrnOrder) !!} || [];
    const grnOrderInput = document.getElementById('grn_field_order_input');

    document.querySelectorAll('.grn-field-checkbox').forEach(cb => {
        if (cb.checked && !selectedGrnOrder.includes(cb.value)) {
            selectedGrnOrder.push(cb.value);
        }
    });
    grnOrderInput.value = JSON.stringify(selectedGrnOrder);

    document.querySelectorAll('.grn-field-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            if (this.checked) {
                if (!selectedGrnOrder.includes(this.value)) {
                    selectedGrnOrder.push(this.value);
                }
            } else {
                selectedGrnOrder = selectedGrnOrder.filter(val => val !== this.value);
            }
            grnOrderInput.value = JSON.stringify(selectedGrnOrder);
        });
    });

    document.getElementById('reset-freight-btn').addEventListener('click', function() {
        document.querySelectorAll('.field-checkbox').forEach(cb => cb.checked = false);
        selectedOrder = [];
        orderInput.value = '[]';
    });

    document.getElementById('reset-grn-btn').addEventListener('click', function() {
        document.querySelectorAll('.grn-field-checkbox').forEach(cb => cb.checked = false);
        selectedGrnOrder = [];
        grnOrderInput.value = '[]';
    });
});
</script>
