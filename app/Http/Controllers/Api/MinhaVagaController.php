<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiHelpers;
use App\Http\Controllers\Controller;
use App\Services\GeocodingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * "Anunciar vaga" do app: criar, editar, excluir, renovar e ver candidatos das MINHAS vagas.
 */
class MinhaVagaController extends Controller
{
    use ApiHelpers;

    private function regras(): array
    {
        return [
            'titulo' => 'required|max:100',
            'descricao_completa' => 'required|max:5500',
            'requisito' => 'nullable|max:600',
            'beneficio' => 'nullable|max:1000',
            'salario' => 'nullable|numeric|min:0|max:999999.99',
            'categorias_id' => 'required|exists:categorias,id',
            'cidade' => 'required|max:100',
            'bairro' => 'nullable|max:100',
            'estado' => 'required|max:60',
            'sigla' => 'required|size:2',
            'descricao_resumida' => 'nullable|max:260',
        ];
    }

    private function minha($id)
    {
        return DB::table('vagas')->where('id', $id)->where('usuario_id', Auth::id())->first();
    }

    private function resposta($id, int $status = 200)
    {
        $vaga = $this->vagasBase()->where('vagas.id', $id)->first();
        $ctx = $this->contextoUsuario(Auth::id());
        $dados = $this->formatarVaga($vaga, $ctx, true);
        $dados['total_candidatos'] = DB::table('candidaturas')->where('vaga_id', $id)->count();

        return response()->json(['vaga' => $dados], $status);
    }

    // GET /api/v1/minhas-vagas
    public function index()
    {
        $vagas = $this->vagasBase()->where('vagas.usuario_id', Auth::id())
            ->orderBy('vagas.id', 'desc')->get();

        $totais = DB::table('candidaturas')
            ->join('vagas', 'candidaturas.vaga_id', '=', 'vagas.id')
            ->where('vagas.usuario_id', Auth::id())
            ->groupBy('candidaturas.vaga_id')
            ->selectRaw('candidaturas.vaga_id as vaga_id, count(*) as total')
            ->pluck('total', 'vaga_id');

        $ctx = $this->contextoUsuario(Auth::id());

        $itens = $vagas->map(function ($v) use ($ctx, $totais) {
            $d = $this->formatarVaga($v, $ctx, true);
            $d['total_candidatos'] = (int) ($totais[$v->id] ?? 0);
            return $d;
        })->values()->all();

        return response()->json(['data' => $itens]);
    }

    // POST /api/v1/minhas-vagas
    public function store(Request $request)
    {
        $request->validate($this->regras());
        $user = Auth::user();
        $coords = GeocodingService::coordenadas($request->cidade, $request->estado, $request->bairro);

        $id = DB::table('vagas')->insertGetId([
            'titulo' => $request->titulo,
            'empresa' => $user->tipo === 'empresa' ? $user->name : null,
            'descricao_completa' => $request->descricao_completa,
            'requisito' => $request->requisito,
            'beneficio' => $request->beneficio,
            'salario' => $request->salario,
            'expires_at' => now()->addDays(30),
            'is_expired' => false,
            'categorias_id' => $request->categorias_id,
            'usuario_id' => $user->id,
            'cidade' => $request->cidade,
            'bairro' => $request->bairro,
            'estado' => $request->estado,
            'sigla' => strtoupper($request->sigla),
            'latitude' => $coords['latitude'],
            'longitude' => $coords['longitude'],
            'descricao_resumida' => $request->descricao_resumida,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->resposta($id, 201);
    }

    // PUT /api/v1/minhas-vagas/{id}
    public function update(Request $request, $id)
    {
        $vaga = $this->minha($id);
        if (!$vaga) {
            return response()->json(['message' => 'Vaga não encontrada ou você não tem permissão.'], 404);
        }

        $request->validate($this->regras());
        $user = Auth::user();
        $coords = GeocodingService::coordenadas($request->cidade, $request->estado, $request->bairro);

        DB::table('vagas')->where('id', $id)->update([
            'titulo' => $request->titulo,
            'empresa' => $user->tipo === 'empresa' ? $user->name : null,
            'descricao_completa' => $request->descricao_completa,
            'requisito' => $request->requisito,
            'beneficio' => $request->beneficio,
            'salario' => $request->salario,
            'categorias_id' => $request->categorias_id,
            'cidade' => $request->cidade,
            'bairro' => $request->bairro,
            'estado' => $request->estado,
            'sigla' => strtoupper($request->sigla),
            'latitude' => $coords['latitude'],
            'longitude' => $coords['longitude'],
            'descricao_resumida' => $request->descricao_resumida,
            'updated_at' => now(),
        ]);

        return $this->resposta($id);
    }

    // DELETE /api/v1/minhas-vagas/{id}
    public function destroy($id)
    {
        if (!$this->minha($id)) {
            return response()->json(['message' => 'Vaga não encontrada ou você não tem permissão.'], 404);
        }

        DB::table('vagas')->where('id', $id)->delete();

        return response()->json(['message' => 'Vaga excluída com sucesso!']);
    }

    // POST /api/v1/minhas-vagas/{id}/renovar  (+30 dias)
    public function renovar($id)
    {
        if (!$this->minha($id)) {
            return response()->json(['message' => 'Vaga não encontrada.'], 404);
        }

        DB::table('vagas')->where('id', $id)->update([
            'expires_at' => now()->addDays(30),
            'is_expired' => false,
            'updated_at' => now(),
        ]);

        return $this->resposta($id);
    }

    // GET /api/v1/minhas-vagas/{id}/candidatos
    public function candidatos($id)
    {
        if (!$this->minha($id)) {
            return response()->json(['message' => 'Vaga não encontrada ou você não tem permissão.'], 404);
        }

        $linhas = DB::table('candidaturas')
            ->join('users', 'candidaturas.usuario_id', '=', 'users.id')
            ->where('candidaturas.vaga_id', $id)
            ->select(
                'candidaturas.id as candidatura_id',
                'candidaturas.mensagem',
                'candidaturas.status',
                'candidaturas.created_at as candidatura_em',
                'users.*'
            )
            ->orderByDesc('candidaturas.created_at')
            ->get();

        $itens = $linhas->map(function ($l) {
            return [
                'id' => (int) $l->candidatura_id,
                'mensagem' => $l->mensagem,
                'status' => $l->status,
                'criada_em' => $this->iso($l->candidatura_em),
                'candidato' => $this->formatarUsuario($l, true),
            ];
        })->values()->all();

        return response()->json(['data' => $itens]);
    }
}
