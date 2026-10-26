<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiHelpers;
use App\Http\Controllers\Controller;
use App\Http\Controllers\NotificacaoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AvaliacaoController extends Controller
{
    use ApiHelpers;

    // POST /api/v1/avaliacoes/{avaliadoId}   { nota 1-5, comentario?, vaga_id?, tipo: trabalhador|empresa }
    public function store(Request $request, $avaliadoId)
    {
        $request->validate([
            'nota' => 'required|integer|min:1|max:5',
            'comentario' => 'nullable|string|max:1000',
            'vaga_id' => 'nullable|exists:vagas,id',
            'tipo' => 'required|in:trabalhador,empresa',
        ]);

        $eu = Auth::id();

        if ((int) $avaliadoId === (int) $eu) {
            return response()->json(['message' => 'Você não pode se auto-avaliar.'], 422);
        }
        if (!DB::table('users')->where('id', $avaliadoId)->exists()) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }
        if (!$this->vinculoAprovado($eu, $avaliadoId)) {
            return response()->json(['message' => 'Você só pode avaliar após uma candidatura ser aprovada.'], 403);
        }

        $jaAvaliou = DB::table('avaliacoes')
            ->where('avaliador_id', $eu)
            ->where('avaliado_id', $avaliadoId)
            ->where('vaga_id', $request->vaga_id)
            ->exists();
        if ($jaAvaliou) {
            return response()->json(['message' => 'Você já avaliou essa pessoa nessa vaga.'], 422);
        }

        DB::table('avaliacoes')->insert([
            'avaliador_id' => $eu,
            'avaliado_id' => $avaliadoId,
            'vaga_id' => $request->vaga_id,
            'tipo' => $request->tipo,
            'nota' => $request->nota,
            'comentario' => $request->comentario,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        NotificacaoController::criar(
            $avaliadoId,
            'nova_avaliacao',
            Auth::user()->name . ' te avaliou com ' . $request->nota . ' estrelas ⭐',
            '⭐',
            route('perfil.publico', $eu)
        );

        return response()->json(['message' => 'Avaliação enviada com sucesso!'], 201);
    }

    // DELETE /api/v1/avaliacoes/{id}  (só quem avaliou)
    public function destroy($id)
    {
        $a = DB::table('avaliacoes')->where('id', $id)->where('avaliador_id', Auth::id())->first();
        if (!$a) {
            return response()->json(['message' => 'Avaliação não encontrada.'], 404);
        }

        DB::table('avaliacoes')->where('id', $id)->delete();

        return response()->json(['message' => 'Avaliação removida.']);
    }
}
