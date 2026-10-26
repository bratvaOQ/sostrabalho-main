<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Support\Facades\DB;

/**
 * Funções compartilhadas pelos controllers da API do app:
 * formatação de JSON (vaga, usuário...) e regras de permissão (chat / avaliação).
 */
trait ApiHelpers
{
    // ─────────────────────────── FORMATAÇÃO ───────────────────────────

    protected function fotoUrl($foto): ?string
    {
        return $foto ? asset('storage/' . ltrim($foto, '/')) : null;
    }

    protected function iso($data): ?string
    {
        return $data ? \Carbon\Carbon::parse($data)->toIso8601String() : null;
    }

    /** Consulta base de vagas (com categoria e anunciante). */
    protected function vagasBase()
    {
        return DB::table('vagas')
            ->join('categorias', 'vagas.categorias_id', '=', 'categorias.id')
            ->join('users', 'vagas.usuario_id', '=', 'users.id')
            ->select(
                'vagas.*',
                'categorias.titulo as categoria_nome',
                'users.name as anunciante_nome',
                'users.tipo as anunciante_tipo',
                'users.foto as anunciante_foto'
            );
    }

    /** Só vagas que ainda não venceram. */
    protected function apenasAtivas($query)
    {
        return $query->where('vagas.is_expired', false)
            ->where(function ($q) {
                $q->whereNull('vagas.expires_at')->orWhere('vagas.expires_at', '>', now());
            });
    }

    /** Favoritos e candidaturas do usuário, pra marcar nas listas sem N+1. */
    protected function contextoUsuario($userId): array
    {
        $favoritos = DB::table('favoritos')->where('usuario_id', $userId)->pluck('vaga_id')->all();

        $candidaturas = [];
        foreach (DB::table('candidaturas')->where('usuario_id', $userId)->get(['id', 'vaga_id', 'status']) as $c) {
            $candidaturas[$c->vaga_id] = ['id' => (int) $c->id, 'status' => $c->status];
        }

        return ['favoritos' => $favoritos, 'candidaturas' => $candidaturas, 'user_id' => $userId];
    }

    protected function formatarVaga($v, array $ctx, bool $completa = true): array
    {
        $exp = $v->expires_at ? \Carbon\Carbon::parse($v->expires_at) : null;
        $expirada = (bool) $v->is_expired || ($exp && $exp->isPast());
        $dias = $exp ? (int) ceil(($exp->getTimestamp() - time()) / 86400) : null;

        $local = trim(($v->bairro ? $v->bairro . ' - ' : '') . $v->cidade . '/' . $v->sigla);

        $dados = [
            'id' => (int) $v->id,
            'titulo' => $v->titulo,
            'empresa' => $v->empresa,
            'anunciante' => [
                'id' => (int) $v->usuario_id,
                'nome' => $v->anunciante_nome,
                'tipo' => $v->anunciante_tipo,
                'foto_url' => $this->fotoUrl($v->anunciante_foto ?? null),
            ],
            'categoria' => ['id' => (int) $v->categorias_id, 'titulo' => $v->categoria_nome],
            'descricao_resumida' => $v->descricao_resumida,
            'salario' => $v->salario !== null ? (float) $v->salario : null,
            'cidade' => $v->cidade,
            'bairro' => $v->bairro ?? null,
            'estado' => $v->estado,
            'sigla' => $v->sigla,
            'localizacao' => $local,
            'expira_em' => $this->iso($v->expires_at),
            'dias_restantes' => $dias,
            'expirada' => $expirada,
            'minha' => (int) $v->usuario_id === (int) $ctx['user_id'],
            'favoritada' => in_array($v->id, $ctx['favoritos']),
            'candidatura' => $ctx['candidaturas'][$v->id] ?? null,
            'criada_em' => $this->iso($v->created_at),
        ];

        if ($completa) {
            $dados['descricao_completa'] = $v->descricao_completa;
            $dados['requisito'] = $v->requisito;
            $dados['beneficio'] = $v->beneficio;
            $dados['latitude'] = $v->latitude !== null ? (float) $v->latitude : null;
            $dados['longitude'] = $v->longitude !== null ? (float) $v->longitude : null;
        }

        return $dados;
    }

    /** Usuário "público" (o que qualquer logado pode ver). */
    protected function formatarUsuario($u, bool $privado = false): array
    {
        $stats = DB::table('avaliacoes')->where('avaliado_id', $u->id)
            ->selectRaw('avg(nota) as media, count(*) as total')->first();

        $dados = [
            'id' => (int) $u->id,
            'tipo' => $u->tipo,
            'name' => $u->name,
            'cargo' => $u->cargo,
            'biografia' => $u->biografia,
            'cidade' => $u->cidade,
            'estado' => $u->estado,
            'sigla' => $u->sigla,
            'foto_url' => $this->fotoUrl($u->foto),
            'media_avaliacoes' => round((float) ($stats->media ?? 0), 1),
            'total_avaliacoes' => (int) ($stats->total ?? 0),
            'membro_desde' => $this->iso($u->created_at),
        ];

        if ($privado) {
            $dados['email'] = $u->email;
            $dados['cnpj'] = $u->cnpj;
        }

        return $dados;
    }

    protected function paginado($paginador, array $itens): array
    {
        return [
            'data' => $itens,
            'meta' => [
                'pagina' => $paginador->currentPage(),
                'ultima_pagina' => $paginador->lastPage(),
                'total' => $paginador->total(),
            ],
        ];
    }

    // ─────────────────────────── PERMISSÕES ───────────────────────────

    /** Há candidatura APROVADA entre os dois (em qualquer sentido)? Libera chat e avaliação. */
    protected function vinculoAprovado($a, $b): bool
    {
        $nosDoisSentidos = function ($candidato, $anunciante) {
            return DB::table('candidaturas')
                ->join('vagas', 'candidaturas.vaga_id', '=', 'vagas.id')
                ->where('candidaturas.usuario_id', $candidato)
                ->where('vagas.usuario_id', $anunciante)
                ->where('candidaturas.status', 'aprovado')
                ->exists();
        };

        return $nosDoisSentidos($a, $b) || $nosDoisSentidos($b, $a);
    }

    protected function usuariosPermitidosChat($userId): array
    {
        $anunciantes = DB::table('candidaturas')
            ->join('vagas', 'candidaturas.vaga_id', '=', 'vagas.id')
            ->where('candidaturas.usuario_id', $userId)
            ->where('candidaturas.status', 'aprovado')
            ->pluck('vagas.usuario_id')->all();

        $candidatos = DB::table('candidaturas')
            ->join('vagas', 'candidaturas.vaga_id', '=', 'vagas.id')
            ->where('vagas.usuario_id', $userId)
            ->where('candidaturas.status', 'aprovado')
            ->pluck('candidaturas.usuario_id')->all();

        return array_values(array_unique(array_merge($anunciantes, $candidatos)));
    }
}
