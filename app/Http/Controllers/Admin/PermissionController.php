<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use DB;

class PermissionController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('view permissions') && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $permissions = Permission::orderBy('name')->paginate(20);
        $groupedPermissions = Permission::all()->groupBy(function($perm) {
            $parts = explode('-', $perm->name);
            return count($parts) > 1 ? $parts[1] : 'general';
        });
        return view('admin.permissions.index', compact('permissions', 'groupedPermissions'));
    }

    public function create()
    {
        return view('admin.permissions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name|max:255',
            'group' => 'nullable|string|max:255',
        ]);

        $permission = Permission::create(['name' => $validated['name']]);

        ActivityLog::log('permission_created', "Created permission: {$permission->name}", $permission);

        return redirect()->route('admin.permissions.index')->with('success', 'Permission created successfully.');
    }

    public function show(Permission $permission)
    {
        $permission->load('roles');
        return view('admin.permissions.show', compact('permission'));
    }

    public function edit(Permission $permission)
    {
        return view('admin.permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $permission->id . '|max:255',
        ]);

        $permission->update(['name' => $validated['name']]);

        ActivityLog::log('permission_updated', "Updated permission: {$permission->name}", $permission);

        return redirect()->route('admin.permissions.index')->with('success', 'Permission updated successfully.');
    }

    public function destroy(Permission $permission)
    {
        if ($permission->roles()->count() > 0) {
            return back()->with('error', 'Cannot delete permission assigned to roles.');
        }

        $permission->delete();

        ActivityLog::log('permission_deleted', "Deleted permission: {$permission->name}");

        return redirect()->route('admin.permissions.index')->with('success', 'Permission deleted successfully.');
    }

    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'required|string',
        ]);

        $created = 0;
        foreach ($validated['permissions'] as $permName) {
            Permission::firstOrCreate(['name' => $permName]);
            $created++;
        }

        ActivityLog::log('bulk_permissions_created', "Created {$created} permissions");

        return back()->with('success', "{$created} permissions created successfully.");
    }
}
