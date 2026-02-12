<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // Mostrar el formulario de login
    public function showLogin()
    {
        return view('welcome'); // tu vista de login
    }

    // Procesar login
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            return redirect()->intended('/pedidos'); // redirige a pedidos si login correcto
        }

        return back()->withErrors(['email' => 'Credenciales incorrectas']);
    }

    // Logout
    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }
}
