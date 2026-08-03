@extends('admin.layouts.app')

@section('title', 'Letterhead Documents')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row page-titles mx-0 mb-3 align-items-center">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4 class="text-primary font-weight-bold"><i class="bx bx-envelope-open me-2"></i>Dynamic Letterheads</h4>
                <p class="mb-0">Manage dynamic company letterheads, generate PDFs, and send via email.</p>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            @can('create letterheads')
            <a href="{{ route('admin.letterheads.create') }}" class="btn btn-primary btn-sm shadow-sm font-weight-bold">
                <i class="bx bx-plus-circle me-1"></i> Create New Letterhead
            </a>
            @endcan
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bx bx-error me-1"></i> {{ session('warning') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light py-3">
            <h6 class="card-title text-dark mb-0"><i class="bx bx-filter-alt me-1 text-primary"></i> Filter Documents</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.letterheads.index') }}" method="GET">
                <div class="row">
                    <div class="col-md-3 col-lg-3 mb-2">
                        <label class="small font-weight-bold">Search Keywords</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Ref No, Recipient, Subject, Email..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3 col-lg-2 mb-2">
                        <label class="small font-weight-bold">Company</label>
                        <select name="company_id" class="form-control form-control-sm">
                            <option value="">All Companies</option>
                            @foreach($companies as $comp)
                                <option value="{{ $comp->id }}" {{ request('company_id') == $comp->id ? 'selected' : '' }}>{{ $comp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-lg-2 mb-2">
                        <label class="small font-weight-bold">From Date</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2 col-lg-2 mb-2">
                        <label class="small font-weight-bold">To Date</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2 col-lg-3 mb-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm font-weight-bold mr-1 flex-grow-1">
                            <i class="bx bx-search me-1"></i> Search
                        </button>
                        <a href="{{ route('admin.letterheads.index') }}" class="btn btn-outline-secondary btn-sm font-weight-bold flex-grow-1 text-center" title="Reset Filters">
                            <i class="bx bx-refresh me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>Ref No.</th>
                            <th>Date</th>
                            <th>Company</th>
                            <th>Recipient Details</th>
                            <th>Subject</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($letterheads as $lh)
                            <tr>
                                <td class="font-weight-bold text-primary">
                                    <a href="{{ route('admin.letterheads.pdf', $lh->id) }}" target="_blank" title="View PDF">
                                        {{ $lh->letter_no }}
                                    </a>
                                </td>
                                <td>
                                    <div class="font-weight-bold text-dark">{{ \Carbon\Carbon::parse($lh->letter_date)->format('d M, Y') }}</div>
                                    <div class="small text-muted"><i class="bx bx-time-five me-1"></i>{{ \Carbon\Carbon::parse($lh->created_at ?? $lh->letter_date)->format('h:i A') }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-info p-2">{{ $lh->company->name ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <div class="font-weight-bold text-dark">{{ $lh->recipient_name }}</div>
                                    @if($lh->recipient_company)
                                        <div class="small text-muted">{{ $lh->recipient_company }}</div>
                                    @endif
                                    @if($lh->recipient_email)
                                        <div class="small text-info"><i class="bx bx-envelope me-1"></i>{{ $lh->recipient_email }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div style="max-width: 250px;" class="text-truncate font-weight-bold text-dark" title="{{ $lh->subject }}">
                                        {{ $lh->subject }}
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <!-- View / Print PDF -->
                                        @can('view letterheads')
                                        <a href="{{ route('admin.letterheads.pdf', $lh->id) }}" target="_blank" class="btn btn-outline-primary" title="View / Print PDF">
                                            <i class="bx bx-show"></i>
                                        </a>

                                        <!-- Download PDF -->
                                        <a href="{{ route('admin.letterheads.pdf', ['letterhead' => $lh->id, 'action' => 'download']) }}" class="btn btn-outline-info" title="Download PDF">
                                            <i class="bx bx-download"></i>
                                        </a>
                                        @endcan

                                        <!-- Send Email Modal Button -->
                                        @can('send letterheads mail')
                                        <button type="button" class="btn btn-outline-success btn-send-mail" 
                                                data-id="{{ $lh->id }}" 
                                                data-ref="{{ $lh->letter_no }}"
                                                data-email="{{ $lh->recipient_email }}"
                                                title="Send Mail to Recipient">
                                            <i class="bx bx-paper-plane"></i>
                                        </button>
                                        @endcan

                                        <!-- Edit -->
                                        @can('edit letterheads')
                                        <a href="{{ route('admin.letterheads.edit', $lh->id) }}" class="btn btn-outline-warning" title="Edit Letterhead">
                                            <i class="bx bx-edit-alt"></i>
                                        </a>
                                        @endcan

                                        <!-- Delete -->
                                        @can('delete letterheads')
                                        <form action="{{ route('admin.letterheads.destroy', $lh->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this letterhead?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete Letterhead">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fa fa-folder-open fa-2x mb-2 d-block text-secondary"></i>
                                    No letterhead records found. <a href="{{ route('admin.letterheads.create') }}">Create one now</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($letterheads->hasPages())
            <div class="card-footer bg-white d-flex justify-content-end py-2">
                {{ $letterheads->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Send Email Modal -->
<div class="modal fade" id="sendMailModal" tabindex="-1" role="dialog" aria-labelledby="sendMailModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-header text-white mb-0" id="sendMailModalLabel"><i class="bx bx-paper-plane me-2"></i>Send Letterhead via Email</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="sendMailForm">
                @csrf
                <input type="hidden" id="modal_letterhead_id">
                <div class="modal-body">
                    <div class="alert alert-info py-2 small">
                        Letterhead document <strong id="modal_ref_no"></strong> will be attached as a PDF and sent to the email address below.
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold text-dark">Recipient Email Address (Mail ID) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                            <input type="email" id="modal_recipient_email" class="form-control" placeholder="Enter recipient email..." required>
                        </div>
                    </div>
                    <div id="mail_response_msg"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" id="btnSubmitMail" class="btn btn-success font-weight-bold">
                        <i class="bx bx-paper-plane me-1"></i> Send Email Now
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
$(document).ready(function() {
    // Open Send Mail Modal
    $('.btn-send-mail').on('click', function() {
        var id = $(this).data('id');
        var ref = $(this).data('ref');
        var email = $(this).data('email');

        $('#modal_letterhead_id').val(id);
        $('#modal_ref_no').text('#' + ref);
        $('#modal_recipient_email').val(email || '');
        $('#mail_response_msg').html('');
        $('#sendMailModal').modal('show');
    });

    // Handle AJAX Email Sending
    $('#sendMailForm').on('submit', function(e) {
        e.preventDefault();

        var id = $('#modal_letterhead_id').val();
        var email = $('#modal_recipient_email').val();

        if (!email) {
            $('#mail_response_msg').html('<div class="alert alert-danger py-2 mt-2">Please enter a valid email address.</div>');
            return;
        }

        var btn = $('#btnSubmitMail');
        btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Sending Email...');
        $('#mail_response_msg').html('<div class="alert alert-info py-2 mt-2"><i class="bx bx-loader-alt bx-spin me-1"></i> Sending letterhead PDF to ' + email + '...</div>');

        $.ajax({
            url: "{{ url('admin/letterheads') }}/" + id + "/send-mail",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                email: email
            },
            dataType: "json",
            success: function(response) {
                btn.prop('disabled', false).html('<i class="bx bx-paper-plane me-1"></i> Send Email Now');
                if (response.success) {
                    $('#mail_response_msg').html('<div class="alert alert-success py-2 mt-2"><i class="bx bx-check-circle me-1"></i> ' + response.message + '</div>');
                    setTimeout(function() {
                        $('#sendMailModal').modal('hide');
                        location.reload();
                    }, 1500);
                } else {
                    $('#mail_response_msg').html('<div class="alert alert-danger py-2 mt-2">' + response.message + '</div>');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="bx bx-paper-plane me-1"></i> Send Email Now');
                var errMsg = 'An error occurred while sending email.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errMsg = xhr.responseJSON.message;
                }
                $('#mail_response_msg').html('<div class="alert alert-danger py-2 mt-2"><i class="bx bx-error me-1"></i> ' + errMsg + '</div>');
            }
        });
    });
});
</script>
@endsection
