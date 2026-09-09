@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-sm-6 col-lg-4 mb-4 mb-lg-0">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="d-block text-muted mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase;">Total Feedbacks</span>
                            <h3 class="mb-0">{{ $feedbacks->count() }}</h3>
                        </div>
                        <div class="stat-card-icon stat-icon-purple">
                            <i class="bx bx-message-square-dots"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4 mb-4 mb-lg-0">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="d-block text-muted mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase;">Positive (4-5 ★)</span>
                            <h3 class="mb-0 text-success">{{ $positiveCount }}</h3>
                        </div>
                        <div class="stat-card-icon stat-icon-emerald">
                            <i class="bx bx-star"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="d-block text-muted mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase;">Negative (1-2 ★)</span>
                            <h3 class="mb-0 text-danger">{{ $negativeCount }}</h3>
                        </div>
                        <div class="stat-card-icon stat-icon-amber">
                            <i class="bx bx-star"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Staff-wise Ratings -->
        <div class="col-md-6 mb-4 mb-md-0">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Staff-wise Ratings</h5>
                    <small class="text-muted">Average rating per staff member</small>
                </div>
                <div class="card-body">
                    @forelse($staffRatings as $staff)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded-circle bg-label-primary" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                                    {{ substr($staff->staff_name ?? $staff->name, 0, 2) }}
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0 text-sm">{{ $staff->staff_name ?? $staff->name }}</h6>
                                <small class="text-muted">{{ $staff->total }} reviews</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="fw-semibold">{{ number_format($staff->avg_rating, 1) }}</span>
                            <span class="text-muted">/ 5</span>
                            <div class="text-warning" style="font-size: 0.75rem;">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= round($staff->avg_rating))
                                        <i class="bx bxs-star"></i>
                                    @else
                                        <i class="bx bx-star"></i>
                                    @endif
                                @endfor
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center mb-0">No staff ratings yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Service-wise Ratings -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Service-wise Ratings</h5>
                    <small class="text-muted">Average rating per service</small>
                </div>
                <div class="card-body">
                    @forelse($serviceRatings as $service)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-0 text-sm">{{ $service->service_name ?? $service->name }}</h6>
                            <small class="text-muted">{{ $service->total }} reviews</small>
                        </div>
                        <div class="text-end">
                            <span class="fw-semibold">{{ number_format($service->avg_rating, 1) }}</span>
                            <span class="text-muted">/ 5</span>
                            <div class="text-warning" style="font-size: 0.75rem;">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= round($service->avg_rating))
                                        <i class="bx bxs-star"></i>
                                    @else
                                        <i class="bx bx-star"></i>
                                    @endif
                                @endfor
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center mb-0">No service ratings yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Feedbacks Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">All Feedbacks</h5>
                    <small class="text-muted">Customer feedback and ratings</small>
                </div>
                <div class="table-responsive text-nowrap">
<table class="table table-hover" id="feedbacksTable">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Appointment</th>
                                <th>Staff</th>
                                <th>Service</th>
                                <th>Rating</th>
                                <th>Comments</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($feedbacks as $feedback)
                            <tr>
                                <td class="fw-semibold">{{ $feedback->customer_name ?? $feedback->customer->name ?? 'N/A' }}</td>
                                <td>#{{ $feedback->appointment_id ?? 'N/A' }}</td>
                                <td>{{ $feedback->staff_name ?? $feedback->staff->name ?? 'N/A' }}</td>
                                <td>{{ $feedback->service_name ?? $feedback->service->name ?? 'N/A' }}</td>
                                <td>
                                    <div class="text-warning text-nowrap">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $feedback->rating)
                                                <i class="bx bxs-star"></i>
                                            @else
                                                <i class="bx bx-star"></i>
                                            @endif
                                        @endfor
                                    </div>
                                </td>
                                <td>{{ Str::limit($feedback->comments, 50) }}</td>
                                <td>{{ \Carbon\Carbon::parse($feedback->created_at)->format('d M Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $feedbacks->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    $('#feedbacksTable').DataTable({
        paging: false,
        info: false
    });
});
</script>
@endsection
