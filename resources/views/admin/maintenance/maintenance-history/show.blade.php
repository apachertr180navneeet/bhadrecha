@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Maintenance Record Details</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Maintenance</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.maintenance.maintenance-history.index') }}">Maintenance History</a></li>
                    <li class="breadcrumb-item active">Details</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.maintenance.maintenance-history.edit', $maintenanceHistory) }}" class="btn btn-outline-primary"><i class="bx bx-edit me-1"></i> Edit</a>
            <a href="{{ route('admin.maintenance.maintenance-history.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i> Back</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Service Information</h5>
                    @php
                        $badge = ['completed' => 'success', 'pending' => 'warning', 'cancelled' => 'secondary'];
                    @endphp
                    <span class="badge bg-label-{{ $badge[$maintenanceHistory->status] ?? 'secondary' }} fs-6">{{ ucfirst($maintenanceHistory->status) }}</span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="140"><strong>Vehicle:</strong></td>
                                    <td>{{ $maintenanceHistory->vehicle?->vehicle_number ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Service Type:</strong></td>
                                    <td>{{ $maintenanceHistory->service_type }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Service Date:</strong></td>
                                    <td>{{ $maintenanceHistory->service_date?->format('d-m-Y') ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Vendor:</strong></td>
                                    <td>{{ $maintenanceHistory->vendor?->name ?? $maintenanceHistory->vendor_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Branch:</strong></td>
                                    <td>{{ $maintenanceHistory->branch?->name ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="140"><strong>Current KM:</strong></td>
                                    <td>{{ $maintenanceHistory->current_km ? number_format($maintenanceHistory->current_km, 0) . ' km' : 'Not recorded' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Cost:</strong></td>
                                    <td>{{ $maintenanceHistory->cost ? '₹ ' . number_format($maintenanceHistory->cost, 2) : 'Not recorded' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td><span class="badge bg-label-{{ $badge[$maintenanceHistory->status] ?? 'secondary' }}">{{ ucfirst($maintenanceHistory->status) }}</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($maintenanceHistory->description)
                    <h6 class="fw-semibold mt-3 mb-2">Description</h6>
                    <p class="mb-0">{{ $maintenanceHistory->description }}</p>
                    @endif
                </div>
            </div>

            @if($maintenanceHistory->next_service_date || $maintenanceHistory->next_service_km)
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Next Scheduled Service</h5></div>
                <div class="card-body">
                    <div class="row">
                        @if($maintenanceHistory->next_service_date)
                        <div class="col-md-6">
                            <strong>Next Service Date:</strong>
                            <p>{{ $maintenanceHistory->next_service_date->format('d-m-Y') }}</p>
                        </div>
                        @endif
                        @if($maintenanceHistory->next_service_km)
                        <div class="col-md-6">
                            <strong>Next Service KM:</strong>
                            <p>{{ number_format($maintenanceHistory->next_service_km, 0) . ' km' }}</p>
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
                        <a href="{{ route('admin.maintenance.maintenance-history.edit', $maintenanceHistory) }}" class="btn btn-outline-primary"><i class="bx bx-edit me-1"></i> Edit Record</a>
                        <form method="POST" action="{{ route('admin.maintenance.maintenance-history.destroy', $maintenanceHistory) }}" onsubmit="return confirm('Delete this record?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100"><i class="bx bx-trash me-1"></i> Delete</button>
                        </form>
                    </div>
                </div>
            </div>

            @if($maintenanceHistory->serviceSchedule)
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Linked Schedule</h5></div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td><strong>Type:</strong></td>
                            <td>{{ $maintenanceHistory->serviceSchedule->service_type }}</td>
                        </tr>
                        <tr>
                            <td><strong>Date:</strong></td>
                            <td>{{ $maintenanceHistory->serviceSchedule->scheduled_date?->format('d-m-Y') ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            @endif

            @if($maintenanceHistory->sparePart)
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Spare Part Used</h5></div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td><strong>Name:</strong></td>
                            <td>{{ $maintenanceHistory->sparePart->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Part #:</strong></td>
                            <td>{{ $maintenanceHistory->sparePart->part_number ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            @endif

            @if($maintenanceHistory->notes)
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Notes</h5></div>
                <div class="card-body">
                    <p class="mb-0">{{ $maintenanceHistory->notes }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
