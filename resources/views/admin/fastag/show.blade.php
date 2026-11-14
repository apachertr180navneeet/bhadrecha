@extends('admin.layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">FASTag /</span> Transaction Detail
            </h4>
            <p class="text-muted mb-0 small">Transaction ID: <span class="font-monospace text-primary">{{ $transaction->transaction_id }}</span></p>
        </div>
        <div>
            <a href="{{ route('admin.fastag.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class="bx bx-arrow-back"></i> Back to Transactions
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Transaction Information</h5>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-4">
                    <label class="text-muted small d-block">Transaction ID</label>
                    <strong class="font-monospace">{{ $transaction->transaction_id }}</strong>
                </div>
                <div class="col-md-4">
                    <label class="text-muted small d-block">Vehicle Number</label>
                    <span class="badge bg-label-dark font-monospace fw-bold fs-6">{{ $transaction->vehicle_number }}</span>
                    @if($transaction->vehicle)
                        <small class="text-muted d-block">{{ $transaction->vehicle->make_model ?: $transaction->vehicle->vehicle_type }}</small>
                    @endif
                </div>
                <div class="col-md-4">
                    <label class="text-muted small d-block">Amount</label>
                    <h4 class="text-danger font-monospace mb-0">₹{{ number_format($transaction->amount, 2) }}</h4>
                </div>
                <div class="col-md-4">
                    <label class="text-muted small d-block">Toll Plaza Name</label>
                    <strong>{{ $transaction->toll_plaza_name ?: 'Unknown' }}</strong>
                </div>
                <div class="col-md-4">
                    <label class="text-muted small d-block">Date & Time</label>
                    <span>{{ $transaction->transaction_date->format('M d, Y h:i:s A') }}</span>
                </div>
                <div class="col-md-4">
                    <label class="text-muted small d-block">Status / Type</label>
                    <span class="badge bg-label-success">{{ $transaction->transaction_status }}</span>
                    <span class="badge bg-label-primary">{{ $transaction->transaction_type }}</span>
                </div>
            </div>
        </div>
    </div>

    @if($transaction->raw_response)
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Provider Raw JSON Payload</h5>
            </div>
            <div class="card-body">
                <pre class="bg-light p-3 rounded small text-dark mb-0 font-monospace"><code>{{ json_encode($transaction->raw_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
            </div>
        </div>
    @endif
</div>
@endsection
