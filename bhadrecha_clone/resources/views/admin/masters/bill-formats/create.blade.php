@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">Add Bill Format</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0 mt-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">Masters</li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.masters.bill-formats.index') }}">Bill Formats</a></li>
                    <li class="breadcrumb-item active">Add</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.masters.bill-formats.store') }}" method="POST">
                @csrf
                @include('admin.masters.bill-formats._form', ['format' => null])
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Save Format</button>
                    <a href="{{ route('admin.masters.bill-formats.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('style')
<style>
    .field-checkbox-grid { max-height: 400px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px; }
    .field-checkbox-grid .form-check { margin-bottom: 6px; }
</style>
@endsection

@section('script')
<script>
$(function () {
    function loadDepots(companySelect, depotSelect) {
        var companyId = companySelect.val();
        depotSelect.find('option:not([value=""])').remove();
        if (companyId) {
            $.get('{{ route("admin.masters.bill-formats.get-depots") }}', { company_id: companyId }, function (data) {
                $.each(data, function (i, d) {
                    depotSelect.append('<option value="' + d.id + '">' + d.name + '</option>');
                });
            });
        }
    }

    function loadParties(companySelect, partySelect) {
        var companyId = companySelect.val();
        partySelect.find('option:not([value=""])').remove();
        if (companyId) {
            $.get('{{ route("admin.masters.bill-formats.get-parties") }}', { company_id: companyId }, function (data) {
                $.each(data, function (i, p) {
                    partySelect.append('<option value="' + p.id + '">' + p.name + '</option>');
                });
            });
        }
    }

    $('select[name="company_id"]').on('change', function () {
        loadDepots($(this), $('select[name="depot_id"]'));
        loadParties($(this), $('select[name="party_id"]'));
    });
});
</script>
@endsection
