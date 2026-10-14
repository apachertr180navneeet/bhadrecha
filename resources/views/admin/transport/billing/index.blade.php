@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Generate Bill</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Generate Bill</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#enterLrModal">
                <i class="bx bx-list-plus me-1"></i> Enter / Paste LR Numbers
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.transport.billing') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search LR No, PO No, Consignor, Consignee..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="consignor_id" class="form-select">
                        <option value="">All Consignors</option>
                        @foreach($consignors as $consignor)
                            <option value="{{ $consignor->id }}" {{ request('consignor_id') == $consignor->id ? 'selected' : '' }}>
                                {{ $consignor->name }} ({{ $consignor->phone }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="From Date">
                </div>
                <div class="col-md-2">
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" placeholder="To Date">
                </div>
                <div class="col-md-2">
                    <select name="date_sort" class="form-select" onchange="this.form.submit()">
                        <option value="latest" {{ request('date_sort', 'latest') == 'latest' ? 'selected' : '' }}>Latest to Oldest</option>
                        <option value="oldest" {{ request('date_sort') == 'oldest' ? 'selected' : '' }}>Oldest to Latest</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
                @if(request()->filled('search') || request()->filled('consignor_id') || request()->filled('from_date') || request()->filled('to_date') || request('date_sort') === 'oldest')
                <div class="col-md-2">
                    <a href="{{ route('admin.transport.billing') }}" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Selected Count Bar -->
    <div id="selectedBar" class="alert alert-info d-none py-2 mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="bx bx-check-circle fs-5"></i>
            <span><strong id="selectedCount">0</strong> LR(s) selected across pages</span>
            <button type="button" id="clearSelectionBtn" class="btn btn-outline-secondary btn-sm ms-2">
                <i class="bx bx-x me-1"></i> Clear Selection
            </button>
        </div>
        <button id="generateBillBtn" class="btn btn-success btn-sm" disabled>
            <i class="bx bx-receipt me-1"></i> Generate Bill
        </button>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th style="width: 50px;">Action</th>
                            <th>LR No</th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['date_sort' => request('date_sort') === 'oldest' ? 'latest' : 'oldest']) }}" class="text-white text-decoration-none d-inline-flex align-items-center gap-1">
                                    Date
                                    <i class="bx {{ request('date_sort') === 'oldest' ? 'bx-sort-up text-warning' : 'bx-sort-down text-warning' }}"></i>
                                </a>
                            </th>
                            <th>Consignor</th>
                            <th>Consignee</th>
                            <th>From → To</th>
                            <th>PO No</th>
                            <th>Freight (₹)</th>
                            <th>GST (₹)</th>
                            <th>Other (₹)</th>
                            <th>Total (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bulties as $bulty)
                        <tr>
                            <td><input type="checkbox" class="bulty-checkbox" value="{{ $bulty->id }}"></td>
                            <td>
                                <a href="{{ route('admin.transport.bulties.show', $bulty->id) }}" class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="bx bx-show"></i>
                                </a>
                            </td>
                            <td><strong>{{ $bulty->lr_no }}</strong></td>
                            <td>{{ $bulty->lr_date->format('d M Y') }}</td>
                            <td>{{ $bulty->consignor->name ?? '-' }}</td>
                            <td>{{ $bulty->consignee->name ?? '-' }}</td>
                            <td>
                                {{ $bulty->originCity->name ?? '-' }}
                                <i class="bx bx-chevron-right bx-xs"></i>
                                {{ $bulty->destinationCity->name ?? '-' }}
                            </td>
                            <td>{{ $bulty->bultyDetail->po_no ?? '-' }}</td>
                            <td class="text-end">{{ number_format($bulty->freight_charges, 2) }}</td>
                            <td class="text-end">{{ number_format($bulty->gst_amount, 2) }}</td>
                            <td class="text-end">{{ number_format($bulty->other_charges, 2) }}</td>
                            <td class="text-end"><strong>{{ number_format($bulty->total_amount, 2) }}</strong></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="text-center py-4 text-muted">No unbilled LRs found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($bulties->hasPages())
        <div class="card-footer">
            {{ $bulties->links() }}
        </div>
        @endif
    <!-- Modal to Enter/Paste Multiple LR Numbers -->
    <div class="modal fade" id="enterLrModal" tabindex="-1" aria-labelledby="enterLrModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="enterLrModalLabel">
                        <i class="bx bx-receipt me-1 text-primary"></i> Enter / Paste Multiple LR Numbers
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-2">
                        Enter or paste multiple LR numbers below separated by commas, spaces, or new lines (e.g. paste 44 LR numbers from Excel or WhatsApp).
                    </p>
                    <div class="mb-3">
                        <textarea class="form-control font-monospace" id="pasteLrNumbersInput" rows="6" placeholder="LR-1001, LR-1002, LR-1003&#10;or one LR number per line..."></textarea>
                    </div>
                    <div id="matchLrStatus" class="d-none alert mb-0"></div>
                </div>
                <div class="modal-footer justify-content-between">
                    <div>
                        <span id="lrCountPill" class="badge bg-label-secondary">0 LR numbers entered</span>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-info" id="validateLrsBtn">
                            <i class="bx bx-check-double me-1"></i> Check & Match LRs
                        </button>
                        <button type="button" class="btn btn-success" id="proceedWithLrsBtn" disabled>
                            <i class="bx bx-right-arrow-alt me-1"></i> Generate Bill
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    const STORAGE_KEY = 'selected_billing_lrs';
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.bulty-checkbox');
    const selectedBar = document.getElementById('selectedBar');
    const selectedCount = document.getElementById('selectedCount');
    const generateBtn = document.getElementById('generateBillBtn');
    const clearSelectionBtn = document.getElementById('clearSelectionBtn');

    function getSelectedIds() {
        try {
            const raw = sessionStorage.getItem(STORAGE_KEY);
            return raw ? JSON.parse(raw) : [];
        } catch (e) {
            return [];
        }
    }

    function saveSelectedIds(ids) {
        try {
            sessionStorage.setItem(STORAGE_KEY, JSON.stringify(ids));
        } catch (e) {}
    }

    function updateSelectedBar() {
        const ids = getSelectedIds();
        const count = ids.length;
        if (count > 0) {
            selectedBar.classList.remove('d-none');
            selectedCount.textContent = count;
            generateBtn.disabled = false;
        } else {
            selectedBar.classList.add('d-none');
            generateBtn.disabled = true;
        }
    }

    function syncUIFromStorage() {
        const ids = getSelectedIds();
        const idSet = new Set(ids.map(String));
        let allPageChecked = checkboxes.length > 0;

        checkboxes.forEach(cb => {
            if (idSet.has(String(cb.value))) {
                cb.checked = true;
            } else {
                cb.checked = false;
                allPageChecked = false;
            }
        });

        if (selectAll) {
            selectAll.checked = allPageChecked && checkboxes.length > 0;
        }

        updateSelectedBar();
    }

    selectAll?.addEventListener('change', function() {
        let ids = getSelectedIds();
        const isChecked = this.checked;

        checkboxes.forEach(cb => {
            cb.checked = isChecked;
            const val = String(cb.value);
            if (isChecked) {
                if (!ids.includes(val)) {
                    ids.push(val);
                }
            } else {
                ids = ids.filter(id => id !== val);
            }
        });

        saveSelectedIds(ids);
        updateSelectedBar();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            let ids = getSelectedIds();
            const val = String(this.value);

            if (this.checked) {
                if (!ids.includes(val)) {
                    ids.push(val);
                }
            } else {
                ids = ids.filter(id => id !== val);
            }

            saveSelectedIds(ids);

            if (selectAll) {
                selectAll.checked = Array.from(checkboxes).every(c => c.checked) && checkboxes.length > 0;
            }

            updateSelectedBar();
        });
    });

    clearSelectionBtn?.addEventListener('click', function() {
        sessionStorage.removeItem(STORAGE_KEY);
        checkboxes.forEach(cb => cb.checked = false);
        if (selectAll) selectAll.checked = false;
        updateSelectedBar();
    });

    generateBtn?.addEventListener('click', function() {
        const ids = getSelectedIds();
        if (ids.length > 0) {
            window.location.href = '{{ route("admin.transport.billing.create") }}?ids=' + ids.join(',');
        }
    });

    // Enter / Paste Multiple LR Numbers Logic
    function getEnteredLrs() {
        const raw = document.getElementById('pasteLrNumbersInput')?.value || '';
        return raw.split(/[\s,\r\n\t]+/).map(s => s.trim()).filter(Boolean);
    }

    document.getElementById('pasteLrNumbersInput')?.addEventListener('input', function() {
        const lrs = getEnteredLrs();
        const pill = document.getElementById('lrCountPill');
        if (pill) {
            pill.textContent = `${lrs.length} LR number(s) entered`;
        }
    });

    document.getElementById('validateLrsBtn')?.addEventListener('click', function() {
        const lrs = getEnteredLrs();
        if (lrs.length === 0) {
            alert('Please enter at least one LR number.');
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Checking...');

        fetch('{{ route("admin.transport.billing.match-lrs") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ lr_numbers: lrs.join(',') })
        })
        .then(r => r.json())
        .then(data => {
            btn.prop('disabled', false).html('<i class="bx bx-check-double me-1"></i> Check & Match LRs');
            const statusDiv = $('#matchLrStatus');
            statusDiv.removeClass('d-none alert-success alert-warning alert-danger');

            if (data.matched_count > 0) {
                let msg = `<strong>Found ${data.matched_count} unbilled LR(s) matching in database.</strong>`;
                if (data.missing_count > 0) {
                    msg += `<div class="mt-1 small text-danger">${data.missing_count} LR(s) not found or already billed: ${data.missing_lr_nos.join(', ')}</div>`;
                    statusDiv.addClass('alert-warning');
                } else {
                    statusDiv.addClass('alert-success');
                }
                statusDiv.html(msg);

                $('#proceedWithLrsBtn').prop('disabled', false).data('matched-ids', data.matched_ids).data('matched-lrs', data.matched_lr_nos);

                // Merge into sessionStorage
                let currentIds = getSelectedIds();
                data.matched_ids.forEach(id => {
                    const sId = String(id);
                    if (!currentIds.includes(sId)) {
                        currentIds.push(sId);
                    }
                });
                saveSelectedIds(currentIds);
                syncUIFromStorage();
            } else {
                statusDiv.addClass('alert-danger').html(`<strong>No unbilled LRs found.</strong> All ${lrs.length} entered LR(s) are either not in the system or already billed.`);
                $('#proceedWithLrsBtn').prop('disabled', true);
            }
        })
        .catch(err => {
            btn.prop('disabled', false).html('<i class="bx bx-check-double me-1"></i> Check & Match LRs');
            alert('Error verifying LR numbers. Please try again.');
        });
    });

    document.getElementById('proceedWithLrsBtn')?.addEventListener('click', function() {
        const ids = $(this).data('matched-ids');
        const lrNos = $(this).data('matched-lrs');
        if (ids && ids.length > 0) {
            let url = '{{ route("admin.transport.billing.create") }}?ids=' + ids.join(',');
            if (lrNos && lrNos.length > 0) {
                url += '&lr_nos=' + encodeURIComponent(lrNos.join(','));
            }
            window.location.href = url;
        }
    });

    // Run on initial load
    document.addEventListener('DOMContentLoaded', syncUIFromStorage);
    syncUIFromStorage();
</script>
@endsection
