@extends('admin.layouts.app') @section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="card-title mb-0">Expenses</h5>
                        <small class="text-muted">Manage your expenses</small>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        @can('view expense categories')
                        <a href="{{ route('admin.expense-categories.index') }}" class="btn btn-outline-primary me-1 me-sm-2">
                            <i class="bx bx-category me-1"></i> Manage Categories
                        </a>
                        @endcan
                        @can('create expenses')
                        <button
                            type="button"
                            class="btn btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#expenseModal"
                        >
                            <i class="bx bx-plus me-1"></i> Add Expense
                        </button>
                        @endcan
                    </div>
                </div>
                <!-- Filter Section -->
                <div class="card-body border-bottom">
                    <form method="GET" action="{{ route('admin.expenses.index') }}" id="expenseFilterForm" class="row g-3 align-items-end">
                        <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                            <label class="form-label text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Date From</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                            <label class="form-label text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Date To</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                            <label class="form-label text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                            <label class="form-label text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Store</label>
                            <select name="store_id" id="expenseFilterStore" class="form-select" {{ (auth()->user()->store_id && !($canViewAllStores ?? (auth()->user()->hasRole('Admin') || auth()->user()->can('view stores')))) ? 'disabled' : '' }}>
                                <option value="">All Stores</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}" {{ (string)($selectedStoreId ?? request('store_id')) === (string)$store->id ? 'selected' : '' }}>{{ $store->store_name ?? $store->name }}</option>
                                @endforeach
                            </select>
                            @if(auth()->user()->store_id && !($canViewAllStores ?? (auth()->user()->hasRole('Admin') || auth()->user()->can('view stores'))))
                                <input type="hidden" name="store_id" value="{{ auth()->user()->store_id }}">
                            @endif
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                            <label class="form-label text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600;">Branch</label>
                            <select name="branch_id" id="expenseFilterBranch" class="form-select" {{ (auth()->user()->branch_id && !($canViewAllBranches ?? (auth()->user()->hasRole('Admin') || auth()->user()->can('view branches')))) ? 'disabled' : '' }}>
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" data-store-id="{{ $branch->store_id }}" {{ (string)($selectedBranchId ?? request('branch_id')) === (string)$branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            @if(auth()->user()->branch_id && !($canViewAllBranches ?? (auth()->user()->hasRole('Admin') || auth()->user()->can('view branches'))))
                                <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                            @endif
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-xl-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="bx bx-filter-alt me-1"></i>Filter</button>
                            <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-secondary flex-grow-1"><i class="bx bx-reset me-1"></i>Reset</a>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover" id="expensesTable">
                            <thead>
                                <tr>
                                    @if(auth()->user()->can('edit expenses') || auth()->user()->can('delete expenses'))
                                    <th class="text-center text-nowrap" style="width: 80px;">Actions</th>
                                    @endif
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Amount</th>
                                    <th>Remarks</th>
                                    <th>Attachment</th>
                                    <th>Store</th>
                                    <th>Branch</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expenses as $expense)
                                <tr>
                                    @if(auth()->user()->can('edit expenses') || auth()->user()->can('delete expenses'))
                                    <td class="text-center text-nowrap">
                                        <div class="dropdown">
                                            <button
                                                class="btn p-0 dropdown-toggle hide-arrow"
                                                type="button"
                                                data-bs-toggle="dropdown"
                                                data-bs-boundary="viewport"
                                            >
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                @can('edit expenses')
                                                <li>
                                                    <a
                                                        class="dropdown-item"
                                                        href="javascript:void(0);"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#expenseModal"
                                                        data-id="{{ $expense->id }}"
                                                        data-date="{{ \Carbon\Carbon::parse($expense->date)->format('Y-m-d') }}"
                                                        data-category_id="{{ $expense->category_id }}"
                                                        data-amount="{{ $expense->amount }}"
                                                        data-remarks="{{ $expense->remarks }}"
                                                        data-store_id="{{ $expense->store_id }}"
                                                        data-branch_id="{{ $expense->branch_id }}"
                                                    >
                                                        <i class="bx bx-edit-alt me-1"></i> Edit
                                                    </a>
                                                </li>
                                                @endcan
                                                @can('delete expenses')
                                                <li>
                                                    <a
                                                        class="dropdown-item text-danger delete-expense"
                                                        href="javascript:void(0);"
                                                        data-id="{{ $expense->id }}"
                                                    >
                                                        <i class="bx bx-trash me-1"></i> Delete
                                                    </a>
                                                </li>
                                                @endcan
                                            </ul>
                                        </div>
                                    </td>
                                    @endif
                                    <td>{{ \Carbon\Carbon::parse($expense->date)->format('d M Y') }}</td>
                                    <td>
                                        <span class="badge bg-label-info">{{ $expense->category->name ?? 'N/A' }}</span>
                                    </td>
                                    <td class="fw-semibold">₹{{ number_format($expense->amount, 2) }}</td>
                                    <td>{{ Str::limit($expense->remarks, 40) }}</td>
                                    <td>
                                        @if($expense->attachment)
                                        <a
                                            href="{{ asset($expense->attachment) }}"
                                            target="_blank"
                                            class="btn btn-sm btn-label-primary"
                                        >
                                            <i class="bx bx-download"></i>
                                        </a>
                                        @else
                                        <span class="text-muted">--</span>
                                        @endif
                                    </td>
                                    <td>{{ $expense->store->store_name ?? $expense->store->name ?? 'N/A' }}</td>
                                    <td>{{ $expense->branch->name ?? 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{{ $expenses->withQueryString()->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="expenseModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="expenseModalTitle">Add Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="expenseForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="expenseId" />
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="expenseDate">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="date" id="expenseDate" required />
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="expenseCategory"
                            >Category <span class="text-danger">*</span></label
                        >
                        <select class="form-select" name="category_id" id="expenseCategory" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="expenseAmount">Amount <span class="text-danger">*</span></label>
                        <input
                            type="number"
                            step="0.01"
                            class="form-control"
                            name="amount"
                            id="expenseAmount"
                            placeholder="0.00"
                            required
                        />
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="expenseRemarks">Remarks</label>
                        <textarea
                            class="form-control"
                            name="remarks"
                            id="expenseRemarks"
                            rows="2"
                            placeholder="Enter remarks"
                        ></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="expenseModalStore">Store <span class="text-danger">*</span></label>
                        <select class="form-select" name="store_id" id="expenseModalStore" required {{ (auth()->user()->store_id && !($canViewAllStores ?? (auth()->user()->hasRole('Admin') || auth()->user()->can('view stores')))) ? 'disabled' : '' }}>
                            <option value="">Select Store</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}">{{ $store->store_name ?? $store->name }}</option>
                            @endforeach
                        </select>
                        @if(auth()->user()->store_id && !($canViewAllStores ?? (auth()->user()->hasRole('Admin') || auth()->user()->can('view stores'))))
                            <input type="hidden" name="store_id" value="{{ auth()->user()->store_id }}">
                        @endif
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="expenseModalBranch">Branch <span class="text-danger">*</span></label>
                        <select class="form-select" name="branch_id" id="expenseModalBranch" required {{ (auth()->user()->branch_id && !($canViewAllBranches ?? (auth()->user()->hasRole('Admin') || auth()->user()->can('view branches')))) ? 'disabled' : '' }}>
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" data-store-id="{{ $branch->store_id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        @if(auth()->user()->branch_id && !($canViewAllBranches ?? (auth()->user()->hasRole('Admin') || auth()->user()->can('view branches'))))
                            <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                        @endif
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="expenseAttachment">Attachment</label>
                        <input type="file" class="form-control" name="attachment" id="expenseAttachment" />
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="expenseSaveBtn">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection @section('script')
<script>
    $(document).ready(function () {
        let table = $("#expensesTable").DataTable({
            paging: false,
            info: false,
            "order": []
        });

        if (window.setupDependentBranchSelect) {
            window.setupDependentBranchSelect('#expenseFilterStore', '#expenseFilterBranch');
            window.setupDependentBranchSelect('#expenseModalStore', '#expenseModalBranch');
        }

        $("#expenseModal").on("show.bs.modal", function (e) {
            let btn = $(e.relatedTarget);
            let id = btn.data("id");
            let modal = $(this);

            $("#expenseForm")[0].reset();
            $("#expenseForm").removeClass("was-validated");
            $(".invalid-feedback").text("");

            if (id) {
                modal.find(".modal-title").text("Edit Expense");
                modal.find("#expenseId").val(id);
                modal.find("#expenseDate").val(btn.data("date"));
                modal.find("#expenseCategory").val(btn.data("category_id"));
                modal.find("#expenseAmount").val(btn.data("amount"));
                modal.find("#expenseRemarks").val(btn.data("remarks"));
                modal.find("#expenseModalStore").val(btn.data("store_id"));
                if (window.setupDependentBranchSelect) {
                    window.setupDependentBranchSelect('#expenseModalStore', '#expenseModalBranch');
                }
                modal.find("#expenseModalBranch").val(btn.data("branch_id"));
                modal.find("#expenseSaveBtn").text("Update");
            } else {
                modal.find(".modal-title").text("Add Expense");
                modal.find("#expenseId").val("");
                if (window.setupDependentBranchSelect) {
                    window.setupDependentBranchSelect('#expenseModalStore', '#expenseModalBranch');
                }
                modal.find("#expenseSaveBtn").text("Save");
            }
        });

        $("#expenseModal").on("hidden.bs.modal", function () {
            $("#expenseForm")[0].reset();
            $("#expenseForm").removeClass("was-validated");
            $(".invalid-feedback").text("");
        });

        $("#expenseForm").on("submit", function (e) {
            e.preventDefault();
            let form = $(this);
            let id = $("#expenseId").val();
            let url = id
                ? '{{ route("admin.expenses.update", ":id") }}'.replace(":id", id)
                : '{{ route("admin.expenses.store") }}';
            let method = id ? "PUT" : "POST";
            let formData = new FormData(this);
            if (id) formData.append("_method", "PUT");

            $.ajax({
                url: url,
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    if (res.success) {
                        $("#expenseModal").modal("hide");
                        showSuccessToast(res.message);
                        setTimeout(() => location.reload(), 500);
                    }
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function (key, msg) {
                            let input = form.find('[name="' + key + '"]');
                            input.addClass("is-invalid");
                            input.siblings(".invalid-feedback").text(msg[0]);
                        });
                    }
                },
            });
        });

        $(document).on("click", ".delete-expense", function () {
            let id = $(this).data("id");
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#8592a3',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("admin.expenses.destroy", ":id") }}'.replace(":id", id),
                        method: "DELETE",
                        data: { _token: "{{ csrf_token() }}" },
                        success: function (res) {
                            if (res.success) {
                                showSuccessToast(res.message);
                                setTimeout(() => location.reload(), 500);
                            }
                        },
                    });
                }
            });
        });
    });
</script>
@endsection
