<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Empresas REAIS (usuários com tipo = 'empresa') + quantidade de vagas abertas e nota média.
 * Usado pelo site (home / empresas) e pela API do app, assim os dois mostram a mesma coisa.
 */
class EmpresasService
{
    public static function listar(?int $limite = null, ?string $busca = null)
    {
        $query = DB::table('users')
            ->where('users.tipo', 'empresa')
            ->select('users.id', 'users.name', 'users.biografia', 'users.cidade', 'users.sigla', 'users.foto')
            ->selectSub(
                DB::table('vagas')
                    ->whereColumn('vagas.usuario_id', 'users.id')
                    ->where('vagas.is_expired', false)
                    ->where(function ($q) {
                        $q->whereNull('vagas.expires_at')->orWhere('vagas.expires_at', '>', now());
                    })
                    ->selectRaw('count(*)'),
                'vagas_abertas'
            )
            ->selectSub(
                DB::table('avaliacoes')
                    ->whereColumn('avaliacoes.avaliado_id', 'users.id')
                    ->selectRaw('avg(nota)'),
                'media_avaliacoes'
            );

        if ($busca) {
            $query->where('users.name', 'like', '%' . $busca . '%');
        }

        $query->orderByDesc('vagas_abertas')->orderBy('users.name');

        if ($limite) {
            $query->limit($limite);
        }

        return $query->get();
    }
}
