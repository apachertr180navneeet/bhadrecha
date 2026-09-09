@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h5 class="card-title mb-0">Activity Logs</h5>
                        <small class="text-muted">Monitor all system activity and user actions</small>
                    </div>
                </div>
                <div class="table-responsive text-nowrap">
<table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date / Time</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Module</th>
                                <th>Description</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td>
                                    <span class="text-nowrap">{{ $log->created_at->format('M d, Y') }}</span>
                                    <br>
                                    <small class="text-muted text-nowrap">{{ $log->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    @if($log->user)
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <span class="avatar-initial rounded-circle bg-label-primary" style="width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 600;">
                                                    {{ strtoupper(substr($log->user->first_name, 0, 1)) }}{{ strtoupper(substr($log->user->last_name, 0, 1)) }}
                                                </span>
                                            </div>
                                            <span class="fw-semibold">{{ $log->user->first_name }} {{ $log->user->last_name }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted">System</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $actionBadge = match($log->action) {
                                            'created' => 'success',
                                            'updated' => 'info',
                                            'deleted' => 'danger',
                                            'login' => 'primary',
                                            'logout' => 'secondary',
                                            default => 'warning'
                                        };
                                    @endphp
                                    <span class="badge bg-label-{{ $actionBadge }} text-uppercase">{{ $log->action }}</span>
                                </td>
                                <td>{{ $log->module ?? '—' }}</td>
                                <td>{{ $log->description }}</td>
                                <td><code>{{ $log->ip_address ?? '—' }}</code></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No activity logs found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($logs->hasPages())
                <div class="card-footer">
                    {{ $logs->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
