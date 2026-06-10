<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
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
        \Illuminate\Support\Facades\Log::info('Login attempt for email: ' . $request->email);

        try {
            $request->authenticate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('Login failed for email: ' . $request->email . ' - ' . $e->getMessage());
            throw $e;
        }

        $request->session()->regenerate();

        \Illuminate\Support\Facades\Log::info('Login successful for email: ' . $request->email);

        return redirect()->intended(route('profile.edit', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
