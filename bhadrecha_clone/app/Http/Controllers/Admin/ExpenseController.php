<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Company;
use App\Models\Branch;
use Illuminate\Support\Facades\Validator;
use Exception;

class ExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view expenses')->only(['index', 'show']);
        $this->middleware('permission:create expenses')->only(['create', 'store']);
        $this->middleware('permission:edit expenses')->only(['edit', 'update']);
        $this->middleware('permission:delete expenses')->only('destroy');
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $userCompanyId = $user->company_id;
        $userBranchId = $user->branch_id;

        $canViewAllStores = $user->hasRole('Admin')
            || $user->hasRole('Super Admin')
            || in_array(strtolower($user->role ?? ''), ['admin', 'super_admin'])
            || $user->can('view companies');

        $canViewAllBranches = $user->hasRole('Admin')
            || $user->hasRole('Super Admin')
            || in_array(strtolower($user->role ?? ''), ['admin', 'super_admin'])
            || $user->can('view branches');

        $selectedStoreId = ($userCompanyId && !$canViewAllStores) ? $userCompanyId : $request->get('store_id');
        $selectedBranchId = ($userBranchId && !$canViewAllBranches) ? $userBranchId : $request->get('branch_id');

        $expenses = Expense::with(['category', 'company', 'branch'])
            ->when($selectedStoreId, fn($q) => $q->where(function($sq) use ($selectedStoreId) {
                $sq->where('company_id', $selectedStoreId)->orWhere('store_id', $selectedStoreId);
            }))
            ->when($selectedBranchId, fn($q) => $q->where('branch_id', $selectedBranchId))
            ->when($request->filled('category_id'), fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('date', '<=', $request->date_to))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = ExpenseCategory::where('status', true)->orderBy('name')->get();
        // Stores are mapped to Companies in this ERP
        $stores = Company::where('status', 'active')->orderBy('name')->get();
        $branches = Branch::where('status', 'active')->orderBy('name')->get();

        return view('admin.expenses.index', compact('expenses', 'categories', 'stores', 'branches', 'selectedStoreId', 'selectedBranchId', 'canViewAllStores', 'canViewAllBranches'));
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'category_id' => 'required|exists:expense_categories,id',
                'amount' => 'required|numeric|min:0',
                'date' => 'required|date',
                'store_id' => 'nullable',
                'branch_id' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $request->all();
            $companyId = $request->store_id ?? $request->company_id ?? auth()->user()->company_id;
            $data['company_id'] = $companyId;
            $data['store_id'] = $companyId;
            $data['branch_id'] = $request->branch_id ?? auth()->user()->branch_id;

            if ($request->file('attachment')) {
                $file = $request->file('attachment');
                $filename = time() . '_' . $file->getClientOriginalName();
                $folder = 'uploads/expenses/';
                if (!file_exists(public_path($folder))) {
                    mkdir(public_path($folder), 0777, true);
                }
                $file->move(public_path($folder), $filename);
                $data['attachment'] = $folder . $filename;
            }

            Expense::create($data);
            if (function_exists('logActivity')) {
                logActivity('Created', 'Expense', "Created expense of {$request->amount}");
            }
            return response()->json(['success' => true, 'message' => 'Expense created successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        return response()->json(Expense::with(['company', 'branch', 'category'])->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        try {
            $expense = Expense::findOrFail($id);
            $validator = Validator::make($request->all(), [
                'category_id' => 'required|exists:expense_categories,id',
                'amount' => 'required|numeric|min:0',
                'date' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $request->all();
            if ($request->filled('store_id')) {
                $data['company_id'] = $request->store_id;
                $data['store_id'] = $request->store_id;
            }

            if ($request->file('attachment')) {
                $file = $request->file('attachment');
                $filename = time() . '_' . $file->getClientOriginalName();
                $folder = 'uploads/expenses/';
                if (!file_exists(public_path($folder))) {
                    mkdir(public_path($folder), 0777, true);
                }
                $file->move(public_path($folder), $filename);
                $data['attachment'] = $folder . $filename;
            }

            $expense->update($data);
            return response()->json(['success' => true, 'message' => 'Expense updated successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            Expense::findOrFail($id)->delete();
            return response()->json(['success' => true, 'message' => 'Expense deleted successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
