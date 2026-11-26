@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1">
                <span class="text-muted fw-light">Document Management /</span> Audit Log & Activity Trail
            </h4>
            <p class="text-muted mb-0">Complete security audit trail tracking uploads, edits, downloads, previews, and deletes.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Document</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>IP Address</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $index => $act)
                    <tr>
                        <td>{{ $activities->firstItem() + $index }}</td>
                        <td>
                            @if($act->document)
                                <a href="{{ route('admin.documents.show', $act->document->id) }}" class="fw-bold text-primary">
                                    {{ Str::limit($act->document->name, 25) }}
                                </a>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td><small class="fw-bold">{{ $act->user?->full_name ?? 'System' }}</small></td>
                        <td>
                            <span class="badge bg-label-info">{{ ucfirst($act->action) }}</span>
                        </td>
                        <td><small>{{ $act->description }}</small></td>
                        <td><code>{{ $act->ip_address }}</code></td>
                        <td><small>{{ $act->created_at->format('d M Y, h:i:s A') }}</small></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No activity log entries found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-end">
            {{ $activities->links() }}
        </div>
    </div>
</div>
@endsection
