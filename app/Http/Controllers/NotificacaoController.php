<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificacaoController extends Controller
{
    // Lista todas
    public function index()
    {
        $notificacoes = DB::table('notificacoes')
            ->where('usuario_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Marca todas como lidas ao abrir
        DB::table('notificacoes')
            ->where('usuario_id', Auth::id())
            ->where('lida', false)
            ->update(['lida' => true]);

        return view('notificacoes', compact('notificacoes'));
    }

    // Contador de não lidas (AJAX pro sininho)
    public function contador()
    {
        $count = DB::table('notificacoes')
            ->where('usuario_id', Auth::id())
            ->where('lida', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    // Últimas 5 (dropdown)
    public function ultimas()
    {
        $notificacoes = DB::table('notificacoes')
            ->where('usuario_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($n) {
                $n->tempo = \Carbon\Carbon::parse($n->created_at)->diffForHumans();
                return $n;
            });

        return response()->json($notificacoes);
    }

    // Marca uma como lida
    public function marcarLida($id)
    {
        DB::table('notificacoes')
            ->where('id', $id)
            ->where('usuario_id', Auth::id())
            ->update(['lida' => true]);

        return response()->json(['success' => true]);
    }

    // Marca TODAS as notificações como lidas
    public function marcarTodasLidas()
    {
        DB::table('notificacoes')
            ->where('usuario_id', Auth::id())
            ->where('lida', false)
            ->update(['lida' => true]);

        return response()->json(['success' => true]);
    }

    // Deleta uma
    public function destroy($id)
    {
        DB::table('notificacoes')
            ->where('id', $id)
            ->where('usuario_id', Auth::id())
            ->delete();

        return response()->json(['success' => true]);
    }

    // Deleta todas
    public function limparTodas()
    {
        DB::table('notificacoes')
            ->where('usuario_id', Auth::id())
            ->delete();

        return redirect()->route('notificacoes')->with('sucesso', 'Notificações removidas!');
    }

    // ═══ MÉTODO AUXILIAR (usado em outros controllers) ═══
    public static function criar($usuarioId, $tipo, $mensagem, $icone = '🔔', $link = null, $vagaId = null)
    {
        DB::table('notificacoes')->insert([
            'usuario_id' => $usuarioId,
            'tipo' => $tipo,
            'mensagem' => $mensagem,
            'icone' => $icone,
            'link' => $link,
            'vaga_id' => $vagaId,
            'lida' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
