@extends('admin.layouts.app')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Vehicles /</span> Recycle Bin </h4>

        <a href="{{ route('admin.masters.vehicles.index') }}" class="btn btn-secondary"><i class="bx bx-arrow-back"></i> Back to List</a>

    </div>

    <div class="card">

        <h5 class="card-header">Recycle Bin</h5>

        <div class="table-responsive text-nowrap">

            <table class="table">

                <thead>

                    <tr>

                        <th>#</th>

                        <th>Vehicle No</th>

                        <th>Type</th>

                        <th>Capacity</th>

                        <th>Owner</th>

                        <th>Insurance</th>

                        <th>Deleted At</th>

                        <th>Actions</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($vehicles as $index => $vehicle)

                    <tr>

                        <td>{{ $vehicles->firstItem() + $index }}</td>

                        <td><strong>{{ $vehicle->vehicle_number }}</strong></td>

                        <td>{{ $vehicle->vehicle_type ?? '-' }}</td>

                        <td>{{ $vehicle->capacity_tons }} Tons</td>

                        <td>{{ $vehicle->owner_name ?? '-' }}</td>

                        <td>{{ $vehicle->insurance_expiry ? date('d M Y', strtotime($vehicle->insurance_expiry)) : '-' }}</td>

                        <td>{{ $vehicle->deleted_at->format('d M Y, h:i A') }}</td>

                        <td class="text-center text-nowrap">

                            <button type="button" class="btn btn-sm btn-icon btn-outline-success" onclick="handleRestore({{ $vehicle->id }}, '{{ $vehicle->vehicle_number }}')" title="Restore"><i class="bx bx-revision"></i></button>

                            <button type="button" class="btn btn-sm btn-icon btn-outline-danger" onclick="handleForceDelete({{ $vehicle->id }}, '{{ $vehicle->vehicle_number }}')" title="Permanently Delete"><i class="bx bx-trash"></i></button>

                        </td>

                    </tr>

                    @empty

                    <tr><td colspan="8" class="text-center">No vehicles in recycle bin</td></tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="card-footer">

            {{ $vehicles->links() }}

        </div>

    </div>

</div>

<form id="force-delete-form" method="POST" style="display: none;">

    @csrf @method('DELETE')

</form>

<form id="restore-form" method="POST" style="display: none;">

    @csrf @method('PUT')

</form>

@endsection

@section('script')

<script>

    function handleRestore(id, name) {

        Swal.fire({ title: 'Restore Vehicle?', text: "Restore '" + name + "'?", icon: 'question', showCancelButton: true, confirmButtonColor: '#28a745', cancelButtonColor: '#3085d6', confirmButtonText: 'Yes, restore it!' }).then((result) => {

            if (result.isConfirmed) { const form = document.getElementById('restore-form'); form.action = "{{ url('admin/masters/vehicles') }}/" + id + "/restore"; form.submit(); }

        })

    }

    function handleForceDelete(id, name) {

        Swal.fire({ title: 'Permanently Delete?', text: "This will permanently delete '" + name + "'!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Yes, delete it!' }).then((result) => {

            if (result.isConfirmed) { const form = document.getElementById('force-delete-form'); form.action = "{{ url('admin/masters/vehicles') }}/" + id + "/force-delete"; form.submit(); }

        })

    }

</script>

@endsection
