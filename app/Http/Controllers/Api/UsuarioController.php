<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiHelpers;
use App\Http\Controllers\Controller;
use App\Services\EmpresasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Perfis públicos (pessoas e empresas) + lista de empresas parceiras reais.
 */
class UsuarioController extends Controller
{
    use ApiHelpers;

    // GET /api/v1/empresas?busca=
    public function empresas(Request $request)
    {
        $lista = EmpresasService::listar(null, $request->get('busca'))->map(function ($e) {
            return [
                'id' => (int) $e->id,
                'name' => $e->name,
                'biografia' => $e->biografia,
                'cidade' => $e->cidade,
                'sigla' => $e->sigla,
                'foto_url' => $this->fotoUrl($e->foto),
                'vagas_abertas' => (int) $e->vagas_abertas,
                'media_avaliacoes' => round((float) $e->media_avaliacoes, 1),
            ];
        })->values()->all();

        return response()->json(['data' => $lista]);
    }

    // GET /api/v1/usuarios/{id}
    public function show($id)
    {
        $user = DB::table('users')->where('id', $id)->first();
        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }

        $eu = Auth::id();
        $ctx = $this->contextoUsuario($eu);

        $vagas = $this->apenasAtivas($this->vagasBase())
            ->where('vagas.usuario_id', $id)->orderByDesc('vagas.id')->get()
            ->map(fn($v) => $this->formatarVaga($v, $ctx, false))->values()->all();

        $avaliacoes = DB::table('avaliacoes')
            ->join('users', 'avaliacoes.avaliador_id', '=', 'users.id')
            ->where('avaliacoes.avaliado_id', $id)
            ->select('avaliacoes.*', 'users.name as avaliador_nome', 'users.foto as avaliador_foto', 'users.tipo as avaliador_tipo')
            ->orderByDesc('avaliacoes.created_at')
            ->get()
            ->map(function ($a) use ($eu) {
                return [
                    'id' => (int) $a->id,
                    'nota' => (int) $a->nota,
                    'comentario' => $a->comentario,
                    'tipo' => $a->tipo,
                    'criada_em' => $this->iso($a->created_at),
                    'avaliador' => [
                        'id' => (int) $a->avaliador_id,
                        'nome' => $a->avaliador_nome,
                        'tipo' => $a->avaliador_tipo,
                        'foto_url' => $this->fotoUrl($a->avaliador_foto),
                    ],
                    'minha' => (int) $a->avaliador_id === (int) $eu,
                ];
            })->values()->all();

        // Vagas em que posso avaliar essa pessoa (candidatura aprovada entre nós, ainda sem avaliação minha)
        $avaliaveis = [];
        if ((int) $id !== (int) $eu) {
            $avaliaveis = DB::table('candidaturas')
                ->join('vagas', 'candidaturas.vaga_id', '=', 'vagas.id')
                ->where('candidaturas.status', 'aprovado')
                ->where(function ($q) use ($id, $eu) {
                    $q->where(function ($q2) use ($id, $eu) {
                        $q2->where('candidaturas.usuario_id', $eu)->where('vagas.usuario_id', $id);
                    })->orWhere(function ($q2) use ($id, $eu) {
                        $q2->where('candidaturas.usuario_id', $id)->where('vagas.usuario_id', $eu);
                    });
                })
                ->select('vagas.id', 'vagas.titulo')
                ->distinct()
                ->get()
                ->filter(function ($v) use ($id, $eu) {
                    return !DB::table('avaliacoes')
                        ->where('avaliador_id', $eu)->where('avaliado_id', $id)->where('vaga_id', $v->id)->exists();
                })
                ->map(fn($v) => ['id' => (int) $v->id, 'titulo' => $v->titulo])
                ->values()->all();
        }

        return response()->json([
            'usuario' => $this->formatarUsuario($user),
            'vagas' => $vagas,
            'avaliacoes' => $avaliacoes,
            'vagas_avaliaveis' => $avaliaveis,
            'pode_conversar' => (int) $id !== (int) $eu && $this->vinculoAprovado($eu, $id),
        ]);
    }
}
