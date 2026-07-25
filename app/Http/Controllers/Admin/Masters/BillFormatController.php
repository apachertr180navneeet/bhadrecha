<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Models\BillFormat;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Consignor;
use App\Models\GstMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillFormatController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = BillFormat::with(['company', 'depot', 'party', 'gstMaster', 'user'])
                ->orderBy('company_id')->orderBy('format_name');

            if ($request->filled('company_id')) {
                $query->where('company_id', $request->company_id);
            }

            if ($request->filled('search')) {
                $query->where('format_name', 'like', '%' . $request->search . '%');
            }

            $formats = $query->paginate(20);
            $companies = Company::where('status', 'active')->orderBy('name')->get();
            $gstMasters = GstMaster::where('status', 'active')->get();
            return view('admin.masters.bill-formats.index', compact('formats', 'companies', 'gstMasters'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load bill formats: ' . $e->getMessage());
        }
    }

    public function create()
    {
        try {
            $companies = Company::where('status', 'active')->orderBy('name')->get();
            $gstMasters = GstMaster::where('status', 'active')->get();
            return view('admin.masters.bill-formats.create', compact('companies', 'gstMasters'));
        } catch (\Exception $e) {
            return redirect()->route('admin.masters.bill-formats.index')->with('error', 'Failed to load form: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'company_id' => 'required|exists:companies,id',
                'format_name' => 'required|string|max:255',
                'depot_id' => 'nullable|exists:branches,id',
                'party_id' => 'nullable|exists:consignors,id',
                'template_type' => 'nullable|string|in:standard,nathdwara',
                'visible_fields' => 'nullable|array',
                'visible_fields.*' => 'string',
                'grn_fields' => 'nullable|array',
                'grn_fields.*' => 'string',
                'field_order' => 'nullable|string',
                'grn_field_order' => 'nullable|string',
                'grn_new_page' => 'boolean',
                'gst_master_id' => 'nullable|exists:gst_masters,id',
            ]);

            $data['user_id'] = auth()->id();
            $data['grn_new_page'] = $request->boolean('grn_new_page');
            if ($request->has('field_order')) {
                $data['field_order'] = json_decode($request->field_order, true);
            }
            if ($request->has('grn_field_order')) {
                $data['grn_field_order'] = json_decode($request->grn_field_order, true);
            }

            DB::beginTransaction();
            BillFormat::create($data);
            DB::commit();

            return redirect()->route('admin.masters.bill-formats.index')
                ->with('success', 'Bill format created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to create bill format: ' . $e->getMessage());
        }
    }

    public function edit(BillFormat $billFormat)
    {
        try {
            $companies = Company::where('status', 'active')->orderBy('name')->get();
            $gstMasters = GstMaster::where('status', 'active')->get();
            $depots = Branch::where('company_id', $billFormat->company_id)->orderBy('name')->get();
            $parties = Consignor::where('company_id', $billFormat->company_id)->where('status', 'active')->orderBy('name')->get();
            return view('admin.masters.bill-formats.edit', compact('billFormat', 'companies', 'gstMasters', 'depots', 'parties'));
        } catch (\Exception $e) {
            return redirect()->route('admin.masters.bill-formats.index')->with('error', 'Failed to load bill format: ' . $e->getMessage());
        }
    }

    public function update(Request $request, BillFormat $billFormat)
    {
        try {
            $data = $request->validate([
                'company_id' => 'required|exists:companies,id',
                'format_name' => 'required|string|max:255',
                'depot_id' => 'nullable|exists:branches,id',
                'party_id' => 'nullable|exists:consignors,id',
                'template_type' => 'nullable|string|in:standard,nathdwara',
                'visible_fields' => 'nullable|array',
                'visible_fields.*' => 'string',
                'grn_fields' => 'nullable|array',
                'grn_fields.*' => 'string',
                'field_order' => 'nullable|string',
                'grn_field_order' => 'nullable|string',
                'grn_new_page' => 'boolean',
                'gst_master_id' => 'nullable|exists:gst_masters,id',
            ]);

            $data['grn_new_page'] = $request->boolean('grn_new_page');
            if ($request->has('field_order')) {
                $data['field_order'] = json_decode($request->field_order, true);
            }
            if ($request->has('grn_field_order')) {
                $data['grn_field_order'] = json_decode($request->grn_field_order, true);
            }

            DB::beginTransaction();
            $billFormat->update($data);
            DB::commit();

            return redirect()->route('admin.masters.bill-formats.index')
                ->with('success', 'Bill format updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to update bill format: ' . $e->getMessage());
        }
    }

    public function destroy(BillFormat $billFormat)
    {
        try {
            DB::beginTransaction();
            $billFormat->delete();
            DB::commit();

            return redirect()->route('admin.masters.bill-formats.index')
                ->with('success', 'Bill format deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to delete bill format: ' . $e->getMessage());
        }
    }

    public function getDepots(Request $request)
    {
        try {
            $depots = Branch::where('company_id', $request->company_id)->orderBy('name')->get(['id', 'name']);
            return response()->json($depots);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getParties(Request $request)
    {
        try {
            $parties = Consignor::where('company_id', $request->company_id)
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name']);
            return response()->json($parties);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getFormats(Request $request)
    {
        try {
            $query = BillFormat::with(['company', 'depot', 'party', 'gstMaster']);

            if ($request->filled('id')) {
                $query->where('id', $request->id);
            }
            if ($request->filled('company_id')) {
                $query->where('company_id', $request->company_id);
            }
            if ($request->filled('depot_id')) {
                $query->where('depot_id', $request->depot_id);
            }
            if ($request->filled('party_id')) {
                $query->where('party_id', $request->party_id);
            }

            $formats = $query->orderBy('format_name')->get();
            return response()->json($formats);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
