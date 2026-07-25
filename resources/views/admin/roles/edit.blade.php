@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Edit Role</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Role Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $role->name) }}" placeholder="e.g. Branch Manager" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <label class="form-label fw-semibold mb-1">Permissions</label>
                <div class="permissions-info mb-3">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-primary bg-opacity-10 text-primary">Module Matrix</span>
                        <span class="text-muted small">Assign access by module and action for clearer control.</span>
                    </div>
                    <div class="small">Use row selectors to grant all actions on a module, or choose actions individually.</div>
                </div>

                @php
                    $selectedPermissions = old('permissions', $rolePermissions);
                    $actionLabels = ['view' => 'View', 'create' => 'Add', 'edit' => 'Edit', 'delete' => 'Delete'];
                    $permissionRows = [];
                    $otherPermissions = [];
                    foreach ($permissions as $entity => $perms) {
                        foreach ($perms as $perm) {
                            $parts = explode(' ', $perm->name);
                            $action = array_shift($parts);
                            if (array_key_exists($action, $actionLabels)) {
                                $permissionRows[$entity][$action] = $perm;
                            } else {
                                $otherPermissions[$entity][] = $perm;
                            }
                        }
                    }
                @endphp

                <div class="table-responsive mb-3">
                    <table class="table table-bordered permissions-table">
                        <thead class="table-light">
                            <tr>
                                <th>Module</th>
                                <th class="text-center">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input all-select-all" type="checkbox" id="select_all_permissions">
                                        <label class="form-check-label" for="select_all_permissions">All</label>
                                    </div>
                                </th>
                                @foreach($actionLabels as $label)
                                    <th class="text-center">{{ $label }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($permissionRows as $entity => $rowPermissions)
                                @php $rowSlug = Illuminate\Support\Str::slug($entity, '_'); @endphp
                                <tr data-entity="{{ $rowSlug }}">
                                    <td>
                                        <span class="module-chip">{{ ucwords($entity) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check mb-0">
                                            <input class="form-check-input row-select-all" type="checkbox" data-row="{{ $rowSlug }}" id="row_all_{{ $rowSlug }}">
                                        </div>
                                    </td>
                                    @foreach(array_keys($actionLabels) as $actionKey)
                                        <td class="text-center">
                                            @if(isset($rowPermissions[$actionKey]))
                                                <div class="form-check mb-0">
                                                    <input class="form-check-input permissions-checkbox entity-checkbox entity-{{ $rowSlug }}" type="checkbox" name="permissions[]" value="{{ $rowPermissions[$actionKey]->id }}" id="perm_{{ $rowPermissions[$actionKey]->id }}" {{ in_array($rowPermissions[$actionKey]->id, $selectedPermissions) ? 'checked' : '' }}>
                                                </div>
                                            @else
                                                &mdash;
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if(count($otherPermissions))
                    <div class="card mb-3 border shadow-sm">
                        <div class="card-header bg-light fw-semibold">Other Permissions</div>
                        <div class="card-body">
                            <div class="row g-3">
                                @foreach($otherPermissions as $entity => $perms)
                                    <div class="col-12">
                                        <div class="fw-semibold mb-2">{{ ucwords($entity) }}</div>
                                    </div>
                                    @foreach($perms as $perm)
                                        <div class="col-auto">
                                            <div class="form-check">
                                                <input class="form-check-input permissions-checkbox" type="checkbox" name="permissions[]" value="{{ $perm->id }}" id="perm_{{ $perm->id }}" {{ in_array($perm->id, $selectedPermissions) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm_{{ $perm->id }}">{{ ucfirst($perm->name) }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
                @error('permissions')<div class="text-danger small mb-3">{{ $message }}</div>@enderror

                <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('style')
<style>
.permissions-info {
    padding: 1rem 1.25rem;
    background: #eef4ff;
    border: 1px solid #d6e2ff;
    border-radius: 0.75rem;
}
.permissions-info .badge {
    font-size: 0.75rem;
}
.permissions-table {
    border-radius: 1rem;
    overflow: hidden;
    min-width: 650px;
    border: 1px solid #e6ebff;
    box-shadow: 0 14px 30px rgba(95, 103, 243, 0.05);
}
.permissions-table thead {
    background: linear-gradient(135deg, #eef4ff 0%, #dfe7ff 100%);
}
.permissions-table th {
    font-weight: 700;
    color: #1f2a60;
    border-right: 1px solid rgba(124, 143, 255, 0.16);
    border-bottom: none;
    padding: 1rem 0.85rem;
}
.permissions-table th:last-child {
    border-right: none;
}
.permissions-table td {
    background: #ffffff;
    border-right: 1px solid rgba(108, 122, 255, 0.08);
    border-bottom: 1px solid rgba(108, 122, 255, 0.08);
    padding: 0.9rem 0.85rem;
    white-space: nowrap;
}
.permissions-table tr:hover td {
    background: #f6f8ff;
}
.permissions-table tr:last-child td {
    border-bottom: none;
}
.module-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.55rem 0.85rem;
    border-radius: 0.85rem;
    background: #eef4ff;
    color: #1f2a60;
    font-weight: 600;
}
.permissions-table .form-check-input {
    transform: scale(1.2);
    accent-color: #5e72ff;
}
.permissions-table .form-check-input:checked {
    box-shadow: 0 0 0 0.2rem rgba(94, 114, 255, 0.18);
}
.other-permissions .form-check {
    padding: 0.35rem 0;
}
.other-permissions .form-check-label {
    margin-left: 0.45rem;
}
</style>
@endsection

@section('script')
<script>
function updatePermissionState() {
    var allCheckboxes = document.querySelectorAll('.permissions-checkbox');
    var allCheckedCount = document.querySelectorAll('.permissions-checkbox:checked').length;
    var allSelect = document.querySelector('.all-select-all');
    if (allSelect) {
        allSelect.checked = allCheckboxes.length > 0 && allCheckedCount === allCheckboxes.length;
    }

    document.querySelectorAll('.permissions-table tbody tr').forEach(function(row) {
        var rowCheckboxes = row.querySelectorAll('.entity-checkbox');
        var rowChecked = row.querySelectorAll('.entity-checkbox:checked');
        var rowSelect = row.querySelector('.row-select-all');
        if (rowSelect) {
            rowSelect.checked = rowCheckboxes.length > 0 && rowChecked.length === rowCheckboxes.length;
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.permissions-checkbox').forEach(function(cb) {
        cb.addEventListener('change', updatePermissionState);
    });

    document.querySelectorAll('.row-select-all').forEach(function(cb) {
        cb.addEventListener('change', function() {
            var row = this.closest('tr');
            var checked = this.checked;
            row.querySelectorAll('.entity-checkbox').forEach(function(c) {
                c.checked = checked;
            });
            updatePermissionState();
        });
    });

    var allSelect = document.querySelector('.all-select-all');
    if (allSelect) {
        allSelect.addEventListener('change', function() {
            var checked = this.checked;
            document.querySelectorAll('.permissions-checkbox').forEach(function(cb) {
                cb.checked = checked;
            });
            updatePermissionState();
        });
    }

    updatePermissionState();
});
</script>
@endsection
