@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">Bill Formats</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0 mt-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">Masters</li>
                    <li class="breadcrumb-item active">Bill Formats</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.masters.bill-formats.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Add Bill Format
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body border-bottom py-3">
            <form method="GET" class="row g-2">
                <div class="col-12 col-md-4">
                    <select name="company_id" class="form-select">
                        <option value="">All Companies</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by format name..." value="{{ request('search') }}">
                </div>
                <div class="col-12 col-md-auto"><button type="submit" class="btn btn-outline-secondary w-100"><i class="bx bx-search me-1"></i> Search</button></div>
                @if(request()->hasAny(['company_id', 'search']))
                <div class="col-12 col-md-auto"><a href="{{ route('admin.masters.bill-formats.index') }}" class="btn btn-outline-danger w-100"><i class="bx bx-x me-1"></i> Clear</a></div>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Company</th>
                        <th>Format Name</th>
                        <th>Depot</th>
                        <th>Party</th>
                        <th>Fields</th>
                        <th>GRN New Page</th>
                        <th>GST Rate</th>
                        <th>Created By</th>
                        <th class="text-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($formats as $index => $format)
                    <tr>
                        <td class="text-nowrap">{{ $formats->firstItem() + $index }}</td>
                        <td>{{ $format->company?->name ?? '-' }}</td>
                        <td><strong>{{ $format->format_name }}</strong></td>
                        <td>{{ $format->depot?->name ?? '—' }}</td>
                        <td>{{ $format->party?->name ?? '—' }}</td>
                        <td>{{ is_array($format->visible_fields) ? count($format->visible_fields) : 0 }} fields</td>
                        <td>{!! $format->grn_new_page ? '<span class="badge bg-label-success">Yes</span>' : '<span class="badge bg-label-secondary">No</span>' !!}</td>
                        <td>{{ $format->gstMaster?->gst_rate ?? '—' }}</td>
                        <td>{{ $format->user?->name ?? '-' }}</td>
                        <td class="text-center text-nowrap">
                            <a href="{{ route('admin.masters.bill-formats.edit', $format->id) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Edit">
                                <i class="bx bx-edit"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-icon btn-outline-danger"
                                onclick="deleteFormat({{ $format->id }}, '{{ $format->format_name }}')" title="Delete">
                                <i class="bx bx-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center py-4 text-muted">No bill formats found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $formats->withQueryString()->links() }}</div>
    </div>
</div>

<form id="deleteFormatForm" method="POST" style="display:none;">@csrf @method('DELETE')</form>

@section('style')
<style>
    .field-checkbox-grid { max-height: 400px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px; }
    .field-checkbox-grid .form-check { margin-bottom: 6px; }
</style>
@endsection

@section('script')
<script>
function deleteFormat(id, name) {
    if (confirm('Delete bill format "' + name + '"? This cannot be undone.')) {
        var form = document.getElementById('deleteFormatForm');
        form.action = '{{ route("admin.masters.bill-formats.index") }}/' + id;
        form.submit();
    }
}
</script>
@endsection
@endsection
