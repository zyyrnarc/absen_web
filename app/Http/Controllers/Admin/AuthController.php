<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Tampilkan halaman login
    public function showLogin(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return view('auth.login');
    }

    // Tampilkan halaman konfirmasi logout
    public function showLogout()
    {
        return view('auth.logout');
    }

    // Proses login
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        $credentials = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'role' => 'admin',
            'is_active' => true,
        ];

        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();
            $request->session()->put('admin_last_activity', time());
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->withInput();
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->forget('admin_last_activity');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
