<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Descobre latitude/longitude de um endereço (OpenStreetMap/Nominatim).
 * Mesma lógica usada no AnunciarController do site; falhou? devolve nulls e segue o baile.
 */
class GeocodingService
{
    public static function coordenadas(string $cidade, string $estado, ?string $bairro = null): array
    {
        if ($bairro) {
            $coords = self::buscar("{$bairro}, {$cidade}, {$estado}, Brasil");
            if ($coords['latitude']) {
                return $coords;
            }
        }

        return self::buscar("{$cidade}, {$estado}, Brasil");
    }

    private static function buscar(string $query): array
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders(['User-Agent' => 'SOS Trabalho App'])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $query,
                    'format' => 'json',
                    'limit' => 1,
                    'countrycodes' => 'br',
                ]);

            if ($response->successful()) {
                $dados = $response->json();
                if (!empty($dados[0])) {
                    return [
                        'latitude' => $dados[0]['lat'],
                        'longitude' => $dados[0]['lon'],
                    ];
                }
            }
        } catch (\Throwable $e) {
            // sem internet / limite do serviço: ignora
        }

        return ['latitude' => null, 'longitude' => null];
    }
}
