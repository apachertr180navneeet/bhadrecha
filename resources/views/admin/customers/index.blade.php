@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="mb-0">Customers</h5>
                    <div class="d-flex flex-wrap align-items-center gap-2 w-100 w-sm-auto justify-content-sm-end">
                        <form method="GET" action="{{ route('admin.customers.index') }}" class="d-flex align-items-center gap-2 mb-0 flex-grow-1 flex-sm-grow-0">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Search customer..." value="{{ request('search') }}">
                                <button class="btn btn-outline-primary" type="submit"><i class="bx bx-search"></i> Search</button>
                                @if(request()->filled('search'))
                                    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i> Reset</a>
                                @endif
                            </div>
                        </form>
                        @can('create customers')
                        <a href="{{ route('admin.customers.create') }}" class="btn btn-primary text-nowrap flex-grow-1 flex-sm-grow-0 text-center">Add Customer</a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                    <table class="table table-hover" id="customersTable">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                @if(auth()->user()->can('view customers') || auth()->user()->can('edit customers') || auth()->user()->can('delete customers'))
                                <th class="text-nowrap" style="width: 80px;">Actions</th>
                                @endif
                                <th>Name</th>
                                <th>Mobile</th>
                                <th>Email</th>
                                <th>Gender</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $index => $customer)
                            <tr>
                                <td>{{ $customers->firstItem() + $index }}</td>
                                @if(auth()->user()->can('view customers') || auth()->user()->can('edit customers') || auth()->user()->can('delete customers'))
                                <td>
                                    <div class="dropdown">
                                        <button class="btn p-0 dropdown-toggle hide-arrow" type="button" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            @can('view customers')
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.customers.show', $customer->id) }}">
                                                    <i class="bx bx-show me-1"></i> View
                                                </a>
                                            </li>
                                            @endcan
                                            @can('edit customers')
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.customers.edit', $customer->id) }}">
                                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                                </a>
                                            </li>
                                            @endcan
                                            @can('delete customers')
                                            <li>
                                                <a class="dropdown-item text-danger delete-customer" href="javascript:void(0);" data-url="{{ route('admin.customers.destroy', $customer->id) }}">
                                                    <i class="bx bx-trash me-1"></i> Delete
                                                </a>
                                            </li>
                                            @endcan
                                        </ul>
                                    </div>
                                </td>
                                @endif
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->mobile }}</td>
                                <td>{{ $customer->email }}</td>
                                <td>{{ ucfirst($customer->gender ?? 'N/A') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ (auth()->user()->can('view customers') || auth()->user()->can('edit customers') || auth()->user()->can('delete customers')) ? 6 : 5 }}" class="text-center text-muted py-4">No customers found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                    <div class="mt-3">{{ $customers->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    var table = $('#customersTable').DataTable({
        paging: false,
        info: false,
        searching: false,
        lengthChange: false,
        ordering: false
    });

    $(document).on('click', '.delete-customer', function() {
        var btn = $(this);
        var url = btn.data('url');
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                    success: function(res) {
                        btn.closest('tr').remove();
                        setFlesh('success', res.message || 'Customer deleted');
                    }
                });
            }
        });
    });
});
</script>
@endsection
