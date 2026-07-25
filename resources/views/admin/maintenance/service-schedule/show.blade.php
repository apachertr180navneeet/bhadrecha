@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Service Schedule Details</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Maintenance</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.maintenance.service-schedule.index') }}">Service Schedule</a></li>
                    <li class="breadcrumb-item active">Details</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.maintenance.service-schedule.edit', $serviceSchedule) }}" class="btn btn-outline-primary"><i class="bx bx-edit me-1"></i> Edit</a>
            <a href="{{ route('admin.maintenance.service-schedule.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i> Back</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Schedule Information</h5>
                    @php
                        $badge = ['upcoming' => 'primary', 'overdue' => 'danger', 'completed' => 'success', 'cancelled' => 'secondary'];
                    @endphp
                    <span class="badge bg-label-{{ $badge[$serviceSchedule->status] ?? 'secondary' }} fs-6">{{ ucfirst($serviceSchedule->status) }}</span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="140"><strong>Vehicle:</strong></td>
                                    <td>{{ $serviceSchedule->vehicle?->vehicle_number ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Service Type:</strong></td>
                                    <td>{{ $serviceSchedule->service_type }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Branch:</strong></td>
                                    <td>{{ $serviceSchedule->branch?->name ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="140"><strong>Scheduled Date:</strong></td>
                                    <td>{{ $serviceSchedule->scheduled_date?->format('d-m-Y') ?? 'Not set' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Scheduled KM:</strong></td>
                                    <td>{{ $serviceSchedule->scheduled_km ? number_format($serviceSchedule->scheduled_km, 0) . ' km' : 'Not set' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td><span class="badge bg-label-{{ $badge[$serviceSchedule->status] ?? 'secondary' }}">{{ ucfirst($serviceSchedule->status) }}</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <h6 class="fw-semibold mt-4 mb-2">Service History</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="160"><strong>Last Service Date:</strong></td>
                                    <td>{{ $serviceSchedule->last_service_date?->format('d-m-Y') ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Last Service KM:</strong></td>
                                    <td>{{ $serviceSchedule->last_service_km ? number_format($serviceSchedule->last_service_km, 0) . ' km' : 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="160"><strong>Interval (Days):</strong></td>
                                    <td>{{ $serviceSchedule->interval_days ? $serviceSchedule->interval_days . ' days' : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Interval (KM):</strong></td>
                                    <td>{{ $serviceSchedule->interval_km ? number_format($serviceSchedule->interval_km, 0) . ' km' : 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($serviceSchedule->notes)
                    <h6 class="fw-semibold mt-4 mb-2">Notes</h6>
                    <p class="mb-0">{{ $serviceSchedule->notes }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Actions</h5></div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.maintenance.service-schedule.edit', $serviceSchedule) }}" class="btn btn-outline-primary"><i class="bx bx-edit me-1"></i> Edit Schedule</a>
                        @if($serviceSchedule->status !== 'completed')
                        <form method="POST" action="{{ route('admin.maintenance.service-schedule.mark-completed', $serviceSchedule) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-success w-100"><i class="bx bx-check me-1"></i> Mark Completed</button>
                        </form>
                        @endif
                        <form method="POST" action="{{ route('admin.maintenance.service-schedule.destroy', $serviceSchedule) }}" onsubmit="return confirm('Delete this schedule?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100"><i class="bx bx-trash me-1"></i> Delete</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="mb-0">Vehicle Info</h5></div>
                <div class="card-body">
                    @if($serviceSchedule->vehicle)
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td><strong>Number:</strong></td>
                            <td>{{ $serviceSchedule->vehicle->vehicle_number }}</td>
                        </tr>
                        <tr>
                            <td><strong>Type:</strong></td>
                            <td>{{ $serviceSchedule->vehicle->vehicle_type ?? 'N/A' }}</td>
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
