<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('pages.auth.login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('login', 'password');
        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            if (Auth::user()->role === 'student') {
                return redirect()->route('student.home');
            }

            if (Auth::user()->role === 'admin') {
                return redirect()->route('admin.dashboard.index');
            }

            return redirect()->route('loginForm');
        }

        return back()
            ->withErrors([
                'login' => 'Login yoki parol noto‘g‘ri!',
            ])
            ->onlyInput('login');
    }


    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('loginForm');
    }
}
