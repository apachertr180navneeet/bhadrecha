<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Exception;

class ItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view items')->only(['index', 'show']);
        $this->middleware('permission:create items')->only(['create', 'store']);
        $this->middleware('permission:edit items')->only(['edit', 'update', 'toggleStatus']);
        $this->middleware('permission:delete items')->only('destroy');
    }

    public function index(Request $request)
    {
        $query = Item::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $items = $query->latest()->paginate(10);

        return view('admin.items.index', compact('items'));
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('items', 'name')->whereNull('deleted_at'),
                ],
            ]);

            if ($validator->fails()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
                return redirect()->back()->withInput()->withErrors($validator->errors());
            }

            $status = in_array((string)$request->status, ['1', 'active', 'true'], true) ? 1 : 0;

            $item = Item::create([
                'name' => trim($request->name),
                'status' => $status,
            ]);

            logActivity('Created', 'Item', "Created item {$item->name}");

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Item created successfully']);
            }
            return redirect()->route('admin.items.index')->with('success', 'Item created successfully');
        } catch (Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $item = Item::findOrFail($id);
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json($item);
        }
        return response()->json($item);
    }

    public function update(Request $request, $id)
    {
        try {
            $item = Item::findOrFail($id);
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('items', 'name')->ignore($id)->whereNull('deleted_at'),
                ],
            ]);

            if ($validator->fails()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
                return redirect()->back()->withInput()->withErrors($validator->errors());
            }

            $data = ['name' => trim($request->name)];
            if ($request->has('status')) {
                $data['status'] = in_array((string)$request->status, ['1', 'active', 'true'], true) ? 1 : 0;
            }

            $item->update($data);
            logActivity('Updated', 'Item', "Updated item {$item->name}");

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Item updated successfully']);
            }
            return redirect()->route('admin.items.index')->with('success', 'Item updated successfully');
        } catch (Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        try {
            $item = Item::findOrFail($id);
            $item->status = $item->status == 1 ? 0 : 1;
            $item->save();
            logActivity('Updated', 'Item', "Toggled status of item {$item->name}");
            return response()->json(['success' => true, 'status' => $item->status, 'message' => 'Status updated successfully']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $item = Item::findOrFail($id);
            $name = $item->name;
            $item->delete();
            logActivity('Deleted', 'Item', "Deleted item {$name}");

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Item deleted successfully']);
            }
            return redirect()->route('admin.items.index')->with('success', 'Item deleted successfully');
        } catch (Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
