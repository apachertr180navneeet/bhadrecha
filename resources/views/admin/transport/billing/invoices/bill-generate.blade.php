@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1">Bill Generate: {{ $invoice->invoice_no }}</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.transport.invoices.index') }}">Invoice History</a></li>
                    <li class="breadcrumb-item active">Bill Generate</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.transport.invoices.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back
            </a>
            <a href="{{ route('admin.transport.invoices.toll-print', $invoice->id) }}" class="btn btn-primary" target="_blank">
                <i class="bx bx-printer me-1"></i> Toll Print View
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="mb-4">
                <h6 class="fw-bold">Invoice Number: <span class="text-primary">{{ $invoice->invoice_no }}</span></h6>
                <h6 class="fw-bold">Invoice Date: {{ $invoice->invoice_date->format('d M Y') }}</h6>
                <h6 class="fw-bold">Total LRs: {{ $invoice->bulties->count() }}</h6>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>LR No.</th>
                            <th>LR Date</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Consignor</th>
                            <th>Consignee</th>
                            <th class="text-end">Freight (₹)</th>
                            <th class="text-end">GST (₹)</th>
                            <th class="text-end">Other (₹)</th>
                            <th class="text-end">Total (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoice->bulties as $index => $bulty)
                        @php
                            $isMaiharUnloading = ($invoice->template_type === 'maihar_unloading');
                            $isGypsum = ($invoice->template_type === 'gypsum');
                            
                            if ($isMaiharUnloading) {
                                $weight = $bulty->bultyItems ? floatval($bulty->bultyItems->sum('weight')) : 0;
                                $ulRate = $bulty->bultyDetail ? floatval($bulty->bultyDetail->ul_rate) : 0;
                                $lineFreight = $weight * $ulRate;
                                $lineOther = floatval($bulty->other_charges);
                            } elseif ($isGypsum) {
                                $finalWgt = $bulty->bultyDetail ? floatval($bulty->bultyDetail->final_wgt) : 0;
                                $rateMt = $bulty->bultyDetail ? floatval($bulty->bultyDetail->rate_mt) : 0;
                                $lineFreight = $finalWgt * $rateMt;
                                $lineOther = floatval($bulty->other_charges);
                            } else {
                                $lineFreight = floatval($bulty->freight_charges) - floatval($bulty->advance_amount);
                                $lineOther = floatval($bulty->other_charges);
                            }
                            
                            $gstPercentage = $invoice->gstMaster ? floatval($invoice->gstMaster->percentage) : null;
                            if ($invoice->invoice_type === 'toll' && $gstPercentage === null) {
                                $gstPercentage = 18.00;
                            }
                            
                            if ($gstPercentage !== null) {
                                $lineGst = $lineFreight * ($gstPercentage / 100);
                            } else {
                                $lineGst = floatval($bulty->gst_amount);
                            }
                            
                            $lineTotal = $lineFreight + $lineOther + $lineGst;
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $bulty->lr_no }}</strong></td>
                            <td>{{ $bulty->lr_date ? $bulty->lr_date->format('d M Y') : '-' }}</td>
                            <td>{{ $bulty->originCity->name ?? '-' }}</td>
                            <td>{{ $bulty->destinationCity->name ?? '-' }}</td>
                            <td>{{ $bulty->consignor->name ?? '-' }}</td>
                            <td>{{ $bulty->consignee->name ?? '-' }}</td>
                            <td class="text-end">{{ number_format($lineFreight, 2) }}</td>
                            <td class="text-end">{{ number_format($lineGst, 2) }}</td>
                            <td class="text-end">{{ number_format($lineOther, 2) }}</td>
                            <td class="text-end"><strong>{{ number_format($lineTotal, 2) }}</strong></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center py-4">No LRs found for this invoice.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-dark">
                        <tr>
                            <th colspan="7" class="text-end">Totals:</th>
                            <th class="text-end">{{ number_format($invoice->total_freight, 2) }}</th>
                            <th class="text-end">{{ number_format($invoice->total_gst, 2) }}</th>
                            <th class="text-end">{{ number_format($invoice->total_other, 2) }}</th>
                            <th class="text-end">{{ number_format($invoice->total_amount, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    @foreach($invoice->bulties as $bulty)
        @if($bulty->trip && $bulty->trip->fastTagDetails->isNotEmpty())
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="fw-bold mb-0">
                    Fast Tag Details - {{ $bulty->lr_no }}
                    <span class="text-muted ms-2">(Total: ₹ {{ number_format($bulty->trip->fasttag_total_amount, 2) }})</span>
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered mb-0">
                        <thead class="table-secondary">
                            <tr>
                                <th>#</th>
                                <th>Date Time</th>
                                <th class="text-end">Amount (₹)</th>
                                <th>Location</th>
                                <th>Description</th>
                                <th>Transaction ID</th>
                                <th class="text-end">One Way (₹)</th>
                                <th class="text-end">Return (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bulty->trip->fastTagDetails as $ftIdx => $ft)
                            <tr>
                                <td>{{ $ftIdx + 1 }}</td>
                                <td>{{ $ft->transaction_time ? $ft->transaction_time->format('d M Y h:i A') : '-' }}</td>
                                <td class="text-end">{{ number_format($ft->amount, 2) }}</td>
                                <td>{{ $ft->location ?? '-' }}</td>
                                <td>{{ $ft->description ?? '-' }}</td>
                                <td>{{ $ft->transaction_id ?? '-' }}</td>
                                <td class="text-end">{{ $ft->one_way ? number_format($ft->one_way, 2) : '-' }}</td>
                                <td class="text-end">{{ $ft->return ? number_format($ft->return, 2) : '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="2" class="text-end">Total:</th>
                                <th class="text-end">{{ number_format($bulty->trip->fastTagDetails->sum('amount'), 2) }}</th>
                                <th colspan="3"></th>
                                <th class="text-end">{{ number_format($bulty->trip->fastTagDetails->sum('one_way'), 2) }}</th>
                                <th class="text-end">{{ number_format($bulty->trip->fastTagDetails->sum('return'), 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        @endif
    @endforeach
</div>
@endsection
