<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class IBGEService
{
    const IBGE_API = 'https://servicodados.ibge.gov.br/api/v1/localidades';

    /**
     * Buscar estados do IBGE
     */
    public static function getStates()
    {
        return Cache::remember('ibge_states', now()->addDays(30), function () {
            $response = Http::get(self::IBGE_API . '/estados');
            return $response->json();
        });
    }

    /**
     * Buscar cidades de um estado
     */
    public static function getCitiesByState($stateCode)
    {
        return Cache::remember("ibge_cities_{$stateCode}", now()->addDays(30), function () use ($stateCode) {
            $response = Http::get(self::IBGE_API . "/estados/{$stateCode}/municipios");
            return $response->json();
        });
    }

    /**
     * Calcular distância entre dois pontos (Haversine)
     */
    public static function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371; // Raio da Terra em km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $R * $c;

        return round($distance, 2);
    }

    /**
     * Ordenar jobs por proximidade
     */
    public static function sortByProximity($jobs, $userLat, $userLon)
    {
        return $jobs->map(function ($job) use ($userLat, $userLon) {
            $job->distance = self::calculateDistance(
                $userLat,
                $userLon,
                $job->latitude,
                $job->longitude
            );
            return $job;
        })->sortBy('distance');
    }
}
