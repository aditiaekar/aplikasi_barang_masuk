<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Menampilkan halaman login.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Memproses login pengguna.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        $loginData = [
            'username' => $credentials['username'],
            'password' => $credentials['password'],
            'is_active' => true,
        ];

        if (! Auth::attempt($loginData, $remember)) {
            throw ValidationException::withMessages([
                'username' => 'Username atau password tidak sesuai.',
            ]);
        }

        $request->session()->regenerate();

        $request->user()->update([
            'last_login_at' => now(),
        ]);

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Memproses logout pengguna.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
