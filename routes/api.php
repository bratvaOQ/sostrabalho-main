<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CidadeController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VagaController;
use App\Http\Controllers\Api\MinhaVagaController;
use App\Http\Controllers\Api\CandidaturaController;
use App\Http\Controllers\Api\FavoritoController;
use App\Http\Controllers\Api\MensagemController;
use App\Http\Controllers\Api\NotificacaoController;
use App\Http\Controllers\Api\AvaliacaoController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\PaginaController;

/*
|--------------------------------------------------------------------------
| API do aplicativo (Flutter)  →  /api/v1/...
|--------------------------------------------------------------------------
| Login por token (Authorization: Bearer <token>). Mesmo banco e mesmas regras do site:
| quem se cadastra no app consegue entrar no site e vice-versa.
*/

Route::prefix('v1')->group(function () {

    // Usado pelo app pra testar se o endereço do servidor está certo
    Route::get('ping', fn() => response()->json(['ok' => true, 'app' => 'SOS Trabalho', 'api' => 1]));

    // ─── PÚBLICO ───
    Route::middleware('throttle:15,1')->group(function () {
        Route::post('auth/register', [AuthController::class, 'register']);
        Route::post('auth/login', [AuthController::class, 'login']);
    });

    Route::get('categorias', [VagaController::class, 'categorias']);
    Route::get('paginas', [PaginaController::class, 'index']);
    Route::get('paginas/{slug}', [PaginaController::class, 'show']);
    // ⚠️ /buscar ANTES de /{sigla}
    Route::get('cidades/buscar', [CidadeController::class, 'buscar']);
    Route::get('cidades/{sigla}', [CidadeController::class, 'porEstado']);

    // ─── LOGADO ───
    Route::middleware(['auth.token', 'throttle:240,1'])->group(function () {

        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::put('me', [AuthController::class, 'update']);

        // Vagas (⚠️ rotas fixas antes de /{id})
        Route::get('vagas', [VagaController::class, 'index']);
        Route::get('vagas/cidades', [VagaController::class, 'cidades']);
        Route::get('vagas/{id}', [VagaController::class, 'show'])->whereNumber('id');
        Route::post('vagas/{id}/candidatar', [CandidaturaController::class, 'store'])->whereNumber('id');

        // Anunciar (minhas vagas)
        Route::get('minhas-vagas', [MinhaVagaController::class, 'index']);
        Route::post('minhas-vagas', [MinhaVagaController::class, 'store']);
        Route::put('minhas-vagas/{id}', [MinhaVagaController::class, 'update'])->whereNumber('id');
        Route::delete('minhas-vagas/{id}', [MinhaVagaController::class, 'destroy'])->whereNumber('id');
        Route::post('minhas-vagas/{id}/renovar', [MinhaVagaController::class, 'renovar'])->whereNumber('id');
        Route::get('minhas-vagas/{id}/candidatos', [MinhaVagaController::class, 'candidatos'])->whereNumber('id');

        // Candidaturas
        Route::get('candidaturas', [CandidaturaController::class, 'index']);
        Route::delete('candidaturas', [CandidaturaController::class, 'limparTodas']);
        Route::delete('candidaturas/{id}', [CandidaturaController::class, 'destroy'])->whereNumber('id');
        Route::patch('candidaturas/{id}/status', [CandidaturaController::class, 'atualizarStatus'])->whereNumber('id');

        // Favoritos
        Route::get('favoritos', [FavoritoController::class, 'index']);
        Route::post('favoritos/{vagaId}/toggle', [FavoritoController::class, 'toggle'])->whereNumber('vagaId');

        // Empresas e perfis públicos
        Route::get('empresas', [UsuarioController::class, 'empresas']);
        Route::get('usuarios/{id}', [UsuarioController::class, 'show'])->whereNumber('id');

        // Avaliações
        Route::post('avaliacoes/{avaliadoId}', [AvaliacaoController::class, 'store'])->whereNumber('avaliadoId');
        Route::delete('avaliacoes/{id}', [AvaliacaoController::class, 'destroy'])->whereNumber('id');

        // Notificações (⚠️ fixas antes de /{id})
        Route::get('notificacoes', [NotificacaoController::class, 'index']);
        Route::get('notificacoes/contador', [NotificacaoController::class, 'contador']);
        Route::post('notificacoes/marcar-todas-lidas', [NotificacaoController::class, 'marcarTodasLidas']);
        Route::delete('notificacoes', [NotificacaoController::class, 'limparTodas']);
        Route::post('notificacoes/{id}/lida', [NotificacaoController::class, 'marcarLida'])->whereNumber('id');
        Route::delete('notificacoes/{id}', [NotificacaoController::class, 'destroy'])->whereNumber('id');

        // Mensagens (chat)
        Route::get('mensagens', [MensagemController::class, 'index']);
        Route::post('mensagens', [MensagemController::class, 'enviar']);
        Route::get('mensagens/{outroId}', [MensagemController::class, 'conversa'])->whereNumber('outroId');
    });
});
