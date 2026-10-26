<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PerfilController extends Controller
{
    public function perfil(Request $request)
    {
        $user = Auth::user();
        $aba = $request->get('aba', 'vagas');

        // Minhas Vagas
        $minhasVagas = DB::table('vagas')
            ->join('categorias', 'vagas.categorias_id', '=', 'categorias.id')
            ->where('vagas.usuario_id', $user->id)
            ->select(
                'vagas.*',
                'categorias.titulo as categoria_nome'
            )
            ->orderBy('vagas.id', 'desc')
            ->get();

        // Minhas Candidaturas
        $minhasCandidaturas = DB::table('candidaturas')
            ->leftJoin('vagas', 'candidaturas.vaga_id', '=', 'vagas.id')
            ->leftJoin('users', 'vagas.usuario_id', '=', 'users.id')
            ->leftJoin('categorias', 'vagas.categorias_id', '=', 'categorias.id')
            ->where('candidaturas.usuario_id', $user->id)
            ->select(
                'candidaturas.id as candidatura_id',
                'candidaturas.status',
                'candidaturas.created_at as candidatura_data',
                'vagas.titulo as vaga_titulo',
                'vagas.salario as vaga_salario',
                'vagas.cidade as vaga_cidade',
                'vagas.bairro as vaga_bairro',
                'vagas.sigla as vaga_sigla',
                'vagas.descricao_resumida as vaga_descricao_resumida',
                'vagas.descricao_completa as vaga_descricao_completa',
                'categorias.titulo as vaga_categoria',
                'users.name as anunciante_nome',
                'users.tipo as anunciante_tipo',
                'users.id as anunciante_id'
            )
            ->orderBy('candidaturas.id', 'desc')
            ->get();

        // ✨ MINHAS AVALIAÇÕES RECEBIDAS
        $avaliacoes = DB::table('avaliacoes')
            ->join('users', 'avaliacoes.avaliador_id', '=', 'users.id')
            ->where('avaliacoes.avaliado_id', $user->id)
            ->select(
                'avaliacoes.*',
                'users.id as avaliador_id',
                'users.name as avaliador_nome',
                'users.foto as avaliador_foto',
                'users.tipo as avaliador_tipo'
            )
            ->orderBy('avaliacoes.created_at', 'desc')
            ->get();

        $mediaAvaliacoes = round($avaliacoes->avg('nota') ?? 0, 1);
        $totalAvaliacoes = $avaliacoes->count();

        return view('perfil', compact(
            'user',
            'aba',
            'minhasVagas',
            'minhasCandidaturas',
            'avaliacoes',
            'mediaAvaliacoes',
            'totalAvaliacoes'
        ));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|max:255',
            'cargo' => 'nullable|max:100',
            'biografia' => 'nullable|max:1000',
            'cidade' => 'nullable|max:100',
            'estado' => 'nullable|max:60',
            'sigla' => 'nullable|max:2',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $dados = [
            'name' => $request->name,
            'cargo' => $request->cargo,
            'biografia' => $request->biografia,
            'cidade' => $request->cidade,
            'estado' => $request->estado,
            'sigla' => $request->sigla ? strtoupper($request->sigla) : null,
        ];

        // Upload de foto
        if ($request->hasFile('foto')) {
            // Deletar foto antiga
            if ($user->foto && Storage::disk('public')->exists($user->foto)) {
                Storage::disk('public')->delete($user->foto);
            }
            $path = $request->file('foto')->store('perfis', 'public');
            $dados['foto'] = $path;
        }

        DB::table('users')->where('id', $user->id)->update($dados);

        return redirect()->route('perfil')->with('sucesso', 'Perfil atualizado com sucesso!');
    }

    public function perfilPublico($id)
    {
        $user = DB::table('users')->where('id', $id)->first();

        if (!$user) {
            return redirect()->route('home')->with('erro', 'Usuário não encontrado.');
        }

        // Vagas anunciadas por esse usuário
        $vagas = DB::table('vagas')
            ->join('categorias', 'vagas.categorias_id', '=', 'categorias.id')
            ->where('vagas.usuario_id', $id)
            ->select(
                'vagas.*',
                'categorias.titulo as categoria_nome'
            )
            ->orderBy('vagas.id', 'desc')
            ->get();

        // ✨ Avaliações recebidas
        $avaliacoes = DB::table('avaliacoes')
            ->join('users', 'avaliacoes.avaliador_id', '=', 'users.id')
            ->where('avaliacoes.avaliado_id', $id)
            ->select(
                'avaliacoes.*',
                'users.name as avaliador_nome',
                'users.foto as avaliador_foto',
                'users.tipo as avaliador_tipo'
            )
            ->orderBy('avaliacoes.created_at', 'desc')
            ->get();

        // ✨ Média e total
        $mediaAvaliacoes = round($avaliacoes->avg('nota') ?? 0, 1);
        $totalAvaliacoes = $avaliacoes->count();

        // ✨ Verifica se o usuário logado pode avaliar
        $podeAvaliar = false;
        $vagasAvaliaveis = [];

        if (Auth::check() && Auth::id() != $id) {
            $vagasAvaliaveis = DB::table('candidaturas')
                ->join('vagas', 'candidaturas.vaga_id', '=', 'vagas.id')
                ->where('candidaturas.status', 'aprovado')
                ->where(function ($q) use ($id) {
                    $q->where(function ($q2) use ($id) {
                        $q2->where('candidaturas.usuario_id', Auth::id())
                            ->where('vagas.usuario_id', $id);
                    })
                        ->orWhere(function ($q2) use ($id) {
                            $q2->where('candidaturas.usuario_id', $id)
                                ->where('vagas.usuario_id', Auth::id());
                        });
                })
                ->select('vagas.id', 'vagas.titulo')
                ->distinct()
                ->get();

            // Remove vagas já avaliadas
            $vagasAvaliaveis = $vagasAvaliaveis->filter(function ($vaga) use ($id) {
                return !DB::table('avaliacoes')
                    ->where('avaliador_id', Auth::id())
                    ->where('avaliado_id', $id)
                    ->where('vaga_id', $vaga->id)
                    ->exists();
            })->values();

            $podeAvaliar = $vagasAvaliaveis->count() > 0;
        }

        return view('perfil-publico', compact(
            'user',
            'vagas',
            'avaliacoes',
            'mediaAvaliacoes',
            'totalAvaliacoes',
            'podeAvaliar',
            'vagasAvaliaveis'
        ));
    }
}
