<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiHelpers;
use App\Http\Controllers\Controller;
use App\Http\Controllers\NotificacaoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CandidaturaController extends Controller
{
    use ApiHelpers;

    // GET /api/v1/candidaturas  (as MINHAS candidaturas)
    public function index()
    {
        $linhas = DB::table('candidaturas')
            ->join('vagas', 'candidaturas.vaga_id', '=', 'vagas.id')
            ->join('users', 'vagas.usuario_id', '=', 'users.id')
            ->join('categorias', 'vagas.categorias_id', '=', 'categorias.id')
            ->where('candidaturas.usuario_id', Auth::id())
            ->select(
                'candidaturas.id as candidatura_id',
                'candidaturas.status',
                'candidaturas.mensagem',
                'candidaturas.created_at as candidatura_em',
                'vagas.id as vaga_id',
                'vagas.titulo',
                'vagas.salario',
                'vagas.cidade',
                'vagas.bairro',
                'vagas.sigla',
                'vagas.expires_at',
                'categorias.titulo as categoria',
                'users.id as anunciante_id',
                'users.name as anunciante_nome',
                'users.tipo as anunciante_tipo',
                'users.foto as anunciante_foto'
            )
            ->orderByDesc('candidaturas.id')
            ->get();

        $itens = $linhas->map(function ($l) {
            return [
                'id' => (int) $l->candidatura_id,
                'status' => $l->status,
                'mensagem' => $l->mensagem,
                'criada_em' => $this->iso($l->candidatura_em),
                'vaga' => [
                    'id' => (int) $l->vaga_id,
                    'titulo' => $l->titulo,
                    'salario' => $l->salario !== null ? (float) $l->salario : null,
                    'localizacao' => trim(($l->bairro ? $l->bairro . ' - ' : '') . $l->cidade . '/' . $l->sigla),
                    'categoria' => $l->categoria,
                    'expirada' => $l->expires_at ? \Carbon\Carbon::parse($l->expires_at)->isPast() : false,
                ],
                'anunciante' => [
                    'id' => (int) $l->anunciante_id,
                    'nome' => $l->anunciante_nome,
                    'tipo' => $l->anunciante_tipo,
                    'foto_url' => $this->fotoUrl($l->anunciante_foto),
                ],
            ];
        })->values()->all();

        return response()->json(['data' => $itens]);
    }

    // POST /api/v1/vagas/{id}/candidatar   { mensagem? }
    public function store(Request $request, $vagaId)
    {
        $vaga = DB::table('vagas')->where('id', $vagaId)->first();

        if (!$vaga) {
            return response()->json(['message' => 'Vaga não encontrada.'], 404);
        }
        if ($vaga->usuario_id == Auth::id()) {
            return response()->json(['message' => 'Você não pode se candidatar à sua própria vaga.'], 422);
        }
        if ($vaga->is_expired || ($vaga->expires_at && \Carbon\Carbon::parse($vaga->expires_at)->isPast())) {
            return response()->json(['message' => 'Esta vaga já expirou.'], 422);
        }

        $jaSeCandidatou = DB::table('candidaturas')
            ->where('vaga_id', $vagaId)->where('usuario_id', Auth::id())->exists();
        if ($jaSeCandidatou) {
            return response()->json(['message' => 'Você já se candidatou a esta vaga.'], 422);
        }

        $request->validate(['mensagem' => 'nullable|max:1000']);

        $id = DB::table('candidaturas')->insertGetId([
            'vaga_id' => $vagaId,
            'usuario_id' => Auth::id(),
            'mensagem' => $request->mensagem,
            'status' => 'pendente',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        NotificacaoController::criar(
            $vaga->usuario_id,
            'nova_candidatura',
            Auth::user()->name . ' se candidatou à sua vaga "' . $vaga->titulo . '"',
            '👤',
            route('anunciar.candidatos', $vagaId),
            $vagaId
        );

        return response()->json([
            'message' => 'Candidatura enviada com sucesso!',
            'candidatura' => ['id' => (int) $id, 'status' => 'pendente'],
        ], 201);
    }

    // DELETE /api/v1/candidaturas/{id}  (candidato cancela a sua)
    public function destroy($id)
    {
        $c = DB::table('candidaturas')->where('id', $id)->where('usuario_id', Auth::id())->first();
        if (!$c) {
            return response()->json(['message' => 'Candidatura não encontrada.'], 404);
        }

        DB::table('candidaturas')->where('id', $id)->delete();

        return response()->json(['message' => 'Candidatura cancelada.']);
    }

    // DELETE /api/v1/candidaturas  (cancela todas as minhas)
    public function limparTodas()
    {
        DB::table('candidaturas')->where('usuario_id', Auth::id())->delete();

        return response()->json(['message' => 'Todas as suas candidaturas foram canceladas.']);
    }

    // PATCH /api/v1/candidaturas/{id}/status   { status: pendente|aprovado|rejeitado }  (dono da vaga)
    public function atualizarStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:pendente,aprovado,rejeitado']);

        $c = DB::table('candidaturas')
            ->join('vagas', 'candidaturas.vaga_id', '=', 'vagas.id')
            ->where('candidaturas.id', $id)
            ->where('vagas.usuario_id', Auth::id())
            ->select('candidaturas.*', 'vagas.titulo as vaga_titulo')
            ->first();

        if (!$c) {
            return response()->json(['message' => 'Candidatura não encontrada ou você não tem permissão.'], 404);
        }

        DB::table('candidaturas')->where('id', $id)->update([
            'status' => $request->status,
            'updated_at' => now(),
        ]);

        $texto = [
            'aprovado' => '✅ Sua candidatura foi APROVADA!',
            'rejeitado' => '❌ Sua candidatura foi rejeitada',
            'pendente' => '⏳ Sua candidatura voltou para análise',
        ];
        $icone = ['aprovado' => '✅', 'rejeitado' => '❌', 'pendente' => '⏳'];

        NotificacaoController::criar(
            $c->usuario_id,
            'status_candidatura',
            $texto[$request->status] . ' - Vaga: ' . $c->vaga_titulo,
            $icone[$request->status],
            route('perfil', ['aba' => 'candidaturas']),
            $c->vaga_id
        );

        return response()->json([
            'message' => 'Status atualizado com sucesso!',
            'candidatura' => ['id' => (int) $id, 'status' => $request->status],
        ]);
    }
}
