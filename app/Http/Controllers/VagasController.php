<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VagasController extends Controller
{
    public function vagas(Request $request)
    {
        $query = DB::table('vagas')
            ->join('categorias', 'vagas.categorias_id', '=', 'categorias.id')
            ->join('users', 'vagas.usuario_id', '=', 'users.id')
            ->select(
                'vagas.*',
                'categorias.titulo as categoria_nome',
                'users.name as anunciante_nome',
                'users.tipo as anunciante_tipo'
            );

        // Busca por palavra-chave
        if ($request->filled('busca')) {
            $termo = $request->busca;
            $query->where(function ($q) use ($termo) {
                $q->where('vagas.titulo', 'like', "%{$termo}%")
                    ->orWhere('vagas.descricao_completa', 'like', "%{$termo}%")
                    ->orWhere('vagas.descricao_resumida', 'like', "%{$termo}%");
            });
        }

        if ($request->filled('cidade')) {
            $query->where('vagas.cidade', $request->cidade);
        }

        if ($request->filled('categoria')) {
            $query->where('vagas.categorias_id', $request->categoria);
        }

        // ✨ ORDENAR POR PROXIMIDADE (com raio)
        if ($request->filled('lat') && $request->filled('lng')) {
            $lat = floatval($request->lat);
            $lng = floatval($request->lng);
            $raio = $request->filled('raio') ? floatval($request->raio) : null; // km

            $query->selectRaw("
            (6371 * acos(
                cos(radians(?)) * cos(radians(vagas.latitude)) *
                cos(radians(vagas.longitude) - radians(?)) +
                sin(radians(?)) * sin(radians(vagas.latitude))
            )) AS distancia
        ", [$lat, $lng, $lat])
                ->whereNotNull('vagas.latitude')
                ->whereNotNull('vagas.longitude');

            // Aplica raio se especificado
            if ($raio) {
                $query->havingRaw('distancia <= ?', [$raio]);
            }

            $query->orderBy('distancia', 'asc');
        } else {
            $query->orderBy('vagas.id', 'desc');
        }

        $vagas = $query->get();
        $categorias = DB::table('categorias')->orderBy('titulo')->get();

        $cidadesDisponiveis = DB::table('vagas')
            ->select('cidade', 'sigla')
            ->distinct()
            ->orderBy('cidade')
            ->get();

        $vagasCandidatadas = [];
        $vagasFavoritadas = [];
        if (Auth::check()) {
            $vagasCandidatadas = DB::table('candidaturas')
                ->where('usuario_id', Auth::id())
                ->pluck('vaga_id')
                ->toArray();

            $vagasFavoritadas = DB::table('favoritos')
                ->where('usuario_id', Auth::id())
                ->pluck('vaga_id')
                ->toArray();
        }

        return view('vagas', compact('vagas', 'categorias', 'cidadesDisponiveis', 'vagasCandidatadas', 'vagasFavoritadas'));
    }
}
