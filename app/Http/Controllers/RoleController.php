<?php

namespace App\Http\Controllers;

use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    protected AuditTrailService $auditTrail;

    public function __construct(AuditTrailService $auditTrail)
    {
        $this->auditTrail = $auditTrail;
    }

    public function index(Request $request)
    {
        $query = Role::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $roles = $query
            ->withCount('users')
            ->withCount('permissions')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = $this->manageablePermissions();

        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $permissions = $this->sanitizePermissions($validated['permissions'] ?? []);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($permissions);

        $this->auditTrail->role(
            'create',
            'Menambahkan role ' . $role->name,
            [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'permissions' => $permissions,
            ]
        );

        return redirect()->route('roles.index')->with('success', 'Role berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $role = Role::with(['permissions', 'users'])->findOrFail($id);

        return view('roles.show', compact('role'));
    }

    public function edit(string $id)
    {
        $role = Role::with('permissions')->findOrFail($id);

        if ($role->name === 'Super Admin' && !Auth::user()->can('permission.view')) {
            abort(403, 'Role Super Admin hanya dapat dikelola oleh Super Admin.');
        }

        $permissions = $this->manageablePermissions();

        return view('roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, string $id)
    {
        $role = Role::findOrFail($id);

        if ($role->name === 'Super Admin' && !Auth::user()->can('permission.view')) {
            abort(403, 'Role Super Admin hanya dapat dikelola oleh Super Admin.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name,' . $role->id],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $permissions = $this->sanitizePermissions($validated['permissions'] ?? []);

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($permissions);

        $this->auditTrail->role(
            'update',
            'Mengubah role ' . $role->name,
            [
                'role_id' => $role->id,
                'permissions' => $permissions,
            ]
        );

        return redirect()->route('roles.index')->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $role = Role::with('users')->findOrFail($id);

        if ($role->name === 'Super Admin') {
            return back()->with('error', 'Role Super Admin tidak boleh dihapus.');
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', 'Role masih digunakan oleh user.');
        }

        $this->auditTrail->role(
            'delete',
            'Menghapus role ' . $role->name,
            ['role_id' => $role->id]
        );

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role berhasil dihapus.');
    }

    private function manageablePermissions()
    {
        $permissions = Permission::orderBy('name')->get();

        if (!Auth::user()->can('permission.view')) {
            $permissions = $permissions->reject(function ($permission) {
                return str_starts_with($permission->name, 'permission.');
            });
        }

        return $permissions->values();
    }

    private function sanitizePermissions(array $permissions): array
    {
        if (Auth::user()->can('permission.view')) {
            return array_values(array_unique($permissions));
        }

        return array_values(array_unique(array_filter($permissions, function ($permission) {
            return !str_starts_with($permission, 'permission.');
        })));
    }
}
