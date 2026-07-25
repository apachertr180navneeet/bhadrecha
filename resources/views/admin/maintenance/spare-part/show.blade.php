@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Spare Part Details</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Maintenance</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.maintenance.spare-part.index') }}">Spare Parts</a></li>
                    <li class="breadcrumb-item active">{{ $sparePart->name }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.maintenance.spare-part.edit', $sparePart) }}" class="btn btn-primary"><i class="bx bx-edit me-1"></i> Edit</a>
            <form method="POST" action="{{ route('admin.maintenance.spare-part.destroy', $sparePart) }}" class="d-inline" onsubmit="return confirm('Move this part to trash?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger"><i class="bx bx-trash me-1"></i> Delete</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3"><strong>Part Name:</strong> {{ $sparePart->name }}</div>
                <div class="col-md-6 mb-3"><strong>Part Number:</strong> {{ $sparePart->part_number ?? '-' }}</div>
                <div class="col-md-6 mb-3"><strong>Vehicle:</strong> {{ $sparePart->vehicle?->vehicle_number ?? '-' }}</div>
                <div class="col-md-6 mb-3"><strong>Supplier:</strong> {{ $sparePart->supplier?->name ?? '-' }}</div>
                <div class="col-md-3 mb-3"><strong>Quantity:</strong> {{ $sparePart->quantity }}</div>
                <div class="col-md-3 mb-3"><strong>Unit Price:</strong> ₹{{ number_format($sparePart->unit_price, 2) }}</div>
                <div class="col-md-3 mb-3"><strong>Amount:</strong> ₹{{ number_format($sparePart->amount ?? ($sparePart->quantity * $sparePart->unit_price), 2) }}</div>
                @if($sparePart->description)
                <div class="col-12 mb-3"><strong>Description:</strong><br>{{ $sparePart->description }}</div>
                @endif
                <div class="col-12 mb-3"><strong>Part Change Date:</strong> {{ $sparePart->part_change_date?->format('d-m-Y') ?? '-' }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
