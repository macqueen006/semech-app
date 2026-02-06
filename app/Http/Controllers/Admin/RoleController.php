<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{

public function index(Request $request)
{
    if ($request->ajax()) {
        $query = Role::withCount('users')->orderBy('id', $request->order ?? 'desc');

        if ($request->search) {
            $keywords = explode(' ', $request->search);
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('name', 'like', '%' . $keyword . '%');
                }
            });
        }

        $roles = $request->limit == 0
            ? $query->get()
            : $query->paginate($request->limit ?? 20);

        return response()->json($roles);
    }

    return view('admin.roles.index');
}

public function create()
{
    $permissions = Permission::all()->groupBy(function ($permission) {
        return explode('-', $permission->name)[0];
    });

    return view('admin.roles.create', compact('permissions'));
}

public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|unique:roles,name',
        'permissions' => 'required|array|min:1',
    ]);

    $role = Role::create(['name' => $validated['name']]);

    $permissionNames = Permission::whereIn('id', $validated['permissions'])
        ->pluck('name')
        ->toArray();

    $role->syncPermissions($permissionNames);

    activity('roles')
        ->performedOn($role)
        ->causedBy(auth()->user())
        ->withProperties([
            'role_name' => $role->name,
            'permissions' => $permissionNames,
            'permissions_count' => count($permissionNames),
        ])
        ->log('created');

    return response()->json([
        'success' => true,
        'message' => 'Role created successfully!',
        'redirect' => route('admin.roles.index')
    ]);
}

public function show($id)
{
    $role = Role::findOrFail($id);

    $permissions = $role->permissions;
    $grouped = [];

    foreach ($permissions as $permission) {
        $group = explode('-', $permission->name)[0];
        if (!isset($grouped[$group])) {
            $grouped[$group] = [];
        }
        $grouped[$group][] = $permission->name;
    }

    return view('admin.roles.show', compact('role', 'grouped'));
}

public function edit($id)
{
    $role = Role::findOrFail($id);

    if ($role->name == 'Admin') {
        abort(403, 'Cannot edit Admin role.');
    }

    if (auth()->user()->roles->isNotEmpty() && $role->id == auth()->user()->roles[0]->id) {
        abort(403, 'Cannot edit your own role.');
    }

    $permissions = Permission::all()->groupBy(function ($permission) {
        return explode('-', $permission->name)[0];
    });

    $selectedPermissions = DB::table('role_has_permissions')
        ->where('role_id', $id)
        ->pluck('permission_id')
        ->toArray();

    return view('admin.roles.edit', compact('role', 'permissions', 'selectedPermissions'));
}

public function update(Request $request, $id)
{
    $role = Role::findOrFail($id);

    if ($role->name == 'Admin') {
        abort(403);
    }

    if (!auth()->user()->hasRole('Admin') &&
        auth()->user()->roles->isNotEmpty() &&
        $role->id == auth()->user()->roles[0]->id) {
        abort(403);
    }

    $validated = $request->validate([
        'name' => 'required',
        'permissions' => 'required|array|min:1',
    ]);

    $originalName = $role->name;
    $originalPermissions = $role->permissions->pluck('name')->toArray();

    if ($request->name !== $originalName) {
        $role->name = $validated['name'];
        $role->save();

        activity('roles')
            ->performedOn($role)
            ->causedBy(auth()->user())
            ->withProperties([
                'old_name' => $originalName,
                'new_name' => $request->name,
            ])
            ->log('name changed');
    }

    $permissionNames = Permission::whereIn('id', $validated['permissions'])
        ->pluck('name')
        ->toArray();

    $addedPermissions = array_diff($permissionNames, $originalPermissions);
    $removedPermissions = array_diff($originalPermissions, $permissionNames);

    if (!empty($addedPermissions) || !empty($removedPermissions)) {
        $role->syncPermissions($permissionNames);

        activity('roles')
            ->performedOn($role)
            ->causedBy(auth()->user())
            ->withProperties([
                'role_name' => $role->name,
                'old_permissions' => $originalPermissions,
                'new_permissions' => $permissionNames,
                'added_permissions' => array_values($addedPermissions),
                'removed_permissions' => array_values($removedPermissions),
                'total_permissions' => count($permissionNames),
            ])
            ->log('permissions updated');
    }

    return response()->json([
        'success' => true,
        'message' => 'Role updated successfully!',
        'redirect' => route('admin.roles.index')
    ]);
}

public function destroy($id)
{
    $role = Role::findOrFail($id);

    if ($role->name == 'Admin') {
        return response()->json([
            'success' => false,
            'message' => 'Cannot delete Admin role.'
        ], 403);
    }

    if (auth()->user()->roles->isNotEmpty() && auth()->user()->roles[0]->name == $role->name) {
        return response()->json([
            'success' => false,
            'message' => 'Cannot delete your own role.'
        ], 403);
    }

    $roleName = $role->name;
    $usersCount = $role->users()->count();
    $permissionsCount = $role->permissions()->count();

    activity('roles')
        ->performedOn($role)
        ->causedBy(auth()->user())
        ->withProperties([
            'role_name' => $roleName,
            'users_count' => $usersCount,
            'permissions_count' => $permissionsCount,
        ])
        ->log('deleted');

    $role->delete();

    return response()->json([
        'success' => true,
        'message' => 'Role deleted successfully!'
    ]);
}
}
