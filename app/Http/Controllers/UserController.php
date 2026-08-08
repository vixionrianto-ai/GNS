<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    protected AuditTrailService $auditTrail;

    public function __construct(
        AuditTrailService $auditTrail
    ) {
        $this->auditTrail = $auditTrail;
    }

        /**
         * Display a listing of users.
         */
        public function index(Request $request)
        {
            $query = User::query();

            /*
            |--------------------------------------------------------------------------
            | Search
            |--------------------------------------------------------------------------
            */

            if ($request->filled('search')) {

                $query->where(function ($q) use ($request) {

                    $q->where(
                        'name',
                        'like',
                        '%' . $request->search . '%'
                    )->orWhere(
                        'email',
                        'like',
                        '%' . $request->search . '%'
                    );

                });

            }

            /*
            |--------------------------------------------------------------------------
            | Filter Role
            |--------------------------------------------------------------------------
            */

            if ($request->filled('role')) {

                $query->role(
                    $request->role
                );

            }

            $users = $query
                ->latest()
                ->paginate(15)
                ->withQueryString();

            $roles = Role::orderBy('name')
                ->get();

            return view(

                'users.index',

                compact(

                    'users',

                    'roles'

                )

            );
        }

    /**
     * Show the form for creating a new resource.
     */
    
        /**
         * Show create user form.
         */
        public function create()
        {
            $roles = Role::orderBy('name')
                ->get();

            return view(
                'users.create',
                compact('roles')
            );
        }
    /**
     * Store a newly created resource in storage.
     */
        /**
         * Store new user.
         */
        public function store(Request $request)
        {
            $validated = $request->validate([

                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'email' => [
                    'required',
                    'email',
                    'unique:users,email',
                ],

                'password' => [
                    'required',
                    'confirmed',
                    Password::defaults(),
                ],

                'role' => [
                    'required',
                    'exists:roles,name',
                ],

            ]);

            $user = User::create([

                'name' => $validated['name'],

                'email' => $validated['email'],

                'password' => Hash::make(
                    $validated['password']
                ),

            ]);

            /*
            |--------------------------------------------------------------------------
            | Assign Role
            |--------------------------------------------------------------------------
            */

            $user->assignRole(
                $validated['role']
            );

            /*
            |--------------------------------------------------------------------------
            | Audit Trail
            |--------------------------------------------------------------------------
            */

            $this->auditTrail->user(

                'create',

                'Menambahkan user ' . $user->name,

                [

                    'user_id' => $user->id,

                    'email' => $user->email,

                    'role' => $validated['role'],

                ]

            );

            return redirect()

                ->route('users.index')

                ->with(

                    'success',

                    'User berhasil ditambahkan.'

                );
        }

        /**
         * Display user detail.
         */
        public function show(string $id)
        {
            $user = User::with('roles')
                ->findOrFail($id);

            return view(
                'users.show',
                compact('user')
            );
        }

        /**
         * Show edit user form.
         */
        public function edit(string $id)
        {
            $user = User::findOrFail($id);

            $roles = Role::orderBy('name')
                ->get();

            return view(
                'users.edit',
                compact(
                    'user',
                    'roles'
                )
            );
        }

        /**
         * Update user.
         */
        public function update(Request $request, string $id)
        {
            $user = User::findOrFail($id);

            $validated = $request->validate([

                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'email' => [
                    'required',
                    'email',
                    'unique:users,email,' . $user->id,
                ],

                'role' => [
                    'required',
                    'exists:roles,name',
                ],

                'password' => [
                    'nullable',
                    'confirmed',
                    Password::defaults(),
                ],

            ]);

            $user->name = $validated['name'];

            $user->email = $validated['email'];

            /*
            |--------------------------------------------------------------------------
            | Password
            |--------------------------------------------------------------------------
            */

            if (!empty($validated['password'])) {

                $user->password = Hash::make(
                    $validated['password']
                );

            }

            $user->save();

            /*
            |--------------------------------------------------------------------------
            | Sync Role
            |--------------------------------------------------------------------------
            */

            $user->syncRoles([
                $validated['role']
            ]);

            /*
            |--------------------------------------------------------------------------
            | Audit Trail
            |--------------------------------------------------------------------------
            */

            $this->auditTrail->user(

                'update',

                'Mengubah user ' . $user->name,

                [

                    'user_id' => $user->id,

                    'email' => $user->email,

                    'role' => $validated['role'],

                ]

            );

            return redirect()

                ->route('users.index')

                ->with(

                    'success',

                    'User berhasil diperbarui.'

                );
        }
        /**
         * Remove user.
         */
        public function destroy(string $id)
        {
            $user = User::findOrFail($id);

            /*
            |--------------------------------------------------------------------------
            | Tidak boleh menghapus akun sendiri
            |--------------------------------------------------------------------------
            */

            if (auth()->id() === $user->id) {

                return back()->with(

                    'error',

                    'Anda tidak dapat menghapus akun yang sedang digunakan.'

                );

            }

            /*
            |--------------------------------------------------------------------------
            | Super Admin terakhir tidak boleh dihapus
            |--------------------------------------------------------------------------
            */

            if (

                $user->hasRole('Super Admin')

                &&

                User::role('Super Admin')->count() <= 1

            ) {

                return back()->with(

                    'error',

                    'Minimal harus ada satu Super Admin.'

                );

            }

            /*
            |--------------------------------------------------------------------------
            | Audit Trail
            |--------------------------------------------------------------------------
            */

            $this->auditTrail->user(

                'delete',

                'Menghapus user ' . $user->name,

                [

                    'user_id' => $user->id,

                    'email' => $user->email,

                ]

            );

            $user->delete();

            return redirect()

                ->route('users.index')

                ->with(

                    'success',

                    'User berhasil dihapus.'

                );
        }
}
