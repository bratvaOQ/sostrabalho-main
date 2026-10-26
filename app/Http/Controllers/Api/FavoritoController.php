<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiHelpers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FavoritoController extends Controller
{
    use ApiHelpers;

    // GET /api/v1/favoritos
    public function index()
    {
        $vagas = $this->vagasBase()
            ->join('favoritos', 'favoritos.vaga_id', '=', 'vagas.id')
            ->where('favoritos.usuario_id', Auth::id())
            ->orderByDesc('favoritos.created_at')
            ->get();

        $ctx = $this->contextoUsuario(Auth::id());

        return response()->json([
            'data' => $vagas->map(fn($v) => $this->formatarVaga($v, $ctx, false))->values()->all(),
        ]);
    }

    // POST /api/v1/favoritos/{vagaId}/toggle
    public function toggle($vagaId)
    {
        if (!DB::table('vagas')->where('id', $vagaId)->exists()) {
            return response()->json(['message' => 'Vaga não encontrada.'], 404);
        }

        $filtro = ['usuario_id' => Auth::id(), 'vaga_id' => $vagaId];

        if (DB::table('favoritos')->where($filtro)->exists()) {
            DB::table('favoritos')->where($filtro)->delete();
            return response()->json(['favoritada' => false, 'message' => 'Removido dos favoritos']);
        }

        DB::table('favoritos')->insert($filtro + ['created_at' => now(), 'updated_at' => now()]);

        return response()->json(['favoritada' => true, 'message' => 'Adicionado aos favoritos']);
    }
}
