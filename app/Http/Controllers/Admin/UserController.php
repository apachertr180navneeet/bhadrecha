<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use App\Models\Branch;
use App\Models\ActivityLog;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['company', 'branch', 'roles'])
            ->whereDoesntHave('roles', function($q) {
                $q->where('name', 'Super Admin');
            });

        if (auth()->user()->isSuperAdmin()) {
            if ($request->filled('company_id')) {
                $query->where('company_id', $request->company_id);
            }
        } else {
            $query->where('company_id', auth()->user()->company_id);
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);
        $companies = Company::where('status', 'active')->get();
        $roles = Role::all();
        $allRoles = Role::pluck('name')->toArray();

        return view('admin.users.index', compact('users', 'companies', 'roles', 'allRoles'));
    }

    public function create()
    {
        $companies = Company::where('status', 'active')->orderBy('name')->get();
        $branches = [];
        $roles = Role::all();
        $states = City::distinct()->where('status', 'active')->orderBy('state')->pluck('state')->mapWithKeys(fn($s) => [$s => true])->toArray();
        return view('admin.users.create', compact('companies', 'branches', 'roles', 'states'));
    }

    public function getBranchesByCompany(Request $request, $companyId = null)
    {
        $companyId = $companyId ?? $request->input('company_id');
        $branches = Branch::where('company_id', $companyId)->where('status', 'active')->get();
        return response()->json($branches);
    }

    public function getCitiesByState(Request $request, $state = null)
    {
        $state = $state ?? $request->input('state');
        $cities = City::where('state', $state)->where('status', 'active')->orderBy('name')->pluck('name');
        return response()->json($cities);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|min:6|confirmed',
            'company_id' => 'nullable|exists:companies,id',
            'branch_id' => 'nullable|exists:branches,id',
            'role' => 'required|exists:roles,name',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
        ]);

        // Set default status for new users
        $validated['status'] = 'active';

        $user = new User();
        $user->first_name = $validated['first_name'];
        $user->last_name = $validated['last_name'];
        $user->full_name = $validated['first_name'] . ' ' . $validated['last_name'];
        $user->slug = strtolower(str_replace(' ', '-', $validated['first_name']) . '-' . time());
        $user->email = $validated['email'];
        $user->phone = $validated['phone'];
        $user->password = Hash::make($validated['password']);
        $user->company_id = $validated['company_id'] ?? null;
        $user->branch_id = $validated['branch_id'] ?? null;
        $user->status = $validated['status'];
        $user->address = $validated['address'] ?? null;
        $user->city = $validated['city'] ?? null;
        $user->state = $validated['state'] ?? null;
        $user->role = in_array($validated['role'], ['admin', 'user']) ? $validated['role'] : 'user';

        $user->save();
        $user->assignRole($validated['role']);

        ActivityLog::log('user_created', "Created user: {$user->full_name}", $user);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        $user->load(['company', 'branch', 'roles']);
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $companies = Company::where('status', 'active')->orderBy('name')->get();
        $branches = Branch::where('company_id', $user->company_id)->get();
        $roles = Role::all();
        $states = City::distinct()->where('status', 'active')->orderBy('state')->pluck('state')->mapWithKeys(fn($s) => [$s => true])->toArray();
        return view('admin.users.edit', compact('user', 'companies', 'branches', 'roles', 'states'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|unique:users,phone,' . $user->id,
            'password' => 'nullable|min:6|confirmed',
            'company_id' => 'nullable|exists:companies,id',
            'branch_id' => 'nullable|exists:branches,id',
            'role' => 'required|exists:roles,name',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
        ]);

        $user->first_name = $validated['first_name'];
        $user->last_name = $validated['last_name'];
        $user->full_name = $validated['first_name'] . ' ' . $validated['last_name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->company_id = $validated['company_id'] ?? null;
        $user->branch_id = $validated['branch_id'] ?? null;
        $user->address = $validated['address'] ?? null;
        $user->city = $validated['city'] ?? null;
        $user->state = $validated['state'] ?? null;

        $user->save();

        $user->syncRoles([$validated['role']]);

        ActivityLog::log('user_updated', "Updated user: {$user->full_name}", $user);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->syncRoles([]);
        $user->delete();

        ActivityLog::log('user_deleted', "Deleted user: {$user->full_name}");

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        ActivityLog::log('user_status_changed', "Changed status of {$user->full_name} to {$user->status}", $user);

        return back()->with('success', 'User status updated.');
    }
}
