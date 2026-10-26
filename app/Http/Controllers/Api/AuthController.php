<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiHelpers;
use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\User;
use App\Rules\CnpjValido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    use ApiHelpers;

    // POST /api/v1/auth/register  (mesmas regras do cadastro do site)
    public function register(Request $request)
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
        if (!empty($dados['sigla'])) {
            $dados['sigla'] = strtoupper($dados['sigla']);
        }

        $user = User::create($dados);
        $token = ApiToken::emitir($user, $request->input('dispositivo', 'app'));

        return response()->json([
            'token' => $token,
            'user' => $this->formatarUsuario($user, true),
        ], 201);
    }

    // POST /api/v1/auth/login
    public function login(Request $request)
    {
        $dados = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $dados['email'])->first();

        if (!$user || !Hash::check($dados['password'], $user->password)) {
            return response()->json(['message' => 'Email ou senha incorretos.'], 422);
        }

        $token = ApiToken::emitir($user, $request->input('dispositivo', 'app'));

        return response()->json([
            'token' => $token,
            'user' => $this->formatarUsuario($user, true),
        ]);
    }

    // POST /api/v1/auth/logout  (invalida só o token deste aparelho)
    public function logout(Request $request)
    {
        $token = $request->attributes->get('api_token');
        if ($token) {
            $token->delete();
        }

        return response()->json(['message' => 'Você saiu da sua conta.']);
    }

    // GET /api/v1/me
    public function me(Request $request)
    {
        return response()->json(['user' => $this->formatarUsuario(Auth::user(), true)]);
    }

    // PUT /api/v1/me   (aceita JSON ou multipart com "foto")
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|max:255',
            'cargo' => 'nullable|max:100',
            'biografia' => 'nullable|max:1000',
            'cidade' => 'nullable|max:100',
            'estado' => 'nullable|max:60',
            'sigla' => 'nullable|max:2',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $dados = [
            'name' => $request->name,
            'cargo' => $request->cargo,
            'biografia' => $request->biografia,
            'cidade' => $request->cidade,
            'estado' => $request->estado,
            'sigla' => $request->sigla ? strtoupper($request->sigla) : null,
        ];

        if ($request->hasFile('foto')) {
            if ($user->foto && Storage::disk('public')->exists($user->foto)) {
                Storage::disk('public')->delete($user->foto);
            }
            $dados['foto'] = $request->file('foto')->store('perfis', 'public');
        }

        DB::table('users')->where('id', $user->id)->update($dados + ['updated_at' => now()]);

        return response()->json(['user' => $this->formatarUsuario(User::find($user->id), true)]);
    }
}
