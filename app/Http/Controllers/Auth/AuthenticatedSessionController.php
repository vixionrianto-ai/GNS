<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditTrailService;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    protected AuditTrailService $auditTrail;

    public function __construct(AuditTrailService $auditTrail)
    {
        $this->auditTrail = $auditTrail;
    }
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        $this->auditTrail->login(
            'Login berhasil: ' . Auth::user()->email,
            [
                'user_id' => Auth::id(),
                'email'   => Auth::user()->email,
            ]
        );

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $this->auditTrail->logout(
            'Logout: ' . Auth::user()->email,
            [
                'user_id' => Auth::id(),
                'email'   => Auth::user()->email,
            ]
        );
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
