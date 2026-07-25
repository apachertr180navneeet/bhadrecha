<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Bilty - {{ $bulty->lr_no }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>

    .bilty-toast {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: #059669;
        color: #fff;
        padding: 14px 28px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 15px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        z-index: 9999;
        display: none;
        animation: slideDown 0.4s ease;
    }
    @keyframes slideDown {
        from { opacity: 0; transform: translateX(-50%) translateY(-20px); }
        to { opacity: 1; transform: translateX(-50%) translateY(0); }
    }

    .bilty-container { max-width: 900px; margin: 40px auto; padding: 0 20px; }

    .bilty-header { background: linear-gradient(135deg, #062E39 0%, #0a4a5c 100%); color: #fff; padding: 30px; border-radius: 16px 16px 0 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }

    .bilty-header h1 { margin: 0; font-size: 24px; font-weight: 700; }

    .bilty-header .lr-badge { background: rgba(255,255,255,0.15); padding: 8px 20px; border-radius: 8px; font-size: 18px; font-weight: 700; letter-spacing: 1px; }

    .bilty-body { background: #fff; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 16px 16px; padding: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }

    .bilty-section { margin-bottom: 28px; }

    .bilty-section:last-child { margin-bottom: 0; }

    .bilty-section h5 { font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; font-weight: 600; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid #f3f4f6; }

    .bilty-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }

    .bilty-field label { display: block; font-size: 11px; text-transform: uppercase; color: #9ca3af; font-weight: 600; margin-bottom: 4px; }

    .bilty-field span { font-size: 15px; font-weight: 600; color: #1f2937; }

    .party-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

    @media (max-width: 640px) { .party-grid { grid-template-columns: 1fr; } }

    .party-card { background: #f9fafb; padding: 16px; border-radius: 12px; border-left: 4px solid #062E39; }

    .party-card.consignee { border-left-color: #FD5523; }

    .party-card h6 { margin: 0 0 8px; font-size: 13px; font-weight: 700; text-transform: uppercase; color: #374151; }

    .party-card .name { font-size: 16px; font-weight: 700; color: #111827; }

    .party-card .phone { font-size: 14px; color: #4b5563; margin-top: 4px; }

    .party-card .address { font-size: 13px; color: #6b7280; margin-top: 4px; }

    .items-table { width: 100%; border-collapse: collapse; font-size: 14px; }

    .items-table th { background: #f3f4f6; padding: 10px 12px; text-align: left; font-size: 12px; text-transform: uppercase; color: #6b7280; font-weight: 600; }

    .items-table td { padding: 10px 12px; border-bottom: 1px solid #f3f4f6; color: #374151; }

    .total-row { background: #f9fafb; font-weight: 700; }

    .total-row td { border-bottom: none; }

    .billing-summary { background: #f9fafb; border-radius: 12px; padding: 20px; }

    .billing-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px; }

    .billing-row.total { border-top: 2px solid #e5e7eb; margin-top: 8px; padding-top: 12px; font-size: 18px; font-weight: 700; color: #062E39; }

    .status-badge { display: inline-block; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; text-transform: capitalize; }

    .status-pending { background: #f3f4f6; color: #6b7280; }

    .status-planned { background: #dbeafe; color: #1d4ed8; }

    .status-dispatched { background: #fef3c7; color: #b45309; }

    .status-in_transit { background: #e0e7ff; color: #4338ca; }

    .status-delivered { background: #d1fae5; color: #059669; }

    .status-rejected { background: #fee2e2; color: #dc2626; }

</style>



<div class="bilty-container">

    <div class="bilty-header">

        <div>

            <h1>Bilty Details</h1>

            <div style="font-size:13px; opacity:0.8; margin-top:4px;">Transport Receipt</div>

        </div>

        <div style="display:flex; align-items:center; gap:12px;">
            @if($bulty->material_document && $bulty->material_document_status)
            <a href="{{ route('bilty.pdf', $bulty->share_token) }}" style="background:#fff; color:#062E39; padding:8px 16px; border-radius:8px; text-decoration:none; font-weight:600; font-size:14px; display:inline-flex; align-items:center; gap:6px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                PDF
            </a>
            @endif
            <div class="lr-badge">{{ $bulty->lr_no }}</div>
        </div>

    </div>



    <div class="bilty-body">

        @if(session('success'))

        <div style="margin-bottom:16px; padding:14px 20px; background:#d1fae5; border:1px solid #bbf7d0; border-radius:12px; color:#065f46; font-weight:600; text-align:center; font-size:15px;">{{ session('success') }}</div>

        @endif

        {{-- Route --}}

        <div class="bilty-section">

            <h5>Route Information</h5>

            <div class="bilty-grid">

                <div class="bilty-field">

                    <label>Origin</label>

                    <span>{{ $bulty->originCity->name ?? '-' }}</span>

                </div>

                <div class="bilty-field">

                    <label>Destination</label>

                    <span>{{ $bulty->destinationCity->name ?? '-' }}</span>

                </div>

                <div class="bilty-field">

                    <label>LR Date</label>

                    <span>{{ $bulty->lr_date ? date('d M Y', strtotime($bulty->lr_date)) : '-' }}</span>

                </div>

                <div class="bilty-field">

                    <label>Status</label>

                    <span class="status-badge status-{{ $bulty->status }}">{{ ucfirst(str_replace('_', ' ', $bulty->status)) }}</span>

                </div>

            </div>

        </div>



        {{-- Parties --}}

        <div class="bilty-section">

            <h5>Parties</h5>

            <div class="party-grid">

                <div class="party-card">

                    <h6>Consignor (Sender)</h6>

                    <div class="name">{{ $bulty->consignor->name ?? '-' }}</div>

                    <div class="phone"><i class="fas fa-phone"></i> {{ $bulty->consignor->phone ?? '-' }}</div>

                    @if($bulty->consignor?->address)

                    <div class="address">{{ $bulty->consignor->address }}</div>

                    @endif

                </div>

                <div class="party-card consignee">

                    <h6>Consignee (Receiver)</h6>

                    <div class="name">{{ $bulty->consignee->name ?? '-' }}</div>

                    <div class="phone"><i class="fas fa-phone"></i> {{ $bulty->consignee->phone ?? '-' }}</div>

                    @if($bulty->consignee?->address)

                    <div class="address">{{ $bulty->consignee->address }}</div>

                    @endif

                </div>

            </div>

        </div>



        {{-- Items --}}

        @if($bulty->bultyItems->isNotEmpty())

        <div class="bilty-section">

            <h5>Items / Goods</h5>

            <table class="items-table">

                <thead>

                    <tr>

                        <th>Item</th>

                        <th>Packaging</th>

                        <th>Articles</th>

                        <th>Weight</th>

                        <th>Amount</th>

                    </tr>

                </thead>

                <tbody>

                    @foreach($bulty->bultyItems as $item)

                    <tr>

                        <td>{{ $item->item_name ?? 'N/A' }}</td>

                        <td>{{ $item->packaging_type ?? '-' }}</td>

                        <td>{{ $item->articles }}</td>

                        <td>{{ number_format($item->weight, 2) }} {{ $item->unit ?? 'kg' }}</td>

                        <td>₹{{ number_format($item->amount, 2) }}</td>

                    </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

        @endif



        {{-- Driver & Vehicle --}}

        <div class="bilty-section">

            <h5>Vehicle & Driver</h5>

            <div class="bilty-grid">

                <div class="bilty-field">

                    <label>Vehicle Number</label>

                    <span>
                        @if($bulty->vehicle)
                            <a href="{{ route('admin.masters.vehicles.edit', $bulty->vehicle_id) }}">{{ $bulty->vehicle->vehicle_number }}</a>
                        @else
                            N/A
                        @endif
                    </span>

                </div>

                <div class="bilty-field">

                    <label>Vehicle Type</label>

                    <span>{{ $bulty->vehicle->vehicle_type ?? 'N/A' }}</span>

                </div>

                <div class="bilty-field">

                    <label>Driver Name</label>

                    <span>
                        @if($bulty->driver)
                            <a href="{{ route('admin.masters.drivers.edit', $bulty->driver_id) }}">{{ $bulty->driver->name }}</a>
                        @else
                            N/A
                        @endif
                    </span>

                </div>

                <div class="bilty-field">

                    <label>Driver Mobile</label>

                    <span>{{ $bulty->driver->phone ?? 'N/A' }}</span>

                </div>

            </div>

        </div>



        {{-- Reference & Invoice --}}

        <div class="bilty-section">

            <h5>Reference & Invoice</h5>

            <div class="bilty-grid">

                <div class="bilty-field">

                    <label>Order Number</label>

                    <span>{{ $bulty->order_number ?? '-' }}</span>

                </div>

                <div class="bilty-field">

                    <label>Delivery Number</label>

                    <span>{{ $bulty->delivery_number ?? '-' }}</span>

                </div>

                <div class="bilty-field">

                    <label>From No.</label>

                    <span>{{ $bulty->from_no ?? '-' }}</span>

                </div>

                <div class="bilty-field">

                    <label>Invoice Number</label>

                    <span>{{ $bulty->invoice_number ?? '-' }}</span>

                </div>

                <div class="bilty-field">

                    <label>Invoice Date</label>

                    <span>{{ $bulty->invoice_date ? date('d M Y', strtotime($bulty->invoice_date)) : '-' }}</span>

                </div>

            </div>

        </div>



        {{-- E-Way Bill --}}

        <div class="bilty-section">

            <h5>E-Way Bill</h5>

            <div class="bilty-grid">

                <div class="bilty-field">

                    <label>E-Way Bill No.</label>

                    <span>{{ $bulty->eway_bill_no ?? '-' }}</span>

                </div>

                <div class="bilty-field">

                    <label>Generation Date</label>

                    <span>{{ $bulty->generation_date ? date('d M Y', strtotime($bulty->generation_date)) : '-' }}</span>

                </div>

                <div class="bilty-field">

                    <label>Expiry Date</label>

                    <span>{{ $bulty->expiry_date ? date('d M Y', strtotime($bulty->expiry_date)) : '-' }}</span>

                </div>

            </div>

        </div>



        {{-- Remark --}}

        @if($bulty->remark)

        <div class="bilty-section">

            <h5>Remark</h5>

            <p style="font-size:14px; color:#374151; white-space:pre-wrap;">{{ $bulty->remark }}</p>

        </div>

        @endif



        {{-- Material Document --}}

        <div class="bilty-section">

            <h5>Material Document</h5>

            @if($bulty->material_document && $bulty->material_document_status)

                <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px; padding:16px; margin-bottom:12px; display:flex; align-items:center; gap:12px;">

                    <span style="font-size:24px;">&#9989;</span>

                    <div>

                        <div style="font-weight:600; color:#166534;">Document Approved</div>

                        <a href="{{ $bulty->material_document }}" target="_blank" style="font-size:14px; color:#2563eb; text-decoration:underline;">View Document</a>

                    </div>

                </div>

            @elseif($bulty->material_document && !$bulty->material_document_status)

                <div style="background:#fefce8; border:1px solid #fde68a; border-radius:12px; padding:16px; margin-bottom:12px; display:flex; align-items:center; gap:12px;">

                    <span style="font-size:24px;">&#128196;</span>

                    <div>

                        <div style="font-weight:600; color:#92400e;">File Uploaded</div>

                        <div style="font-size:14px; color:#b45309;">Waiting for admin approval</div>

                    </div>

                </div>

            @else

                <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:12px; padding:16px; margin-bottom:12px; display:flex; align-items:center; gap:12px;">

                    <span style="font-size:24px;">&#10060;</span>

                    <div>

                        <div style="font-weight:600; color:#991b1b;">Document Not Uploaded</div>

                        <div style="font-size:14px; color:#6b7280;">Please upload the material document below.</div>

                    </div>

                </div>

            @endif

            @if(!$bulty->material_document_status)

            <form method="POST" action="{{ route('bilty.upload-document', $bulty->share_token) }}" enctype="multipart/form-data" style="background:#f9fafb; border:2px dashed #d1d5db; border-radius:12px; padding:24px; text-align:center;">

                @csrf

                <div style="margin-bottom:12px;">

                    <label for="material_document" style="display:inline-block; padding:10px 24px; background:#062E39; color:#fff; border-radius:8px; cursor:pointer; font-weight:600; font-size:14px;">

                        Choose File

                    </label>

                    <input type="file" name="material_document" id="material_document" accept=".jpg,.jpeg,.png,.pdf" required style="display:none;" onchange="document.getElementById('file_name').textContent = this.files[0]?.name || 'No file selected'">

                    <span id="file_name" style="display:block; margin-top:8px; font-size:13px; color:#6b7280;">No file selected</span>

                </div>

                <div style="font-size:12px; color:#9ca3af; margin-bottom:12px;">Accepted: JPG, PNG, PDF (Max 10MB)</div>

                <button type="submit" style="padding:10px 32px; background:#059669; color:#fff; border:none; border-radius:8px; font-weight:600; cursor:pointer; font-size:14px;">Upload Document</button>

            </form>

            @endif

        </div>



        {{-- POD Document --}}

        <div class="bilty-section">

            <h5>POD (Proof of Delivery)</h5>

            @if($bulty->pod_document && $bulty->pod_document_status)

                <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px; padding:16px; margin-bottom:12px; display:flex; align-items:center; gap:12px;">

                    <span style="font-size:24px;">&#9989;</span>

                    <div>

                        <div style="font-weight:600; color:#166534;">POD Approved</div>

                        <a href="{{ $bulty->pod_document }}" target="_blank" style="font-size:14px; color:#2563eb; text-decoration:underline;">View POD</a>

                    </div>

                </div>

            @elseif($bulty->pod_document && !$bulty->pod_document_status)

                <div style="background:#fefce8; border:1px solid #fde68a; border-radius:12px; padding:16px; margin-bottom:12px; display:flex; align-items:center; gap:12px;">

                    <span style="font-size:24px;">&#128196;</span>

                    <div>

                        <div style="font-weight:600; color:#92400e;">POD Uploaded</div>

                        <div style="font-size:14px; color:#b45309;">Waiting for admin approval</div>

                        <a href="{{ $bulty->pod_document }}" target="_blank" style="font-size:14px; color:#2563eb; text-decoration:underline;">View Uploaded POD</a>

                    </div>

                </div>

            @else

                <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:12px; padding:16px; margin-bottom:12px; display:flex; align-items:center; gap:12px;">

                    <span style="font-size:24px;">&#10060;</span>

                    <div>

                        <div style="font-weight:600; color:#991b1b;">POD Not Uploaded</div>

                        <div style="font-size:14px; color:#6b7280;">Please upload the POD document below.</div>

                    </div>

                </div>

            @endif

            @if(!$bulty->pod_document_status)

            <form method="POST" action="{{ route('bilty.upload-pod', $bulty->share_token) }}" enctype="multipart/form-data" style="background:#f9fafb; border:2px dashed #d1d5db; border-radius:12px; padding:24px; text-align:center;">

                @csrf

                <div style="margin-bottom:12px;">

                    <label for="pod_file" style="display:inline-block; padding:10px 24px; background:#062E39; color:#fff; border-radius:8px; cursor:pointer; font-weight:600; font-size:14px;">

                        Choose File

                    </label>

                    <input type="file" name="pod_file" id="pod_file" accept=".jpg,.jpeg,.png,.pdf" required style="display:none;" onchange="document.getElementById('pod_file_name').textContent = this.files[0]?.name || 'No file selected'">

                    <span id="pod_file_name" style="display:block; margin-top:8px; font-size:13px; color:#6b7280;">No file selected</span>

                </div>

                <div style="font-size:12px; color:#9ca3af; margin-bottom:12px;">Accepted: JPG, PNG, PDF (Max 5MB)</div>

                <button type="submit" style="padding:10px 32px; background:#059669; color:#fff; border:none; border-radius:8px; font-weight:600; cursor:pointer; font-size:14px;">Upload POD</button>

            </form>

            @endif

        </div>




    </div>

</div>

<div class="bilty-toast" id="viewToast">Document opened successfully</div>

<script>
    document.querySelectorAll('.bilty-section a[target="_blank"]').forEach(function(link) {
        link.addEventListener('click', function() {
            var toast = document.getElementById('viewToast');
            toast.style.display = 'block';
            toast.textContent = 'Opening ' + (this.textContent.trim() || 'document') + '...';
            setTimeout(function() {
                toast.style.display = 'none';
            }, 3000);
        });
    });
</script>

</body>

</html>

