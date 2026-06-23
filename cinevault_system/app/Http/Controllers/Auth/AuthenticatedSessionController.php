<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();

        AuditLog::write('LOGIN', "{$user->name} logged in.", $user->id);

        // Redirect based on role
        return match($user->role) {
            'admin' => redirect()->intended(route('admin.dashboard')),
            'staff' => redirect()->intended(route('staff.dashboard')),
            default => redirect()->intended(route('user.dashboard')),
        };
    }

    public function destroy(Request $request): RedirectResponse
    {
        AuditLog::write('LOGOUT', auth()->user()->name . " logged out.", auth()->id());

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
