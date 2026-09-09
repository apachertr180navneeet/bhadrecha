<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Branch;
use App\Models\Store;
use Validator, Exception;

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
        $userStoreId = $user->store_id;
        $userBranchId = $user->branch_id;

        $canViewAllStores = $user->hasRole('Admin')
            || $user->hasRole('Super Admin')
            || in_array(strtolower($user->role ?? ''), ['admin', 'super_admin'])
            || $user->can('view stores');

        $canViewAllBranches = $user->hasRole('Admin')
            || $user->hasRole('Super Admin')
            || in_array(strtolower($user->role ?? ''), ['admin', 'super_admin'])
            || $user->can('view branches');

        $selectedStoreId = ($userStoreId && !$canViewAllStores) ? $userStoreId : $request->get('store_id');
        $selectedBranchId = ($userBranchId && !$canViewAllBranches) ? $userBranchId : $request->get('branch_id');

        $expenses = Expense::with(['category', 'store', 'branch'])
            ->when($selectedStoreId, fn($q) => $q->where('store_id', $selectedStoreId))
            ->when($selectedBranchId, fn($q) => $q->where('branch_id', $selectedBranchId))
            ->when($request->filled('category_id'), fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('date', '<=', $request->date_to))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = ExpenseCategory::where('status', true)->get();
        $stores = Store::all();
        $branches = Branch::all();

        return view('admin.expenses.index', compact('expenses', 'categories', 'stores', 'branches', 'selectedStoreId', 'selectedBranchId', 'canViewAllStores', 'canViewAllBranches'));
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'category_id' => 'required|exists:expense_categories,id',
                'amount' => 'required|numeric|min:0',
                'date' => 'required|date',
                'store_id' => 'required|exists:stores,id',
                'branch_id' => 'required|exists:branches,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $request->all();
            $data['store_id'] = $request->store_id ?? auth()->user()->store_id;
            $data['branch_id'] = $request->branch_id ?? auth()->user()->branch_id;

            if ($request->file('attachment')) {
                $file = $request->file('attachment');
                $filename = time() . '_' . $file->getClientOriginalName();
                $folder = 'uploads/expenses/';
                $file->move(public_path($folder), $filename);
                $data['attachment'] = $folder . $filename;
            }

            Expense::create($data);
            logActivity('Created', 'Expense', "Created expense of {$request->amount}");
            return response()->json(['success' => true, 'message' => 'Expense created successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        return response()->json(Expense::with(['store', 'branch'])->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        try {
            $expense = Expense::findOrFail($id);
            $data = $request->all();

            if ($request->file('attachment')) {
                $file = $request->file('attachment');
                $filename = time() . '_' . $file->getClientOriginalName();
                $folder = 'uploads/expenses/';
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
