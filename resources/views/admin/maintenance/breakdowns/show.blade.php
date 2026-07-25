@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Breakdown Details</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Maintenance</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.maintenance.breakdowns.index') }}">Breakdowns</a></li>
                    <li class="breadcrumb-item active">Details</li>
                </ol>
            </nav>
        </div>
        <div>
            @if($breakdown->status !== 'resolved')
            <form method="POST" action="{{ route('admin.maintenance.breakdowns.mark-resolved', $breakdown) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-success"><i class="bx bx-check me-1"></i> Mark Resolved</button>
            </form>
            @endif
            <a href="{{ route('admin.maintenance.breakdowns.edit', $breakdown) }}" class="btn btn-outline-primary"><i class="bx bx-edit me-1"></i> Edit</a>
            <a href="{{ route('admin.maintenance.breakdowns.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i> Back</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Breakdown Information</h5>
                    <div>
                        @php
                            $sevBadge = ['minor' => 'success', 'major' => 'warning', 'critical' => 'danger'];
                            $badge = ['reported' => 'danger', 'in_progress' => 'warning', 'resolved' => 'success', 'towed' => 'secondary'];
                        @endphp
                        <span class="badge bg-label-{{ $sevBadge[$breakdown->severity] ?? 'secondary' }} me-1">{{ ucfirst($breakdown->severity) }}</span>
                        <span class="badge bg-label-{{ $badge[$breakdown->status] ?? 'secondary' }}">{{ str_replace('_', ' ', ucfirst($breakdown->status)) }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="140"><strong>Vehicle:</strong></td>
                                    <td>{{ $breakdown->vehicle?->vehicle_number ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Driver:</strong></td>
                                    <td>{{ $breakdown->driver?->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Issue Type:</strong></td>
                                    <td>{{ $breakdown->issue_type }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Date:</strong></td>
                                    <td>{{ $breakdown->breakdown_date?->format('d-m-Y') ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Time:</strong></td>
                                    <td>{{ $breakdown->breakdown_time ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Branch:</strong></td>
                                    <td>{{ $breakdown->branch?->name ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="140"><strong>Location:</strong></td>
                                    <td>{{ $breakdown->location }}</td>
                                </tr>
                                @if($breakdown->latitude && $breakdown->longitude)
                                <tr>
                                    <td><strong>Coordinates:</strong></td>
                                    <td>{{ $breakdown->latitude }}, {{ $breakdown->longitude }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td><strong>Severity:</strong></td>
                                    <td><span class="badge bg-label-{{ $sevBadge[$breakdown->severity] ?? 'secondary' }}">{{ ucfirst($breakdown->severity) }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td><span class="badge bg-label-{{ $badge[$breakdown->status] ?? 'secondary' }}">{{ str_replace('_', ' ', ucfirst($breakdown->status)) }}</span></td>
                                </tr>
                                @if($breakdown->resolved_at)
                                <tr>
                                    <td><strong>Resolved At:</strong></td>
                                    <td>{{ $breakdown->resolved_at->format('d-m-Y h:i A') }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td><strong>Downtime:</strong></td>
                                    <td>{{ $breakdown->downtime_hours ? $breakdown->downtime_hours . ' hrs' : 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($breakdown->description)
                    <h6 class="fw-semibold mt-3 mb-2">Description</h6>
                    <p class="mb-0">{{ $breakdown->description }}</p>
                    @endif

                    @if($breakdown->resolution_notes)
                    <h6 class="fw-semibold mt-3 mb-2">Resolution Notes</h6>
                    <p class="mb-0">{{ $breakdown->resolution_notes }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Actions</h5></div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.maintenance.breakdowns.edit', $breakdown) }}" class="btn btn-outline-primary"><i class="bx bx-edit me-1"></i> Edit Record</a>
                        @if($breakdown->status !== 'resolved')
                        <form method="POST" action="{{ route('admin.maintenance.breakdowns.mark-resolved', $breakdown) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-success w-100"><i class="bx bx-check me-1"></i> Mark Resolved</button>
                        </form>
                        @endif
                        <form method="POST" action="{{ route('admin.maintenance.breakdowns.destroy', $breakdown) }}" onsubmit="return confirm('Delete this breakdown record?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100"><i class="bx bx-trash me-1"></i> Delete</button>
                        </form>
                    </div>
                </div>
            </div>

            @if($breakdown->vendor)
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Workshop / Vendor</h5></div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td><strong>Name:</strong></td>
                            <td>{{ $breakdown->vendor->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Phone:</strong></td>
                            <td>{{ $breakdown->vendor->phone ?? 'N/A' }}</td>
                        </tr>
                        @if($breakdown->repair_cost)
                        <tr>
                            <td><strong>Repair Cost:</strong></td>
                            <td>₹ {{ number_format($breakdown->repair_cost, 2) }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
            @endif

            <div class="card">
                <div class="card-header"><h5 class="mb-0">Vehicle Info</h5></div>
                <div class="card-body">
                    @if($breakdown->vehicle)
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td><strong>Number:</strong></td>
                            <td>{{ $breakdown->vehicle->vehicle_number }}</td>
                        </tr>
                        <tr>
                            <td><strong>Type:</strong></td>
                            <td>{{ $breakdown->vehicle->vehicle_type ?? 'N/A' }}</td>
                        </tr>
                    </table>
                    @else
                    <p class="text-muted mb-0">Vehicle not found</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
