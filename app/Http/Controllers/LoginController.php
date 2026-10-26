<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // Mostra o formulário de login
    public function showLoginForm()
    {
        return view('login');
    }

    // Valida email/senha e faz o login
    public function login(Request $request)
    {
        $credenciais = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credenciais, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->route('vagas')->with('sucesso', 'Login realizado com sucesso!');
        }

        return back()->withErrors([
            'email' => 'Email ou senha incorretos.',
        ])->onlyInput('email');
    }

    // Desloga o usuário
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home')->with('sucesso', 'Você saiu da sua conta.');
    }
}
