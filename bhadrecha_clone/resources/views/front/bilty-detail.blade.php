<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>बिल्टी विवरण / Bilty - {{ $bulty->lr_no }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    :root {
        --primary: #062E39;
        --primary-light: #0a4a5c;
        --secondary: #FD5523;
        --success: #059669;
        --success-bg: #ecfdf5;
        --warning: #d97706;
        --warning-bg: #fffbeb;
        --danger: #dc2626;
        --danger-bg: #fef2f2;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-900: #111827;
    }

    * { box-sizing: border-box; }
    body {
        margin: 0;
        padding: 0;
        font-family: 'Noto Sans Devanagari', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        background: #f0f4f8;
        color: var(--gray-900);
        line-height: 1.5;
    }

    .bilty-container {
        max-width: 920px;
        margin: 24px auto 40px;
        padding: 0 16px;
    }

    /* Top Bar with Language Selector */
    .top-lang-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        padding: 6px 12px;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    }
    .lang-toggle-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #062E39;
        color: #ffffff;
        border: none;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
    }
    .lang-toggle-btn:hover {
        background: #0a4a5c;
    }
    .driver-welcome {
        font-size: 13px;
        font-weight: 600;
        color: var(--gray-600);
    }

    /* Header */
    .bilty-header {
        background: linear-gradient(135deg, #062E39 0%, #0a4a5c 100%);
        color: #fff;
        padding: 24px 28px;
        border-radius: 16px 16px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }
    .bilty-header h1 {
        margin: 0;
        font-size: 22px;
        font-weight: 800;
        letter-spacing: -0.02em;
    }
    .bilty-header .sub-title {
        font-size: 13px;
        opacity: 0.85;
        margin-top: 2px;
    }
    .lr-badge-container {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .lr-badge {
        background: rgba(255,255,255,0.18);
        border: 1px solid rgba(255,255,255,0.25);
        padding: 6px 16px;
        border-radius: 8px;
        font-size: 17px;
        font-weight: 800;
        letter-spacing: 0.5px;
    }
    .pdf-download-btn {
        background: #ffffff;
        color: #062E39;
        padding: 7px 14px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 700;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .pdf-download-btn:hover {
        background: #f3f4f6;
    }

    /* Body */
    .bilty-body {
        background: #fff;
        border: 1px solid var(--gray-200);
        border-top: none;
        border-radius: 0 0 16px 16px;
        padding: 24px 28px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.05);
    }

    /* Section Cards */
    .bilty-section {
        margin-bottom: 24px;
        background: #ffffff;
        border: 1px solid #edf2f7;
        border-radius: 12px;
        padding: 18px 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .bilty-section:last-child { margin-bottom: 0; }
    .bilty-section h5 {
        margin: 0 0 14px 0;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--primary);
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 8px;
        padding-bottom: 8px;
        border-bottom: 2px solid #f1f5f9;
    }

    /* Grid Fields */
    .bilty-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 14px;
    }
    .bilty-field {
        background: var(--gray-50);
        padding: 10px 14px;
        border-radius: 8px;
        border: 1px solid #edf2f7;
    }
    .bilty-field label {
        display: block;
        font-size: 11px;
        text-transform: uppercase;
        color: #64748b;
        font-weight: 700;
        margin-bottom: 3px;
    }
    .bilty-field span {
        font-size: 14px;
        font-weight: 700;
        color: var(--gray-900);
        word-break: break-word;
    }

    /* Status Badges */
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .status-pending { background: #f1f5f9; color: #475569; }
    .status-planned { background: #dbeafe; color: #1e40af; }
    .status-dispatched { background: #fef3c7; color: #92400e; }
    .status-in_transit { background: #e0e7ff; color: #3730a3; }
    .status-partially_delivered { background: #ffedd5; color: #9a3412; }
    .status-delivered { background: #dcfce7; color: #166534; }
    .status-rejected { background: #fee2e2; color: #991b1b; }

    /* Parties Grid */
    .party-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    @media (max-width: 640px) {
        .party-grid { grid-template-columns: 1fr; }
        .bilty-header h1 { font-size: 18px; }
        .bilty-body { padding: 16px; }
    }
    .party-card {
        background: var(--gray-50);
        padding: 14px 16px;
        border-radius: 10px;
        border-left: 4px solid var(--primary);
        border-top: 1px solid #edf2f7;
        border-right: 1px solid #edf2f7;
        border-bottom: 1px solid #edf2f7;
    }
    .party-card.consignee { border-left-color: var(--secondary); }
    .party-card h6 {
        margin: 0 0 6px;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        color: #64748b;
    }
    .party-card .name { font-size: 15px; font-weight: 700; color: var(--gray-900); }
    .party-card .phone {
        font-size: 13px;
        color: var(--gray-700);
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .party-card .phone a {
        color: var(--primary);
        text-decoration: none;
        font-weight: 600;
    }
    .party-card .phone a:hover { text-decoration: underline; }
    .party-card .address { font-size: 12px; color: #64748b; margin-top: 4px; }

    /* Items Table */
    .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .items-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        margin-top: 6px;
    }
    .items-table th {
        background: #f8fafc;
        padding: 10px 12px;
        text-align: left;
        font-size: 11px;
        text-transform: uppercase;
        color: #475569;
        font-weight: 700;
        border-bottom: 2px solid #e2e8f0;
    }
    .items-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
        color: var(--gray-700);
        font-weight: 500;
    }
    .items-table tr:last-child td { border-bottom: none; }

    /* Alerts */
    .alert-box {
        padding: 12px 16px;
        border-radius: 10px;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 14px;
    }
    .alert-success { background: var(--success-bg); border: 1px solid #a7f3d0; color: #065f46; }
    .alert-warning { background: var(--warning-bg); border: 1px solid #fde68a; color: #92400e; }
    .alert-danger { background: var(--danger-bg); border: 1px solid #fecaca; color: #991b1b; }

    /* Multiple Photo Upload Box */
    .upload-card {
        background: #f8fafc;
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        transition: all 0.2s;
    }
    .upload-card:hover {
        border-color: var(--primary);
        background: #f1f5f9;
    }
    .upload-btn-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 24px;
        background: #062E39;
        color: #fff;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 700;
        font-size: 14px;
        transition: background 0.2s;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .upload-btn-label:hover { background: #0a4a5c; }
    .submit-upload-btn {
        padding: 10px 32px;
        background: #059669;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 2px 6px rgba(5, 150, 105, 0.25);
    }
    .submit-upload-btn:hover { background: #047857; }

    /* Selected Preview Gallery */
    .preview-container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
        margin-top: 14px;
    }
    .preview-item {
        position: relative;
        width: 80px;
        height: 80px;
        border-radius: 8px;
        overflow: hidden;
        border: 2px solid #e2e8f0;
        background: #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    }
    .preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .preview-item .file-icon-badge {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        color: #475569;
        background: #f1f5f9;
        font-weight: 700;
    }
    .preview-item .file-icon-badge i { font-size: 24px; color: #dc2626; margin-bottom: 4px; }
    .photo-count-badge {
        display: inline-block;
        background: #dbeafe;
        color: #1e40af;
        font-weight: 700;
        font-size: 12px;
        padding: 4px 10px;
        border-radius: 12px;
        margin-top: 8px;
    }

    /* Uploaded Gallery Grid */
    .uploaded-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 12px;
        margin-top: 10px;
    }
    .gallery-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 5px rgba(0,0,0,0.04);
        transition: transform 0.2s;
    }
    .gallery-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }
    .gallery-card-thumb {
        height: 100px;
        width: 100%;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        cursor: pointer;
    }
    .gallery-card-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .gallery-card-thumb .pdf-view-icon {
        font-size: 32px;
        color: #dc2626;
    }
    .gallery-card-body {
        padding: 8px 10px;
        text-align: center;
    }
    .gallery-card-body a {
        font-size: 12px;
        font-weight: 700;
        color: #2563eb;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .gallery-card-body a:hover { text-decoration: underline; }

    /* Lightbox Modal */
    .lightbox-modal {
        display: none;
        position: fixed;
        z-index: 99999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.88);
        align-items: center;
        justify-content: center;
        padding: 20px;
        box-sizing: border-box;
    }
    .lightbox-content {
        max-width: 90%;
        max-height: 90vh;
        border-radius: 8px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        object-fit: contain;
    }
    .lightbox-close {
        position: absolute;
        top: 20px;
        right: 25px;
        color: #fff;
        font-size: 35px;
        font-weight: bold;
        cursor: pointer;
        background: rgba(0,0,0,0.5);
        border-radius: 50%;
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
</head>

<body>

@php
    $statusHindi = [
        'pending' => 'लंबित',
        'planned' => 'योजनाबद्ध',
        'dispatched' => 'गाड़ी रवाना (डिस्पैच्ड)',
        'in_transit' => 'मार्ग में (रास्ते में)',
        'partially_delivered' => 'आंशिक रूप से डिलीवर',
        'delivered' => 'डिलीवर हो गया',
        'rejected' => 'अस्वीकृत',
    ];

    $materialDocsList = $bulty->material_documents_list;
    $podDocsList = $bulty->pod_documents_list;
@endphp

<div class="bilty-container">

    <!-- Top Language & Driver Bar -->
    <div class="top-lang-bar">
        <div class="driver-welcome">
            <i class="fa-solid fa-truck text-primary me-1"></i>
            <span class="lang-text" data-hi="ड्राइवर बिल्टी पोर्टल" data-en="Driver Bilty Portal">ड्राइवर बिल्टी पोर्टल</span>
        </div>
        <button type="button" class="lang-toggle-btn" id="langToggleBtn" onclick="toggleLanguage()">
            <i class="fa-solid fa-language"></i>
            <span id="langBtnText">English</span>
        </button>
    </div>

    <!-- Header Section -->
    <div class="bilty-header">
        <div>
            <h1>
                <span class="lang-text" data-hi="बिल्टी विवरण" data-en="Bilty Details">बिल्टी विवरण</span>
            </h1>
            <div class="sub-title">
                <span class="lang-text" data-hi="ट्रांसपोर्ट रसीद व गाड़ी पर्ची" data-en="Transport Receipt & Goods Consignment">ट्रांसपोर्ट रसीद व गाड़ी पर्ची</span>
            </div>
        </div>

        <div class="lr-badge-container">
            @if(!empty($materialDocsList) && $bulty->material_document_status)
            <a href="{{ route('bilty.pdf', $bulty->share_token) }}" class="pdf-download-btn" title="Download PDF">
                <i class="fa-solid fa-file-pdf text-danger"></i> PDF
            </a>
            @endif
            <div class="lr-badge">{{ $bulty->lr_no }}</div>
        </div>
    </div>

    <div class="bilty-body">

        @if(session('success'))
        <div class="alert-box alert-success">
            <i class="fa-solid fa-circle-check fs-4"></i>
            <div>{{ session('success') }}</div>
        </div>
        @endif

        @if(session('error'))
        <div class="alert-box alert-danger">
            <i class="fa-solid fa-triangle-exclamation fs-4"></i>
            <div>{{ session('error') }}</div>
        </div>
        @endif

        <!-- 1. Route Information -->
        <div class="bilty-section">
            <h5>
                <i class="fa-solid fa-route"></i>
                <span class="lang-text" data-hi="रूट की जानकारी" data-en="Route Information">रूट की जानकारी</span>
            </h5>
            <div class="bilty-grid">
                <div class="bilty-field">
                    <label class="lang-text" data-hi="कहाँ से (शुरुआत)" data-en="Origin (From)">कहाँ से (शुरुआत)</label>
                    <span>{{ $bulty->originCity->name ?? '-' }}</span>
                </div>
                <div class="bilty-field">
                    <label class="lang-text" data-hi="कहाँ तक (गंतव्य)" data-en="Destination (To)">कहाँ तक (गंतव्य)</label>
                    <span>{{ $bulty->destinationCity->name ?? '-' }}</span>
                </div>
                <div class="bilty-field">
                    <label class="lang-text" data-hi="बिल्टी दिनांक" data-en="LR Date">बिल्टी दिनांक</label>
                    <span>{{ $bulty->lr_date ? date('d M Y', strtotime($bulty->lr_date)) : '-' }}</span>
                </div>
                <div class="bilty-field">
                    <label class="lang-text" data-hi="बिल्टी स्थिति" data-en="Status">बिल्टी स्थिति</label>
                    <div>
                        <span class="status-badge status-{{ $bulty->status }}">
                            <span class="lang-text" data-hi="{{ $statusHindi[$bulty->status] ?? ucfirst(str_replace('_', ' ', $bulty->status)) }}" data-en="{{ ucfirst(str_replace('_', ' ', $bulty->status)) }}">
                                {{ $statusHindi[$bulty->status] ?? ucfirst(str_replace('_', ' ', $bulty->status)) }}
                            </span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Parties Information -->
        <div class="bilty-section">
            <h5>
                <i class="fa-solid fa-users"></i>
                <span class="lang-text" data-hi="पार्टियों का विवरण" data-en="Parties Details">पार्टियों का विवरण</span>
            </h5>
            <div class="party-grid">
                <!-- Consignor / Sender -->
                <div class="party-card">
                    <h6>
                        <span class="lang-text" data-hi="कंसाइनर (माल भेजने वाला)" data-en="Consignor (Sender)">कंसाइनर (माल भेजने वाला)</span>
                    </h6>
                    <div class="name">{{ $bulty->consignor->name ?? '-' }}</div>
                    @if($bulty->consignor?->phone)
                    <div class="phone">
                        <i class="fa-solid fa-phone text-primary"></i>
                        <a href="tel:{{ $bulty->consignor->phone }}">{{ $bulty->consignor->phone }}</a>
                    </div>
                    @endif
                    @if($bulty->consignor?->address)
                    <div class="address"><i class="fa-solid fa-location-dot me-1"></i>{{ $bulty->consignor->address }}</div>
                    @endif
                </div>

                <!-- Consignee / Receiver -->
                <div class="party-card consignee">
                    <h6>
                        <span class="lang-text" data-hi="कंसाइनी (माल पाने वाला)" data-en="Consignee (Receiver)">कंसाइनी (माल पाने वाला)</span>
                    </h6>
                    <div class="name">{{ $bulty->consignee->name ?? '-' }}</div>
                    @if($bulty->consignee?->phone)
                    <div class="phone">
                        <i class="fa-solid fa-phone text-secondary"></i>
                        <a href="tel:{{ $bulty->consignee->phone }}">{{ $bulty->consignee->phone }}</a>
                    </div>
                    @endif
                    @if($bulty->consignee?->address)
                    <div class="address"><i class="fa-solid fa-location-dot me-1"></i>{{ $bulty->consignee->address }}</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 3. Goods / Items Details -->
        @if($bulty->bultyItems->isNotEmpty())
        <div class="bilty-section">
            <h5>
                <i class="fa-solid fa-boxes-stacked"></i>
                <span class="lang-text" data-hi="सामान / माल का विवरण" data-en="Goods & Items Details">सामान / माल का विवरण</span>
            </h5>
            <div class="table-responsive">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th><span class="lang-text" data-hi="सामान का नाम" data-en="Item Name">सामान का नाम</span></th>
                            <th><span class="lang-text" data-hi="पैकिंग प्रकार" data-en="Packaging">पैकिंग प्रकार</span></th>
                            <th><span class="lang-text" data-hi="नग / मात्रा" data-en="Articles">नग / मात्रा</span></th>
                            <th><span class="lang-text" data-hi="वजन" data-en="Weight">वजन</span></th>
                            <th><span class="lang-text" data-hi="राशि (₹)" data-en="Amount (₹)">राशि (₹)</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bulty->bultyItems as $item)
                        <tr>
                            <td><strong>{{ $item->item_name ?? 'N/A' }}</strong></td>
                            <td>{{ $item->packaging_type ?? '-' }}</td>
                            <td>{{ $item->articles }}</td>
                            <td>{{ number_format($item->weight, 2) }} {{ $item->unit ?? 'kg' }}</td>
                            <td>₹{{ number_format($item->amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- 4. Vehicle & Driver Details -->
        <div class="bilty-section">
            <h5>
                <i class="fa-solid fa-truck-front"></i>
                <span class="lang-text" data-hi="गाड़ी और ड्राइवर" data-en="Vehicle & Driver">गाड़ी और ड्राइवर</span>
            </h5>
            <div class="bilty-grid">
                <div class="bilty-field">
                    <label class="lang-text" data-hi="गाड़ी नंबर" data-en="Vehicle Number">गाड़ी नंबर</label>
                    <span>{{ $bulty->vehicle->vehicle_number ?? 'N/A' }}</span>
                </div>
                <div class="bilty-field">
                    <label class="lang-text" data-hi="गाड़ी का प्रकार" data-en="Vehicle Type">गाड़ी का प्रकार</label>
                    <span>{{ $bulty->vehicle->vehicle_type ?? 'N/A' }}</span>
                </div>
                <div class="bilty-field">
                    <label class="lang-text" data-hi="ड्राइवर का नाम" data-en="Driver Name">ड्राइवर का नाम</label>
                    <span>{{ $bulty->driver->name ?? 'N/A' }}</span>
                </div>
                <div class="bilty-field">
                    <label class="lang-text" data-hi="ड्राइवर मोबाइल नंबर" data-en="Driver Mobile">ड्राइवर मोबाइल नंबर</label>
                    <span>
                        @if($bulty->driver?->phone)
                            <a href="tel:{{ $bulty->driver->phone }}" style="color:var(--primary); text-decoration:none; font-weight:700;">
                                <i class="fa-solid fa-phone me-1"></i>{{ $bulty->driver->phone }}
                            </a>
                        @else
                            N/A
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- 5. Reference & Invoice -->
        <div class="bilty-section">
            <h5>
                <i class="fa-solid fa-file-invoice"></i>
                <span class="lang-text" data-hi="संदर्भ और चालान" data-en="Reference & Invoice">संदर्भ और चालान</span>
            </h5>
            <div class="bilty-grid">
                <div class="bilty-field">
                    <label class="lang-text" data-hi="ऑर्डर नंबर" data-en="Order Number">ऑर्डर नंबर</label>
                    <span>{{ $bulty->order_number ?? '-' }}</span>
                </div>
                <div class="bilty-field">
                    <label class="lang-text" data-hi="डिलीवरी नंबर" data-en="Delivery Number">डिलीवरी नंबर</label>
                    <span>{{ $bulty->delivery_number ?? '-' }}</span>
                </div>
                <div class="bilty-field">
                    <label class="lang-text" data-hi="फॉर्म नंबर" data-en="From No.">फॉर्म नंबर</label>
                    <span>{{ $bulty->from_no ?? '-' }}</span>
                </div>
                <div class="bilty-field">
                    <label class="lang-text" data-hi="चालान / इनवॉइस नंबर" data-en="Invoice Number">चालान / इनवॉइस नंबर</label>
                    <span>{{ $bulty->invoice_number ?? '-' }}</span>
                </div>
                <div class="bilty-field">
                    <label class="lang-text" data-hi="चालान दिनांक" data-en="Invoice Date">चालान दिनांक</label>
                    <span>{{ $bulty->invoice_date ? date('d M Y', strtotime($bulty->invoice_date)) : '-' }}</span>
                </div>
            </div>
        </div>

        <!-- 6. E-Way Bill -->
        @if($bulty->eway_bill_no)
        <div class="bilty-section">
            <h5>
                <i class="fa-solid fa-receipt"></i>
                <span class="lang-text" data-hi="ई-वे बिल विवरण" data-en="E-Way Bill Details">ई-वे बिल विवरण</span>
            </h5>
            <div class="bilty-grid">
                <div class="bilty-field">
                    <label class="lang-text" data-hi="ई-वे बिल नंबर" data-en="E-Way Bill No.">ई-वे बिल नंबर</label>
                    <span>{{ $bulty->eway_bill_no ?? '-' }}</span>
                </div>
                <div class="bilty-field">
                    <label class="lang-text" data-hi="जारी करने की तारीख" data-en="Generation Date">जारी करने की तारीख</label>
                    <span>{{ $bulty->generation_date ? date('d M Y', strtotime($bulty->generation_date)) : '-' }}</span>
                </div>
                <div class="bilty-field">
                    <label class="lang-text" data-hi="समाप्ति की तारीख" data-en="Expiry Date">समाप्ति की तारीख</label>
                    <span>{{ $bulty->expiry_date ? date('d M Y', strtotime($bulty->expiry_date)) : '-' }}</span>
                </div>
            </div>
        </div>
        @endif

        <!-- 7. Remark -->
        @if($bulty->remark)
        <div class="bilty-section">
            <h5>
                <i class="fa-solid fa-note-sticky"></i>
                <span class="lang-text" data-hi="टिप्पणी / रिमार्क" data-en="Remark">टिप्पणी / रिमार्क</span>
            </h5>
            <p style="font-size:14px; color:var(--gray-700); margin:0; white-space:pre-wrap;">{{ $bulty->remark }}</p>
        </div>
        @endif

        <!-- 8. Material Document(s) Section (Multiple Photo Upload) -->
        <div class="bilty-section">
            <h5>
                <i class="fa-solid fa-images"></i>
                <span class="lang-text" data-hi="सामग्री दस्तावेज़ (Material Document फ़ोटो)" data-en="Material Document (Photos)">सामग्री दस्तावेज़ (Material Document फ़ोटो)</span>
            </h5>

            @if(!empty($materialDocsList) && $bulty->material_document_status)
                <div class="alert-box alert-success">
                    <i class="fa-solid fa-circle-check fs-4"></i>
                    <div>
                        <strong class="lang-text" data-hi="दस्तावेज़ स्वीकृत हो गया है" data-en="Document Approved">दस्तावेज़ स्वीकृत हो गया है</strong>
                        <div class="small lang-text" data-hi="प्रशासन द्वारा सामग्री दस्तावेज़ को मंज़ूरी मिल चुकी है।" data-en="Material document has been verified & approved by admin.">प्रशासन द्वारा सामग्री दस्तावेज़ को मंज़ूरी मिल चुकी है।</div>
                    </div>
                </div>

                <!-- Uploaded Gallery -->
                <div class="uploaded-gallery">
                    @foreach($materialDocsList as $idx => $docUrl)
                    <div class="gallery-card">
                        <div class="gallery-card-thumb" onclick="openLightbox('{{ $docUrl }}')">
                            @if(preg_match('/\.(pdf)$/i', $docUrl))
                                <i class="fa-solid fa-file-pdf pdf-view-icon"></i>
                            @else
                                <img src="{{ $docUrl }}" alt="Material Photo {{ $idx + 1 }}">
                            @endif
                        </div>
                        <div class="gallery-card-body">
                            <a href="{{ $docUrl }}" target="_blank">
                                <i class="fa-solid fa-up-right-from-square"></i>
                                <span class="lang-text" data-hi="फ़ोटो {{ $idx + 1 }} देखें" data-en="View Photo {{ $idx + 1 }}">फ़ोटो {{ $idx + 1 }} देखें</span>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>

            @elseif(!empty($materialDocsList) && !$bulty->material_document_status)
                <div class="alert-box alert-warning">
                    <i class="fa-solid fa-clock fs-4"></i>
                    <div>
                        <strong class="lang-text" data-hi="फ़ोटो अपलोड हो गई हैं (मंज़ूरी की प्रतीक्षा है)" data-en="Photos Uploaded (Pending Approval)">फ़ोटो अपलोड हो गई हैं (मंज़ूरी की प्रतीक्षा है)</strong>
                        <div class="small lang-text" data-hi="आपकी फ़ाइलें अपलोड हो चुकी हैं और एडमिन मंज़ूरी के लिए पेंडिंग हैं।" data-en="Uploaded successfully. Waiting for admin review and approval.">आपकी फ़ाइलें अपलोड हो चुकी हैं और एडमिन मंज़ूरी के लिए पेंडिंग हैं।</div>
                    </div>
                </div>

                <!-- Uploaded Gallery -->
                <div class="uploaded-gallery mb-3">
                    @foreach($materialDocsList as $idx => $docUrl)
                    <div class="gallery-card">
                        <div class="gallery-card-thumb" onclick="openLightbox('{{ $docUrl }}')">
                            @if(preg_match('/\.(pdf)$/i', $docUrl))
                                <i class="fa-solid fa-file-pdf pdf-view-icon"></i>
                            @else
                                <img src="{{ $docUrl }}" alt="Material Photo {{ $idx + 1 }}">
                            @endif
                        </div>
                        <div class="gallery-card-body">
                            <a href="{{ $docUrl }}" target="_blank">
                                <i class="fa-solid fa-eye"></i>
                                <span class="lang-text" data-hi="फ़ोटो {{ $idx + 1 }}" data-en="Photo {{ $idx + 1 }}">फ़ोटो {{ $idx + 1 }}</span>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="alert-box alert-danger">
                    <i class="fa-solid fa-circle-exclamation fs-4"></i>
                    <div>
                        <strong class="lang-text" data-hi="दस्तावेज़ अभी अपलोड नहीं हुआ है" data-en="Document Not Uploaded">दस्तावेज़ अभी अपलोड नहीं हुआ है</strong>
                        <div class="small lang-text" data-hi="कृपया नीचे दिए गए विकल्प से सामग्री दस्तावेज़ की फ़ोटो अपलोड करें।" data-en="Please upload the material document photos below.">कृपया नीचे दिए गए विकल्प से सामग्री दस्तावेज़ की फ़ोटो अपलोड करें।</div>
                    </div>
                </div>
            @endif

            @if(!$bulty->material_document_status)
            <form method="POST" action="{{ route('bilty.upload-document', $bulty->share_token) }}" enctype="multipart/form-data" class="upload-card mt-3" id="matUploadForm">
                @csrf
                <div style="font-size: 32px; color: var(--primary); margin-bottom: 8px;">
                    <i class="fa-solid fa-camera"></i>
                </div>
                <div style="font-weight: 700; font-size: 15px; margin-bottom: 4px;">
                    <span class="lang-text" data-hi="सामग्री दस्तावेज़ की फ़ोटो चुनें" data-en="Select Material Document Photos">सामग्री दस्तावेज़ की फ़ोटो चुनें</span>
                </div>
                <div style="font-size: 12px; color: #64748b; margin-bottom: 12px;">
                    <span class="lang-text" data-hi="आप एक साथ कम से कम 2 या अधिक फ़ोटो (JPG, PNG, PDF) चुनकर अपलोड कर सकते हैं।" data-en="You can select and upload multiple photos (minimum 2 photos supported, JPG, PNG, PDF max 10MB each).">आप एक साथ कम से कम 2 या अधिक फ़ोटो (JPG, PNG, PDF) चुनकर अपलोड कर सकते हैं।</span>
                </div>

                <label for="material_documents" class="upload-btn-label">
                    <i class="fa-solid fa-images"></i>
                    <span class="lang-text" data-hi="फ़ोटो चुनें (Select Photos)" data-en="Choose Photos">फ़ोटो चुनें (Select Photos)</span>
                </label>
                <input type="file" name="material_documents[]" id="material_documents" accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png" multiple required style="display:none;" onchange="handleMultiFilesSelect(this, 'mat_preview_box', 'mat_count_badge')">

                <!-- Preview Grid -->
                <div id="mat_count_badge" style="display:none;" class="photo-count-badge"></div>
                <div id="mat_preview_box" class="preview-container"></div>

                <div style="margin-top: 16px;">
                    <button type="submit" class="submit-upload-btn">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span class="lang-text" data-hi="दस्तावेज़ अपलोड करें" data-en="Upload Document">दस्तावेज़ अपलोड करें</span>
                    </button>
                </div>
            </form>
            @endif
        </div>

        <!-- 9. POD Document(s) Section (Multiple Photo Upload) -->
        <div class="bilty-section">
            <h5>
                <i class="fa-solid fa-signature"></i>
                <span class="lang-text" data-hi="डिलीवरी का प्रमाण - पीओडी (POD Proof)" data-en="Proof of Delivery (POD Photos)">डिलीवरी का प्रमाण - पीओडी (POD Proof)</span>
            </h5>

            @if(!empty($podDocsList) && $bulty->pod_document_status)
                <div class="alert-box alert-success">
                    <i class="fa-solid fa-circle-check fs-4"></i>
                    <div>
                        <strong class="lang-text" data-hi="पीओडी स्वीकृत हो गया है" data-en="POD Approved">पीओडी स्वीकृत हो गया है</strong>
                        <div class="small lang-text" data-hi="डिलीवरी का प्रमाण (POD) सत्यापित व स्वीकृत हो चुका है।" data-en="Proof of delivery has been verified & approved.">डिलीवरी का प्रमाण (POD) सत्यापित व स्वीकृत हो चुका है।</div>
                    </div>
                </div>

                <!-- Uploaded Gallery -->
                <div class="uploaded-gallery">
                    @foreach($podDocsList as $idx => $docUrl)
                    <div class="gallery-card">
                        <div class="gallery-card-thumb" onclick="openLightbox('{{ $docUrl }}')">
                            @if(preg_match('/\.(pdf)$/i', $docUrl))
                                <i class="fa-solid fa-file-pdf pdf-view-icon"></i>
                            @else
                                <img src="{{ $docUrl }}" alt="POD Photo {{ $idx + 1 }}">
                            @endif
                        </div>
                        <div class="gallery-card-body">
                            <a href="{{ $docUrl }}" target="_blank">
                                <i class="fa-solid fa-up-right-from-square"></i>
                                <span class="lang-text" data-hi="पीओडी {{ $idx + 1 }} देखें" data-en="View POD {{ $idx + 1 }}">पीओडी {{ $idx + 1 }} देखें</span>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>

            @elseif(!empty($podDocsList) && !$bulty->pod_document_status)
                <div class="alert-box alert-warning">
                    <i class="fa-solid fa-clock fs-4"></i>
                    <div>
                        <strong class="lang-text" data-hi="पीओडी अपलोड हो गई है (मंज़ूरी की प्रतीक्षा है)" data-en="POD Uploaded (Pending Approval)">पीओडी अपलोड हो गई है (मंज़ूरी की प्रतीक्षा है)</strong>
                        <div class="small lang-text" data-hi="पीओडी फ़ोटो अपलोड हो चुकी हैं और एडमिन मंज़ूरी के लिए पेंडिंग हैं।" data-en="POD photos uploaded. Waiting for admin approval.">पीओडी फ़ोटो अपलोड हो चुकी हैं और एडमिन मंज़ूरी के लिए पेंडिंग हैं।</div>
                    </div>
                </div>

                <!-- Uploaded Gallery -->
                <div class="uploaded-gallery mb-3">
                    @foreach($podDocsList as $idx => $docUrl)
                    <div class="gallery-card">
                        <div class="gallery-card-thumb" onclick="openLightbox('{{ $docUrl }}')">
                            @if(preg_match('/\.(pdf)$/i', $docUrl))
                                <i class="fa-solid fa-file-pdf pdf-view-icon"></i>
                            @else
                                <img src="{{ $docUrl }}" alt="POD Photo {{ $idx + 1 }}">
                            @endif
                        </div>
                        <div class="gallery-card-body">
                            <a href="{{ $docUrl }}" target="_blank">
                                <i class="fa-solid fa-eye"></i>
                                <span class="lang-text" data-hi="पीओडी {{ $idx + 1 }}" data-en="POD {{ $idx + 1 }}">पीओडी {{ $idx + 1 }}</span>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="alert-box alert-danger">
                    <i class="fa-solid fa-circle-exclamation fs-4"></i>
                    <div>
                        <strong class="lang-text" data-hi="पीओडी अभी अपलोड नहीं हुआ है" data-en="POD Not Uploaded">पीओडी अभी अपलोड नहीं हुआ है</strong>
                        <div class="small lang-text" data-hi="कृपया माल डिलीवर होने के बाद हस्ताक्षर युक्त पीओडी की फ़ोटो अपलोड करें।" data-en="Please upload the signed POD receipt photos below.">कृपया माल डिलीवर होने के बाद हस्ताक्षर युक्त पीओडी की फ़ोटो अपलोड करें।</div>
                    </div>
                </div>
            @endif

            @if(!$bulty->pod_document_status)
            <form method="POST" action="{{ route('bilty.upload-pod', $bulty->share_token) }}" enctype="multipart/form-data" class="upload-card mt-3" id="podUploadForm">
                @csrf
                <div style="font-size: 32px; color: var(--secondary); margin-bottom: 8px;">
                    <i class="fa-solid fa-file-circle-check"></i>
                </div>
                <div style="font-weight: 700; font-size: 15px; margin-bottom: 4px;">
                    <span class="lang-text" data-hi="पीओडी (डिलीवरी रसीद) की फ़ोटो चुनें" data-en="Select POD (Delivery Receipt) Photos">पीओडी (डिलीवरी रसीद) की फ़ोटो चुनें</span>
                </div>
                <div style="font-size: 12px; color: #64748b; margin-bottom: 12px;">
                    <span class="lang-text" data-hi="आप एक साथ कम से कम 2 या अधिक फ़ोटो (JPG, PNG, PDF) चुनकर अपलोड कर सकते हैं।" data-en="You can select and upload multiple photos (minimum 2 photos supported, JPG, PNG, PDF max 10MB each).">आप एक साथ कम से कम 2 या अधिक फ़ोटो (JPG, PNG, PDF) चुनकर अपलोड कर सकते हैं।</span>
                </div>

                <label for="pod_files" class="upload-btn-label" style="background:#FD5523;">
                    <i class="fa-solid fa-camera"></i>
                    <span class="lang-text" data-hi="पीओडी फ़ोटो चुनें (Select Photos)" data-en="Choose POD Photos">पीओडी फ़ोटो चुनें (Select Photos)</span>
                </label>
                <input type="file" name="pod_files[]" id="pod_files" accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png" multiple required style="display:none;" onchange="handleMultiFilesSelect(this, 'pod_preview_box', 'pod_count_badge')">

                <!-- Preview Grid -->
                <div id="pod_count_badge" style="display:none;" class="photo-count-badge"></div>
                <div id="pod_preview_box" class="preview-container"></div>

                <div style="margin-top: 16px;">
                    <button type="submit" class="submit-upload-btn" style="background:#062E39;">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span class="lang-text" data-hi="पीओडी अपलोड करें" data-en="Upload POD">पीओडी अपलोड करें</span>
                    </button>
                </div>
            </form>
            @endif
        </div>

    </div>
</div>

<!-- Lightbox Modal for Photo Zoom -->
<div id="imageLightbox" class="lightbox-modal" onclick="closeLightbox()">
    <span class="lightbox-close">&times;</span>
    <img id="lightboxImg" class="lightbox-content" src="" alt="Zoomed Photo" onclick="event.stopPropagation()">
</div>

<script>
    // Language Toggle Handling
    let currentLang = localStorage.getItem('bilty_driver_lang') || 'hi';

    function setLanguage(lang) {
        currentLang = lang;
        localStorage.setItem('bilty_driver_lang', lang);
        document.documentElement.lang = lang;

        document.querySelectorAll('.lang-text').forEach(function(el) {
            const val = el.getAttribute('data-' + lang);
            if (val) {
                el.textContent = val;
            }
        });

        const btnText = document.getElementById('langBtnText');
        if (btnText) {
            btnText.textContent = lang === 'hi' ? 'English' : 'हिन्दी';
        }
    }

    function toggleLanguage() {
        const newLang = currentLang === 'hi' ? 'en' : 'hi';
        setLanguage(newLang);
    }

    // Initialize language on load
    document.addEventListener('DOMContentLoaded', function() {
        setLanguage(currentLang);
    });

    // Multiple Files Preview Handling
    function handleMultiFilesSelect(input, previewContainerId, countBadgeId) {
        const previewBox = document.getElementById(previewContainerId);
        const countBadge = document.getElementById(countBadgeId);
        previewBox.innerHTML = '';

        if (!input.files || input.files.length === 0) {
            countBadge.style.display = 'none';
            return;
        }

        const count = input.files.length;
        countBadge.style.display = 'inline-block';
        if (currentLang === 'hi') {
            countBadge.textContent = '✓ ' + count + ' फ़ोटो चुनी गई हैं (' + count + ' Files Selected)';
        } else {
            countBadge.textContent = '✓ ' + count + ' photos selected';
        }

        Array.from(input.files).forEach(function(file, index) {
            const itemDiv = document.createElement('div');
            itemDiv.className = 'preview-item';

            if (file.type.startsWith('image/')) {
                const img = document.createElement('img');
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
                itemDiv.appendChild(img);
            } else {
                const badge = document.createElement('div');
                badge.className = 'file-icon-badge';
                badge.innerHTML = '<i class="fa-solid fa-file-pdf"></i><span>PDF</span>';
                itemDiv.appendChild(badge);
            }

            previewBox.appendChild(itemDiv);
        });
    }

    // Lightbox handling
    function openLightbox(url) {
        if (url.toLowerCase().endsWith('.pdf')) {
            window.open(url, '_blank');
            return;
        }
        const modal = document.getElementById('imageLightbox');
        const img = document.getElementById('lightboxImg');
        img.src = url;
        modal.style.display = 'flex';
    }

    function closeLightbox() {
        const modal = document.getElementById('imageLightbox');
        modal.style.display = 'none';
    }
</script>

</body>
</html>
