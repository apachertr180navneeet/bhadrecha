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
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
                @if(request()->filled('search') || request()->filled('consignor_id') || request()->filled('from_date') || request()->filled('to_date'))
                <div class="col-md-2">
                    <a href="{{ route('admin.transport.billing') }}" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Selected Count Bar -->
    <div id="selectedBar" class="alert alert-info d-none py-2 mb-3 d-flex align-items-center justify-content-between">
        <span><strong id="selectedCount">0</strong> LR(s) selected</span>
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
                            <th>LR No</th>
                            <th>Date</th>
                            <th>Consignor</th>
                            <th>Consignee</th>
                            <th>From → To</th>
                            <th>PO No</th>
                            <th>Freight (₹)</th>
                            <th>GST (₹)</th>
                            <th>Other (₹)</th>
                            <th>Total (₹)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bulties as $bulty)
                        <tr>
                            <td><input type="checkbox" class="bulty-checkbox" value="{{ $bulty->id }}"></td>
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
                            <td>
                                <a href="{{ route('admin.transport.bulties.show', $bulty->id) }}" class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="bx bx-show"></i>
                                </a>
                            </td>
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
    </div>
</div>
@endsection

@section('script')
<script>
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.bulty-checkbox');
    const selectedBar = document.getElementById('selectedBar');
    const selectedCount = document.getElementById('selectedCount');
    const generateBtn = document.getElementById('generateBillBtn');

    function updateSelectedBar() {
        const checked = document.querySelectorAll('.bulty-checkbox:checked');
        const count = checked.length;
        if (count > 0) {
            selectedBar.classList.remove('d-none');
            selectedCount.textContent = count;
            generateBtn.disabled = false;
        } else {
            selectedBar.classList.add('d-none');
            generateBtn.disabled = true;
        }
    }

    selectAll?.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateSelectedBar();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            if (!this.checked) {
                selectAll.checked = false;
            } else {
                const allChecked = document.querySelectorAll('.bulty-checkbox:checked').length === checkboxes.length;
                selectAll.checked = allChecked;
            }
            updateSelectedBar();
        });
    });

    generateBtn?.addEventListener('click', function() {
        const ids = [];
        document.querySelectorAll('.bulty-checkbox:checked').forEach(cb => ids.push(cb.value));
        if (ids.length > 0) {
            window.location.href = '{{ route("admin.transport.billing.create") }}?ids=' + ids.join(',');
        }
    });
</script>
@endsection
