<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiHelpers;
use App\Http\Controllers\Controller;
use App\Http\Controllers\NotificacaoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Chat. Regra do site mantida: só conversa quem tem uma candidatura APROVADA entre si.
 */
class MensagemController extends Controller
{
    use ApiHelpers;

    private function entre($a, $b)
    {
        return DB::table('mensagens')->where(function ($q) use ($a, $b) {
            $q->where(function ($q2) use ($a, $b) {
                $q2->where('remetente_id', $a)->where('destinatario_id', $b);
            })->orWhere(function ($q2) use ($a, $b) {
                $q2->where('remetente_id', $b)->where('destinatario_id', $a);
            });
        });
    }

    private function formatarMensagem($m): array
    {
        return [
            'id' => (int) $m->id,
            'remetente_id' => (int) $m->remetente_id,
            'destinatario_id' => (int) $m->destinatario_id,
            'texto' => $m->mensagem,
            'lida' => (bool) $m->lida,
            'criada_em' => $this->iso($m->created_at),
        ];
    }

    // GET /api/v1/mensagens  (lista de conversas)
    public function index()
    {
        $eu = Auth::id();
        $ids = $this->usuariosPermitidosChat($eu);

        if (empty($ids)) {
            return response()->json(['data' => []]);
        }

        $conversas = DB::table('users')->whereIn('id', $ids)->get()->map(function ($u) use ($eu) {
            $ultima = $this->entre($eu, $u->id)->orderByDesc('id')->first();
            $naoLidas = DB::table('mensagens')
                ->where('remetente_id', $u->id)->where('destinatario_id', $eu)->where('lida', false)->count();

            return [
                'usuario' => [
                    'id' => (int) $u->id,
                    'name' => $u->name,
                    'tipo' => $u->tipo,
                    'foto_url' => $this->fotoUrl($u->foto),
                ],
                'ultima_mensagem' => $ultima ? $ultima->mensagem : null,
                'ultima_em' => $ultima ? $this->iso($ultima->created_at) : null,
                'nao_lidas' => (int) $naoLidas,
            ];
        });

        // Quem ainda não tem mensagem (chat recém-liberado) aparece primeiro, como no site.
        $ordenadas = $conversas->sortByDesc(fn($c) => $c['ultima_em'] ?? '9999')->values()->all();

        return response()->json(['data' => $ordenadas]);
    }

    // GET /api/v1/mensagens/{outroId}?depois_de=ID   (abre a conversa / polling de novas)
    public function conversa(Request $request, $outroId)
    {
        $eu = Auth::id();

        if (!$this->vinculoAprovado($eu, $outroId)) {
            return response()->json(['message' => 'Você só pode conversar após uma candidatura ser aprovada.'], 403);
        }

        $outro = DB::table('users')->where('id', $outroId)->first();
        if (!$outro) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }

        $query = $this->entre($eu, $outroId);
        if ($request->filled('depois_de')) {
            $query->where('id', '>', (int) $request->depois_de);
        }
        $mensagens = $query->orderBy('id')->get();

        DB::table('mensagens')
            ->where('remetente_id', $outroId)->where('destinatario_id', $eu)->where('lida', false)
            ->update(['lida' => true]);

        // Quais mensagens MINHAS o outro já leu (pra mostrar o "visto")
        $lidasPorElePorId = DB::table('mensagens')
            ->where('remetente_id', $eu)->where('destinatario_id', $outroId)->where('lida', true)
            ->pluck('id')->map(fn($i) => (int) $i)->all();

        return response()->json([
            'usuario' => [
                'id' => (int) $outro->id,
                'name' => $outro->name,
                'tipo' => $outro->tipo,
                'foto_url' => $this->fotoUrl($outro->foto),
            ],
            'mensagens' => $mensagens->map(fn($m) => $this->formatarMensagem($m))->values()->all(),
            'minhas_lidas' => $lidasPorElePorId,
        ]);
    }

    // POST /api/v1/mensagens   { destinatario_id, mensagem }
    public function enviar(Request $request)
    {
        $request->validate([
            'destinatario_id' => 'required|exists:users,id',
            'mensagem' => 'required|string|max:5000',
        ]);

        $eu = Auth::id();
        $destino = (int) $request->destinatario_id;

        if ($destino === (int) $eu) {
            return response()->json(['message' => 'Você não pode enviar mensagem para si mesmo.'], 422);
        }
        if (!$this->vinculoAprovado($eu, $destino)) {
            return response()->json(['message' => 'Você só pode conversar após uma candidatura ser aprovada.'], 403);
        }

        $id = DB::table('mensagens')->insertGetId([
            'remetente_id' => $eu,
            'destinatario_id' => $destino,
            'mensagem' => $request->mensagem,
            'lida' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        NotificacaoController::criar(
            $destino,
            'nova_mensagem',
            Auth::user()->name . ' te enviou uma mensagem',
            '💬',
            route('mensagens.conversa', $eu)
        );

        $m = DB::table('mensagens')->where('id', $id)->first();

        return response()->json(['mensagem' => $this->formatarMensagem($m)], 201);
    }
}
