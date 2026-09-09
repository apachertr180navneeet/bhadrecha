@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row align-items-center text-center text-md-start">
                        <div class="flex-shrink-0 mb-3 mb-md-0">
                            <div class="avatar avatar-xl me-md-4" style="width: 80px; height: 80px;">
                                <span class="avatar-initial rounded-circle bg-label-primary d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem; font-weight: 600;">
                                    {{ strtoupper(substr($customer->name, 0, 2)) }}
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="mb-1">{{ $customer->name }}</h4>
                            <p class="text-muted mb-2">{{ $customer->email ?? 'No email' }} &bull; {{ $customer->mobile ?? 'No phone' }}</p>
                            <div class="d-flex flex-wrap justify-content-center justify-content-md-start gap-3">
                                <span class="badge bg-label-info fs-6 px-3 py-2">
                                    <i class="bx bx-gift me-1"></i> {{ $customer->loyalty_points ?? 0 }} Points
                                </span>
                                <span class="badge bg-label-primary fs-6 px-3 py-2">
                                    <i class="bx bx-calendar me-1"></i> {{ $customer->gender ? ucfirst($customer->gender) : 'Not set' }}
                                </span>
                                @php
                                    $bal = $customer->getBalanceDetails();
                                @endphp
                                <span class="badge {{ $bal['badge_class'] }} fs-6 px-3 py-2">
                                    <i class="bx {{ $bal['type'] == 'credit' ? 'bx-wallet' : ($bal['type'] == 'debit' ? 'bx-error-circle' : 'bx-check-circle') }} me-1"></i> {{ $bal['text'] }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-3 mt-md-0">
                            @can('edit customers')
                            <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn btn-primary"><i class="bx bx-edit me-1"></i> Edit</a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Address</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $customer->address ?? 'No address on file' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Notes</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $customer->notes ?? 'No notes' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Visit History</h5>
                </div>
                <div class="table-responsive text-nowrap">
<table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Service</th>
                                <th>Staff</th>
                                <th>Store</th>
                                <th>Final Amount</th>
                                <th>Paid Amount</th>
                                <th>Credit / Debit</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customer->appointments as $appt)
                            <tr>
                                <td>{{ $appt->appointment_date ? \Carbon\Carbon::parse($appt->appointment_date)->format('d M Y, h:i A') : 'N/A' }}</td>
                                <td>
                                    @if($appt->appointmentServices && $appt->appointmentServices->count() > 0)
                                        @foreach($appt->appointmentServices as $appSvc)
                                            @php
                                                $mNames = $appSvc->staffMembers()->pluck('full_name')->filter()->implode(', ');
                                                if (empty($mNames) && $appSvc->staff) {
                                                    $mNames = $appSvc->staff->full_name;
                                                }
                                            @endphp
                                            <div>
                                                {{ $appSvc->service->service_name ?? 'N/A' }}
                                                @if(!empty($mNames))
                                                    <small class="text-muted">({{ $mNames }})</small>
                                                @endif
                                            </div>
                                        @endforeach
                                    @else
                                        @php
                                            $stf = $appt->staff->full_name ?? $appt->staff->name ?? null;
                                        @endphp
                                        {{ $appt->service->service_name ?? 'N/A' }}
                                        @if(!empty($stf))
                                            <small class="text-muted">({{ $stf }})</small>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if($appt->appointmentServices && $appt->appointmentServices->count() > 0)
                                        @php
                                            $staffList = $appt->appointmentServices->map(function($s) {
                                                $m = $s->staffMembers();
                                                return $m->count() > 0 ? $m->pluck('full_name')->implode(', ') : ($s->staff->full_name ?? null);
                                            })->filter()->unique()->implode(', ');
                                        @endphp
                                        {{ !empty($staffList) ? $staffList : 'N/A' }}
                                    @else
                                        {{ $appt->staff->full_name ?? $appt->staff->name ?? 'N/A' }}
                                    @endif
                                </td>
                                <td>{{ $appt->store->store_name ?? 'N/A' }}</td>
                                <td class="fw-semibold">₹{{ number_format($appt->final_amount ?? $appt->total_amount ?? 0, 2) }}</td>
                                <td class="fw-semibold text-success">₹{{ number_format($appt->paid_amount ?? 0, 2) }}</td>
                                <td>
                                    @php
                                        $adj = $adjustedBalances[$appt->id] ?? null;
                                        $rawCredit = $adj ? $adj['raw_credit'] : ($appt->credit_amount ?? 0);
                                        $rawDebit = $adj ? $adj['raw_debit'] : ($appt->debit_amount ?? 0);
                                        $adjCredit = $adj ? $adj['adjusted_credit'] : $rawCredit;
                                        $adjDebit = $adj ? $adj['adjusted_debit'] : $rawDebit;
                                        $creditUsed = $adj ? $adj['credit_used'] : 0;
                                        $creditCovered = $adj ? $adj['credit_covered'] : 0;
                                    @endphp
                                    @if($rawDebit > 0)
                                        @if($adjDebit == 0 && $creditCovered > 0)
                                            <span class="badge bg-label-info" title="Due of ₹{{ number_format($rawDebit, 2) }} adjusted from Customer Credit"><i class="bx bx-check-circle me-1"></i>Due: ₹0.00 (Adj ₹{{ number_format($creditCovered, 2) }})</span>
                                        @elseif($adjDebit > 0 && $creditCovered > 0)
                                            <span class="badge bg-label-danger" title="₹{{ number_format($creditCovered, 2) }} adjusted from Credit"><i class="bx bx-minus-circle me-1"></i>Due: ₹{{ number_format($adjDebit, 2) }} (Adj ₹{{ number_format($creditCovered, 2) }})</span>
                                        @else
                                            <span class="badge bg-label-danger"><i class="bx bx-minus-circle me-1"></i>Due: ₹{{ number_format($rawDebit, 2) }}</span>
                                        @endif
                                    @elseif($rawCredit > 0)
                                        @if($creditUsed > 0 && $adjCredit == 0)
                                            <span class="badge bg-label-success" title="₹{{ number_format($rawCredit, 2) }} Credit adjusted towards other appointments"><i class="bx bx-check-circle me-1"></i>Credit: ₹{{ number_format($rawCredit, 2) }} (Adjusted)</span>
                                        @elseif($creditUsed > 0 && $adjCredit > 0)
                                            <span class="badge bg-label-success" title="₹{{ number_format($creditUsed, 2) }} Credit adjusted"><i class="bx bx-plus-circle me-1"></i>Credit: ₹{{ number_format($adjCredit, 2) }} (Used ₹{{ number_format($creditUsed, 2) }})</span>
                                        @else
                                            <span class="badge bg-label-success"><i class="bx bx-plus-circle me-1"></i>Credit: ₹{{ number_format($rawCredit, 2) }}</span>
                                        @endif
                                    @else
                                        <span class="badge bg-label-secondary">Clear</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-label-{{ $appt->status == 'completed' ? 'success' : ($appt->status == 'cancelled' ? 'danger' : 'warning') }}">
                                        {{ ucfirst(str_replace('_', ' ', $appt->status)) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No appointments yet</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Service History</h5>
                </div>
                <div class="table-responsive text-nowrap">
<table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Service</th>
                                <th>Staff</th>
                                <th>Duration</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customer->appointments->where('status', 'completed') as $appt)
                            <tr>
                                <td>{{ date('d M Y', strtotime($appt->date)) }}</td>
                                <td>{{ $appt->service->name ?? 'N/A' }}</td>
                                <td>{{ $appt->staff->full_name ?? 'N/A' }}</td>
                                <td>{{ $appt->service->duration ?? 'N/A' }} min</td>
                                <td>₹{{ number_format($appt->service->price ?? 0, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No completed services</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
