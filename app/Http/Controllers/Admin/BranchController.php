<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\City;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $companies = Company::where('status', 'active')->get();
        $branches = Branch::with(['company', 'users'])
            ->withCount('users')
            ->orderBy('name')
            ->paginate(15);
        return view('admin.branches.index', compact('branches', 'companies'));
    }

    public function getAllBranches(Request $request)
    {
        $query = Branch::with('company')->withCount('users');

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $branches = $query->orderBy('created_at', 'desc')->get();

        return response()->json(['data' => $branches]);
    }

    public function create()
    {
        $companies = Company::where('status', 'active')->orderBy('name')->get();
        $states = City::distinct()->where('status', 'active')->orderBy('state')->pluck('state')->mapWithKeys(fn($s) => [$s => true])->toArray();
        $cities = City::where('status', 'active')->orderBy('state')->orderBy('name')->get()->groupBy('state')->map(fn($g) => $g->pluck('name')->toArray())->toArray();
        return view('admin.branches.create', compact('companies', 'states', 'cities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:branches,email',
            'phone' => 'nullable|digits_between:10,15|unique:branches,phone',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
        ]);

        $validated['status'] = 'active';
        $branch = Branch::create($validated);

        ActivityLog::log('branch_created', "Created branch: {$branch->name}", $branch);

        return redirect()->route('admin.branches.index')->with('success', 'Branch created successfully.');
    }

    public function show(Branch $branch)
    {
        $branch->load(['company', 'users']);
        $users = User::where('branch_id', $branch->id)->active()->get();

        return view('admin.branches.show', compact('branch', 'users'));
    }

    public function edit(Branch $branch)
    {
        $companies = Company::where('status', 'active')->orderBy('name')->get();
        $states = City::distinct()->where('status', 'active')->orderBy('state')->pluck('state')->mapWithKeys(fn($s) => [$s => true])->toArray();
        $cities = City::where('status', 'active')->orderBy('state')->orderBy('name')->get()->groupBy('state')->map(fn($g) => $g->pluck('name')->toArray())->toArray();
        return view('admin.branches.edit', compact('branch', 'companies', 'states', 'cities'));
    }

    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:branches,email,' . $branch->id,
            'phone' => 'nullable|digits_between:10,15|unique:branches,phone,' . $branch->id,
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
        ]);

        $branch->update($validated);

        ActivityLog::log('branch_updated', "Updated branch: {$branch->name}", $branch);

        return redirect()->route('admin.branches.index')->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch)
    {
        if ($branch->users()->count() > 0) {
            return back()->with('error', 'Cannot delete branch with assigned users.');
        }

        $branch->delete();

        ActivityLog::log('branch_deleted', "Deleted branch: {$branch->name}");

        return redirect()->route('admin.branches.index')->with('success', 'Branch deleted successfully.');
    }

    public function trashed()
    {
        $branches = Branch::onlyTrashed()->paginate(15);
        return view('admin.branches.trashed', compact('branches'));
    }

    public function restore($id)
    {
        $branch = Branch::onlyTrashed()->findOrFail($id);
        $branch->restore();

        return redirect()->route('admin.branches.trashed')->with('success', 'Branch restored successfully.');
    }

    public function forceDelete($id)
    {
        $branch = Branch::onlyTrashed()->findOrFail($id);

        if ($branch->users()->count() > 0) {
            return back()->with('error', 'Cannot permanently delete branch with assigned users.');
        }

        $branch->forceDelete();

        return redirect()->route('admin.branches.trashed')->with('success', 'Branch permanently deleted.');
    }

    public function toggleStatus(Branch $branch)
    {
        $branch->status = $branch->status === 'active' ? 'inactive' : 'active';
        $branch->save();

        ActivityLog::log('branch_status_changed', "Changed status of {$branch->name} to {$branch->status}", $branch);

        return back()->with('success', 'Branch status updated.');
    }
}
