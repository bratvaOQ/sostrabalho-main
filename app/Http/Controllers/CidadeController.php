<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CidadeController extends Controller
{
    // Retorna todas as cidades por estado (usando sigla)
    public function porEstado($sigla)
    {
        $sigla = strtoupper($sigla);

        // Cache por 30 dias (as cidades quase não mudam)
        return Cache::remember("cidades_ibge_{$sigla}", 60 * 60 * 24 * 30, function () use ($sigla) {
            $response = Http::get("https://servicodados.ibge.gov.br/api/v1/localidades/estados/{$sigla}/municipios");

            if ($response->successful()) {
                return $response->json();
            }

            return [];
        });
    }

    // Busca de cidades (autocomplete)
    public function buscar(Request $request)
    {
        $termo = $request->get('q', '');
        $sigla = strtoupper($request->get('estado', ''));

        if (strlen($termo) < 2 || !$sigla) {
            return response()->json([]);
        }

        $cidades = $this->porEstado($sigla);

        $filtradas = collect($cidades)
            ->filter(function ($cidade) use ($termo) {
                return stripos($cidade['nome'], $termo) !== false;
            })
            ->take(10)
            ->pluck('nome')
            ->values();

        return response()->json($filtradas);
    }

    public function bairros(Request $request)
    {
        $cidade = $request->get('cidade', '');
        $estado = $request->get('estado', '');
        $termo = $request->get('q', '');

        if (empty($cidade) || empty($estado) || strlen($termo) < 2) {
            return response()->json([]);
        }

        $cacheKey = "bairros_v2_" . md5("{$cidade}_{$estado}");

        $todosBairros = \Illuminate\Support\Facades\Cache::remember($cacheKey, 86400, function () use ($cidade, $estado) {
            try {
                $bairros = [];

                // ✨ ViaCEP: busca ampla por bairros da cidade
                $response = \Illuminate\Support\Facades\Http::timeout(10)
                    ->get("https://viacep.com.br/ws/{$estado}/{$cidade}/a/json/");

                if ($response->successful()) {
                    $dados = $response->json();
                    if (is_array($dados)) {
                        $bairros = collect($dados)
                            ->pluck('bairro')
                            ->filter()
                            ->unique()
                            ->values()
                            ->toArray();
                    }
                }

                // Se ViaCEP não retornar nada, tenta Nominatim
                if (empty($bairros)) {
                    $response = \Illuminate\Support\Facades\Http::timeout(5)
                        ->withHeaders(['User-Agent' => 'SOS Trabalho App'])
                        ->get('https://nominatim.openstreetmap.org/search', [
                            'city' => $cidade,
                            'state' => $estado,
                            'country' => 'Brasil',
                            'format' => 'json',
                            'addressdetails' => 1,
                            'limit' => 50,
                        ]);

                    if ($response->successful()) {
                        $dados = $response->json();
                        $bairros = collect($dados)
                            ->flatMap(function ($item) {
                                $b = [];
                                $address = $item['address'] ?? [];
                                foreach (['suburb', 'neighbourhood', 'quarter', 'city_district', 'residential', 'hamlet'] as $tipo) {
                                    if (!empty($address[$tipo])) {
                                        $b[] = $address[$tipo];
                                    }
                                }
                                return $b;
                            })
                            ->filter()
                            ->unique()
                            ->values()
                            ->toArray();
                    }
                }

                return $bairros;
            } catch (\Exception $e) {
                return [];
            }
        });

        // Filtra pelo termo digitado
        $filtrados = collect($todosBairros)
            ->filter(function ($bairro) use ($termo) {
                return stripos($bairro, $termo) !== false;
            })
            ->take(10)
            ->values()
            ->toArray();

        return response()->json($filtrados);
    }
}
