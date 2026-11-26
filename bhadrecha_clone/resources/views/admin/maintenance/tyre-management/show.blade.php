@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Tyre Record Details</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Maintenance</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.maintenance.tyre-management.index') }}">Tyre Management</a></li>
                    <li class="breadcrumb-item active">Details</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.maintenance.tyre-management.edit', $tyreManagement) }}" class="btn btn-outline-primary"><i class="bx bx-edit me-1"></i> Edit</a>
            <a href="{{ route('admin.maintenance.tyre-management.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i> Back</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Tyre Information</h5>
                    @php
                        $badge = ['active' => 'success', 'removed' => 'warning', 'scrap' => 'danger'];
                    @endphp
                    <span class="badge bg-label-{{ $badge[$tyreManagement->status] ?? 'secondary' }} fs-6">{{ ucfirst($tyreManagement->status) }}</span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="140"><strong>Vehicle:</strong></td>
                                    <td>{{ $tyreManagement->vehicle?->vehicle_number ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Position:</strong></td>
                                    <td>{{ $tyreManagement->tyre_position }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Brand:</strong></td>
                                    <td>{{ $tyreManagement->tyre_brand }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Size:</strong></td>
                                    <td>{{ $tyreManagement->tyre_size }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Model:</strong></td>
                                    <td>{{ $tyreManagement->tyre_model ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Serial Number:</strong></td>
                                    <td>{{ $tyreManagement->serial_number ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Branch:</strong></td>
                                    <td>{{ $tyreManagement->branch?->name ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="140"><strong>Purchase Date:</strong></td>
                                    <td>{{ $tyreManagement->purchase_date?->format('d-m-Y') ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Purchase Cost:</strong></td>
                                    <td>{{ $tyreManagement->purchase_cost ? '₹ ' . number_format($tyreManagement->purchase_cost, 2) : 'Not recorded' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Installation Date:</strong></td>
                                    <td>{{ $tyreManagement->installation_date?->format('d-m-Y') ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Installation KM:</strong></td>
                                    <td>{{ $tyreManagement->installation_km ? number_format($tyreManagement->installation_km, 0) . ' km' : 'Not recorded' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tread Depth (New):</strong></td>
                                    <td>{{ $tyreManagement->tread_depth_new ? $tyreManagement->tread_depth_new . ' mm' : 'Not recorded' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tread Depth (Current):</strong></td>
                                    <td>{{ $tyreManagement->tread_depth_current ? $tyreManagement->tread_depth_current . ' mm' : 'Not recorded' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Pressure:</strong></td>
                                    <td>{{ $tyreManagement->pressure_psi ? $tyreManagement->pressure_psi . ' PSI' : 'Not recorded' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($tyreManagement->notes)
                    <h6 class="fw-semibold mt-3 mb-2">Notes</h6>
                    <p class="mb-0">{{ $tyreManagement->notes }}</p>
                    @endif
                </div>
            </div>

            @if($tyreManagement->removal_date || $tyreManagement->removal_km || $tyreManagement->removal_reason)
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Removal Details</h5></div>
                <div class="card-body">
                    <div class="row">
                        @if($tyreManagement->removal_date)
                        <div class="col-md-4">
                            <strong>Removal Date:</strong>
                            <p>{{ $tyreManagement->removal_date->format('d-m-Y') }}</p>
                        </div>
                        @endif
                        @if($tyreManagement->removal_km)
                        <div class="col-md-4">
                            <strong>Removal KM:</strong>
                            <p>{{ number_format($tyreManagement->removal_km, 0) . ' km' }}</p>
                        </div>
                        @endif
                        @if($tyreManagement->removal_reason)
                        <div class="col-md-4">
                            <strong>Removal Reason:</strong>
                            <p>{{ $tyreManagement->removal_reason }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Actions</h5></div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.maintenance.tyre-management.edit', $tyreManagement) }}" class="btn btn-outline-primary"><i class="bx bx-edit me-1"></i> Edit Record</a>
                        <form method="POST" action="{{ route('admin.maintenance.tyre-management.destroy', $tyreManagement) }}" onsubmit="return confirm('Delete this tyre record?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100"><i class="bx bx-trash me-1"></i> Delete</button>
                        </form>
                    </div>
                </div>
            </div>

            @if($tyreManagement->tread_depth_new && $tyreManagement->tread_depth_current)
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Tread Wear Summary</h5></div>
                <div class="card-body text-center">
                    @php
                        $wearPercent = $tyreManagement->tread_depth_new > 0
                            ? round((1 - $tyreManagement->tread_depth_current / $tyreManagement->tread_depth_new) * 100, 1)
                            : 0;
                    @endphp
                    <div class="display-6 fw-bold {{ $wearPercent > 75 ? 'text-danger' : ($wearPercent > 50 ? 'text-warning' : 'text-success') }}">
                        {{ $wearPercent }}%
                    </div>
                    <small class="text-muted">Wear Percentage</small>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
