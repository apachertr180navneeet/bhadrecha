<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BiltyAdvanceDetail;
use App\Models\Bulty;
use App\Models\Company;
use App\Models\Branch;
use App\Exports\Reports\ReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class BiltyAdvanceDetailController extends Controller
{
    public function index(Request $request)
    {
        $query = BiltyAdvanceDetail::with(['builty', 'company', 'branch']);

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('bulty_id')) {
            $query->where('bulty_id', $request->bulty_id);
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $totalAdvance = (clone $query)->sum('advance_amount');
        $records = (clone $query)->orderBy('date', 'desc')->orderBy('id', 'desc')->paginate(20)->appends($request->all());

        $editRecord = null;
        if ($request->filled('edit')) {
            $editRecord = BiltyAdvanceDetail::find($request->edit);
        }

        $bulties = Bulty::with(['company', 'branch'])->orderBy('id', 'desc')->get();
        $companies = Company::active()->get();
        $branches = Branch::active()->get();

        return view('admin.reports.bilty_advance_details', compact(
            'records',
            'totalAdvance',
            'editRecord',
            'bulties',
            'companies',
            'branches'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bulty_id' => 'required|exists:bulties,id',
            'date' => 'required|date',
            'advance_amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $bulty = Bulty::findOrFail($request->bulty_id);

        $validated['company_id'] = $bulty->company_id;
        $validated['branch_id'] = $bulty->branch_id;

        $detail = BiltyAdvanceDetail::create($validated);

        // Sync total advance amount on LR
        $bulty->update([
            'advance_amount' => $bulty->biltyAdvanceDetails()->sum('advance_amount')
        ]);

        return redirect()->route('admin.reports.bilty-advance-details.index')
            ->with('success', 'Bilty Advance detail recorded successfully.');
    }

    public function update(Request $request, $id)
    {
        $record = BiltyAdvanceDetail::findOrFail($id);

        $validated = $request->validate([
            'bulty_id' => 'required|exists:bulties,id',
            'date' => 'required|date',
            'advance_amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $bulty = Bulty::findOrFail($request->bulty_id);

        $validated['company_id'] = $bulty->company_id;
        $validated['branch_id'] = $bulty->branch_id;

        $oldBultyId = $record->bulty_id;
        $record->update($validated);

        // Sync advance amount on LR(s)
        $bulty->update([
            'advance_amount' => $bulty->biltyAdvanceDetails()->sum('advance_amount')
        ]);

        if ($oldBultyId != $bulty->id) {
            $oldBulty = Bulty::find($oldBultyId);
            if ($oldBulty) {
                $oldBulty->update([
                    'advance_amount' => $oldBulty->biltyAdvanceDetails()->sum('advance_amount')
                ]);
            }
        }

        return redirect()->route('admin.reports.bilty-advance-details.index')
            ->with('success', 'Bilty Advance detail updated successfully.');
    }

    public function destroy($id)
    {
        $record = BiltyAdvanceDetail::findOrFail($id);
        $bultyId = $record->bulty_id;
        $record->delete();

        $bulty = Bulty::find($bultyId);
        if ($bulty) {
            $bulty->update([
                'advance_amount' => $bulty->biltyAdvanceDetails()->sum('advance_amount')
            ]);
        }

        return redirect()->route('admin.reports.bilty-advance-details.index')
            ->with('success', 'Bilty Advance detail deleted successfully.');
    }

    public function exportExcel(Request $request)
    {
        $query = BiltyAdvanceDetail::with(['builty', 'company', 'branch']);

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('bulty_id')) {
            $query->where('bulty_id', $request->bulty_id);
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $records = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->get();

        $headings = [
            'S.No.',
            'LR Number',
            'Date',
            'Company',
            'Branch',
            'Advance Amount (Rs.)',
            'Remarks',
        ];

        $data = [];
        foreach ($records as $index => $row) {
            $data[] = [
                $index + 1,
                $row->builty?->lr_no ?? '-',
                $row->date?->format('d-m-Y') ?? '-',
                $row->company?->name ?? '-',
                $row->branch?->name ?? '-',
                number_format($row->advance_amount, 2, '.', ''),
                $row->remarks ?? '',
            ];
        }

        return Excel::download(
            new ReportExport($headings, $data, 'Bilty Advance Details Report'),
            'bilty_advance_details_' . date('Y-m-d') . '.xlsx'
        );
    }

    public function getBultyInfo($id)
    {
        $bulty = Bulty::with(['company', 'branch'])->find($id);

        if (!$bulty) {
            return response()->json(['error' => 'LR not found'], 444);
        }

        return response()->json([
            'id' => $bulty->id,
            'lr_no' => $bulty->lr_no,
            'company_id' => $bulty->company_id,
            'company_name' => $bulty->company?->name ?? '-',
            'branch_id' => $bulty->branch_id,
            'branch_name' => $bulty->branch?->name ?? '-',
        ]);
    }
}
