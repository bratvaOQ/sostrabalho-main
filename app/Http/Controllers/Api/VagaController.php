<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiHelpers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VagaController extends Controller
{
    use ApiHelpers;

    // GET /api/v1/categorias
    public function categorias()
    {
        return response()->json([
            'data' => DB::table('categorias')->orderBy('titulo')->get(['id', 'titulo', 'descricao']),
        ]);
    }

    // GET /api/v1/vagas?busca=&cidade=&categoria=&anunciante=&page=
    public function index(Request $request)
    {
        $query = $this->apenasAtivas($this->vagasBase());

        if ($request->filled('busca')) {
            $termo = $request->busca;
            $query->where(function ($q) use ($termo) {
                $q->where('vagas.titulo', 'like', "%{$termo}%")
                    ->orWhere('vagas.descricao_completa', 'like', "%{$termo}%")
                    ->orWhere('vagas.descricao_resumida', 'like', "%{$termo}%")
                    ->orWhere('vagas.bairro', 'like', "%{$termo}%")
                    ->orWhere('users.name', 'like', "%{$termo}%");
            });
        }
        if ($request->filled('cidade')) {
            $query->where('vagas.cidade', $request->cidade);
        }
        if ($request->filled('categoria')) {
            $query->where('vagas.categorias_id', $request->categoria);
        }
        if ($request->filled('anunciante')) {
            $query->where('vagas.usuario_id', $request->anunciante);
        }

        $pagina = $query->orderBy('vagas.id', 'desc')->paginate(15);
        $ctx = $this->contextoUsuario(Auth::id());

        $itens = collect($pagina->items())
            ->map(fn($v) => $this->formatarVaga($v, $ctx, false))
            ->values()->all();

        return response()->json($this->paginado($pagina, $itens));
    }

    // GET /api/v1/vagas/cidades  (cidades que têm vaga aberta — pro filtro)
    public function cidades()
    {
        $q = DB::table('vagas')->select('cidade', 'sigla')->distinct()->orderBy('cidade');
        $q->where('is_expired', false)->where(function ($w) {
            $w->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });

        return response()->json(['data' => $q->get()]);
    }

    // GET /api/v1/vagas/{id}
    public function show($id)
    {
        $vaga = $this->vagasBase()->where('vagas.id', $id)->first();

        if (!$vaga) {
            return response()->json(['message' => 'Vaga não encontrada.'], 404);
        }

        $ctx = $this->contextoUsuario(Auth::id());

        return response()->json(['vaga' => $this->formatarVaga($vaga, $ctx, true)]);
    }
}
