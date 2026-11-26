@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Add Role</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
                    <li class="breadcrumb-item active">Add</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" form="role-form" class="btn btn-primary"><i class="bx bx-save me-1"></i> Save Role</button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form id="role-form" action="{{ route('admin.roles.store') }}" method="POST">
                @csrf
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Role Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Branch Manager" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
                    <div>
                        <label class="form-label fw-semibold mb-1">Permissions</label>
                        <div class="permissions-info">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-primary bg-opacity-10 text-primary">Module Matrix</span>
                                <span class="text-muted small">Assign access by module and action for clearer control.</span>
                            </div>
                            <div class="small">Use row selectors to grant all actions on a module, or choose actions individually.</div>
                        </div>
                    </div>
                    <div style="min-width: 280px;" class="align-self-md-end mb-1">
                        <div class="input-group input-group-merge shadow-sm">
                            <span class="input-group-text bg-white"><i class="bx bx-search text-muted"></i></span>
                            <input type="text" id="permission_search" class="form-control" placeholder="Search modules or permissions..." autocomplete="off">
                            <button type="button" class="btn btn-outline-secondary d-none" id="clear_permission_search" title="Clear search">
                                <i class="bx bx-x"></i>
                            </button>
                        </div>
                    </div>
                </div>

                @php
                    $selectedPermissions = old('permissions', []);
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
                    <div class="card mb-3 border shadow-sm" id="other_permissions_container">
                        <div class="card-header bg-light fw-semibold">Other Permissions</div>
                        <div class="card-body">
                            <div class="row g-3">
                                @foreach($otherPermissions as $entity => $perms)
                                    <div class="col-12 other-permission-group" data-entity="{{ strtolower($entity) }}">
                                        <div class="fw-semibold mb-2 text-primary">{{ ucwords($entity) }}</div>
                                        <div class="row g-2">
                                            @foreach($perms as $perm)
                                                <div class="col-md-3 col-sm-6 other-permission-item">
                                                    <div class="form-check">
                                                        <input class="form-check-input permissions-checkbox" type="checkbox" name="permissions[]" value="{{ $perm->id }}" id="perm_{{ $perm->id }}" {{ in_array($perm->id, $selectedPermissions) ? 'checked' : '' }}>
                                                        <label class="form-check-label text-wrap" for="perm_{{ $perm->id }}">{{ ucfirst($perm->name) }}</label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
                @error('permissions')<div class="text-danger small mb-3">{{ $message }}</div>@enderror
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

    // Permission Search Filter (Module Matrix + Other Permissions)
    var searchInput = document.getElementById('permission_search');
    var clearBtn = document.getElementById('clear_permission_search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            var query = this.value.toLowerCase().trim();
            if (clearBtn) {
                clearBtn.classList.toggle('d-none', query === '');
            }

            // 1. Filter Module Matrix Table Rows
            var rows = document.querySelectorAll('.permissions-table tbody tr:not(#no_permission_match_row)');
            var hasMatrixMatch = false;

            rows.forEach(function(row) {
                var text = row.textContent.toLowerCase();
                var match = text.indexOf(query) !== -1;
                row.style.display = match ? '' : 'none';
                if (match) hasMatrixMatch = true;
            });

            var noMatchRow = document.getElementById('no_permission_match_row');
            if (!hasMatrixMatch) {
                if (!noMatchRow) {
                    var tbody = document.querySelector('.permissions-table tbody');
                    noMatchRow = document.createElement('tr');
                    noMatchRow.id = 'no_permission_match_row';
                    noMatchRow.innerHTML = '<td colspan="6" class="text-center py-4 text-muted"><i class="bx bx-search-alt-2 fs-3 d-block mb-1"></i>No modules matching "' + query + '"</td>';
                    tbody.appendChild(noMatchRow);
                } else {
                    noMatchRow.querySelector('td').innerHTML = '<i class="bx bx-search-alt-2 fs-3 d-block mb-1"></i>No modules matching "' + query + '"';
                    noMatchRow.style.display = '';
                }
            } else if (noMatchRow) {
                noMatchRow.style.display = 'none';
            }

            // 2. Filter Other Permissions
            var otherGroups = document.querySelectorAll('.other-permission-group');
            var hasAnyOtherMatch = false;

            otherGroups.forEach(function(group) {
                var groupText = group.getAttribute('data-entity') || '';
                var items = group.querySelectorAll('.other-permission-item');
                var groupHasMatch = false;

                items.forEach(function(item) {
                    var itemText = item.textContent.toLowerCase();
                    var itemMatch = groupText.indexOf(query) !== -1 || itemText.indexOf(query) !== -1;
                    item.style.display = itemMatch ? '' : 'none';
                    if (itemMatch) {
                        groupHasMatch = true;
                        hasAnyOtherMatch = true;
                    }
                });

                group.style.display = groupHasMatch ? '' : 'none';
            });

            var otherContainer = document.getElementById('other_permissions_container');
            if (otherContainer) {
                if (query !== '' && !hasAnyOtherMatch) {
                    otherContainer.style.display = 'none';
                } else {
                    otherContainer.style.display = '';
                }
            }
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            if (searchInput) {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input'));
                searchInput.focus();
            }
        });
    }
});
</script>
@endsection
