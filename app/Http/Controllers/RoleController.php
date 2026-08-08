<?php

namespace App\Http\Controllers;

use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    protected AuditTrailService $auditTrail;

    public function __construct(
        AuditTrailService $auditTrail
    ) {
        $this->auditTrail = $auditTrail;
    }
        /**
     * Display a listing of roles.
     */
    public function index(Request $request)
    {
        $query = Role::query();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $query->where(
                'name',
                'like',
                '%' . $request->search . '%'
            );

        }

        $roles = $query
            ->withCount('users')
            ->withCount('permissions')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view(
            'roles.index',
            compact('roles')
        );
    }

        /**
     * Show create role form.
     */
    public function create()
    {
        $permissions = Permission::orderBy('name')
            ->get();

        return view(
            'roles.create',
            compact('permissions')
        );
    }

        /**
     * Store new role.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([

            'name' => [
                'required',
                'string',
                'max:100',
                'unique:roles,name',
            ],

            'permissions' => [
                'nullable',
                'array',
            ],

            'permissions.*' => [
                'exists:permissions,name',
            ],

        ]);

        $role = Role::create([

            'name' => $validated['name'],

            'guard_name' => 'web',

        ]);

        /*
        |--------------------------------------------------------------------------
        | Sync Permission
        |--------------------------------------------------------------------------
        */

        if (!empty($validated['permissions'])) {

            $role->syncPermissions(
                $validated['permissions']
            );

        }

        /*
        |--------------------------------------------------------------------------
        | Audit Trail
        |--------------------------------------------------------------------------
        */

        $this->auditTrail->role(

            'create',

            'Menambahkan role ' . $role->name,

            [

                'role_id' => $role->id,

                'role_name' => $role->name,

                'permissions' => $validated['permissions'] ?? [],

            ]

        );

        return redirect()

            ->route('roles.index')

            ->with(

                'success',

                'Role berhasil ditambahkan.'

            );
    }

        /**
     * Display role detail.
     */
    public function show(string $id)
    {
        $role = Role::with([
                'permissions',
                'users',
            ])
            ->findOrFail($id);

        return view(
            'roles.show',
            compact('role')
        );
    }

        /**
     * Show edit role form.
     */
    public function edit(string $id)
    {
        $role = Role::with('permissions')
            ->findOrFail($id);

        $permissions = Permission::orderBy('name')
            ->get();

        return view(
            'roles.edit',
            compact(
                'role',
                'permissions'
            )
        );
    }

    /**
 * Update role.
 */
public function update(Request $request, string $id)
{
    $role = Role::findOrFail($id);

    $validated = $request->validate([

        'name' => [
            'required',
            'string',
            'max:100',
            'unique:roles,name,' . $role->id,
        ],

        'permissions' => [
            'nullable',
            'array',
        ],

        'permissions.*' => [
            'exists:permissions,name',
        ],

    ]);

    /*
    |--------------------------------------------------------------------------
    | Update Role
    |--------------------------------------------------------------------------
    */

    $role->update([

        'name' => $validated['name'],

    ]);

    /*
    |--------------------------------------------------------------------------
    | Sync Permission
    |--------------------------------------------------------------------------
    */

    $role->syncPermissions(

        $validated['permissions'] ?? []

    );

    /*
    |--------------------------------------------------------------------------
    | Audit Trail
    |--------------------------------------------------------------------------
    */

    $this->auditTrail->role(

        'update',

        'Mengubah role ' . $role->name,

        [

            'role_id' => $role->id,

            'permissions' => $validated['permissions'] ?? [],

        ]

    );

    return redirect()

        ->route('roles.index')

        ->with(

            'success',

            'Role berhasil diperbarui.'

        );
}

    /**
 * Delete role.
 */
public function destroy(string $id)
{
    $role = Role::with('users')
        ->findOrFail($id);

    /*
    |--------------------------------------------------------------------------
    | Protect Super Admin
    |--------------------------------------------------------------------------
    */

    if ($role->name === 'Super Admin') {

        return back()->with(

            'error',

            'Role Super Admin tidak boleh dihapus.'

        );

    }

    /*
    |--------------------------------------------------------------------------
    | Role Masih Digunakan
    |--------------------------------------------------------------------------
    */

    if ($role->users()->count() > 0) {

        return back()->with(

            'error',

            'Role masih digunakan oleh user.'

        );

    }

    /*
    |--------------------------------------------------------------------------
    | Audit
    |--------------------------------------------------------------------------
    */

    $this->auditTrail->role(

        'delete',

        'Menghapus role ' . $role->name,

        [

            'role_id' => $role->id,

        ]

    );

    $role->delete();

    return redirect()

        ->route('roles.index')

        ->with(

            'success',

            'Role berhasil dihapus.'

        );
}


    /**
    * Kelompokkan permission berdasarkan prefix.
    */
    private function groupPermissions($permissions): array
    {
        return $permissions
            ->groupBy(function ($permission) {

                return ucfirst(
                    explode('.', $permission->name)[0]
                );

            })
            ->toArray();
    }
}
