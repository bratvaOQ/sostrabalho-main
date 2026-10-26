<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CandidaturaController extends Controller
{
    // Mostra o formulário de candidatura (com mensagem)
    public function create($vagaId)
    {
        $vaga = DB::table('vagas')
            ->join('users', 'vagas.usuario_id', '=', 'users.id')
            ->join('categorias', 'vagas.categorias_id', '=', 'categorias.id')
            ->where('vagas.id', $vagaId)
            ->select(
                'vagas.*',
                'users.name as anunciante_nome',
                'users.tipo as anunciante_tipo',
                'categorias.titulo as categoria_nome'
            )
            ->first();

        if (!$vaga) {
            return redirect()->route('vagas')->with('erro', 'Vaga não encontrada.');
        }

        if ($vaga->usuario_id == Auth::id()) {
            return redirect()->route('vagas')->with('erro', 'Você não pode se candidatar à sua própria vaga.');
        }

        $jaSeCandidatou = DB::table('candidaturas')
            ->where('vaga_id', $vagaId)
            ->where('usuario_id', Auth::id())
            ->exists();

        if ($jaSeCandidatou) {
            return redirect()->route('vagas')->with('erro', 'Você já se candidatou a esta vaga.');
        }

        return view('candidatar', compact('vaga'));
    }

    // Salva a candidatura
    public function store(Request $request, $vagaId)
    {
        $vaga = DB::table('vagas')
            ->join('categorias', 'vagas.categorias_id', '=', 'categorias.id')
            ->where('vagas.id', $vagaId)
            ->select('vagas.*', 'categorias.titulo as categoria_nome')
            ->first();

        if (!$vaga) {
            return redirect()->route('vagas')->with('erro', 'Vaga não encontrada.');
        }

        if ($vaga->usuario_id == Auth::id()) {
            return redirect()->route('vagas')->with('erro', 'Você não pode se candidatar à sua própria vaga.');
        }

        $jaSeCandidatou = DB::table('candidaturas')
            ->where('vaga_id', $vagaId)
            ->where('usuario_id', Auth::id())
            ->exists();

        if ($jaSeCandidatou) {
            return redirect()->route('vagas')->with('erro', 'Você já se candidatou a esta vaga.');
        }

        $request->validate([
            'mensagem' => 'nullable|max:1000',
        ]);

        DB::table('candidaturas')->insert([
            'vaga_id' => $vagaId,
            'usuario_id' => Auth::id(),
            'mensagem' => $request->mensagem,
            'status' => 'pendente',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ✨ ADICIONA AQUI - Notifica o anunciante
        \App\Http\Controllers\NotificacaoController::criar(
            $vaga->usuario_id,
            'nova_candidatura',
            Auth::user()->name . ' se candidatou à sua vaga "' . $vaga->titulo . '"',
            '👤',
            route('anunciar.candidatos', $vagaId),
            $vagaId
        );

        return redirect()->route('vagas')->with('sucesso', 'Candidatura enviada com sucesso!');
    }

    // Cancela candidatura (candidato)
    public function destroy($candidaturaId)
    {
        $candidatura = DB::table('candidaturas')
            ->where('id', $candidaturaId)
            ->where('usuario_id', Auth::id())
            ->first();

        if (!$candidatura) {
            return redirect()->route('perfil', ['aba' => 'candidaturas'])
                ->with('erro', 'Candidatura não encontrada.');
        }

        DB::table('candidaturas')->where('id', $candidaturaId)->delete();

        return redirect()->route('perfil', ['aba' => 'candidaturas'])
            ->with('sucesso', 'Candidatura cancelada.');
    }

    // Ver candidatos de uma vaga (anunciante)
    public function candidatos($vagaId)
    {
        $vaga = DB::table('vagas')
            ->where('id', $vagaId)
            ->where('usuario_id', Auth::id())
            ->first();

        if (!$vaga) {
            return redirect()->route('anunciar')
                ->with('erro', 'Vaga não encontrada ou você não tem permissão.');
        }

        $candidatos = DB::table('candidaturas')
            ->join('users', 'candidaturas.usuario_id', '=', 'users.id')
            ->where('candidaturas.vaga_id', $vagaId)
            ->select(
                'candidaturas.*',
                'users.id as usuario_id',
                'users.name',
                'users.email',
                'users.cargo',
                'users.biografia',
                'users.cidade',
                'users.sigla',
                'users.foto',
                'users.tipo'
            )
            ->orderBy('candidaturas.created_at', 'desc')
            ->get();

        return view('candidatos', compact('vaga', 'candidatos'));
    }

    // Anunciante muda o status da candidatura (aprovado/rejeitado/pendente)
    // Anunciante muda o status da candidatura (aprovado/rejeitado/pendente)
    public function atualizarStatus(Request $request, $candidaturaId)
    {
        $request->validate([
            'status' => 'required|in:pendente,aprovado,rejeitado',
        ]);

        $candidatura = DB::table('candidaturas')
            ->join('vagas', 'candidaturas.vaga_id', '=', 'vagas.id')
            ->where('candidaturas.id', $candidaturaId)
            ->where('vagas.usuario_id', Auth::id())
            ->select('candidaturas.*', 'vagas.id as vaga_id')
            ->first();

        if (!$candidatura) {
            return redirect()->route('anunciar')
                ->with('erro', 'Candidatura não encontrada ou você não tem permissão.');
        }

        DB::table('candidaturas')
            ->where('id', $candidaturaId)
            ->update([
                'status' => $request->status,
                'updated_at' => now(),
            ]);

        // ✨ ADICIONA AQUI - Notifica o candidato
        $statusTexto = [
            'aprovado' => '✅ Sua candidatura foi APROVADA!',
            'rejeitado' => '❌ Sua candidatura foi rejeitada',
            'pendente' => '⏳ Sua candidatura voltou para análise',
        ];

        $iconeTexto = [
            'aprovado' => '✅',
            'rejeitado' => '❌',
            'pendente' => '⏳',
        ];

        $vagaCompleta = DB::table('vagas')->where('id', $candidatura->vaga_id)->first();

        \App\Http\Controllers\NotificacaoController::criar(
            $candidatura->usuario_id,
            'status_candidatura',
            $statusTexto[$request->status] . ' - Vaga: ' . $vagaCompleta->titulo,
            $iconeTexto[$request->status],
            route('perfil', ['aba' => 'candidaturas']),
            $candidatura->vaga_id
        );

        return redirect()->route('anunciar.candidatos', $candidatura->vaga_id)
            ->with('sucesso', 'Status atualizado com sucesso!');
    }

    // Cancela TODAS as candidaturas do usuário logado
    public function limparTodas()
    {
        DB::table('candidaturas')
            ->where('usuario_id', Auth::id())
            ->delete();

        return redirect()->route('perfil', ['aba' => 'candidaturas'])
            ->with('sucesso', 'Todas as suas candidaturas foram canceladas.');
    }
}
