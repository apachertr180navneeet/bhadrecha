@extends('admin.layouts.app') @section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">Expense Categories</h5>
                        <small class="text-muted">Manage your expense categories</small>
                    </div>
                    <div>
                        <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-secondary me-2">
                            <i class="bx bx-arrow-back me-1"></i> Back to Expenses
                        </a>
                        @can('create expense categories')
                        <button
                            type="button"
                            class="btn btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#categoryModal"
                        >
                            <i class="bx bx-plus me-1"></i> Add Category
                        </button>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover" id="categoriesTable">
                            <thead>
                                <tr>
                                    @if(auth()->user()->can('edit expense categories') || auth()->user()->can('delete expense categories'))
                                    <th class="text-center text-nowrap" style="width: 80px;">Actions</th>
                                    @endif
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Expenses Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $category)
                                <tr>
                                    @if(auth()->user()->can('edit expense categories') || auth()->user()->can('delete expense categories'))
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
                                                @can('edit expense categories')
                                                <li>
                                                    <a
                                                        class="dropdown-item"
                                                        href="javascript:void(0);"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#categoryModal"
                                                        data-id="{{ $category->id }}"
                                                        data-name="{{ $category->name }}"
                                                        data-description="{{ $category->description }}"
                                                        data-status="{{ $category->status }}"
                                                    >
                                                        <i class="bx bx-edit-alt me-1"></i> Edit
                                                    </a>
                                                </li>
                                                @endcan
                                                @can('delete expense categories')
                                                <li>
                                                    <a
                                                        class="dropdown-item text-danger delete-category"
                                                        href="javascript:void(0);"
                                                        data-id="{{ $category->id }}"
                                                    >
                                                        <i class="bx bx-trash me-1"></i> Delete
                                                    </a>
                                                </li>
                                                @endcan
                                            </ul>
                                        </div>
                                    </td>
                                    @endif
                                    <td class="fw-semibold">{{ $category->name }}</td>
                                    <td>{{ Str::limit($category->description, 50) }}</td>
                                    <td>
                                        @if($category->status == 1)
                                        <span class="badge bg-label-success">Active</span>
                                        @else
                                        <span class="badge bg-label-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-label-info">{{ $category->expenses_count }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{{ $categories->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalTitle">Add Expense Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="categoryForm">
                @csrf
                <input type="hidden" name="id" id="categoryId" />
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="categoryName">Name <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control"
                            name="name"
                            id="categoryName"
                            placeholder="Enter category name"
                            required
                        />
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="categoryDescription">Description</label>
                        <textarea
                            class="form-control"
                            name="description"
                            id="categoryDescription"
                            rows="3"
                            placeholder="Enter description"
                        ></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="categoryStatus">Status <span class="text-danger">*</span></label>
                        <select class="form-select" name="status" id="categoryStatus" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="categorySaveBtn">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection @section('script')
<script>
    $(document).ready(function () {
        let table = $("#categoriesTable").DataTable({
            paging: false,
            info: false,
            "order": []
        });

        $("#categoryModal").on("show.bs.modal", function (e) {
            let btn = $(e.relatedTarget);
            let id = btn.data("id");
            let modal = $(this);

            $("#categoryForm")[0].reset();
            $("#categoryForm").removeClass("was-validated");
            $(".invalid-feedback").text("");

            if (id) {
                modal.find(".modal-title").text("Edit Expense Category");
                modal.find("#categoryId").val(id);
                modal.find("#categoryName").val(btn.data("name"));
                modal.find("#categoryDescription").val(btn.data("description"));
                modal.find("#categoryStatus").val(btn.data("status"));
                modal.find("#categorySaveBtn").text("Update");
            } else {
                modal.find(".modal-title").text("Add Expense Category");
                modal.find("#categoryId").val("");
                modal.find("#categorySaveBtn").text("Save");
            }
        });

        $("#categoryModal").on("hidden.bs.modal", function () {
            $("#categoryForm")[0].reset();
            $("#categoryForm").removeClass("was-validated");
            $(".invalid-feedback").text("");
        });

        $("#categoryForm").on("submit", function (e) {
            e.preventDefault();
            let form = $(this);
            let id = $("#categoryId").val();
            let url = id
                ? '{{ route("admin.expense-categories.update", ":id") }}'.replace(":id", id)
                : '{{ route("admin.expense-categories.store") }}';
            let method = id ? "PUT" : "POST";

            $.ajax({
                url: url,
                method: method,
                data: form.serialize(),
                success: function (res) {
                    if (res.success) {
                        $("#categoryModal").modal("hide");
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

        $(document).on("click", ".delete-category", function () {
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
                        url: '{{ route("admin.expense-categories.destroy", ":id") }}'.replace(":id", id),
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
