<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FavoritoController extends Controller
{
    public function toggle($vagaId)
    {
        $userId = Auth::id();

        $existe = DB::table('favoritos')
            ->where('usuario_id', $userId)
            ->where('vaga_id', $vagaId)
            ->exists();

        if ($existe) {
            DB::table('favoritos')
                ->where('usuario_id', $userId)
                ->where('vaga_id', $vagaId)
                ->delete();

            return response()->json([
                'success' => true,
                'favoritado' => false,
                'message' => 'Removido dos favoritos'
            ]);
        } else {
            DB::table('favoritos')->insert([
                'usuario_id' => $userId,
                'vaga_id' => $vagaId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'favoritado' => true,
                'message' => 'Adicionado aos favoritos ❤️'
            ]);
        }
    }

    public function index()
    {
        $userId = Auth::id();

        $favoritos = DB::table('favoritos')
            ->join('vagas', 'favoritos.vaga_id', '=', 'vagas.id')
            ->join('categorias', 'vagas.categorias_id', '=', 'categorias.id')
            ->join('users', 'vagas.usuario_id', '=', 'users.id')
            ->where('favoritos.usuario_id', $userId)
            ->select(
                'vagas.*',
                'categorias.titulo as categoria_nome',
                'users.name as anunciante_nome',
                'users.tipo as anunciante_tipo',
                'favoritos.created_at as favoritado_em'
            )
            ->orderBy('favoritos.created_at', 'desc')
            ->get();

        // ✨ NOVO: Pega vagas que o usuário já se candidatou
        $vagasCandidatadas = DB::table('candidaturas')
            ->where('usuario_id', $userId)
            ->pluck('vaga_id')
            ->toArray();

        return view('favoritos', compact('favoritos', 'vagasCandidatadas'));
    }
}
