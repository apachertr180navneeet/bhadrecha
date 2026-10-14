@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h5 class="fw-bold mb-0">Trip Details</h5>
                @php
                    $tripStatus = $trip->status ?? 'pending';
                    $statusBadges = [
                        'pending' => 'bg-label-warning',
                        'complete' => 'bg-label-success',
                        'reject' => 'bg-label-danger'
                    ];
                @endphp
                <span class="badge {{ $statusBadges[$tripStatus] ?? 'bg-label-warning' }}">{{ ucfirst($tripStatus) }}</span>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0 mt-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.transport.trips.index') }}">Trip Details</a></li>
                    <li class="breadcrumb-item active">LR #{{ $trip->builty->lr_no ?? $trip->id }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.transport.trips.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back to Trips
            </a>
            @canany(['view bulties', 'view trips', 'view trip details'])
            @if($trip->builty)
            <a href="{{ route('admin.transport.bulties.show', $trip->builty->id) }}" class="btn btn-outline-primary">
                <i class="bx bx-receipt me-1"></i> View Bilty
            </a>
            @endif
            @endcanany
            @canany(['edit trips', 'edit trip details'])
            <a href="{{ route('admin.transport.trips.edit', $trip->id) }}" class="btn btn-warning">
                <i class="bx bx-edit me-1"></i> Edit Trip
            </a>
            @endcanany
        </div>
    </div>

    <!-- Bilty Information Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light py-2">
            <h6 class="fw-bold mb-0 text-primary"><i class="bx bx-info-circle me-1"></i> Bilty & Vehicle Information</h6>
        </div>
        <div class="card-body pt-3">
            <div class="row g-3">
                <div class="col-md-3 col-6">
                    <label class="form-label small text-muted text-uppercase mb-1">LR Number</label>
                    <p class="fw-bold fs-6 mb-0 text-dark">{{ $trip->builty->lr_no ?? '-' }}</p>
                </div>
                <div class="col-md-3 col-6">
                    <label class="form-label small text-muted text-uppercase mb-1">LR Date</label>
                    <p class="fw-semibold mb-0">{{ $trip->builty && $trip->builty->lr_date ? date('d M Y', strtotime($trip->builty->lr_date)) : '-' }}</p>
                </div>
                <div class="col-md-3 col-6">
                    <label class="form-label small text-muted text-uppercase mb-1">Vehicle Number</label>
                    <p class="fw-bold mb-0 text-primary">{{ $trip->builty->vehicle->vehicle_number ?? ($trip->vehicle->vehicle_number ?? '-') }}</p>
                </div>
                <div class="col-md-3 col-6">
                    <label class="form-label small text-muted text-uppercase mb-1">Driver</label>
                    <p class="fw-semibold mb-0">{{ $trip->builty->driver->name ?? '-' }} {{ $trip->builty && $trip->builty->driver && $trip->builty->driver->phone ? '('.$trip->builty->driver->phone.')' : '' }}</p>
                </div>
                <div class="col-md-3 col-6">
                    <label class="form-label small text-muted text-uppercase mb-1">Consignor</label>
                    <p class="fw-semibold mb-0">{{ $trip->builty->consignor->name ?? '-' }}</p>
                </div>
                <div class="col-md-3 col-6">
                    <label class="form-label small text-muted text-uppercase mb-1">Consignee</label>
                    <p class="fw-semibold mb-0">{{ $trip->builty->consignee->name ?? '-' }}</p>
                </div>
                <div class="col-md-3 col-6">
                    <label class="form-label small text-muted text-uppercase mb-1">Route (From &rarr; To)</label>
                    <p class="fw-semibold mb-0">{{ $trip->builty->originCity->name ?? '-' }} &rarr; {{ $trip->builty->destinationCity->name ?? '-' }}</p>
                </div>
                <div class="col-md-3 col-6">
                    <label class="form-label small text-muted text-uppercase mb-1">Branch / Company</label>
                    <p class="fw-semibold mb-0">{{ $trip->builty->branch->name ?? ($trip->builty->company->name ?? '-') }}</p>
                </div>
                <div class="col-md-3 col-6">
                    <label class="form-label small text-muted text-uppercase mb-1">Total Freight Amount</label>
                    <p class="fw-bold text-success mb-0">₹ {{ number_format($trip->builty->total_amount ?? 0, 2) }}</p>
                </div>
                <div class="col-md-3 col-6">
                    <label class="form-label small text-muted text-uppercase mb-1">Advance Amount</label>
                    <p class="fw-bold text-info mb-0">₹ {{ number_format($trip->builty->advance_amount ?? 0, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-2 col-6">
            <div class="card bg-label-info border-0 shadow-sm h-100">
                <div class="card-body py-2 px-3">
                    <div class="small fw-semibold text-uppercase text-muted">FastTag</div>
                    <h5 class="fw-bold text-info mb-0">₹ {{ number_format($trip->fasttag_total_amount ?? 0, 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card bg-label-primary border-0 shadow-sm h-100">
                <div class="card-body py-2 px-3">
                    <div class="small fw-semibold text-uppercase text-muted">Fuel Amount</div>
                    <h5 class="fw-bold text-primary mb-0">₹ {{ number_format($trip->fuel_amount ?? 0, 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card bg-label-warning border-0 shadow-sm h-100">
                <div class="card-body py-2 px-3">
                    <div class="small fw-semibold text-uppercase text-muted">AdBlue Amount</div>
                    <h5 class="fw-bold text-warning mb-0">₹ {{ number_format($trip->adblue_total_amount ?? 0, 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card bg-label-secondary border-0 shadow-sm h-100">
                <div class="card-body py-2 px-3">
                    <div class="small fw-semibold text-uppercase text-muted">Other Expenses</div>
                    <h5 class="fw-bold text-secondary mb-0">₹ {{ number_format($trip->other_amount ?? 0, 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card bg-label-dark border-0 shadow-sm h-100">
                <div class="card-body py-2 px-3">
                    <div class="small fw-semibold text-uppercase text-muted">Trip Advance</div>
                    <h5 class="fw-bold text-dark mb-0">₹ {{ number_format($trip->advance_total_amount ?? 0, 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            @php
                $totalTripExpense = floatval($trip->fasttag_total_amount ?? 0)
                    + floatval($trip->fuel_amount ?? 0)
                    + floatval($trip->adblue_total_amount ?? 0)
                    + floatval($trip->other_amount ?? 0)
                    + floatval($trip->advance_total_amount ?? 0);
            @endphp
            <div class="card bg-label-danger border-0 shadow-sm h-100">
                <div class="card-body py-2 px-3">
                    <div class="small fw-semibold text-uppercase text-muted">Total Expenses</div>
                    <h5 class="fw-bold text-danger mb-0">₹ {{ number_format($totalTripExpense, 2) }}</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- FastTag Details -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><i class="bx bx-credit-card-front me-1"></i> FastTag Details</h6>
            <span class="badge bg-label-info">{{ $trip->fastTagDetails->count() }} Record(s)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Location</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th class="text-end">One Way (₹)</th>
                            <th class="text-end">Return (₹)</th>
                            <th class="text-end">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trip->fastTagDetails as $idx => $ft)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td><strong>{{ $ft->location ?? '-' }}</strong></td>
                            <td>{{ $ft->date ? date('d M Y', strtotime($ft->date)) : '-' }}</td>
                            <td>{{ $ft->time ?? '-' }}</td>
                            <td class="text-end">{{ number_format($ft->one_way ?? 0, 2) }}</td>
                            <td class="text-end">{{ number_format($ft->return ?? 0, 2) }}</td>
                            <td class="text-end fw-bold">{{ number_format($ft->amount ?? 0, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-3 text-muted">No FastTag details recorded.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($trip->fastTagDetails->isNotEmpty())
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="6" class="text-end">Total FastTag:</td>
                            <td class="text-end">₹ {{ number_format($trip->fastTagDetails->sum('amount'), 2) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Fuel Details -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><i class="bx bx-gas-pump me-1"></i> Fuel Details</h6>
            <span class="badge bg-label-primary">{{ $trip->fuelDetails->count() }} Record(s)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Fuel Pump</th>
                            <th>Fuel Company</th>
                            <th>Slip No</th>
                            <th class="text-end">Litres</th>
                            <th class="text-end">Rate (₹)</th>
                            <th class="text-end">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trip->fuelDetails as $idx => $fd)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>{{ $fd->date ? date('d M Y', strtotime($fd->date)) : '-' }}</td>
                            <td>{{ $fd->fuelPump->name ?? '-' }}</td>
                            <td>{{ $fd->fuelPump->fuelCompany->name ?? '-' }}</td>
                            <td>{{ $fd->slip_no ?? '-' }}</td>
                            <td class="text-end">{{ number_format($fd->litre ?? 0, 2) }}</td>
                            <td class="text-end">{{ number_format($fd->rate ?? 0, 2) }}</td>
                            <td class="text-end fw-bold">₹ {{ number_format($fd->amount ?? 0, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-3 text-muted">No fuel details recorded.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($trip->fuelDetails->isNotEmpty())
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="5" class="text-end">Total:</td>
                            <td class="text-end">{{ number_format($trip->fuelDetails->sum('litre'), 2) }} L</td>
                            <td></td>
                            <td class="text-end">₹ {{ number_format($trip->fuelDetails->sum('amount'), 2) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- AdBlue Details -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><i class="bx bx-water me-1"></i> AdBlue Details</h6>
            <span class="badge bg-label-warning">{{ $trip->adblueDetails->count() }} Record(s)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>AdBlue Company</th>
                            <th class="text-end">Quantity (L)</th>
                            <th class="text-end">Rate (₹)</th>
                            <th class="text-end">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trip->adblueDetails as $idx => $ad)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>{{ $ad->date ? date('d M Y', strtotime($ad->date)) : '-' }}</td>
                            <td>{{ $ad->adBlueCompany->name ?? '-' }}</td>
                            <td class="text-end">{{ number_format($ad->qty ?? 0, 2) }}</td>
                            <td class="text-end">{{ number_format($ad->rate ?? 0, 2) }}</td>
                            <td class="text-end fw-bold">₹ {{ number_format($ad->amount ?? 0, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-3 text-muted">No AdBlue details recorded.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($trip->adblueDetails->isNotEmpty())
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="3" class="text-end">Total:</td>
                            <td class="text-end">{{ number_format($trip->adblueDetails->sum('qty'), 2) }} L</td>
                            <td></td>
                            <td class="text-end">₹ {{ number_format($trip->adblueDetails->sum('amount'), 2) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Other Amount & Advance Details (Two Columns) -->
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="bx bx-receipt me-1"></i> Other Expenses</h6>
                    <span class="badge bg-label-secondary">{{ $trip->otherAmountDetails->count() }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th class="text-end">Amount (₹)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($trip->otherAmountDetails as $idx => $od)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>{{ $od->date ? date('d M Y', strtotime($od->date)) : '-' }}</td>
                                    <td>{{ $od->description ?? '-' }}</td>
                                    <td class="text-end fw-bold">₹ {{ number_format($od->amount ?? 0, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">No other expenses.</td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($trip->otherAmountDetails->isNotEmpty())
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="3" class="text-end">Total:</td>
                                    <td class="text-end">₹ {{ number_format($trip->otherAmountDetails->sum('amount'), 2) }}</td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="bx bx-wallet me-1"></i> Advance Details</h6>
                    <span class="badge bg-label-dark">{{ $trip->advanceDetails->count() }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Payment Mode</th>
                                    <th>Description</th>
                                    <th class="text-end">Amount (₹)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($trip->advanceDetails as $idx => $adv)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>{{ $adv->date ? date('d M Y', strtotime($adv->date)) : '-' }}</td>
                                    <td><span class="badge bg-label-info">{{ ucfirst($adv->payment_mode ?? '-') }}</span></td>
                                    <td>{{ $adv->description ?? '-' }}</td>
                                    <td class="text-end fw-bold">₹ {{ number_format($adv->amount ?? 0, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-3 text-muted">No advance records.</td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($trip->advanceDetails->isNotEmpty())
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="4" class="text-end">Total:</td>
                                    <td class="text-end">₹ {{ number_format($trip->advanceDetails->sum('amount'), 2) }}</td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
