@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0">Edit Bill Format: {{ $billFormat->format_name }}</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0 mt-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">Masters</li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.masters.bill-formats.index') }}">Bill Formats</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.masters.bill-formats.update', $billFormat->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('admin.masters.bill-formats._form', ['format' => $billFormat])
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Update Format</button>
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
    var selectedCompanyId = {{ $billFormat->company_id ?? 'null' }};

    function loadDepots(companySelect, depotSelect, selectedId) {
        var companyId = companySelect.val();
        depotSelect.find('option:not([value=""])').remove();
        if (companyId) {
            $.get('{{ route("admin.masters.bill-formats.get-depots") }}', { company_id: companyId }, function (data) {
                $.each(data, function (i, d) {
                    var sel = selectedId == d.id ? 'selected' : '';
                    depotSelect.append('<option value="' + d.id + '" ' + sel + '>' + d.name + '</option>');
                });
            });
        }
    }

    function loadParties(companySelect, partySelect, selectedId) {
        var companyId = companySelect.val();
        partySelect.find('option:not([value=""])').remove();
        if (companyId) {
            $.get('{{ route("admin.masters.bill-formats.get-parties") }}', { company_id: companyId }, function (data) {
                $.each(data, function (i, p) {
                    var sel = selectedId == p.id ? 'selected' : '';
                    partySelect.append('<option value="' + p.id + '" ' + sel + '>' + p.name + '</option>');
                });
            });
        }
    }

    if (selectedCompanyId) {
        loadDepots($('select[name="company_id"]'), $('select[name="depot_id"]'), {{ $billFormat->depot_id ?? 'null' }});
        loadParties($('select[name="company_id"]'), $('select[name="party_id"]'), {{ $billFormat->party_id ?? 'null' }});
    }

    $('select[name="company_id"]').on('change', function () {
        loadDepots($(this), $('select[name="depot_id"]'), null);
        loadParties($(this), $('select[name="party_id"]'), null);
    });
});
</script>
@endsection
