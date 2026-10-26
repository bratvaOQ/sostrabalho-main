<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

/**
 * Páginas estáticas (mesmo conteúdo do site, vem de config/paginas.php).
 */
class PaginaController extends Controller
{
    // GET /api/v1/paginas
    public function index()
    {
        $lista = [];
        foreach (config('paginas') as $slug => $p) {
            $lista[] = [
                'slug' => $slug,
                'titulo' => $p['titulo'],
                'icone' => $p['icone'] ?? 'article',
                'resumo' => $p['resumo'] ?? '',
            ];
        }

        return response()->json(['data' => $lista]);
    }

    // GET /api/v1/paginas/{slug}
    public function show(string $slug)
    {
        $p = config("paginas.{$slug}");

        if (!$p) {
            return response()->json(['message' => 'Página não encontrada.'], 404);
        }

        return response()->json(['pagina' => ['slug' => $slug] + $p]);
    }
}
