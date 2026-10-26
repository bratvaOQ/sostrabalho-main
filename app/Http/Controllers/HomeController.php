<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Services\EmpresasService;

class HomeController extends Controller
{
    public function home()
    {
        $categorias = Categoria::all();

        // Até 6 empresas reais (as com mais vagas abertas aparecem primeiro)
        $empresas = EmpresasService::listar(6);

        return view('home', compact('categorias', 'empresas'));
    }
}
