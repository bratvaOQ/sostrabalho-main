<?php

use Illuminate\Support\Facades\Route;

// ─── CONTROLLERS ─────────────────────────────
use App\Http\Controllers\HomeController;
use App\Http\Controllers\VagasController;
use App\Http\Controllers\AnunciarController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\CandidaturaController;
use App\Http\Controllers\EmpresasController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\CadastroController;
use App\Http\Controllers\CidadeController;
use App\Http\Controllers\PaginaController;

// Novas features
use App\Http\Controllers\AvaliacaoController;
use App\Http\Controllers\NotificacaoController;
use App\Http\Controllers\MensagemController;
use App\Http\Controllers\FavoritoController;

// ═══════════════════════════════════════════════
// PUBLIC
// ═══════════════════════════════════════════════

Route::get('/', [HomeController::class, 'home'])->name('home');
Route::get('/vagas', [VagasController::class, 'vagas'])->name('vagas');
Route::get('/empresas', [EmpresasController::class, 'empresas'])->name('empresas');
Route::get('/usuario/{id}', [PerfilController::class, 'perfilPublico'])->name('perfil.publico');

// PÁGINAS ESTÁTICAS (conteúdo em config/paginas.php — o app usa o mesmo)
Route::get('/{slug}', [PaginaController::class, 'show'])
    ->whereIn('slug', ['sobre', 'como-funciona', 'ajuda', 'termos', 'privacidade'])
    ->name('pagina');

// LOGIN
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// CADASTRO
Route::get('/cadastro', [CadastroController::class, 'showCadastroForm'])->name('cadastro');
Route::post('/cadastro', [CadastroController::class, 'store'])->name('cadastro.store');

// ─── API IBGE (pública de verdade) ─────────────
// ⚠️ ORDEM IMPORTANTE! /buscar ANTES de /{sigla}
Route::get('/api/cidades/buscar', [CidadeController::class, 'buscar'])->name('api.cidades.buscar');
Route::get('/api/cidades/{sigla}', [CidadeController::class, 'porEstado'])->name('api.cidades');
// Route::get('/api/bairros', [CidadeController::class, 'bairros'])->name('api.bairros');

// ═══════════════════════════════════════════════
// AUTENTICADO
// ═══════════════════════════════════════════════

Route::middleware('auth')->group(function () {

    // ─── PERFIL ─────────────────────────
    Route::get('/perfil', [PerfilController::class, 'perfil'])->name('perfil');
    Route::post('/perfil', [PerfilController::class, 'update'])->name('perfil.update');

    // ─── ANUNCIAR VAGAS ─────────────────────
    Route::get('/anunciar', [AnunciarController::class, 'index'])->name('anunciar');
    Route::post('/anunciar', [AnunciarController::class, 'store'])->name('anunciar.store');
    Route::get('/anunciar/{id}/editar', [AnunciarController::class, 'edit'])->name('anunciar.edit');
    Route::put('/anunciar/{id}', [AnunciarController::class, 'update'])->name('anunciar.update');
    Route::delete('/anunciar/{id}', [AnunciarController::class, 'destroy'])->name('anunciar.destroy');
    Route::post('/vagas/{id}/renovar', [AnunciarController::class, 'renovar'])->name('vaga.renovar');

    // Ver candidatos de uma vaga
    Route::get('/anunciar/{id}/candidatos', [CandidaturaController::class, 'candidatos'])->name('anunciar.candidatos');

    // ─── CANDIDATURAS ───────────────────────
    Route::get('/vagas/{id}/candidatar', [CandidaturaController::class, 'create'])->name('candidatar');
    Route::post('/vagas/{id}/candidatar', [CandidaturaController::class, 'store'])->name('candidatar.store');
    Route::delete('/candidatura/{id}', [CandidaturaController::class, 'destroy'])->name('candidatura.destroy');
    Route::delete('/candidaturas/limpar-todas', [CandidaturaController::class, 'limparTodas'])->name('candidaturas.limpar');
    Route::patch('/candidatura/{id}/status', [CandidaturaController::class, 'atualizarStatus'])->name('candidatura.status');

    // ─── FAVORITOS ──────────────────────────
    Route::post('/favoritos/{vagaId}/toggle', [FavoritoController::class, 'toggle'])->name('favoritos.toggle');
    Route::get('/favoritos', [FavoritoController::class, 'index'])->name('favoritos');

    // ─── AVALIAÇÕES ─────────────────────────
    Route::post('/avaliar/{avaliadoId}', [AvaliacaoController::class, 'store'])->name('avaliacao.store');
    Route::delete('/avaliar/{id}', [AvaliacaoController::class, 'destroy'])->name('avaliacao.destroy');

    // ─── NOTIFICAÇÕES ───────────────────────
    Route::get('/notificacoes', [NotificacaoController::class, 'index'])->name('notificacoes');
    Route::get('/notificacoes/contador', [NotificacaoController::class, 'contador'])->name('notificacoes.contador');
    Route::get('/notificacoes/ultimas', [NotificacaoController::class, 'ultimas'])->name('notificacoes.ultimas');
    Route::post('/notificacoes/{id}/lida', [NotificacaoController::class, 'marcarLida'])->name('notificacoes.lida');
    Route::delete('/notificacoes/{id}', [NotificacaoController::class, 'destroy'])->name('notificacoes.destroy');
    Route::delete('/notificacoes/limpar/todas', [NotificacaoController::class, 'limparTodas'])->name('notificacoes.limpar');
    Route::post('/notificacoes/marcar-todas-lidas', [NotificacaoController::class, 'marcarTodasLidas'])->name('notificacoes.marcarTodasLidas');

    // ─── MENSAGENS (CHAT) ───────────────────
    // ⚠️ ORDEM IMPORTANTE! Rotas específicas ANTES das genéricas
    Route::get('/mensagens', [MensagemController::class, 'index'])->name('mensagens');
    Route::post('/mensagens/enviar', [MensagemController::class, 'enviar'])->name('mensagens.enviar');
    Route::get('/mensagens/{outroUsuarioId}/status', [MensagemController::class, 'statusMensagens'])->name('mensagens.status');
    Route::get('/mensagens/{outroUsuarioId}/novas/{ultimaId?}', [MensagemController::class, 'novasMensagens'])->name('mensagens.novas');
    Route::get('/mensagens/{outroUsuarioId}', [MensagemController::class, 'conversa'])->name('mensagens.conversa');
});
