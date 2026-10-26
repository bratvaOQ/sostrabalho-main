<?php

namespace App\Http\Controllers;

use App\Services\EmpresasService;

class EmpresasController extends Controller
{
    // Empresas REAIS cadastradas (usuários do tipo "empresa")
    public function empresas()
    {
        $empresas = EmpresasService::listar();

        return view('empresas', compact('empresas'));
    }
}
