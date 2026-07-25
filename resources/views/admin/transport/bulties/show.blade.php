@extends('admin.layouts.app')

@section('style')
<style>
    :root {
        --premium-bg: #f8fafc;
        --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.04), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
        --accent-primary: #3b82f6;
        --accent-secondary: #6366f1;
        --text-main: #1e293b;
        --text-muted: #64748b;
        --glass-bg: rgba(255, 255, 255, 0.8);
    }



    /* Glass Card Style */
    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: 20px;
        box-shadow: var(--card-shadow);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
    }

    .glass-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.08);
    }



    .lr-badge {
        background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
        color: white;
        padding: 0.5rem 1.25rem;
        border-radius: 12px;
        font-weight: 700;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    /* Route Visualizer */
    .route-visualizer {
        position: relative;
        padding: 1.5rem 0;
    }

    .route-line {
        position: absolute;
        top: 44px;
        left: 9%;
        right: 9%;
        height: 3px;
        background: #e2e8f0;
        z-index: 1;
    }

    .route-progress {
        position: absolute;
        top: 44px;
        left: 9%;
        width: 0%;
        height: 3px;
        background: linear-gradient(90deg, var(--accent-primary), var(--accent-secondary));
        z-index: 2;
        transition: width 0.8s ease-in-out;
        border-radius: 2px;
    }

    .route-point {
        position: relative;
        z-index: 3;
        background: white;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid #e2e8f0;
        transition: all 0.3s ease;
        cursor: default;
    }

    .route-point.active {
        border-color: var(--accent-primary);
        background: #eff6ff;
        box-shadow: 0 0 15px rgba(59, 130, 246, 0.35);
    }

    .route-point i {
        font-size: 1.1rem;
    }

    /* Info Grouping */
    .info-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        font-weight: 700;
        color: var(--text-muted);
        letter-spacing: 1px;
        margin-bottom: 0.25rem;
    }

    .info-value {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-main);
    }

    /* Sidebar Components */
    .status-card {
        text-align: center;
        padding: 2rem;
        background: white;
    }

    .status-pulse {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
    }

    .billing-receipt {
        background: #f1f5f9;
        border-radius: 15px;
        padding: 1.5rem;
        position: relative;
    }

    .billing-receipt::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 0;
        right: 0;
        height: 10px;
        background-image: linear-gradient(-45deg, transparent 33.33%, #f1f5f9 33.33%, #f1f5f9 66.66%, transparent 66.66%),
                          linear-gradient(45deg, transparent 33.33%, #f1f5f9 33.33%, #f1f5f9 66.66%, transparent 66.66%);
        background-size: 20px 40px;
    }

    .action-btn {
        padding: 0.6rem 1.25rem;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .action-btn:hover {
        transform: translateY(-2px);
    }

    .party-icon {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-bottom: 1rem;
    }

    .consignor-theme { background: rgba(6, 46, 57, 0.1); color: #062E39; }
    .consignee-theme { background: rgba(253, 85, 35, 0.1); color: #FD5523; }

</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Premium Header -->
    <div class="glass-card mb-4 p-4" style="background: white;">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <span class="lr-badge">{{ $bulty->lr_no }}</span>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-muted small">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.transport.bulties.index') }}" class="text-muted small">Bilties</a></li>
                            <li class="breadcrumb-item active small" aria-current="page">{{ $bulty->lr_no }}</li>
                        </ol>
                    </nav>
                </div>
                <h2 class="fw-bold text-main mb-0">Bilty Details</h2>
            </div>
            <div class="col-md-6 mt-3 mt-md-0 text-md-end">
                <div class="d-flex gap-2 justify-content-md-end flex-wrap">
                    @php
                        $shareUrl = route('bilty.share', $bulty->share_token);
                        $driverPhone = $bulty->driver->phone ?? '';
                        $whatsappMsg = urlencode("Bilty Details: {$bulty->lr_no}\nFrom: " . ($bulty->originCity->name ?? 'N/A') . " To: " . ($bulty->destinationCity->name ?? 'N/A') . "\nView: {$shareUrl}");
                        $whatsappUrl = $driverPhone ? "https://wa.me/{$driverPhone}?text={$whatsappMsg}" : '#';
                    @endphp
                    
                    <button class="btn btn-light action-btn border" onclick="copyShareLink()">
                        <i class="bx bx-copy-alt me-1"></i> Copy Link
                    </button>

                    <button type="button" class="btn btn-light action-btn border" data-bs-toggle="modal" data-bs-target="#sendMailModal">
                        <i class="bx bx-envelope me-1"></i> Send Mail
                    </button>
                    
                    @if($driverPhone)
                    <a href="{{ $whatsappUrl }}" target="_blank" class="btn btn-success action-btn">
                        <i class="bx bxl-whatsapp me-1"></i> WhatsApp
                    </a>
                    @endif

                    <a href="{{ route('admin.transport.bulties.edit', $bulty->id) }}" class="btn btn-primary action-btn">
                        <i class="bx bx-edit-alt me-1"></i> Edit
                    </a>
                    @if(in_array($bulty->status, ['pending', 'planned']))
                    <button type="button" class="btn btn-outline-danger action-btn" onclick="handleReject({{ $bulty->id }}, '{{ $bulty->lr_no }}')">
                        <i class="bx bx-x-circle me-1"></i> Reject
                    </button>
                    @endif
                    <a href="{{ route('admin.transport.bulties.print-bill', $bulty->id) }}" target="_blank" class="btn btn-secondary action-btn">
                        <i class="bx bx-printer me-1"></i> Print Bill
                    </a>
                    <a href="{{ route('admin.transport.bulties.pdf', $bulty->id) }}" class="btn btn-danger action-btn">
                        <i class="bx bxs-file-pdf me-1"></i> PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
            <!-- Left Column: Main Info -->
            <div class="col-lg-8">
                
                <!-- 1. Route Timeline Card -->
                <div class="glass-card mb-4 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold m-0"><i class="bx bx-map-pin text-primary me-2"></i>Route Timeline</h5>
                        <span class="text-muted small fw-bold">{{ $bulty->lr_date ? date('D, d M Y', strtotime($bulty->lr_date)) : '-' }}</span>
                    </div>
                    
                    @php
                        $statusOrder = ['pending' => 0, 'planned' => 1, 'dispatched' => 2, 'partially_delivered' => 3, 'delivered' => 4];
                        $currentLevel = $statusOrder[$bulty->status] ?? 0;
                        $progressPercent = $currentLevel * 25;
                        $statusSteps = [
                            ['key' => 'pending', 'label' => 'Pending', 'icon' => 'bx bx-time'],
                            ['key' => 'planned', 'label' => 'Planned', 'icon' => 'bx bx-calendar-check'],
                            ['key' => 'dispatched', 'label' => 'Dispatched', 'icon' => 'bx bx-truck'],
                            ['key' => 'partially_delivered', 'label' => 'Partial Delivered', 'icon' => 'bx bx-package'],
                            ['key' => 'delivered', 'label' => 'Delivered', 'icon' => 'bx bx-check-double'],
                        ];
                    @endphp
                    <div class="route-visualizer px-2">
                        <div class="route-line"></div>
                        <div class="route-progress" style="width: {{ $progressPercent }}%"></div>
                        
                        <div class="d-flex justify-content-between align-items-center" style="position: relative;">
                            @foreach($statusSteps as $index => $step)
                            <div class="text-center" style="width: 18%;">
                                @php
                                    $isActive = $currentLevel >= $index;
                                    $isCurrent = $bulty->status === $step['key'];
                                @endphp
                                <div class="route-point {{ $isActive ? 'active' : '' }} mx-auto" style="{{ $isCurrent ? 'border-color: var(--accent-primary); transform: scale(1.15);' : '' }}">
                                    <i class="bx {{ $step['icon'] }} text-{{ $isActive ? 'primary' : 'muted' }}"></i>
                                </div>
                                <div class="mt-2">
                                    <div class="info-value small" style="font-size: 0.7rem; {{ $isActive ? 'color: var(--accent-primary);' : '' }}">
                                        {{ $step['label'] }}
                                    </div>
                                </div>
                                @if($index == 0 || $index == count($statusSteps) - 1)
                                <div class="mt-1">
                                    <div class="info-label" style="font-size: 0.6rem;">
                                        {{ $index == 0 ? $bulty->originCity->name ?? 'N/A' : $bulty->destinationCity->name ?? 'N/A' }}
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- 2. Party Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="glass-card p-4">
                            <div class="party-icon consignor-theme">
                                <i class="bx bx-user-voice"></i>
                            </div>
                            <h6 class="fw-bold mb-3">Consignor Details</h6>
                            <div class="mb-3">
                                <div class="info-value" style="font-size: 1.1rem;">{{ $bulty->consignor->name ?? '-' }}</div>
                                <div class="text-muted small"><i class="bx bx-phone me-1"></i> {{ $bulty->consignor->phone ?? '-' }}</div>
                            </div>
                            @if($bulty->consignor?->address)
                            <p class="text-muted small mb-0"><i class="bx bx-buildings me-1"></i> {{ $bulty->consignor->address }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="glass-card p-4">
                            <div class="party-icon consignee-theme">
                                <i class="bx bx-user-check"></i>
                            </div>
                            <h6 class="fw-bold mb-3">Consignee Details</h6>
                            <div class="mb-3">
                                <div class="info-value" style="font-size: 1.1rem;">{{ $bulty->consignee->name ?? '-' }}</div>
                                <div class="text-muted small"><i class="bx bx-phone me-1"></i> {{ $bulty->consignee->phone ?? '-' }}</div>
                            </div>
                            @if($bulty->consignee?->address)
                            <p class="text-muted small mb-0"><i class="bx bx-buildings me-1"></i> {{ $bulty->consignee->address }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- 3. Consignment & Driver Details -->
                <div class="glass-card mb-4">
                    <div class="p-4 border-bottom">
                        <h5 class="fw-bold m-0"><i class="bx bx-package text-primary me-2"></i>Consignment Information</h5>
                    </div>
                    <div class="p-4">
                        <div class="row g-4 mb-4">
                            <div class="col-md-4">
                                <div class="info-label">Items Description</div>
                                <div class="info-value">{{ $bulty->bultyItems->pluck('item_name')->filter()->implode(', ') ?: 'N/A' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-label">Total Quantity</div>
                                <div class="info-value">{{ $bulty->bultyItems->sum('articles') }} Articles</div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-label">Total Weight</div>
                                <div class="info-value">{{ number_format($bulty->bultyItems->sum('weight'), 2) }} {{ $bulty->bultyItems->first()?->unit ?? 'kg' }}</div>
                            </div>
                        </div>

                        <hr class="my-4 opacity-50">

                        <h6 class="fw-bold mb-3"><i class="bx bx-bus-school text-primary me-2"></i>Vehicle & Driver</h6>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="info-label">Vehicle Number</div>
                                <div class="info-value">{{ $bulty->vehicle->vehicle_number ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-label">Driver Name</div>
                                <div class="info-value">
                                    @if($bulty->driver)
                                        <a href="{{ route('admin.masters.drivers.edit', $bulty->driver_id) }}">{{ $bulty->driver->name }}</a>
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-label">Contact Number</div>
                                <div class="info-value">{{ $bulty->driver->phone ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3a. Reference & Invoice -->
                <div class="glass-card mb-4">
                    <div class="p-4 border-bottom">
                        <h5 class="fw-bold m-0"><i class="bx bx-receipt text-primary me-2"></i>Reference & Invoice</h5>
                    </div>
                    <div class="p-4">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="info-label">Order Number</div>
                                <div class="info-value">{{ $bulty->order_number ?? '-' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-label">Delivery Number</div>
                                <div class="info-value">{{ $bulty->delivery_number ?? '-' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-label">From No.</div>
                                <div class="info-value">{{ $bulty->from_no ?? '-' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-label">Invoice Number</div>
                                <div class="info-value">{{ $bulty->invoice_number ?? '-' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-label">Invoice Date</div>
                                <div class="info-value">{{ $bulty->invoice_date ? date('d M Y', strtotime($bulty->invoice_date)) : '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3b. E-Way Bill -->
                <div class="glass-card mb-4">
                    <div class="p-4 border-bottom">
                        <h5 class="fw-bold m-0"><i class="bx bx-qr text-primary me-2"></i>E-Way Bill</h5>
                    </div>
                    <div class="p-4">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="info-label">E-Way Bill No.</div>
                                <div class="info-value">{{ $bulty->eway_bill_no ?? '-' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-label">Generation Date</div>
                                <div class="info-value">{{ $bulty->generation_date ? date('d M Y', strtotime($bulty->generation_date)) : '-' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-label">Expiry Date</div>
                                <div class="info-value">{{ $bulty->expiry_date ? date('d M Y', strtotime($bulty->expiry_date)) : '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3c. Remark -->
                @if($bulty->remark)
                <div class="glass-card mb-4 p-4">
                    <h5 class="fw-bold mb-3"><i class="bx bx-notepad text-primary me-2"></i>Remark</h5>
                    <p class="text-muted mb-0" style="white-space: pre-wrap;">{{ $bulty->remark }}</p>
                </div>
                @endif
            </div>

            <!-- Right Column: Sidebar -->
            <div class="col-lg-4">
                
                <!-- 4. Status Card -->
                <div class="glass-card mb-4 status-card">
                    <div class="info-label mb-3">Live Status</div>
                    @php
                        $statusColors = [
                            'pending' => ['bg' => '#f1f5f9', 'text' => '#64748b'],
                            'planned' => ['bg' => '#eff6ff', 'text' => '#3b82f6'],
                            'dispatched' => ['bg' => '#fffbeb', 'text' => '#d97706'],
                            'in_transit' => ['bg' => '#f0f9ff', 'text' => '#0ea5e9'],
                            'partially_delivered' => ['bg' => '#fefce8', 'text' => '#a16207'],
                            'delivered' => ['bg' => '#f0fdf4', 'text' => '#16a34a'],
                            'rejected' => ['bg' => '#fef2f2', 'text' => '#dc2626']
                        ];
                        $s = $statusColors[$bulty->status] ?? $statusColors['pending'];
                    @endphp
                    <div class="d-inline-flex align-items-center px-4 py-2 rounded-pill mb-3" style="background: {{ $s['bg'] }}; color: {{ $s['text'] }}; border: 1px solid {{ $s['text'] }}33;">
                        <span class="status-pulse" style="background: {{ $s['text'] }};"></span>
                        <span class="fw-bold text-uppercase small">{{ str_replace('_', ' ', $bulty->status) }}</span>
                    </div>
                    <div class="text-muted small">Updated on {{ date('d M, H:i') }}</div>
                </div>

                <!-- 5. Documents Card -->
                <div class="glass-card mb-4 p-4">
                    <h6 class="fw-bold mb-4"><i class="bx bx-file-blank text-primary me-2"></i>Documents</h6>
                    
                    <!-- Material Document -->
                    <div class="d-flex align-items-center justify-content-between mb-3 p-3 rounded-3 bg-light border">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bx bx-receipt fs-4 text-muted"></i>
                            <div>
                                <div class="fw-bold small">Material Doc</div>
                                <div class="text-muted" style="font-size: 10px;">{{ $bulty->material_document ? 'Available' : 'Pending' }}</div>
                            </div>
                        </div>
                        @if($bulty->material_document)
                        <a href="{{ $bulty->material_document }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                        @else
                        <span class="badge bg-label-secondary small">N/A</span>
                        @endif
                    </div>

                    <!-- POD Document -->
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-light border mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bx bx-check-shield fs-4 text-muted"></i>
                            <div>
                                <div class="fw-bold small">POD Proof</div>
                                <div>
                                    @if($bulty->pod_document_status)
                                        <span class="badge bg-success bg-opacity-10 text-success small">Approved</span>
                                    @elseif($bulty->pod_document)
                                        <span class="badge bg-warning bg-opacity-10 text-warning small">Pending</span>
                                    @else
                                        <span class="text-muted" style="font-size: 10px;">Not Uploaded</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if($bulty->pod_document)
                        <a href="{{ $bulty->pod_document }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                        @else
                        <span class="badge bg-label-secondary small">N/A</span>
                        @endif
                    </div>

                    @if($bulty->pod_document && !$bulty->pod_document_status)
                    <div class="mb-3 d-flex gap-2">
                        <form action="{{ route('admin.transport.bulties.approve-pod', $bulty->id) }}" method="POST" class="flex-grow-1">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm w-100">Approve</button>
                        </form>
                        <form action="{{ route('admin.transport.bulties.reject-pod', $bulty->id) }}" method="POST" class="flex-grow-1">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100" onclick="return confirm('Reject POD? Driver will need to re-upload.')">Reject</button>
                        </form>
                    </div>
                    @endif

                    @if($bulty->material_document && !$bulty->material_document_status)
                    <div class="mt-3 d-flex gap-2">
                        <form action="{{ route('admin.transport.bulties.approve-document', $bulty->id) }}" method="POST" class="flex-grow-1">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm w-100">Approve</button>
                        </form>
                        <form action="{{ route('admin.transport.bulties.reject-document', $bulty->id) }}" method="POST" class="flex-grow-1">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100" onclick="return confirm('Reject?')">Reject</button>
                        </form>
                    </div>
                    @endif
                </div>

                <!-- 6. Billing Receipt -->
                <div class="glass-card p-4" style="overflow: visible;">
                    <h6 class="fw-bold mb-4"><i class="bx bx-wallet text-primary me-2"></i>Financial Summary</h6>
                    
                    <div class="billing-receipt mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Freight</span>
                            <span class="fw-bold">₹{{ number_format($bulty->freight_charges, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Bilty Commission</span>
                            <span class="fw-bold">₹{{ number_format($bulty->bilty_commission, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Tax (GST)</span>
                            <span class="fw-bold">₹{{ number_format($bulty->gst_amount, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Other Charges</span>
                            <span class="fw-bold">₹{{ number_format($bulty->other_charges, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted small">Advance Amount</span>
                            <span class="fw-bold" style="color:#dc2626;">-₹{{ number_format($bulty->advance_amount, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted small fw-bold">Remaining Amount</span>
                            <span class="fw-bold" style="color:#059669;">₹{{ number_format($bulty->remaining_amount, 2) }}</span>
                        </div>
                        <div class="border-top pt-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-main">Total Amount</span>
                            <h4 class="fw-bold text-primary mb-0">₹{{ number_format($bulty->total_amount, 2) }}</h4>
                        </div>
                    </div>

                    <div class="text-center mt-3">
                        <span class="badge w-100 py-2 fs-6 {{ $bulty->payment_type == 'paid' ? 'bg-label-success' : 'bg-label-warning' }}">
                            {{ strtoupper($bulty->payment_type) }} PAYMENT
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<form id="reject-form" method="POST" style="display: none;">@csrf</form>
<input type="hidden" id="shareUrl" value="{{ $shareUrl }}">

<!-- Send Mail Modal -->
<div class="modal fade" id="sendMailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bx bx-envelope me-2"></i>Send Bilty PDF</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="sendMailForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="recipientEmail" class="form-label fw-semibold">Email Address</label>
                        <input type="email" class="form-control" id="recipientEmail" name="email"
                               placeholder="Enter email address" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="sendMailBtn">
                        <span id="sendMailBtnText">Send</span>
                        <span id="sendMailBtnSpinner" class="spinner-border spinner-border-sm ms-1 d-none" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
function copyShareLink() {
    const shareUrl = document.getElementById('shareUrl').value;
    navigator.clipboard.writeText(shareUrl).then(() => {
        if (typeof setFlesh === 'function') {
            setFlesh('success', 'Link copied to clipboard!');
        } else {
            Swal.fire({ icon: 'success', title: 'Copied!', text: 'Share link copied to clipboard!', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
        }
    });
}

$(function () {
    $('#sendMailForm').on('submit', function (e) {
        e.preventDefault();
        var email = $('#recipientEmail').val().trim();
        if (!email) return;

        $('#sendMailBtn').prop('disabled', true);
        $('#sendMailBtnText').text('Sending...');
        $('#sendMailBtnSpinner').removeClass('d-none');

        $.ajax({
            url: '{{ route("admin.transport.bulties.send-mail", $bulty->id) }}',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ email: email }),
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function (data) {
                $('#sendMailModal').modal('hide');
                $('#recipientEmail').val('');
                setFlesh('success', data.message);
            },
            error: function (xhr) {
                var msg = xhr.responseJSON?.message || 'Failed to send email.';
                setFlesh('error', msg);
            },
            complete: function () {
                $('#sendMailBtn').prop('disabled', false);
                $('#sendMailBtnText').text('Send');
                $('#sendMailBtnSpinner').addClass('d-none');
            }
        });
    });
});

function handleReject(id, lrNo) {
    Swal.fire({
        title: 'Reject Bilty?',
        text: "Are you sure you want to reject Bilty '" + lrNo + "'? This will move it to Recycle Bin.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Reject it',
        cancelButtonText: 'Cancel',
        customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-secondary' },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('reject-form');
            form.action = "{{ url('admin/transport/bulties') }}/" + id + "/reject";
            form.submit();
        }
    })
}
</script>
@endsection

