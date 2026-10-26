<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AvaliacaoController extends Controller
{
    // Salvar nova avaliação
    public function store(Request $request, $avaliadoId)
    {
        $request->validate([
            'nota' => 'required|integer|min:1|max:5',
            'comentario' => 'nullable|string|max:1000',
            'vaga_id' => 'nullable|exists:vagas,id',
            'tipo' => 'required|in:trabalhador,empresa',
        ]);

        $userId = Auth::id();

        if ($avaliadoId == $userId) {
            return response()->json(['error' => 'Você não pode se auto-avaliar'], 400);
        }

        // ✨ Verifica se pode avaliar (candidatura aprovada entre eles)
        if (!$this->podeAvaliar($userId, $avaliadoId)) {
            return response()->json([
                'error' => 'Você só pode avaliar após uma candidatura ser aprovada.'
            ], 403);
        }

        // ✨ Verifica se já avaliou essa pessoa nessa vaga
        $jaAvaliou = DB::table('avaliacoes')
            ->where('avaliador_id', $userId)
            ->where('avaliado_id', $avaliadoId)
            ->where('vaga_id', $request->vaga_id)
            ->exists();

        if ($jaAvaliou) {
            return response()->json(['error' => 'Você já avaliou essa pessoa nessa vaga'], 400);
        }

        DB::table('avaliacoes')->insert([
            'avaliador_id' => $userId,
            'avaliado_id' => $avaliadoId,
            'vaga_id' => $request->vaga_id,
            'tipo' => $request->tipo,
            'nota' => $request->nota,
            'comentario' => $request->comentario,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ✨ Cria notificação
        \App\Http\Controllers\NotificacaoController::criar(
            $avaliadoId,
            'nova_avaliacao',
            Auth::user()->name . ' te avaliou com ' . $request->nota . ' estrelas ⭐',
            '⭐',
            route('perfil.publico', $userId)
        );

        return response()->json([
            'success' => true,
            'message' => 'Avaliação enviada com sucesso!'
        ]);
    }

    // Verifica se pode avaliar (candidatura aprovada)
    private function podeAvaliar($userId1, $userId2)
    {
        // Caso 1: user1 se candidatou na vaga de user2 e foi aprovado
        $caso1 = DB::table('candidaturas')
            ->join('vagas', 'candidaturas.vaga_id', '=', 'vagas.id')
            ->where('candidaturas.usuario_id', $userId1)
            ->where('vagas.usuario_id', $userId2)
            ->where('candidaturas.status', 'aprovado')
            ->exists();

        if ($caso1) return true;

        // Caso 2: user2 se candidatou na vaga de user1 e foi aprovado
        $caso2 = DB::table('candidaturas')
            ->join('vagas', 'candidaturas.vaga_id', '=', 'vagas.id')
            ->where('candidaturas.usuario_id', $userId2)
            ->where('vagas.usuario_id', $userId1)
            ->where('candidaturas.status', 'aprovado')
            ->exists();

        return $caso2;
    }

    // Deletar avaliação (só o avaliador)
    public function destroy($id)
    {
        $avaliacao = DB::table('avaliacoes')
            ->where('id', $id)
            ->where('avaliador_id', Auth::id())
            ->first();

        if (!$avaliacao) {
            return response()->json(['error' => 'Avaliação não encontrada'], 404);
        }

        DB::table('avaliacoes')->where('id', $id)->delete();

        return response()->json(['success' => true, 'message' => 'Avaliação removida']);
    }
}
