<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AnunciarController extends Controller
{
    // Exibe o formulário e a lista de vagas DO USUÁRIO LOGADO
    public function index()
    {
        $usuarioId = Auth::id();

        $vagas = DB::table('vagas')
            ->join('categorias', 'vagas.categorias_id', '=', 'categorias.id')
            ->where('vagas.usuario_id', $usuarioId)
            ->select(
                'vagas.*',
                'categorias.titulo as categoria_nome'
            )
            ->orderBy('vagas.id', 'desc')
            ->get();

        $categorias = DB::table('categorias')->orderBy('titulo')->get();

        return view('anunciar', compact('vagas', 'categorias'));
    }

    // Salva uma nova vaga
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|max:100',
            'descricao_completa' => 'required|max:5500',
            'requisito' => 'nullable|max:600',
            'beneficio' => 'nullable|max:1000',
            'salario' => 'nullable|numeric',
            'categorias_id' => 'required|exists:categorias,id',
            'cidade' => 'required|max:100',
            'bairro' => 'nullable|max:100',              // ✨ NOVO
            'estado' => 'required|max:60',
            'sigla' => 'required|max:2',
            'descricao_resumida' => 'nullable|max:260',
        ]);

        $user = Auth::user();

        // ✨ Geocodificar COM bairro
        $coords = $this->geocodificar($request->cidade, $request->estado, $request->bairro);

        DB::table('vagas')->insert([
            'titulo' => $request->titulo,
            'empresa' => $user->tipo === 'empresa' ? $user->name : null,
            'descricao_completa' => $request->descricao_completa,
            'requisito' => $request->requisito,
            'beneficio' => $request->beneficio,
            'salario' => $request->salario,
            'expires_at' => now()->addDays(30),
            'is_expired' => false,
            'categorias_id' => $request->categorias_id,
            'usuario_id' => $user->id,
            'cidade' => $request->cidade,
            'bairro' => $request->bairro,               // ✨ NOVO
            'estado' => $request->estado,
            'sigla' => strtoupper($request->sigla),
            'latitude' => $coords['latitude'],
            'longitude' => $coords['longitude'],
            'descricao_resumida' => $request->descricao_resumida,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('anunciar')->with('sucesso', 'Vaga publicada com sucesso!');
    }

    // Mostra o formulário de edição
    public function edit($id)
    {
        $vaga = DB::table('vagas')
            ->where('id', $id)
            ->where('usuario_id', Auth::id())
            ->first();

        if (!$vaga) {
            return redirect()->route('anunciar')->with('erro', 'Vaga não encontrada ou você não tem permissão.');
        }

        $categorias = DB::table('categorias')->orderBy('titulo')->get();

        return view('anunciar-editar', compact('vaga', 'categorias'));
    }

    // Atualiza a vaga
    public function update(Request $request, $id)
    {
        $vaga = DB::table('vagas')
            ->where('id', $id)
            ->where('usuario_id', Auth::id())
            ->first();

        if (!$vaga) {
            return redirect()->route('anunciar')->with('erro', 'Vaga não encontrada ou você não tem permissão.');
        }

        $request->validate([
            'titulo' => 'required|max:100',
            'descricao_completa' => 'required|max:5500',
            'requisito' => 'nullable|max:600',
            'beneficio' => 'nullable|max:1000',
            'salario' => 'nullable|numeric',
            'categorias_id' => 'required|exists:categorias,id',
            'cidade' => 'required|max:100',
            'bairro' => 'nullable|max:100',              // ✨ NOVO
            'estado' => 'required|max:60',
            'sigla' => 'required|max:2',
            'descricao_resumida' => 'nullable|max:260',
        ]);

        $user = Auth::user();

        // ✨ Geocodificar COM bairro
        $coords = $this->geocodificar($request->cidade, $request->estado, $request->bairro);

        DB::table('vagas')->where('id', $id)->update([
            'titulo' => $request->titulo,
            'empresa' => $user->tipo === 'empresa' ? $user->name : null,
            'descricao_completa' => $request->descricao_completa,
            'requisito' => $request->requisito,
            'beneficio' => $request->beneficio,
            'salario' => $request->salario,
            'categorias_id' => $request->categorias_id,
            'cidade' => $request->cidade,
            'bairro' => $request->bairro,               // ✨ NOVO
            'estado' => $request->estado,
            'sigla' => strtoupper($request->sigla),
            'latitude' => $coords['latitude'],
            'longitude' => $coords['longitude'],
            'descricao_resumida' => $request->descricao_resumida,
            'updated_at' => now(),
        ]);

        return redirect()->route('anunciar')->with('sucesso', 'Vaga atualizada com sucesso!');
    }

    // Exclui uma vaga
    public function destroy($id)
    {
        $vaga = DB::table('vagas')
            ->where('id', $id)
            ->where('usuario_id', Auth::id())
            ->first();

        if (!$vaga) {
            return redirect()->route('anunciar')->with('erro', 'Vaga não encontrada ou você não tem permissão.');
        }

        DB::table('vagas')->where('id', $id)->delete();

        return redirect()->route('anunciar')->with('sucesso', 'Vaga excluída com sucesso!');
    }

    // Renovar vaga (+ 30 dias)
    public function renovar($id)
    {
        $vaga = DB::table('vagas')
            ->where('id', $id)
            ->where('usuario_id', Auth::id())
            ->first();

        if (!$vaga) {
            return response()->json(['error' => 'Vaga não encontrada'], 404);
        }

        DB::table('vagas')->where('id', $id)->update([
            'expires_at' => now()->addDays(30),
            'is_expired' => false,
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Vaga renovada por mais 30 dias!',
            'expires_at' => now()->addDays(30)->format('d/m/Y'),
        ]);
    }

    // Geocodificação (pega latitude/longitude da cidade)
    private function geocodificar($cidade, $estado, $bairro = null)
    {
        // Tentativa 1: bairro + cidade + estado
        if ($bairro) {
            $coords = $this->buscarNominatim("{$bairro}, {$cidade}, {$estado}, Brasil");
            if ($coords['latitude']) {
                return $coords;
            }
        }

        // Tentativa 2: só cidade + estado
        return $this->buscarNominatim("{$cidade}, {$estado}, Brasil");
    }

    private function buscarNominatim($query)
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)
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
        } catch (\Exception $e) {
            // Ignora
        }

        return ['latitude' => null, 'longitude' => null];
    }
}
