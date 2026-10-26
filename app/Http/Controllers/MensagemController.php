<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MensagemController extends Controller
{
    // Lista todas as conversas do usuário (só de candidaturas aprovadas)
    public function index()
    {
        $userId = Auth::id();

        $usuariosPermitidos = $this->getUsuariosPermitidos($userId);

        if (empty($usuariosPermitidos)) {
            return view('mensagens', ['conversas' => []]);
        }

        $placeholders = implode(',', array_fill(0, count($usuariosPermitidos), '?'));

        $conversas = DB::select("
        SELECT 
            u.id as usuario_id,
            u.name as usuario_nome,
            u.tipo as usuario_tipo,
            u.foto as usuario_foto,
            COALESCE(MAX(m.created_at), datetime('now')) as ultima_mensagem_data,
            (SELECT mensagem FROM mensagens 
             WHERE (remetente_id = ? AND destinatario_id = u.id) 
                OR (remetente_id = u.id AND destinatario_id = ?)
             ORDER BY created_at DESC LIMIT 1) as ultima_mensagem,
            (SELECT COUNT(*) FROM mensagens 
             WHERE remetente_id = u.id AND destinatario_id = ? AND lida = 0) as nao_lidas
        FROM users u
        LEFT JOIN mensagens m ON (
            (m.remetente_id = u.id AND m.destinatario_id = ?) OR
            (m.destinatario_id = u.id AND m.remetente_id = ?)
        )
        WHERE u.id IN ({$placeholders})
        GROUP BY u.id, u.name, u.tipo, u.foto
        ORDER BY ultima_mensagem_data DESC
    ", array_merge([$userId, $userId, $userId, $userId, $userId], $usuariosPermitidos));

        return view('mensagens', compact('conversas'));
    }

    // Abrir chat com uma pessoa específica
    public function conversa($outroUsuarioId)
    {
        $userId = Auth::id();

        if (!$this->podeConversar($userId, $outroUsuarioId)) {
            return redirect()->route('mensagens')
                ->with('erro', 'Você só pode conversar após uma candidatura ser aprovada.');
        }

        $outroUsuario = DB::table('users')->where('id', $outroUsuarioId)->first();

        if (!$outroUsuario) {
            return redirect()->route('mensagens')->with('erro', 'Usuário não encontrado.');
        }

        // Marca mensagens como lidas
        DB::table('mensagens')
            ->where('remetente_id', $outroUsuarioId)
            ->where('destinatario_id', $userId)
            ->where('lida', false)
            ->update(['lida' => true]);

        // Busca todas as mensagens da conversa
        $mensagens = DB::table('mensagens')
            ->where(function ($q) use ($userId, $outroUsuarioId) {
                $q->where(function ($q2) use ($userId, $outroUsuarioId) {
                    $q2->where('remetente_id', $userId)
                        ->where('destinatario_id', $outroUsuarioId);
                })
                    ->orWhere(function ($q2) use ($userId, $outroUsuarioId) {
                        $q2->where('remetente_id', $outroUsuarioId)
                            ->where('destinatario_id', $userId);
                    });
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // Pega a vaga aprovada
        $vaga = DB::table('candidaturas')
            ->join('vagas', 'candidaturas.vaga_id', '=', 'vagas.id')
            ->where('candidaturas.status', 'aprovado')
            ->where(function ($q) use ($userId, $outroUsuarioId) {
                $q->where(function ($q2) use ($userId, $outroUsuarioId) {
                    $q2->where('candidaturas.usuario_id', $userId)
                        ->where('vagas.usuario_id', $outroUsuarioId);
                })
                    ->orWhere(function ($q2) use ($userId, $outroUsuarioId) {
                        $q2->where('candidaturas.usuario_id', $outroUsuarioId)
                            ->where('vagas.usuario_id', $userId);
                    });
            })
            ->select('vagas.titulo', 'vagas.id')
            ->first();

        return view('chat', compact('outroUsuario', 'mensagens', 'vaga'));
    }

    // Enviar mensagem (AJAX)
    public function enviar(Request $request)
    {
        $request->validate([
            'destinatario_id' => 'required|exists:users,id',
            'mensagem' => 'required|string|max:5000',
        ]);

        $userId = Auth::id();
        $destinatarioId = $request->destinatario_id;

        if (!$this->podeConversar($userId, $destinatarioId)) {
            return response()->json([
                'error' => 'Você só pode conversar após uma candidatura ser aprovada.'
            ], 403);
        }

        if ($destinatarioId == $userId) {
            return response()->json(['error' => 'Você não pode enviar mensagem para si mesmo'], 400);
        }

        $id = DB::table('mensagens')->insertGetId([
            'remetente_id' => $userId,
            'destinatario_id' => $destinatarioId,
            'mensagem' => $request->mensagem,
            'lida' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mensagem = DB::table('mensagens')->where('id', $id)->first();

        // ✨ ADICIONA AQUI - Notifica destinatário
        \App\Http\Controllers\NotificacaoController::criar(
            $destinatarioId,
            'nova_mensagem',
            Auth::user()->name . ' te enviou uma mensagem',
            '💬',
            route('mensagens.conversa', $userId)
        );

        return response()->json([
            'success' => true,
            'mensagem' => $mensagem,
            'formatada' => [
                'id' => $mensagem->id,
                'texto' => $mensagem->mensagem,
                'created_at' => \Carbon\Carbon::parse($mensagem->created_at)->toIso8601String(),
                'lida' => false,
            ]
        ]);
    }

    // Buscar novas mensagens (polling AJAX)
    public function novasMensagens($outroUsuarioId, $ultimaId = 0)
    {
        $userId = Auth::id();

        if (!$this->podeConversar($userId, $outroUsuarioId)) {
            return response()->json([]);
        }

        $mensagens = DB::table('mensagens')
            ->where('id', '>', $ultimaId)
            ->where(function ($q) use ($userId, $outroUsuarioId) {
                $q->where(function ($q2) use ($userId, $outroUsuarioId) {
                    $q2->where('remetente_id', $userId)
                        ->where('destinatario_id', $outroUsuarioId);
                })
                    ->orWhere(function ($q2) use ($userId, $outroUsuarioId) {
                        $q2->where('remetente_id', $outroUsuarioId)
                            ->where('destinatario_id', $userId);
                    });
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // Converte created_at para ISO 8601 (UTC)
        $mensagens = $mensagens->map(function ($msg) {
            $msg->created_at = \Carbon\Carbon::parse($msg->created_at)->toIso8601String();
            return $msg;
        });

        // Marca como lidas
        DB::table('mensagens')
            ->where('remetente_id', $outroUsuarioId)
            ->where('destinatario_id', $userId)
            ->where('lida', false)
            ->update(['lida' => true]);

        return response()->json($mensagens);
    }

    // Status de leitura das mensagens
    public function statusMensagens($outroUsuarioId)
    {
        $userId = Auth::id();

        $mensagensLidas = DB::table('mensagens')
            ->where('remetente_id', $userId)
            ->where('destinatario_id', $outroUsuarioId)
            ->where('lida', true)
            ->pluck('id')
            ->toArray();

        return response()->json([
            'lidas' => $mensagensLidas
        ]);
    }

    // ═══ MÉTODOS AUXILIARES ═══

    private function podeConversar($userId1, $userId2)
    {
        $caso1 = DB::table('candidaturas')
            ->join('vagas', 'candidaturas.vaga_id', '=', 'vagas.id')
            ->where('candidaturas.usuario_id', $userId1)
            ->where('vagas.usuario_id', $userId2)
            ->where('candidaturas.status', 'aprovado')
            ->exists();

        $caso2 = DB::table('candidaturas')
            ->join('vagas', 'candidaturas.vaga_id', '=', 'vagas.id')
            ->where('candidaturas.usuario_id', $userId2)
            ->where('vagas.usuario_id', $userId1)
            ->where('candidaturas.status', 'aprovado')
            ->exists();

        return $caso1 || $caso2;
    }

    private function getUsuariosPermitidos($userId)
    {
        $anunciantes = DB::table('candidaturas')
            ->join('vagas', 'candidaturas.vaga_id', '=', 'vagas.id')
            ->where('candidaturas.usuario_id', $userId)
            ->where('candidaturas.status', 'aprovado')
            ->pluck('vagas.usuario_id')
            ->toArray();

        $candidatos = DB::table('candidaturas')
            ->join('vagas', 'candidaturas.vaga_id', '=', 'vagas.id')
            ->where('vagas.usuario_id', $userId)
            ->where('candidaturas.status', 'aprovado')
            ->pluck('candidaturas.usuario_id')
            ->toArray();

        return array_unique(array_merge($anunciantes, $candidatos));
    }
}
