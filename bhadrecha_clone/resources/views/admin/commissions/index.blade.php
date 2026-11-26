@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h5 class="card-title mb-0">Staff Commissions</h5>
                <small class="text-muted">View commission records</small>
            </div>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Staff</th>
                        <th>Appointment ID</th>
                        <th>Service</th>
                        <th>Commission Amount</th>
                        <th>Type</th>
                        <th>Rate</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commissions as $commission)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;">
                                    <span class="avatar-initial rounded-circle bg-label-primary" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:600;">
                                        {{ strtoupper(substr($commission->staff->name ?? '--', 0, 2)) }}
                                    </span>
                                </div>
                                <span class="fw-semibold">{{ $commission->staff->name ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td>#{{ $commission->appointment_id }}</td>
                        <td>{{ $commission->service->name ?? 'N/A' }}</td>
                        <td class="fw-semibold">₹{{ number_format($commission->commission_amount, 2) }}</td>
                        <td>
                            <span class="badge bg-label-{{ $commission->type == 'percentage' ? 'info' : 'primary' }}">
                                {{ ucfirst($commission->type) }}
                            </span>
                        </td>
                        <td>{{ $commission->type == 'percentage' ? $commission->rate . '%' : '₹' . number_format($commission->rate, 2) }}</td>
                        <td>{{ \Carbon\Carbon::parse($commission->created_at)->format('d M Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No commission records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
