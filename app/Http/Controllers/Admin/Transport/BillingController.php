<?php

namespace App\Http\Controllers\Admin\Transport;

use App\Http\Controllers\Controller;
use App\Models\Bulty;
use App\Models\BillFormat;
use App\Models\Invoice;
use App\Models\Consignor;
use App\Models\Consignee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InvoiceExport;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $query = Bulty::with([
            'consignor', 'consignee', 'originCity', 'destinationCity', 'bultyDetail', 'branch', 'company'
        ])->where('bill_status', 'unbilled')
          ->whereNotIn('status', ['pending', 'planned']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            // Check if multiple LR numbers are provided (comma, space, or newline separated)
            $tokens = array_filter(array_map('trim', preg_split('/[\s,\r\n]+/', $search)));
            if (count($tokens) > 1) {
                $query->where(function ($q) use ($tokens) {
                    $q->whereIn('lr_no', $tokens);
                    foreach ($tokens as $token) {
                        $q->orWhere('lr_no', 'LIKE', "%{$token}%");
                    }
                });
            } else {
                $query->where(function ($q) use ($search) {
                    $q->where('lr_no', 'LIKE', "%{$search}%")
                      ->orWhereHas('bultyDetail', function ($qd) use ($search) {
                          $qd->where('po_no', 'LIKE', "%{$search}%");
                      })
                      ->orWhereHas('consignor', function ($qc) use ($search) {
                          $qc->where('name', 'LIKE', "%{$search}%");
                      })
                      ->orWhereHas('consignee', function ($qc) use ($search) {
                          $qc->where('name', 'LIKE', "%{$search}%");
                      });
                });
            }
        }

        if ($request->filled('lr_numbers')) {
            $rawLrs = $request->lr_numbers;
            $lrList = is_array($rawLrs) ? $rawLrs : array_filter(array_map('trim', preg_split('/[\s,\r\n]+/', $rawLrs)));
            if (!empty($lrList)) {
                $query->whereIn('lr_no', $lrList);
            }
        }

        if ($request->filled('consignor_id')) {
            $query->where('consignor_id', $request->consignor_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('lr_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('lr_date', '<=', $request->to_date);
        }

        $dateSort = $request->get('date_sort', 'latest');
        if ($dateSort === 'oldest') {
            $query->orderBy('lr_date', 'asc')->orderBy('id', 'asc');
        } else {
            $query->orderBy('lr_date', 'desc')->orderBy('id', 'desc');
        }

        $bulties = $query->paginate(15)->withQueryString();

        $consignors = \App\Models\Consignor::where('status', 'active')->orderBy('name')->get(['id', 'name', 'phone']);

        return view('admin.transport.billing.index', compact('bulties', 'consignors', 'dateSort'));
    }

    public function matchLrs(Request $request)
    {
        $raw = $request->input('lr_numbers', '');
        $tokens = array_values(array_unique(array_filter(array_map('trim', preg_split('/[\s,\r\n]+/', $raw)))));

        if (empty($tokens)) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter at least one LR number.',
                'total_entered' => 0,
                'found_count' => 0,
                'missing_count' => 0,
                'ids' => [],
            ]);
        }

        $bulties = Bulty::where('bill_status', 'unbilled')
            ->whereNotIn('status', ['pending', 'planned'])
            ->whereIn('lr_no', $tokens)
            ->get(['id', 'lr_no', 'freight_charges', 'total_amount']);

        $foundLrs = $bulties->pluck('lr_no')->toArray();
        $missingLrs = array_values(array_diff($tokens, $foundLrs));

        return response()->json([
            'success' => true,
            'total_entered' => count($tokens),
            'found_count' => $bulties->count(),
            'missing_count' => count($missingLrs),
            'missing_lrs' => $missingLrs,
            'ids' => $bulties->pluck('id')->toArray(),
        ]);
    }

    public function create(Request $request)
    {
        $ids = $request->query('ids');
        $lrNos = $request->query('lr_nos');

        if (!$ids && !$lrNos) {
            return redirect()->route('admin.transport.billing')->with('error', 'No LRs selected');
        }

        $query = Bulty::with([
            'consignor', 'consignee', 'originCity', 'destinationCity', 'bultyDetail.supplier', 'branch', 'company',
            'vehicle', 'driver', 'bultyItems.item'
        ])->where('bill_status', 'unbilled');

        if ($ids) {
            $idArray = is_array($ids) ? $ids : array_filter(array_map('trim', explode(',', $ids)));
            $query->whereIn('id', $idArray);
        } elseif ($lrNos) {
            $lrArray = is_array($lrNos) ? $lrNos : array_filter(array_map('trim', preg_split('/[\s,\r\n]+/', $lrNos)));
            $query->whereIn('lr_no', $lrArray);
        }

        $bulties = $query->get();

        if ($bulties->isEmpty()) {
            return redirect()->route('admin.transport.billing')->with('error', 'Selected LRs not found or already billed');
        }

        $totals = [
            'freight' => $bulties->sum(function($b) {
                return floatval($b->freight_charges) - floatval($b->advance_amount);
            }),
            'damage' => $bulties->sum(fn($b) => floatval($b->damage_amount ?? 0)),
            'shortage' => $bulties->sum(fn($b) => floatval($b->shortage_amount ?? 0)),
            'net_freight' => $bulties->sum(function($b) {
                $f = floatval($b->freight_charges) - floatval($b->advance_amount);
                return $f - floatval($b->damage_amount ?? 0) - floatval($b->shortage_amount ?? 0);
            }),
            'gst' => $bulties->sum('gst_amount'),
            'other' => $bulties->sum('other_charges'),
            'grand' => $bulties->sum(function($b) {
                $f = floatval($b->freight_charges) - floatval($b->advance_amount);
                $netF = $f - floatval($b->damage_amount ?? 0) - floatval($b->shortage_amount ?? 0);
                return $netF + floatval($b->other_charges) + floatval($b->gst_amount);
            }),
        ];

        $billFormats = BillFormat::with('gstMaster')->orderBy('format_name')->get(['id', 'format_name', 'template_type', 'visible_fields', 'field_order', 'grn_fields', 'grn_field_order', 'grn_new_page', 'gst_master_id']);

        $latestFreight = Invoice::where('invoice_type', 'freight')->orderBy('id', 'desc')->first();
        $nextFreightNum = $latestFreight ? (intval(last(explode('-', $latestFreight->invoice_no))) + 1) : 1;
        $nextFreightInvoiceNo = "INV-" . str_pad($nextFreightNum, 4, '0', STR_PAD_LEFT);

        $latestToll = Invoice::where('invoice_type', 'toll')->orderBy('id', 'desc')->first();
        $nextTollNum = $latestToll ? (intval(last(explode('-', $latestToll->invoice_no))) + 1) : 1;
        $nextTollInvoiceNo = "INV-TOLL-" . str_pad($nextTollNum, 4, '0', STR_PAD_LEFT);

        return view('admin.transport.billing.create', compact('bulties', 'totals', 'billFormats', 'nextFreightInvoiceNo', 'nextTollInvoiceNo'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'ids' => 'required_without:lr_nos|nullable|string',
            'lr_nos' => 'required_without:ids|nullable|string',
            'template_type' => 'nullable|string',
            'bill_format_id' => 'required_if:template_type,dynamic|nullable|exists:bill_formats,id',
            'invoice_type' => 'nullable|string|in:freight,toll',
        ]);

        $invoiceType = $request->input('invoice_type', 'freight');

        $query = Bulty::with([
            'consignor', 'consignee', 'originCity', 'destinationCity', 'bultyDetail.supplier', 'company', 'trip.fastTagDetails', 'bultyItems'
        ]);

        if ($request->filled('ids')) {
            $ids = array_filter(array_map('trim', explode(',', $request->ids)));
            $query->whereIn('id', $ids);
        } elseif ($request->filled('lr_nos')) {
            $lrNos = array_filter(array_map('trim', preg_split('/[\s,\r\n]+/', $request->lr_nos)));
            $query->whereIn('lr_no', $lrNos);
        }

        if ($invoiceType === 'toll') {
            $bulties = $query->whereNull('toll_invoice_id')->get();
        } else {
            $bulties = $query->where('bill_status', 'unbilled')->get();
        }

        if ($bulties->isEmpty()) {
            return redirect()->route('admin.transport.billing')->with('error', 'No unbilled LRs found to bill');
        }

        if ($request->filled('bill_number')) {
            $companyId = $bulties->first()->company_id;
            $request->validate([
                'bill_number' => [
                    Rule::unique('invoices', 'invoice_no')->where(function ($query) use ($companyId) {
                        return $query->where('company_id', $companyId);
                    })
                ],
            ], [
                'bill_number.unique' => 'This bill number is already taken. Please use a different one.',
            ]);
        }

        $format = null;
        if (!in_array($request->template_type, ['nathdwara', 'gypsum']) && $request->filled('bill_format_id')) {
            $format = BillFormat::with('gstMaster')->findOrFail($request->bill_format_id);
        }

        $isMaiharUnloading = false;
        if ($format && $format->format_name && str_contains(strtoupper($format->format_name), 'MAIHAR') && str_contains(strtoupper($format->format_name), 'UNLOADING')) {
            $isMaiharUnloading = true;
        }

        $gstPercentage = ($format && $format->gstMaster && $format->gstMaster->percentage) ? floatval($format->gstMaster->percentage) : null;
        if ($invoiceType === 'toll' && $gstPercentage === null) {
            $gstPercentage = 18.00;
        }

        // For toll invoices, group LRs by route for separate bills
        if ($invoiceType === 'toll') {
            $routeGroups = $bulties->groupBy(function ($b) {
                return $b->from_city . '-' . $b->to_city;
            });
        } else {
            $routeGroups = collect([$bulties]);
        }

        $customBillNo = $request->input('bill_number');
        $invoices = collect();
        $isFirstGroup = true;

        foreach ($routeGroups as $routeKey => $groupBulties) {
            $totalFreight = 0;
            $totalGst = 0;
            $totalOther = 0;
            $totalDamage = 0;
            $totalShortage = 0;
            $totalAmount = 0;

            $templateType = $request->input('template_type');
            foreach ($groupBulties as $bulty) {
                $damage = floatval($bulty->damage_amount ?? 0);
                $shortage = floatval($bulty->shortage_amount ?? 0);

                if ($invoiceType === 'toll') {
                    $freight = $bulty->trip ? floatval($bulty->trip->total_fasttag_amount) : 0;
                    $other = 0;
                    $damage = 0;
                    $shortage = 0;
                } elseif ($templateType === 'gypsum') {
                    $finalWgt = $bulty->bultyDetail ? floatval($bulty->bultyDetail->final_wgt) : 0;
                    $rateMt = $bulty->bultyDetail ? floatval($bulty->bultyDetail->rate_mt) : 0;
                    $freight = $finalWgt * $rateMt;
                    $other = floatval($bulty->other_charges);

                    if ($request->filled('nathdwara_rate') && floatval($request->nathdwara_rate) > 0) {
                        $freight = $finalWgt * floatval($request->nathdwara_rate);
                    }
                } elseif ($isMaiharUnloading) {
                    $weight = $bulty->bultyItems ? floatval($bulty->bultyItems->sum('weight')) : 0;
                    $ulRate = $bulty->bultyDetail ? floatval($bulty->bultyDetail->ul_rate) : 0;
                    $freight = $weight * $ulRate;
                    $other = floatval($bulty->other_charges);
                } else {
                    $freight = floatval($bulty->freight_charges) - floatval($bulty->advance_amount);
                    $other = floatval($bulty->other_charges);
                }

                $gst = floatval($bulty->gst_amount);

                if ($gstPercentage !== null) {
                    $netFreightForGst = max(0, $freight - $damage - $shortage);
                    $gst = $netFreightForGst * ($gstPercentage / 100);
                }

                $netFreightLine = $freight - $damage - $shortage;
                $total = $netFreightLine + $other + $gst;

                $totalFreight += $freight;
                $totalGst += $gst;
                $totalOther += $other;
                $totalDamage += $damage;
                $totalShortage += $shortage;
                $totalAmount += $total;
            }

            // Generate Invoice Number
            if ($customBillNo && $isFirstGroup) {
                $invoiceNo = $customBillNo;
            } else {
                $prefix = $invoiceType === 'toll' ? 'INV-TOLL' : 'INV';
                $invoiceNo = DB::transaction(function () use ($prefix) {
                    $latest = Invoice::where('invoice_no', 'like', "{$prefix}-%")
                        ->orderBy('id', 'desc')
                        ->lockForUpdate()
                        ->first();
                    $nextNum = $latest ? (intval(explode('-', $latest->invoice_no)[count(explode('-', $latest->invoice_no)) - 1]) + 1) : 1;
                    return "{$prefix}-" . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
                });
            }

            $firstBulty = $groupBulties->first();
            $consignorId = $firstBulty->consignor_id;
            $consignorName = $firstBulty->consignor ? $firstBulty->consignor->name : null;
            $fromCityName = $firstBulty->originCity ? $firstBulty->originCity->name : null;
            $toCityName = $firstBulty->destinationCity ? $firstBulty->destinationCity->name : null;
            $companyName = $request->company_name ?? ($firstBulty->company ? $firstBulty->company->name : null);

            $companyState = $firstBulty->company ? ($firstBulty->company->state ?? null) : null;
            if (!$companyState && $firstBulty->originCity) {
                $companyState = $firstBulty->originCity->state ?? 'RAJASTHAN';
            }
            $placeOfSupply = $request->input('custom_place_of_supply') ?: ($firstBulty->destinationCity ? ($firstBulty->destinationCity->state ?? 'RAJASTHAN') : 'RAJASTHAN');
            $isSameState = self::isSameGstState($companyState, $placeOfSupply);

            $defaultGstType = $isSameState ? 'CGST_SGST' : 'IGST';
            $gstType = $request->input('gst_type') ?: $defaultGstType;
            $netTotalFreight = $totalFreight - $totalDamage - $totalShortage;

            if ($request->filled('total_gst') && floatval($request->total_gst) > 0) {
                $totalGst = floatval($request->total_gst);
            } elseif ($gstPercentage !== null && $gstPercentage > 0 && $netTotalFreight > 0) {
                $totalGst = round($netTotalFreight * ($gstPercentage / 100), 2);
            }

            if ($gstType === 'IGST') {
                $igstAmount = ($request->filled('igst_amount') && floatval($request->igst_amount) > 0) ? floatval($request->igst_amount) : $totalGst;
                $cgstAmount = 0.00;
                $sgstAmount = 0.00;
            } else {
                $cgstAmount = ($request->filled('cgst_amount') && floatval($request->cgst_amount) > 0) ? floatval($request->cgst_amount) : round($totalGst / 2, 2);
                $sgstAmount = ($request->filled('sgst_amount') && floatval($request->sgst_amount) > 0) ? floatval($request->sgst_amount) : round($totalGst / 2, 2);
                $igstAmount = 0.00;
            }

            $rcmPayable = $request->has('rcm_payable') ? (int)$request->rcm_payable : ($invoiceType === 'toll' ? 0 : 1);

            if ($invoiceType === 'toll') {
                $totalAmount = $netTotalFreight + $totalGst;
            } elseif ($request->filled('total_amount') && floatval($request->total_amount) > 0) {
                $totalAmount = floatval($request->total_amount);
            } else {
                // Final Bill Amount = Original Bill Amount - Shortage Amount - Damage Amount
                // If GST is exclusive and applicable, add GST; otherwise don't add GST again
                if ($rcmPayable == 1 || $totalGst <= 0) {
                    $totalAmount = $netTotalFreight + $totalOther;
                } else {
                    $totalAmount = $netTotalFreight + $totalOther + $totalGst;
                }
            }
            $amountInWords = self::convertNumberToWords($totalAmount);

            $visFields = null;
            if ($format) {
                $visFields = !empty($format->field_order) ? $format->field_order : $format->visible_fields;
                if (is_array($visFields)) {
                    if (!in_array('lr_no', $visFields) && !in_array('lr_number', $visFields)) {
                        array_unshift($visFields, 'lr_no');
                    }
                }
            }
            if (empty($visFields)) {
                $visFields = [
                    'lr_no', 'lr_date', 'consignor_id', 'consignee_id', 'from_city', 'to_city',
                    'vehicle_id', 'freight_charges', 'damage_amount', 'shortage_amount',
                    'gst_amount', 'other_charges', 'total_amount'
                ];
            }

            $invoice = DB::transaction(function () use ($firstBulty, $consignorId, $consignorName, $fromCityName, $toCityName, $netTotalFreight, $totalGst, $totalOther, $invoiceNo, $invoiceType, $totalAmount, $amountInWords, $format, $groupBulties, $request, $isMaiharUnloading, $companyName, $gstType, $cgstAmount, $sgstAmount, $igstAmount, $rcmPayable, $visFields) {
                $invoice = Invoice::create([
                    'company_id' => $firstBulty->company_id,
                    'branch_id' => $firstBulty->branch_id,
                    'consignor_id' => $consignorId,
                    'consignor_name' => $consignorName,
                    'company_name' => $companyName,
                    'billing_address' => $request->billing_address,
                    'custom_hsn_code' => $request->custom_hsn_code,
                    'custom_place_of_supply' => $request->custom_place_of_supply,
                    'from_city_name' => $fromCityName,
                    'to_city_name' => $toCityName,
                    'total_freight' => $netTotalFreight,
                    'total_gst' => $totalGst,
                    'total_other' => $totalOther,
                    'invoice_no' => $invoiceNo,
                    'bill_number' => $invoiceNo,
                    'template_type' => in_array($request->template_type, ['nathdwara', 'gypsum']) ? $request->template_type : ($isMaiharUnloading ? 'maihar_unloading' : ($format->template_type ?? ($request->template_type ?? 'standard'))),
                    'invoice_type' => $invoiceType,
                    'invoice_date' => now(),
                    'total_amount' => $totalAmount,
                    'amount_in_words' => $amountInWords,
                    'visible_fields' => $visFields,
                    'grn_fields' => $format ? (!empty($format->grn_field_order) ? $format->grn_field_order : $format->grn_fields) : null,
                    'grn_new_page' => $format ? $format->grn_new_page : false,
                    'status' => 'pending',
                    'user_id' => auth()->id(),
                    'gst_master_id' => $format ? $format->gst_master_id : null,
                    'mn_number' => $request->mn_number,
                    'no_of_lrs' => $request->filled('no_of_lrs') ? intval($request->no_of_lrs) : $groupBulties->count(),
                    'state_vendor_code' => $request->state_vendor_code,
                    'vendor_code' => $request->vendor_code,
                    'vendor_name' => $request->vendor_name,
                    'epod_status' => $request->epod_status ?? 'N',
                    'description' => $request->nathdwara_description,
                    'custom_rate' => $request->nathdwara_rate,
                    'gst_type' => $gstType,
                    'cgst_amount' => $cgstAmount,
                    'sgst_amount' => $sgstAmount,
                    'igst_amount' => $igstAmount,
                    'rcm_payable' => $rcmPayable,
                ]);

                foreach ($groupBulties as $bulty) {
                    // Auto-set qty_mt from sum of bultyItem weights
                    $itemWeight = $bulty->bultyItems ? $bulty->bultyItems->pluck('weight')->map(fn($w) => (float)$w)->sum() : 0;
                    if ($itemWeight > 0 && $bulty->bultyDetail) {
                        $bulty->bultyDetail->update(['qty_mt' => $itemWeight]);
                    } elseif ($itemWeight > 0) {
                        $bulty->bultyDetail()->create(['bulty_id' => $bulty->id, 'qty_mt' => $itemWeight]);
                    }

                    if ($invoiceType === 'toll') {
                        if ($bulty->toll_invoice_id !== null) continue;
                        $bulty->update([
                            'toll_invoice_id' => $invoice->id,
                        ]);
                    } else {
                        if ($bulty->bill_status !== 'unbilled') continue;
                        $bulty->update([
                            'bill_status' => 'billed',
                            'invoice_id' => $invoice->id,
                        ]);
                    }
                }

                return $invoice;
            });

            if ($invoiceType === 'toll') {
                $this->saveTollDetails($invoice);
                $invoice->load(['tollDetails', 'gstMaster', 'company']);
                $bulties = $invoice->tollBulties()->with('trip.fastTagDetails')->get();
                if ($bulties->isEmpty()) {
                    $bulties = $invoice->bulties;
                }
                $invoice->setRelation('bulties', $bulties);
                $invoice->setRelation('tollBulties', $bulties);

                $grandTollSum = 0;
                if ($invoice->tollDetails->isNotEmpty()) {
                    $grandTollSum = floatval($invoice->tollDetails->sum(function($d) {
                        return floatval($d->one_way) + floatval($d->return_amount);
                    }));
                } else {
                    foreach ($invoice->bulties as $bulty) {
                        if ($bulty->trip) {
                            $grandTollSum += floatval($bulty->trip->total_fasttag_amount);
                        }
                    }
                }

                $appliedGstRate = $invoice->gstMaster ? floatval($invoice->gstMaster->percentage) : ($gstPercentage ?: 18.00);
                $calculatedGst = round($grandTollSum * ($appliedGstRate / 100), 2);
                $grandTotal = round($grandTollSum + $calculatedGst, 2);
                $amountInWords = self::convertNumberToWords($grandTotal);

                if ($gstType === 'CGST_SGST') {
                    $cgstVal = round($calculatedGst / 2, 2);
                    $sgstVal = round($calculatedGst - $cgstVal, 2);
                    $igstVal = 0.00;
                } else {
                    $cgstVal = 0.00;
                    $sgstVal = 0.00;
                    $igstVal = $calculatedGst;
                }

                $invoice->update([
                    'total_freight' => $grandTollSum,
                    'total_gst' => $calculatedGst,
                    'cgst_amount' => $cgstVal,
                    'sgst_amount' => $sgstVal,
                    'igst_amount' => $igstVal,
                    'total_amount' => $grandTotal,
                    'amount_in_words' => $amountInWords,
                    'status' => 'pending',
                ]);
            }

            $invoices->push($invoice);
            $isFirstGroup = false;
        }

        $action = $request->input('action', 'generate_print');
        $totalLrs = $bulties->count();
        $invoiceCount = $invoices->count();

        if ($invoiceCount === 1) {
            $singleInvoice = $invoices->first();
            if ($action === 'save') {
                return redirect()->route('admin.transport.toll-bills.index')
                    ->with('success', 'Invoice ' . $singleInvoice->invoice_no . ' saved successfully for ' . $totalLrs . ' LR(s)');
            }
            return redirect()->route('admin.transport.invoices.show', $singleInvoice->id)
                ->with('success', 'Invoice ' . $singleInvoice->invoice_no . ' generated successfully for ' . $totalLrs . ' LR(s)');
        }

        return redirect()->route('admin.transport.toll-bills.index')
            ->with('success', $invoiceCount . ' invoice(s) generated successfully for ' . $totalLrs . ' LR(s)');
    }

    public function invoiceHistory(Request $request)
    {
        $query = Invoice::with(['consignor', 'company', 'user', 'freightBulties.consignee', 'tollBulties.consignee']);

        if ($request->filled('invoice_type') && in_array($request->invoice_type, ['freight', 'toll'])) {
            $query->where('invoice_type', $request->invoice_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_no', 'LIKE', "%{$search}%")
                  ->orWhere('bill_number', 'LIKE', "%{$search}%")
                  ->orWhere('consignor_name', 'LIKE', "%{$search}%")
                  ->orWhereHas('consignor', function ($qc) use ($search) {
                      $qc->where('name', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('freightBulties.consignee', function ($qc) use ($search) {
                      $qc->where('name', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('tollBulties.consignee', function ($qc) use ($search) {
                      $qc->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($request->filled('consignor_id')) {
            $query->where('consignor_id', $request->consignor_id);
        }

        if ($request->filled('consignee_id')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('freightBulties', function ($qb) use ($request) {
                    $qb->where('consignee_id', $request->consignee_id);
                })->orWhereHas('tollBulties', function ($qb) use ($request) {
                    $qb->where('consignee_id', $request->consignee_id);
                });
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }

        // Summary calculations for full matching dataset
        $statsQuery = clone $query;
        $totalFreight = (float) $statsQuery->sum('total_freight');
        $totalGst = (float) $statsQuery->sum('total_gst');
        $totalOther = (float) $statsQuery->sum('total_other');
        $totalAmount = (float) $statsQuery->sum('total_amount');

        $dateSort = $request->get('date_sort', 'latest');
        if ($dateSort === 'oldest') {
            $query->orderBy('invoice_date', 'asc')->orderBy('id', 'asc');
        } else {
            $query->orderBy('invoice_date', 'desc')->orderBy('id', 'desc');
        }

        $invoices = $query->paginate(15)->withQueryString();
        $consignors = Consignor::where('status', 'active')->orderBy('name')->get(['id', 'name', 'phone']);
        $consignees = Consignee::where('status', 'active')->orderBy('name')->get(['id', 'name', 'phone']);

        return view('admin.transport.billing.invoices.index', compact(
            'invoices', 'consignors', 'consignees', 'dateSort',
            'totalFreight', 'totalGst', 'totalOther', 'totalAmount'
        ));
    }

    public function tollBills(Request $request)
    {
        $query = Invoice::with(['consignor', 'company', 'user', 'tollBulties.consignee'])
            ->where('invoice_type', 'toll');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_no', 'LIKE', "%{$search}%")
                  ->orWhere('bill_number', 'LIKE', "%{$search}%")
                  ->orWhere('consignor_name', 'LIKE', "%{$search}%")
                  ->orWhereHas('consignor', function ($qc) use ($search) {
                      $qc->where('name', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('tollBulties.consignee', function ($qc) use ($search) {
                      $qc->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($request->filled('consignor_id')) {
            $query->where('consignor_id', $request->consignor_id);
        }

        if ($request->filled('consignee_id')) {
            $query->whereHas('tollBulties', function ($qb) use ($request) {
                $qb->where('consignee_id', $request->consignee_id);
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }

        // Calculate accurate dashboard metrics on full dataset before pagination
        $statsQuery = clone $query;
        $totalBills = $statsQuery->count();
        $totalFreight = (float) $statsQuery->sum('total_freight'); // Total Toll Amount
        $totalGst = (float) $statsQuery->sum('total_gst');
        $grandTotal = (float) $statsQuery->sum('total_amount');
        $pendingBills = (clone $query)->where('status', 'pending')->count();
        $paidBills = (clone $query)->where('status', 'paid')->count();

        $dateSort = $request->get('date_sort', 'latest');
        if ($dateSort === 'oldest') {
            $query->orderBy('invoice_date', 'asc')->orderBy('id', 'asc');
        } else {
            $query->orderBy('invoice_date', 'desc')->orderBy('id', 'desc');
        }

        $invoices = $query->paginate(15)->withQueryString();
        $consignors = Consignor::where('status', 'active')->orderBy('name')->get(['id', 'name', 'phone']);
        $consignees = Consignee::where('status', 'active')->orderBy('name')->get(['id', 'name', 'phone']);

        return view('admin.transport.billing.invoices.toll-bills', compact(
            'invoices', 'consignors', 'consignees', 'dateSort',
            'totalBills', 'totalFreight', 'totalGst', 'grandTotal', 'pendingBills', 'paidBills'
        ));
    }

    public function billGenerate(Invoice $invoice)
    {
        $bultyRelation = $invoice->invoice_type === 'toll' ? 'tollBulties' : 'freightBulties';
        $invoice->load([$bultyRelation => function($q) {
            $q->with([
                'consignor', 'consignee', 'originCity', 'destinationCity',
                'trip.fastTagDetails'
            ]);
        }]);
        $invoice->setRelation('bulties', $invoice->{$bultyRelation});

        return view('admin.transport.billing.invoices.bill-generate', compact('invoice'));
    }

    public function showInvoice(Invoice $invoice)
    {
        $bultyRelation = $invoice->invoice_type === 'toll' ? 'tollBulties' : 'freightBulties';
        if (!method_exists($invoice, $bultyRelation)) {
            abort(404, "Invalid invoice type: {$invoice->invoice_type}");
        }
        $invoice->load([
            'consignor', 
            'company', 
            'gstMaster',
            'tollDetails',
            $bultyRelation => function($q) {
                $q->with([
                    'consignor', 'consignee', 'originCity', 'destinationCity', 
                    'bultyDetail.supplier', 'branch', 'company', 'vehicle', 'driver', 'bultyItems.item',
                    'trip.fastTagDetails', 'invoice'
                ]);
            }
        ]);
        $invoice->setRelation('bulties', $invoice->{$bultyRelation});

        if ($invoice->invoice_type === 'toll') {
            return view('admin.transport.billing.invoices.toll-print', compact('invoice'));
        }

        if ($invoice->template_type === 'nathdwara') {
            return view('admin.transport.billing.invoices.show_nathdwara', compact('invoice'));
        } elseif ($invoice->template_type === 'gypsum') {
            return view('admin.transport.billing.invoices.show_gypsum', compact('invoice'));
        }

        return view('admin.transport.billing.invoices.show', compact('invoice'));
    }

    public function tollPrint(Invoice $invoice)
    {
        $bultyRelation = $invoice->invoice_type === 'toll' ? 'tollBulties' : 'freightBulties';
        $invoice->load([
            'consignor', 
            'company', 
            'gstMaster',
            'tollDetails',
            $bultyRelation => function($q) {
                $q->with([
                    'consignor', 'consignee', 'originCity', 'destinationCity', 
                    'bultyDetail', 'branch', 'company', 'vehicle', 'driver', 
                    'trip.fastTagDetails', 'invoice'
                ]);
            }
        ]);
        $invoice->setRelation('bulties', $invoice->{$bultyRelation});

        return view('admin.transport.billing.invoices.toll-print', compact('invoice'));
    }

    public function exportInvoiceExcel(Invoice $invoice)
    {
        $bultyRelation = $invoice->invoice_type === 'toll' ? 'tollBulties' : 'freightBulties';
        $invoice->load([
            'consignor', 
            'company', 
            'gstMaster',
            'tollDetails',
            $bultyRelation => function($q) {
                $q->with([
                    'consignor', 'consignee', 'originCity', 'destinationCity', 
                    'bultyDetail.supplier', 'branch', 'company', 'vehicle', 'driver', 'bultyItems.item',
                    'trip.fastTagDetails', 'invoice'
                ]);
            }
        ]);
        $invoice->setRelation('bulties', $invoice->{$bultyRelation});

        $fileName = 'invoice_' . $invoice->invoice_no . '.xlsx';
        return Excel::download(new InvoiceExport($invoice), $fileName);
    }

    private function saveTollDetails(Invoice $invoice)
    {
        $bulties = $invoice->tollBulties()->with('trip.fastTagDetails')->get();
        if ($bulties->isEmpty()) {
            $bulties = $invoice->freightBulties()->with('trip.fastTagDetails')->get();
        }
        if ($bulties->isEmpty() && $invoice->bulties) {
            $bulties = $invoice->bulties;
        }
        $invoice->setRelation('bulties', $bulties);
        $invoice->setRelation('tollBulties', $bulties);

        $invoice->tollDetails()->delete();

        $tollLocations = collect();
        foreach ($invoice->bulties as $bulty) {
            if ($bulty->trip) {
                foreach ($bulty->trip->fastTagDetails as $ft) {
                    $loc = strtoupper(trim((string)($ft->location ?? '')));
                    if (!empty($loc)) {
                        $tollLocations->push($loc);
                    } elseif (floatval($ft->amount ?? 0) > 0 || floatval($ft->one_way ?? 0) > 0 || floatval($ft->return ?? 0) > 0) {
                        $tollLocations->push('TOLL PLAZA');
                    }
                }
            }
        }
        $tollLocations = $tollLocations->unique()->values();

        if ($tollLocations->isEmpty()) {
            foreach ($invoice->bulties as $bulty) {
                if ($bulty->trip && floatval($bulty->trip->total_fasttag_amount) > 0) {
                    $invoice->tollDetails()->create([
                        'builty_id' => $bulty->id,
                        'location' => 'TOLL PLAZA',
                        'one_way' => floatval($bulty->trip->total_fasttag_amount),
                        'return_amount' => 0,
                    ]);
                }
            }
        } else {
            foreach ($invoice->bulties as $bulty) {
                $ftDetails = $bulty->trip ? $bulty->trip->fastTagDetails : collect();

                foreach ($tollLocations as $location) {
                    $matched = $ftDetails->filter(function($ft) use ($location) {
                        $loc = strtoupper(trim((string)($ft->location ?? '')));
                        if ($location === 'TOLL PLAZA') {
                            return empty($loc) || $loc === 'TOLL PLAZA';
                        }
                        return $loc === $location;
                    });

                    if ($matched->isNotEmpty()) {
                        $oneWaySum = (float) $matched->sum('one_way');
                        $returnSum = (float) $matched->sum('return');
                        $amountSum = (float) $matched->sum('amount');

                        if ($oneWaySum <= 0 && $returnSum <= 0 && $amountSum > 0) {
                            $oneWaySum = $amountSum;
                        }

                        if ($oneWaySum > 0 || $returnSum > 0) {
                            $invoice->tollDetails()->create([
                                'builty_id' => $bulty->id,
                                'location' => $location,
                                'one_way' => $oneWaySum,
                                'return_amount' => $returnSum,
                            ]);
                        }
                    }
                }
            }
        }
    }

    private function resolveTollInvoiceMetaData(Invoice $sourceInvoice, Request $request): array
    {
        $resolveValue = function (string $field) use ($sourceInvoice, $request) {
            if ($request->exists($field)) {
                $value = $request->input($field);

                if (is_string($value)) {
                    $value = trim($value);
                }

                if ($value !== null && $value !== '' && $value !== []) {
                    return $value;
                }
            }

            return data_get($sourceInvoice, $field);
        };

        return [
            'custom_hsn_code' => $resolveValue('custom_hsn_code'),
            'company_name' => $resolveValue('company_name'),
            'billing_address' => $resolveValue('billing_address'),
            'custom_place_of_supply' => $resolveValue('custom_place_of_supply'),
            'custom_district' => $resolveValue('custom_district'),
            'custom_state' => $resolveValue('custom_state'),
            'custom_state_code' => $resolveValue('custom_state_code'),
            'custom_gstn' => $resolveValue('custom_gstn'),
            'custom_pan_no' => $resolveValue('custom_pan_no'),
            'state_vendor_code' => $resolveValue('state_vendor_code'),
            'vendor_code' => $resolveValue('vendor_code'),
            'vendor_name' => $resolveValue('vendor_name'),
            'consignor_name' => $resolveValue('consignor_name'),
            'epod_status' => $resolveValue('epod_status'),
            'mn_number' => $resolveValue('mn_number'),
            'no_of_lrs' => $resolveValue('no_of_lrs'),
        ];
    }

    public function saveTollInvoice(Request $request, Invoice $invoice)
    {
        if ($request->filled('bill_number') && $invoice->invoice_type !== 'toll') {
            $request->validate([
                'bill_number' => [
                    Rule::unique('invoices', 'invoice_no')->where(function ($query) use ($invoice) {
                        return $query->where('company_id', $invoice->company_id);
                    })
                ],
            ], [
                'bill_number.unique' => 'This bill number is already taken. Please use a different one.',
            ]);
        }

        $targetInvoice = $invoice;
        $metaData = $this->resolveTollInvoiceMetaData($invoice, $request);
        
        $firstBulty = $targetInvoice->bulties->first();
        $company = $targetInvoice->company ?? Company::find($targetInvoice->company_id);
        $originState = $company?->state ?: ($company?->gst_number ? substr($company->gst_number, 0, 2) : ($firstBulty?->originCity?->state ?? 'RAJASTHAN'));
        $placeOfSupply = $metaData['custom_place_of_supply'] ?: ($targetInvoice->custom_place_of_supply ?: ($metaData['custom_state'] ?: ($firstBulty?->destinationCity?->state ?? 'RAJASTHAN')));
        $isSameState = self::isSameGstState($originState, $placeOfSupply);
        $autoGstType = $isSameState ? 'CGST_SGST' : 'IGST';

        // Check if user selected GST type or fallback to saved / auto-detected
        $gstType = $request->input('gst_type');
        if (!in_array($gstType, ['CGST_SGST', 'IGST'])) {
            $gstType = ($targetInvoice->invoice_type === 'toll' && !empty($targetInvoice->gst_type)) ? $targetInvoice->gst_type : $autoGstType;
        }

        // Default Payment Status: Pending by default unless explicitly specified
        $submittedStatus = $request->input('status');
        if ($invoice->invoice_type === 'toll') {
            $status = in_array($submittedStatus, ['pending', 'paid', 'cancelled']) ? $submittedStatus : ($invoice->status ?: 'pending');
        } else {
            $status = in_array($submittedStatus, ['pending', 'paid', 'cancelled']) ? $submittedStatus : 'pending';
        }

        if ($invoice->invoice_type !== 'toll') {
            $grandTollSum = 0;
            foreach ($invoice->bulties as $bulty) {
                if ($bulty->trip) {
                    $grandTollSum += floatval($bulty->trip->total_fasttag_amount);
                }
            }
            
            $gstRate = $invoice->gstMaster ? floatval($invoice->gstMaster->percentage) : 18.00;
            $calculatedGst = round($grandTollSum * ($gstRate / 100), 2);
            $grandTotal = round($grandTollSum + $calculatedGst, 2);
            $amountInWords = self::convertNumberToWords($grandTotal);

            if ($gstType === 'CGST_SGST') {
                $cgstVal = round($calculatedGst / 2, 2);
                $sgstVal = round($calculatedGst - $cgstVal, 2);
                $igstVal = 0.00;
            } else {
                $cgstVal = 0.00;
                $sgstVal = 0.00;
                $igstVal = $calculatedGst;
            }
            
            $customBillNo = $request->bill_number;
            $prefix = 'INV-TOLL';
            $invoiceNo = $customBillNo ?: DB::transaction(function () use ($prefix) {
                $latest = Invoice::where('invoice_no', 'like', "{$prefix}-%")
                    ->orderBy('id', 'desc')
                    ->lockForUpdate()
                    ->first();
                $nextNum = $latest ? (intval(explode('-', $latest->invoice_no)[count(explode('-', $latest->invoice_no)) - 1]) + 1) : 1;
                return "{$prefix}-" . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
            });

            $targetInvoice = Invoice::create([
                'company_id' => $invoice->company_id,
                'branch_id' => $invoice->branch_id,
                'consignor_id' => $invoice->consignor_id,
                'consignor_name' => $metaData['consignor_name'],
                'company_name' => $metaData['company_name'],
                'billing_address' => $metaData['billing_address'],
                'custom_hsn_code' => $metaData['custom_hsn_code'],
                'custom_place_of_supply' => $metaData['custom_place_of_supply'],
                'custom_district' => $metaData['custom_district'],
                'custom_state' => $metaData['custom_state'],
                'custom_state_code' => $metaData['custom_state_code'],
                'custom_gstn' => $metaData['custom_gstn'],
                'custom_pan_no' => $metaData['custom_pan_no'],
                'from_city_name' => $invoice->from_city_name,
                'to_city_name' => $invoice->to_city_name,
                'total_freight' => $grandTollSum,
                'total_gst' => $calculatedGst,
                'gst_type' => $gstType,
                'cgst_amount' => $cgstVal,
                'sgst_amount' => $sgstVal,
                'igst_amount' => $igstVal,
                'total_other' => 0,
                'invoice_no' => $invoiceNo,
                'bill_number' => $request->bill_number ?? $invoiceNo,
                'invoice_type' => 'toll',
                'invoice_date' => now(),
                'total_amount' => $grandTotal,
                'amount_in_words' => $amountInWords,
                'visible_fields' => $invoice->visible_fields,
                'grn_fields' => $invoice->grn_fields,
                'grn_new_page' => $invoice->grn_new_page,
                'status' => $status,
                'user_id' => auth()->id(),
                'gst_master_id' => $invoice->gst_master_id,
                'mn_number' => $metaData['mn_number'],
                'no_of_lrs' => $metaData['no_of_lrs'],
                'state_vendor_code' => $metaData['state_vendor_code'],
                'vendor_code' => $metaData['vendor_code'],
                'vendor_name' => $metaData['vendor_name'],
                'epod_status' => $metaData['epod_status'],
            ]);
            
            foreach ($invoice->bulties as $bulty) {
                $bulty->update([
                    'toll_invoice_id' => $targetInvoice->id,
                ]);
            }
        } else {
            $updateData = [];
            if ($request->filled('status') && in_array($request->status, ['pending', 'paid', 'cancelled'])) {
                $updateData['status'] = $request->status;
            }
            if ($request->has('bill_number')) {
                $updateData['bill_number'] = $request->bill_number;
            }
            $updateData['mn_number'] = $metaData['mn_number'];
            $updateData['no_of_lrs'] = $metaData['no_of_lrs'];
            $updateData['company_name'] = $metaData['company_name'];
            $updateData['billing_address'] = $metaData['billing_address'];
            $updateData['custom_hsn_code'] = $metaData['custom_hsn_code'];
            $updateData['custom_place_of_supply'] = $metaData['custom_place_of_supply'];
            $updateData['custom_district'] = $metaData['custom_district'];
            $updateData['custom_state'] = $metaData['custom_state'];
            $updateData['custom_state_code'] = $metaData['custom_state_code'];
            $updateData['custom_gstn'] = $metaData['custom_gstn'];
            $updateData['custom_pan_no'] = $metaData['custom_pan_no'];
            $updateData['state_vendor_code'] = $metaData['state_vendor_code'];
            $updateData['vendor_code'] = $metaData['vendor_code'];
            $updateData['vendor_name'] = $metaData['vendor_name'];
            $updateData['consignor_name'] = $metaData['consignor_name'];
            $updateData['epod_status'] = $metaData['epod_status'];
            $targetInvoice->update($updateData);
        }

        $this->saveTollDetails($targetInvoice);

        if ($request->has('bulty_modes') && is_array($request->bulty_modes)) {
            foreach ($request->bulty_modes as $bultyId => $mode) {
                Bulty::where('id', $bultyId)->where('toll_invoice_id', $targetInvoice->id)->update(['mode' => $mode]);
            }
        }

        $targetInvoice->load(['tollDetails', 'gstMaster', 'company']);
        $bulties = $targetInvoice->tollBulties()->with('trip.fastTagDetails')->get();
        if ($bulties->isEmpty()) {
            $bulties = $targetInvoice->bulties;
        }
        $targetInvoice->setRelation('bulties', $bulties);
        $targetInvoice->setRelation('tollBulties', $bulties);

        $grandTollSum = 0;
        if ($targetInvoice->tollDetails->isNotEmpty()) {
            $grandTollSum = floatval($targetInvoice->tollDetails->sum(function($d) {
                return floatval($d->one_way) + floatval($d->return_amount);
            }));
        } else {
            foreach ($targetInvoice->bulties as $bulty) {
                if ($bulty->trip) {
                    $grandTollSum += floatval($bulty->trip->total_fasttag_amount);
                }
            }
        }

        $gstRate = $targetInvoice->gstMaster ? floatval($targetInvoice->gstMaster->percentage) : 18.00;
        $calculatedGst = round($grandTollSum * ($gstRate / 100), 2);
        $grandTotal = round($grandTollSum + $calculatedGst, 2);
        $amountInWords = self::convertNumberToWords($grandTotal);

        if ($gstType === 'CGST_SGST') {
            $cgstVal = round($calculatedGst / 2, 2);
            $sgstVal = round($calculatedGst - $cgstVal, 2);
            $igstVal = 0.00;
        } else {
            $cgstVal = 0.00;
            $sgstVal = 0.00;
            $igstVal = $calculatedGst;
        }

        $targetInvoice->update([
            'status' => $status,
            'gst_type' => $gstType,
            'total_freight' => $grandTollSum,
            'total_gst' => $calculatedGst,
            'cgst_amount' => $cgstVal,
            'sgst_amount' => $sgstVal,
            'igst_amount' => $igstVal,
            'total_amount' => $grandTotal,
            'amount_in_words' => $amountInWords,
        ]);

        return redirect()->route('admin.transport.toll-bills.index')
            ->with('success', 'Toll Bill ' . $targetInvoice->invoice_no . ' saved successfully.');
    }

    /**
     * Normalize an input state, GSTIN, or code into a 2-digit Indian GST State Code
     *
     * @param mixed $state
     * @return string|null
     */
    public static function normalizeGstStateCode($state)
    {
        if (empty($state)) {
            return null;
        }

        $str = strtoupper(trim((string)$state));

        // If it contains a 15-char GSTIN, take first 2 digits
        if (preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', $str)) {
            return substr($str, 0, 2);
        }

        // If starts with 2 digits e.g. "08-RAJASTHAN" or "24 - GUJARAT"
        if (preg_match('/^([0-9]{2})\b/', $str, $matches)) {
            return $matches[1];
        }

        // Single or 2-digit number e.g. 8 or 24
        if (is_numeric($str) && intval($str) >= 1 && intval($str) <= 38) {
            return str_pad(intval($str), 2, '0', STR_PAD_LEFT);
        }

        // Clean string of symbols and digits
        $clean = strtoupper(trim(preg_replace('/[^A-Z]/', '', $str)));

        $stateMap = [
            'JAMMUANDKASHMIR' => '01', 'JAMMUKASHMIR' => '01', 'JK' => '01',
            'HIMACHALPRADESH' => '02', 'HP' => '02',
            'PUNJAB' => '03', 'PB' => '03',
            'CHANDIGARH' => '04', 'CH' => '04',
            'UTTARAKHAND' => '05', 'UK' => '05',
            'HARYANA' => '06', 'HR' => '06',
            'DELHI' => '07', 'DL' => '07',
            'RAJASTHAN' => '08', 'RJ' => '08',
            'UTTARPRADESH' => '09', 'UP' => '09',
            'BIHAR' => '10', 'BR' => '10',
            'SIKKIM' => '11', 'SK' => '11',
            'ARUNACHALPRADESH' => '12', 'AR' => '12',
            'NAGALAND' => '13', 'NL' => '13',
            'MANIPUR' => '14', 'MN' => '14',
            'MIZORAM' => '15', 'MZ' => '15',
            'TRIPURA' => '16', 'TR' => '16',
            'MEGHALAYA' => '17', 'ML' => '17',
            'ASSAM' => '18', 'AS' => '18',
            'WESTBENGAL' => '19', 'WB' => '19',
            'JHARKHAND' => '20', 'JH' => '20',
            'ODISHA' => '21', 'ORISSA' => '21', 'OD' => '21',
            'CHHATTISGARH' => '22', 'CG' => '22',
            'MADHYAPRADESH' => '23', 'MP' => '23',
            'GUJARAT' => '24', 'GJ' => '24',
            'DADRAANDNAGARHAVELI' => '26', 'DAMANANDDIU' => '26', 'DNHDD' => '26',
            'MAHARASHTRA' => '27', 'MH' => '27',
            'ANDHRAPRADESH' => '37', 'AP' => '37',
            'KARNATAKA' => '29', 'KA' => '29',
            'GOA' => '30', 'GA' => '30',
            'LAKSHADWEEP' => '31', 'LD' => '31',
            'KERALA' => '32', 'KL' => '32',
            'TAMILNADU' => '33', 'TN' => '33',
            'PUDUCHERRY' => '34', 'PY' => '34',
            'ANDAMANANDNICOBAR' => '35', 'AN' => '35',
            'TELANGANA' => '36', 'TS' => '36', 'TG' => '36',
            'LADAKH' => '38', 'LA' => '38',
        ];

        if (isset($stateMap[$clean])) {
            return $stateMap[$clean];
        }

        // Attempt lookup by city name if supplied
        $city = City::where('name', trim((string)$state))->first();
        if ($city && $city->state) {
            $cityClean = strtoupper(trim(preg_replace('/[^A-Z]/', '', $city->state)));
            if (isset($stateMap[$cityClean])) {
                return $stateMap[$cityClean];
            }
        }

        return null;
    }

    /**
     * Determine whether origin state and place of supply match (same GST state)
     *
     * @param string|null $originState
     * @param string|null $placeOfSupply
     * @return bool
     */
    public static function isSameGstState($originState, $placeOfSupply)
    {
        $code1 = self::normalizeGstStateCode($originState);
        $code2 = self::normalizeGstStateCode($placeOfSupply);

        if ($code1 !== null && $code2 !== null) {
            return $code1 === $code2;
        }

        if (empty($originState) || empty($placeOfSupply)) {
            return true;
        }

        $originStr = strtoupper(trim((string)$originState));
        $supplyStr = strtoupper(trim((string)$placeOfSupply));

        if ($originStr === $supplyStr) {
            return true;
        }

        $cleanOrigin = strtoupper(trim(preg_replace('/[^A-Z]/', '', $originStr)));
        $cleanSupply = strtoupper(trim(preg_replace('/[^A-Z]/', '', $supplyStr)));

        if (!empty($cleanOrigin) && !empty($cleanSupply)) {
            return $cleanOrigin === $cleanSupply;
        }

        return false;
    }

    public static function convertNumberToWords($amount)
    {
        $amount = round($amount, 2);
        $rupees = floor($amount);
        $paise = round(($amount - $rupees) * 100);

        $rupeesStr = self::numberToWordsHelper($rupees);
        $result = $rupeesStr ? $rupeesStr . ' Rupees' : 'Zero Rupees';

        if ($paise > 0) {
            $paiseStr = self::numberToWordsHelper($paise);
            $result .= ' and ' . $paiseStr . ' Paise';
        }

        return strtoupper($result . ' Only.');
    }

    private static function numberToWordsHelper($num)
    {
        $ones = array(
            0 => "", 1 => "one", 2 => "two", 3 => "three", 4 => "four", 5 => "five", 6 => "six", 7 => "seven", 8 => "eight", 9 => "nine",
            10 => "ten", 11 => "eleven", 12 => "twelve", 13 => "thirteen", 14 => "fourteen", 15 => "fifteen", 16 => "sixteen", 17 => "seventeen", 18 => "eighteen", 19 => "nineteen"
        );
        $tens = array(
            0 => "", 1 => "", 2 => "twenty", 3 => "thirty", 4 => "forty", 5 => "fifty", 6 => "sixty", 7 => "seventy", 8 => "eighty", 9 => "ninety"
        );

        if ($num == 0) {
            return "";
        }

        $str = "";
        if ($num >= 10000000) {
            $str .= self::numberToWordsHelper(floor($num / 10000000)) . " crore ";
            $num %= 10000000;
        }
        if ($num >= 100000) {
            $str .= self::numberToWordsHelper(floor($num / 100000)) . " lakh ";
            $num %= 100000;
        }
        if ($num >= 1000) {
            $str .= self::numberToWordsHelper(floor($num / 1000)) . " thousand ";
            $num %= 1000;
        }
        if ($num >= 100) {
            $str .= self::numberToWordsHelper(floor($num / 100)) . " hundred ";
            $num %= 100;
        }
        if ($num > 0) {
            if ($str != "") {
                $str .= "and ";
            }
            if ($num < 20) {
                $str .= $ones[$num] . " ";
            } else {
                $str .= $tens[floor($num / 10)] . " ";
                if ($num % 10 > 0) {
                    $str .= $ones[$num % 10] . " ";
                }
            }
        }
        return trim($str);
    }

    public function updateStatus(Request $request, Invoice $invoice)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,cancelled'
        ]);

        $invoice->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Invoice status updated successfully.');
    }

    public function destroy(Invoice $invoice)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->can('delete invoices') && !auth()->user()->can('delete billing')) {
            abort(403, 'Unauthorized action. You do not have permission to delete invoices.');
        }

        DB::transaction(function () use ($invoice) {
            if ($invoice->invoice_type === 'toll') {
                Bulty::withoutGlobalScopes()->where('toll_invoice_id', $invoice->id)->update([
                    'toll_invoice_id' => null
                ]);
            } else {
                Bulty::withoutGlobalScopes()->where('invoice_id', $invoice->id)->update([
                    'bill_status' => 'unbilled',
                    'invoice_id' => null
                ]);
            }

            $invoice->delete();
        });

        return redirect()->back()->with('success', 'Invoice deleted successfully and LRs reverted to unbilled.');
    }
}
