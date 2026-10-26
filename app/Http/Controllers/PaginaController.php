<?php

namespace App\Http\Controllers;

class PaginaController extends Controller
{
    // GET /sobre, /como-funciona, /ajuda, /termos, /privacidade
    public function show(string $slug)
    {
        $pagina = config("paginas.{$slug}");

        if (!$pagina) {
            abort(404);
        }

        return view('pagina', ['slug' => $slug, 'pagina' => $pagina]);
    }
}
