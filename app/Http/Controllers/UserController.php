<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    protected AuditTrailService $auditTrail;

    public function __construct(AuditTrailService $auditTrail)
    {
        $this->auditTrail = $auditTrail;
    }

    private function manageableRoles()
    {
        $roles = Role::with('permissions')->orderBy('name')->get();

        if (!Auth::user()->hasRole('Super Admin')) {
            $roles = $roles->reject(fn ($role) => $role->name === 'Super Admin');
        }

        return $roles->values();
    }

    private function validateManageableRole(string $role): void
    {
        if ($role === 'Super Admin' && !Auth::user()->hasRole('Super Admin')) {
            abort(403, 'Role Super Admin hanya dapat dikelola oleh Super Admin.');
        }
    }

    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $roles = $this->manageableRoles();

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = $this->manageableRoles();

        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'exists:roles,name'],
        ]);

        $this->validateManageableRole($validated['role']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        $this->auditTrail->user('create', 'Menambahkan user ' . $user->name, [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $validated['role'],
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $user = User::with('roles')->findOrFail($id);

        return view('users.show', compact('user'));
    }

    public function edit(string $id)
    {
        $user = User::with('roles')->findOrFail($id);

        if ($user->hasRole('Super Admin') && !Auth::user()->hasRole('Super Admin')) {
            abort(403, 'User Super Admin hanya dapat dikelola oleh Super Admin.');
        }

        $roles = $this->manageableRoles();

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        if ($user->hasRole('Super Admin') && !Auth::user()->hasRole('Super Admin')) {
            abort(403, 'User Super Admin hanya dapat dikelola oleh Super Admin.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'role' => ['required', 'exists:roles,name'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $this->validateManageableRole($validated['role']);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        $user->syncRoles([$validated['role']]);

        $this->auditTrail->user('update', 'Mengubah user ' . $user->name, [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $validated['role'],
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        if ($user->hasRole('Super Admin') && !Auth::user()->hasRole('Super Admin')) {
            abort(403, 'User Super Admin hanya dapat dikelola oleh Super Admin.');
        }

        if (auth()->id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun yang sedang digunakan.');
        }

        if ($user->hasRole('Super Admin') && User::role('Super Admin')->count() <= 1) {
            return back()->with('error', 'Minimal harus ada satu Super Admin.');
        }

        $this->auditTrail->user('delete', 'Menghapus user ' . $user->name, [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}
