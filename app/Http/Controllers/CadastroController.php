<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\CnpjValido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CadastroController extends Controller
{
    // Mostra o formulário de cadastro
    public function showCadastroForm()
    {
        return view('cadastro');
    }

    // Salva o novo usuário no banco
    public function store(Request $request)
    {
        $regras = [
            'tipo' => 'required|in:pessoa,empresa',
            'name' => 'required|max:1000',
            'email' => 'required|email|max:60|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'cidade' => 'nullable|max:100',
            'estado' => 'nullable|max:60',
            'sigla' => 'nullable|max:2',
        ];

        // Só valida CNPJ se for empresa
        if ($request->tipo === 'empresa') {
            $regras['cnpj'] = ['required', 'max:18', new CnpjValido()];
        }

        $dados = $request->validate($regras, [
            'cnpj.required' => 'O CNPJ é obrigatório para empresas.',
            'password.confirmed' => 'As senhas não coincidem.',
            'password.min' => 'A senha deve ter pelo menos 6 caracteres.',
            'email.unique' => 'Este email já está cadastrado.',
        ]);

        if ($request->tipo === 'pessoa') {
            $dados['cnpj'] = null;
        }

        $user = User::create($dados);

        Auth::login($user);

        return redirect()->route('vagas')->with('sucesso', 'Cadastro realizado com sucesso!');
    }
}
