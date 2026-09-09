<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;
use App\Models\Store;
use Validator, Exception, File;

class StaffController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view staffs')->only(['index', 'show']);
        $this->middleware('permission:create staffs')->only(['create', 'store']);
        $this->middleware('permission:edit staffs')->only(['edit', 'update', 'toggleStatus']);
        $this->middleware('permission:delete staffs')->only('destroy');
    }

    public function index()
    {
        $storeId = auth()->user()->store_id;
        $isAdmin = auth()->user()->hasRole('Admin');

        $staffs = Staff::with('store')
            ->when($storeId && !$isAdmin, fn($q) => $q->where('store_id', $storeId))
            ->latest()->paginate(10);

        return view('admin.staffs.index', compact('staffs'));
    }

    public function create()
    {
        $stores = Store::where('status', true)->get();
        return view('admin.staffs.create', compact('stores'));
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'store_id' => 'required|exists:stores,id',
                'first_name' => 'required',
                'last_name' => 'nullable',
                'email' => 'nullable|email|unique:staffs,email',
                'phone' => 'nullable|unique:staffs,phone',
                'salary' => 'nullable|numeric',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->errors());
            }

            $data = $request->all();
            $data['store_id'] = $request->store_id ?? auth()->user()->store_id;
            $data['status'] = true;

            if ($request->file('id_proof')) {
                $file = $request->file('id_proof');
                $filename = time() . '_id_' . $file->getClientOriginalName();
                $file->move(public_path('uploads'), $filename);
                $data['id_proof'] = asset('uploads/' . $filename);
            }

            if ($request->file('qualification_doc')) {
                $file = $request->file('qualification_doc');
                $filename = time() . '_qual_' . $file->getClientOriginalName();
                $file->move(public_path('uploads'), $filename);
                $data['qualification_doc'] = asset('uploads/' . $filename);
            }

            $staff = Staff::create($data);
            logActivity('Created', 'Staff', "Created staff {$staff->full_name}");
            return redirect()->route('admin.staffs.index')->with('success', 'Staff created successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $staff = Staff::findOrFail($id);
        $stores = Store::where('status', true)->get();
        return view('admin.staffs.edit', compact('staff', 'stores'));
    }

    public function update(Request $request, $id)
    {
        try {
            $staff = Staff::findOrFail($id);
            $validator = Validator::make($request->all(), [
                'store_id' => 'required|exists:stores,id',
                'first_name' => 'required',
                'last_name' => 'nullable',
                'email' => 'nullable|email|unique:staffs,email,' . $id,
                'phone' => 'nullable|unique:staffs,phone,' . $id,
                'salary' => 'nullable|numeric',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->errors());
            }

            $data = $request->all();
            $data['status'] = $staff->status;

            if ($request->file('id_proof')) {
                if ($staff->id_proof && File::exists(public_path(str_replace(url('/'), '', $staff->id_proof)))) {
                    File::delete(public_path(str_replace(url('/'), '', $staff->id_proof)));
                }
                $file = $request->file('id_proof');
                $filename = time() . '_id_' . $file->getClientOriginalName();
                $file->move(public_path('uploads'), $filename);
                $data['id_proof'] = asset('uploads/' . $filename);
            }

            if ($request->file('qualification_doc')) {
                if ($staff->qualification_doc && File::exists(public_path(str_replace(url('/'), '', $staff->qualification_doc)))) {
                    File::delete(public_path(str_replace(url('/'), '', $staff->qualification_doc)));
                }
                $file = $request->file('qualification_doc');
                $filename = time() . '_qual_' . $file->getClientOriginalName();
                $file->move(public_path('uploads'), $filename);
                $data['qualification_doc'] = asset('uploads/' . $filename);
            }

            $staff->update($data);
            logActivity('Updated', 'Staff', "Updated staff {$staff->full_name}");
            return redirect()->route('admin.staffs.index')->with('success', 'Staff updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        try {
            $staff = Staff::findOrFail($id);
            $staff->status = !$staff->status;
            $staff->save();
            logActivity('Updated', 'Staff', "Toggled status of staff {$staff->full_name}");
            return response()->json(['success' => true, 'status' => $staff->status, 'message' => 'Status updated successfully']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $staff = Staff::findOrFail($id);
            $staff->delete();
            logActivity('Deleted', 'Staff', "Deleted staff {$staff->full_name}");
            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Staff deleted successfully']);
            }
            return redirect()->route('admin.staffs.index')->with('success', 'Staff deleted successfully');
        } catch (Exception $e) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
