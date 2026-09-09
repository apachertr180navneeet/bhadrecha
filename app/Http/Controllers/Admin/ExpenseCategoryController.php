<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExpenseCategory;
use Validator, Exception;

class ExpenseCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view expense categories')->only(['index', 'show']);
        $this->middleware('permission:create expense categories')->only(['create', 'store']);
        $this->middleware('permission:edit expense categories')->only(['edit', 'update', 'toggleStatus']);
        $this->middleware('permission:delete expense categories')->only('destroy');
    }

    public function index()
    {
        $categories = ExpenseCategory::withCount('expenses')->latest()->paginate(10);
        return view('admin.expense-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:expense_categories,name',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $request->all();
            $data['status'] = in_array($request->status, ['active', '1', 1], true) ? 1 : 0;
            $category = ExpenseCategory::create($data);
            return response()->json(['success' => true, 'message' => 'Category created successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        return response()->json(ExpenseCategory::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        try {
            $category = ExpenseCategory::findOrFail($id);
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:expense_categories,name,' . $id,
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $request->all();
            if ($request->has('status')) {
                $data['status'] = in_array($request->status, ['active', '1', 1], true) ? 1 : 0;
            }
            $category->update($data);
            return response()->json(['success' => true, 'message' => 'Category updated successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            ExpenseCategory::findOrFail($id)->delete();
            return response()->json(['success' => true, 'message' => 'Category deleted successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
