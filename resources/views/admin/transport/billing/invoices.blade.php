@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="card shadow-sm mb-4" style="overflow: visible;">
        <div class="card-body" style="overflow: visible;">
            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="fw-bold mb-0">All Invoices</h5>
                    <small class="text-muted">Manage and print generated invoices</small>
                </div>
                <a href="{{ route('admin.transport.billing') }}" class="btn btn-primary btn-sm">
                    <i class="bx bx-plus me-1"></i> New Invoice
                </a>
            </div>

            {{-- Table --}}
            <div class="table-responsive" style="overflow: visible;">
                <table class="table table-bordered table-hover align-middle" id="invoiceTable">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Invoice No</th>
                            <th>Date</th>
                            <th>Consignor</th>
                            <th>From</th>
                            <th>To</th>
                            <th class="text-end">Freight</th>
                            <th class="text-end">GST</th>
                            <th class="text-end">Other</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">LRs</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $index => $inv)
                            <tr id="invoice-row-{{ $inv->id }}">
                                <td class="text-muted small">{{ $invoices->firstItem() + $index }}</td>
                                <td>
                                    <span class="fw-bold text-primary">{{ $inv->bill_number ?? $inv->invoice_no }}</span>
                                    <small class="text-muted d-block" style="font-size:10px;">{{ $inv->invoice_no }}</small>
                                </td>
                                <td class="text-muted small">
                                    {{ $inv->invoice_date?->format('d M Y') ?? $inv->created_at->format('d M Y') }}
                                </td>
                                <td>{{ $inv->consignor_name ?? '-' }}</td>
                                <td>{{ $inv->from_city_name ?? '-' }}</td>
                                <td>{{ $inv->to_city_name ?? '-' }}</td>
                                <td class="text-end">₹ {{ number_format($inv->total_freight, 2) }}</td>
                                <td class="text-end">₹ {{ number_format($inv->total_gst, 2) }}</td>
                                <td class="text-end">₹ {{ number_format($inv->total_other, 2) }}</td>
                                <td class="text-end fw-bold">₹ {{ number_format($inv->total_amount, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge bg-label-secondary">{{ $inv->bulties->count() }} LR</span>
                                </td>

                                {{-- ── Status Change Dropdown ── --}}
                                <td class="text-center" id="status-cell-{{ $inv->id }}">
                                    @php
                                        $status = $inv->status ?? 'pending';
                                        $statusCfg = [
                                            'pending'   => ['label' => 'Pending',   'btnCls' => 'sdr-btn-warning',  'icon' => 'bx-time-five'],
                                            'paid'      => ['label' => 'Paid',      'btnCls' => 'sdr-btn-success',  'icon' => 'bx-check-circle'],
                                            'cancelled' => ['label' => 'Cancelled', 'btnCls' => 'sdr-btn-danger',   'icon' => 'bx-x-circle'],
                                        ];
                                        $sc = $statusCfg[$status] ?? $statusCfg['pending'];
                                    @endphp
                                    <button type="button"
                                            class="sdr-trigger {{ $sc['btnCls'] }}"
                                            data-invoice-id="{{ $inv->id }}"
                                            data-current="{{ $status }}">
                                        <i class="bx {{ $sc['icon'] }}"></i>
                                        <span class="sdr-label">{{ $sc['label'] }}</span>
                                        <i class="bx bx-chevron-down sdr-arrow"></i>
                                    </button>
                                </td>

                                {{-- ── Action Buttons ── --}}
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="{{ route('admin.transport.billing.create', ['ids' => $inv->bulties->pluck('id')->join(',')]) }}"
                                           class="btn btn-sm btn-outline-secondary"
                                           title="View / Edit Invoice">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        <a href="{{ route('admin.transport.billing.invoices.print', $inv->id) }}"
                                           target="_blank"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Print Invoice">
                                            <i class="bx bx-printer"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="text-center text-muted py-5">
                                    <i class="bx bx-receipt" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:8px;"></i>
                                    No invoices found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="text-muted">{{ $invoices->total() }} invoice(s)</small>
                {{ $invoices->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('style')
<style>
/* ══ Custom Status Dropdown (sdr) ════════════════════════════ */
.sdr-trigger {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 10px 5px 9px;
    border: none;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    transition: filter .15s;
    min-width: 115px;
    justify-content: center;
}
.sdr-trigger:hover  { filter: brightness(.93); }
.sdr-trigger:focus  { outline: none; box-shadow: 0 0 0 3px rgba(0,0,0,.12); }
.sdr-arrow { font-size: 14px; margin-left: auto; opacity: .75; }

/* Colours */
.sdr-btn-warning  { background: #fd7e14; color: #fff; }
.sdr-btn-success  { background: #28a745; color: #fff; }
.sdr-btn-danger   { background: #dc3545; color: #fff; }

/* Spinner state */
.sdr-trigger.sdr-loading { opacity: .55; pointer-events: none; }

/* ══ Floating Menu (appended to <body>) ══════════════════════ */
#sdrMenu {
    position: fixed;
    z-index: 999999;
    background: #fff;
    border: 1px solid #d1d9e0;
    border-radius: 8px;
    box-shadow: 0 8px 24px rgba(15,23,42,.16);
    min-width: 155px;
    padding: 4px 0;
    display: none;
    animation: sdrFadeIn .12s ease;
}
@keyframes sdrFadeIn {
    from { opacity: 0; transform: translateY(-4px); }
    to   { opacity: 1; transform: translateY(0); }
}
.sdr-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 9px 14px;
    font-size: 13px;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    text-decoration: none;
    transition: background .1s;
}
.sdr-item:hover { background: #f1f5f9; color: #111; }
.sdr-item.sdr-active { background: #f8fafc; font-weight: 700; }
.sdr-dot {
    width: 9px; height: 9px;
    border-radius: 50%;
    flex-shrink: 0;
}
.sdr-check { margin-left: auto; font-size: 15px; }
.sdr-divider { border: none; border-top: 1px solid #e5e7eb; margin: 3px 0; }

/* ══ Row flash on status change ══════════════════════════════ */
@keyframes rowFlash {
    0%   { background: transparent; }
    30%  { background: #d1fae5; }
    100% { background: transparent; }
}
.row-status-flash td { animation: rowFlash .65s ease; }
</style>
@endsection

@section('script')
<script>
$(function () {

    /* ── Config ─────────────────────────────────────────────── */
    var statusCfg = {
        pending:   { label: 'Pending',   btnCls: 'sdr-btn-warning', icon: 'bx-time-five',    dot: '#fd7e14' },
        paid:      { label: 'Paid',      btnCls: 'sdr-btn-success', icon: 'bx-check-circle', dot: '#28a745' },
        cancelled: { label: 'Cancelled', btnCls: 'sdr-btn-danger',  icon: 'bx-x-circle',     dot: '#dc3545' },
    };
    var allBtnCls = 'sdr-btn-warning sdr-btn-success sdr-btn-danger';

    /* ── Build the shared floating menu (once) ───────────────── */
    var $menu = $([
        '<div id="sdrMenu">',
          '<a class="sdr-item" data-status="pending">',
            '<span class="sdr-dot" style="background:#fd7e14"></span>Pending',
            '<i class="bx bx-check sdr-check" style="color:#fd7e14;display:none"></i>',
          '</a>',
          '<a class="sdr-item" data-status="paid">',
            '<span class="sdr-dot" style="background:#28a745"></span>Paid',
            '<i class="bx bx-check sdr-check" style="color:#28a745;display:none"></i>',
          '</a>',
          '<hr class="sdr-divider">',
          '<a class="sdr-item" data-status="cancelled">',
            '<span class="sdr-dot" style="background:#dc3545"></span>Cancelled',
            '<i class="bx bx-check sdr-check" style="color:#dc3545;display:none"></i>',
          '</a>',
        '</div>'
    ].join('')).appendTo('body');

    var $activeTrigger = null;

    /* ── Open menu on trigger click ──────────────────────────── */
    $(document).on('click', '.sdr-trigger', function (e) {
        e.stopPropagation();
        var $btn = $(this);

        /* Toggle: close if same button clicked again */
        if ($activeTrigger && $activeTrigger[0] === $btn[0] && $menu.is(':visible')) {
            closeMenu();
            return;
        }

        $activeTrigger = $btn;
        var current    = $btn.data('current');

        /* Mark current active item */
        $menu.find('.sdr-item').each(function () {
            var s = $(this).data('status');
            $(this).toggleClass('sdr-active', s === current);
            $(this).find('.sdr-check').toggle(s === current);
        });

        /* Position using getBoundingClientRect (viewport coords) */
        var rect  = $btn[0].getBoundingClientRect();
        var mW    = 155;
        var left  = rect.right - mW;            /* right-align to button */
        if (left < 4) left = 4;

        $menu.css({
            top:  rect.bottom + 4 + 'px',
            left: left + 'px',
        }).show();
    });

    /* ── Close menu on outside click ────────────────────────── */
    $(document).on('click', function () { closeMenu(); });
    $menu.on('click', function (e) { e.stopPropagation(); });

    function closeMenu() {
        $menu.hide();
        $activeTrigger = null;
    }

    /* ── Status option selected ──────────────────────────────── */
    $menu.on('click', '.sdr-item', function (e) {
        e.preventDefault();
        var newStatus  = $(this).data('status');
        var $btn       = $activeTrigger;
        if (!$btn) return;

        var invoiceId  = $btn.data('invoice-id');
        var current    = $btn.data('current');

        closeMenu();

        /* Skip if same */
        if (current === newStatus) return;

        /* Loading state */
        $btn.addClass('sdr-loading');

        var $cell = $('#status-cell-' + invoiceId);
        var $row  = $('#invoice-row-'  + invoiceId);

        $.ajax({
            url:         '{{ route("admin.transport.billing.invoices.status", "__ID__") }}'.replace('__ID__', invoiceId),
            method:      'PATCH',
            contentType: 'application/json',
            data:        JSON.stringify({ status: newStatus }),
            headers:     { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function (data) {
                $btn.removeClass('sdr-loading');
                if (data.success) {
                    var cfg = statusCfg[data.status] || statusCfg['pending'];

                    /* Update button appearance */
                    $btn.removeClass(allBtnCls).addClass(cfg.btnCls);
                    $btn.find('.bx').first().attr('class', 'bx ' + cfg.icon);
                    $btn.find('.sdr-label').text(cfg.label);
                    $btn.data('current', data.status);

                    /* Flash row */
                    $row.addClass('row-status-flash');
                    setTimeout(function () { $row.removeClass('row-status-flash'); }, 700);

                    /* Toast */
                    Swal.fire({
                        toast: true, position: 'top-end',
                        icon: 'success', title: data.message,
                        showConfirmButton: false,
                        timer: 2000, timerProgressBar: true,
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Could not update status.', confirmButtonColor: '#fd5523' });
                }
            },
            error: function (xhr) {
                $btn.removeClass('sdr-loading');
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Server error.';
                Swal.fire({ icon: 'error', title: 'Error', text: msg, confirmButtonColor: '#fd5523' });
            }
        });
    });

    /* ── Reposition menu on scroll / resize ─────────────────── */
    $(window).on('scroll resize', function () {
        if ($activeTrigger && $menu.is(':visible')) {
            var rect = $activeTrigger[0].getBoundingClientRect();
            var mW   = 155;
            var left = rect.right - mW;
            if (left < 4) left = 4;
            $menu.css({ top: rect.bottom + 4 + 'px', left: left + 'px' });
        }
    });

});
</script>
@endsection