<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;
use Validator, Exception;

class StoreController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view stores')->only(['index', 'show']);
        $this->middleware('permission:create stores')->only(['create', 'store']);
        $this->middleware('permission:edit stores')->only(['edit', 'update', 'toggleStatus']);
        $this->middleware('permission:delete stores')->only('destroy');
    }

    public function index()
    {
        $stores = Store::orderBy('id')->paginate(10);
        return view('admin.stores.index', compact('stores'));
    }

    public function create()
    {
        return view('admin.stores.create');
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'store_name' => 'required',
                'phone' => 'nullable',
                'email' => 'nullable|email',
                'address' => 'nullable',
            ]);

            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
                return redirect()->back()->withInput()->withErrors($validator->errors());
            }

            $data = $request->all();
            $data['status'] = in_array($request->status, ['active', '1', 1], true) ? 1 : (empty($request->status) ? 1 : 0);
            Store::create($data);
            logActivity('Created', 'Store', "Created store {$request->store_name}");
            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Store created successfully']);
            }
            return redirect()->route('admin.stores.index')->with('success', 'Store created successfully');
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $store = Store::findOrFail($id);
        if (request()->ajax()) {
            return response()->json($store);
        }
        return view('admin.stores.edit', compact('store'));
    }

    public function update(Request $request, $id)
    {
        try {
            $store = Store::findOrFail($id);
            $validator = Validator::make($request->all(), [
                'store_name' => 'required',
                'phone' => 'nullable',
                'email' => 'nullable|email',
                'address' => 'nullable',
            ]);

            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
                return redirect()->back()->withInput()->withErrors($validator->errors());
            }

            $data = $request->all();
            if ($request->has('status')) {
                $data['status'] = in_array($request->status, ['active', '1', 1], true) ? 1 : 0;
            }
            $store->update($data);
            logActivity('Updated', 'Store', "Updated store {$store->store_name}");
            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Store updated successfully']);
            }
            return redirect()->route('admin.stores.index')->with('success', 'Store updated successfully');
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        try {
            $store = Store::findOrFail($id);
            $store->status = !$store->status;
            $store->save();
            logActivity('Updated', 'Store', "Toggled status of store {$store->store_name}");
            return response()->json(['success' => true, 'status' => $store->status, 'message' => 'Status updated successfully']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $store = Store::findOrFail($id);
            $store->delete();
            logActivity('Deleted', 'Store', "Deleted store {$store->store_name}");
            return redirect()->route('admin.stores.index')->with('success', 'Store deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
