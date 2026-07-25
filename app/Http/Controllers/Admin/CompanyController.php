<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Branch;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::withCount('branches', 'users')
            ->orderBy('name')
            ->paginate(15);
        return view('admin.companies.index', compact('companies'));
    }

    public function getAllCompanies(Request $request)
    {
        $query = Company::withCount('branches', 'users');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $companies = $query->orderBy('created_at', 'desc')->get();

        return response()->json(['data' => $companies]);
    }

    public function create()
    {
        return view('admin.companies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:companies,name',
            'email' => 'nullable|email|unique:companies,email',
            'phone' => 'nullable|digits_between:10,15|unique:companies,phone',
            'address' => 'nullable|string',
            'state' => 'nullable|string|max:100',
            'disclaimer' => 'nullable|string',
            'declaration' => 'nullable|string',
            'gst_number' => 'nullable|string|max:50',
            'logo' => 'nullable|image|max:2048',
            'bank_holder_name' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_no' => 'nullable|string|max:50',
            'bank_ifsc' => 'nullable|string|max:50',
            'bank_branch' => 'nullable|string|max:255',
            'digital_signature' => 'nullable|image|max:2048',
            'owner_name' => 'nullable|string|max:255',
            'pan_number' => 'nullable|string|max:50',
            'hsn_code' => 'nullable|string|max:50',
        ]);

        $company = new Company();
        $company->name = $validated['name'];
        $company->email = $validated['email'] ?? null;
        $company->phone = $validated['phone'] ?? null;
        $company->address = $validated['address'] ?? null;
        $company->state = $validated['state'] ?? null;
        $company->disclaimer = $validated['disclaimer'] ?? null;
        $company->declaration = $validated['declaration'] ?? null;
        $company->gst_number = $validated['gst_number'] ?? null;
        $company->bank_holder_name = $validated['bank_holder_name'] ?? null;
        $company->bank_name = $validated['bank_name'] ?? null;
        $company->bank_account_no = $validated['bank_account_no'] ?? null;
        $company->bank_ifsc = $validated['bank_ifsc'] ?? null;
        $company->bank_branch = $validated['bank_branch'] ?? null;
        $company->owner_name = $validated['owner_name'] ?? null;
        $company->pan_number = $validated['pan_number'] ?? null;
        $company->hsn_code = $validated['hsn_code'] ?? null;
        $company->status = 'active';

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('companies/logos', 'uploads');
            $company->logo = $path;
        }

        if ($request->hasFile('digital_signature')) {
            $path = $request->file('digital_signature')->store('companies/signatures', 'uploads');
            $company->digital_signature = $path;
        }

        $company->save();

        ActivityLog::log('company_created', "Created company: {$company->name}", $company);

        return redirect()->route('admin.companies.index')->with('success', 'Company created successfully.');
    }

    public function show(Company $company)
    {
        $company->loadCount('branches', 'users');
        $branches = Branch::where('company_id', $company->id)->withCount('users')->get();
        $users = User::where('company_id', $company->id)->active()->get();

        return view('admin.companies.show', compact('company', 'branches', 'users'));
    }

    public function edit(Company $company)
    {
        return view('admin.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:companies,name,' . $company->id,
            'email' => 'nullable|email|unique:companies,email,' . $company->id,
            'phone' => 'nullable|digits_between:10,15|unique:companies,phone,' . $company->id,
            'address' => 'nullable|string',
            'state' => 'nullable|string|max:100',
            'disclaimer' => 'nullable|string',
            'declaration' => 'nullable|string',
            'gst_number' => 'nullable|string|max:50',
            'logo' => 'nullable|image|max:2048',
            'bank_holder_name' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_no' => 'nullable|string|max:50',
            'bank_ifsc' => 'nullable|string|max:50',
            'bank_branch' => 'nullable|string|max:255',
            'digital_signature' => 'nullable|image|max:2048',
            'owner_name' => 'nullable|string|max:255',
            'pan_number' => 'nullable|string|max:50',
            'hsn_code' => 'nullable|string|max:50',
        ]);

        $company->name = $validated['name'];
        $company->email = $validated['email'] ?? null;
        $company->phone = $validated['phone'] ?? null;
        $company->address = $validated['address'] ?? null;
        $company->state = $validated['state'] ?? null;
        $company->disclaimer = $validated['disclaimer'] ?? null;
        $company->declaration = $validated['declaration'] ?? null;
        $company->gst_number = $validated['gst_number'] ?? null;
        $company->bank_holder_name = $validated['bank_holder_name'] ?? null;
        $company->bank_name = $validated['bank_name'] ?? null;
        $company->bank_account_no = $validated['bank_account_no'] ?? null;
        $company->bank_ifsc = $validated['bank_ifsc'] ?? null;
        $company->bank_branch = $validated['bank_branch'] ?? null;
        $company->owner_name = $validated['owner_name'] ?? null;
        $company->pan_number = $validated['pan_number'] ?? null;
        $company->hsn_code = $validated['hsn_code'] ?? null;

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('companies/logos', 'uploads');
            $company->logo = $path;
        }

        if ($request->hasFile('digital_signature')) {
            $path = $request->file('digital_signature')->store('companies/signatures', 'uploads');
            $company->digital_signature = $path;
        }

        $company->save();

        ActivityLog::log('company_updated', "Updated company: {$company->name}", $company);

        return redirect()->route('admin.companies.index')->with('success', 'Company updated successfully.');
    }

    public function trashed()
    {
        $companies = Company::onlyTrashed()->withCount('branches', 'users')->paginate(15);
        return view('admin.companies.trashed', compact('companies'));
    }

    public function restore($id)
    {
        $company = Company::withTrashed()->findOrFail($id);
        $company->restore();
        ActivityLog::log('company_restored', "Restored company: {$company->name}", $company);
        return back()->with('success', 'Company restored successfully.');
    }

    public function forceDelete($id)
    {
        $company = Company::withTrashed()->findOrFail($id);

        $tablesToClean = [
            'users' => 'company_id',
            'branches' => 'company_id',
            'leads' => 'company_id',
            'activity_logs' => 'company_id',
        ];

        foreach ($tablesToClean as $table => $column) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                DB::table($table)->where($column, $company->id)->delete();
            }
        }

        $company->forceDelete();
        ActivityLog::log('company_force_deleted', "Force deleted company: {$company->name}");
        return back()->with('success', 'Company permanently deleted.');
    }

    public function destroy(Company $company)
    {
        $company->delete();

        ActivityLog::log('company_deleted', "Moved to trash: {$company->name}");

        return back()->with('success', 'Company moved to trash.');
    }

    public function toggleStatus(Company $company)
    {
        $company->status = $company->status === 'active' ? 'inactive' : 'active';
        $company->save();

        ActivityLog::log('company_status_changed', "Changed status of {$company->name} to {$company->status}", $company);

        return back()->with('success', 'Company status updated.');
    }

    public function getBranches(Company $company)
    {
        $branches = Branch::where('company_id', $company->id)->withCount('users')->get();
        return response()->json(['data' => $branches]);
    }
}
