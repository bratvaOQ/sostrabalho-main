<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiHelpers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificacaoController extends Controller
{
    use ApiHelpers;

    /**
     * As notificações do site guardam uma URL web. Aqui traduzimos pra uma "ação" que o app entende:
     * { tela: candidatos|candidaturas|chat|perfil_publico, id: número }
     */
    private function destino($n): ?array
    {
        $path = parse_url((string) $n->link, PHP_URL_PATH) ?? '';
        $query = parse_url((string) $n->link, PHP_URL_QUERY) ?? '';

        if (preg_match('#/anunciar/(\d+)/candidatos#', $path, $m)) {
            return ['tela' => 'candidatos', 'id' => (int) $m[1]];
        }
        if (preg_match('#/mensagens/(\d+)#', $path, $m)) {
            return ['tela' => 'chat', 'id' => (int) $m[1]];
        }
        if (preg_match('#/usuario/(\d+)#', $path, $m)) {
            return ['tela' => 'perfil_publico', 'id' => (int) $m[1]];
        }
        if (str_contains($path, '/perfil') && str_contains($query, 'candidaturas')) {
            return ['tela' => 'candidaturas', 'id' => null];
        }

        return null;
    }

    // GET /api/v1/notificacoes
    public function index(Request $request)
    {
        $porPagina = max(1, min(100, (int) $request->get('per_page', 20)));

        $pagina = DB::table('notificacoes')
            ->where('usuario_id', Auth::id())
            ->orderByDesc('id')
            ->paginate($porPagina);

        $itens = collect($pagina->items())->map(function ($n) {
            return [
                'id' => (int) $n->id,
                'tipo' => $n->tipo,
                'mensagem' => $n->mensagem,
                'icone' => $n->icone,
                'lida' => (bool) $n->lida,
                'criada_em' => $this->iso($n->created_at),
                'destino' => $this->destino($n),
            ];
        })->values()->all();

        return response()->json($this->paginado($pagina, $itens));
    }

    // GET /api/v1/notificacoes/contador
    public function contador()
    {
        return response()->json([
            'count' => DB::table('notificacoes')->where('usuario_id', Auth::id())->where('lida', false)->count(),
            // mensagens de chat ainda não lidas (pro "pontinho" da aba Chat do app)
            'mensagens' => DB::table('mensagens')->where('destinatario_id', Auth::id())->where('lida', false)->count(),
        ]);
    }

    // POST /api/v1/notificacoes/{id}/lida
    public function marcarLida($id)
    {
        DB::table('notificacoes')->where('id', $id)->where('usuario_id', Auth::id())->update(['lida' => true]);

        return response()->json(['success' => true]);
    }

    // POST /api/v1/notificacoes/marcar-todas-lidas
    public function marcarTodasLidas()
    {
        DB::table('notificacoes')->where('usuario_id', Auth::id())->where('lida', false)->update(['lida' => true]);

        return response()->json(['success' => true]);
    }

    // DELETE /api/v1/notificacoes/{id}
    public function destroy($id)
    {
        DB::table('notificacoes')->where('id', $id)->where('usuario_id', Auth::id())->delete();

        return response()->json(['success' => true]);
    }

    // DELETE /api/v1/notificacoes
    public function limparTodas()
    {
        DB::table('notificacoes')->where('usuario_id', Auth::id())->delete();

        return response()->json(['success' => true]);
    }
}
