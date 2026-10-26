<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SOS Trabalho - Vagas</title>
    @vite(['resources/css/mainVag.css', 'resources/js/mainVag.js', 'resources/js/components.js', 'resources/css/components.css', 'resources/css/notificacoes.css', 'resources/js/notificacoes.js'])
</head>

<body>
    <header class="navbar">
        <div class="logo">
            <a href="{{ route('home') }}">
                <img src="{{ asset('assets/img/logotipo-sos-trabalho-sem-fundo.png') }}" alt="SOS Trabalho Logo">
            </a>
        </div>
        <nav>
            <ul>
                @auth
                    <li class="nav-notificacoes">
                        <a href="{{ route('notificacoes') }}" id="btn-notificacoes" title="Notificações">
                            🔔 <span class="badge-notificacoes" id="badge-notif" style="display:none;">0</span>
                        </a>
                        <div class="dropdown-notificacoes" id="dropdown-notif">
                            <div class="dropdown-header">
                                <strong>Notificações</strong>
                                <a href="{{ route('notificacoes') }}">Ver todas</a>
                            </div>
                            <div class="dropdown-body" id="dropdown-notif-body">
                                <p class="dropdown-vazio">Carregando...</p>
                            </div>
                        </div>
                    </li>
                @endauth

                <li><a href="{{ route('home') }}">Início</a></li>
                <li><a href="{{ route('vagas') }}" class="ativo">Vagas</a></li>
                <li><a href="{{ route('empresas') }}">Empresas</a></li>
                <li><a href="{{ route('perfil') }}">Perfil</a></li>
                <li><a href="{{ route('anunciar') }}">Anunciar Vaga</a></li>

                @auth
                    <li><a href="{{ route('favoritos') }}">Favoritos</a></li>
                    <li><a href="{{ route('mensagens') }}">Mensagens</a></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn-logout">Sair</button>
                        </form>
                    </li>
                @else
                    <li><a href="{{ route('login') }}">Login</a></li>
                    <li><a href="{{ route('cadastro') }}" class="btn-cadastrar">Cadastre-se</a></li>
                @endauth
            </ul>
        </nav>
    </header>

    <main class="main-container">
        <section class="vagas-header">
            <h1>Vagas Disponíveis</h1>

            {{-- Mensagens --}}
            @if (session('sucesso'))
                <div class="msg-sucesso">{{ session('sucesso') }}</div>
            @endif
            @if (session('erro'))
                <div class="msg-erro">{{ session('erro') }}</div>
            @endif

            <form action="{{ route('vagas') }}" method="GET" class="busca-form">
                <div class="campo-busca">
                    <label>🔍 Buscar por</label>
                    <input type="text" name="busca" placeholder="Título, cargo, palavra-chave..."
                        value="{{ request('busca') }}" class="input-busca" />
                </div>

                <div class="campo-busca">
                    <label>📍 Cidade</label>
                    <input type="text" name="cidade" list="lista-cidades" placeholder="Digite ou escolha uma cidade"
                        value="{{ request('cidade') }}" class="input-busca" />
                    <datalist id="lista-cidades">
                        @foreach ($cidadesDisponiveis as $c)
                            <option value="{{ $c->cidade }}">{{ $c->sigla }}</option>
                        @endforeach
                    </datalist>
                </div>

                <div class="campo-busca">
                    <label>🏷️ Categoria</label>
                    <select name="categoria" class="select-nativo">
                        <option value="">Todas as categorias</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}"
                                {{ request('categoria') == $categoria->id ? 'selected' : '' }}>
                                {{ $categoria->titulo }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="botoes-busca">
                    <button type="submit" class="btn-buscar">Buscar</button>
                    @if (request('busca') || request('cidade') || request('categoria'))
                        <a href="{{ route('vagas') }}" class="btn-limpar">Limpar</a>
                    @endif
                </div>
            </form>
            <div class="filtros-extras">
                <button type="button" id="btn-proximidade" class="btn-proximidade">
                    📍 Ordenar por proximidade
                </button>
                @if (request('lat'))
                    <span class="proximidade-ativa">
                        📍 Ordenado por proximidade
                        @if (request('raio'))
                            (raio de {{ request('raio') }}km)
                        @endif
                    </span>
                    <a href="{{ route('vagas') }}" class="btn-limpar-proximidade">
                        ✕ Remover
                    </a>
                @endif
            </div>
        </section>

        <section class="vagas-list">
            @if ($vagas->isEmpty())
                <p class="sem-vagas">Nenhuma vaga encontrada.</p>
            @else
                <p class="total-vagas">
                    {{ $vagas->count() }} {{ $vagas->count() === 1 ? 'vaga encontrada' : 'vagas encontradas' }}
                </p>

                @foreach ($vagas as $vaga)
                    <article class="vaga-card">
                        <div class="vaga-info">
                            <h3>
                                {{ $vaga->titulo }}
                                @if (isset($vaga->distancia))
                                    <span class="vaga-distancia">📍 {{ number_format($vaga->distancia, 1, ',', '.') }}
                                        km</span>
                                @endif
                                @auth
                                    @if (Auth::id() != $vaga->usuario_id)
                                        <button type="button"
                                            class="btn-favoritar {{ in_array($vaga->id, $vagasFavoritadas) ? 'favoritado' : '' }}"
                                            data-id="{{ $vaga->id }}" title="Favoritar">
                                            <span
                                                class="coracao">{{ in_array($vaga->id, $vagasFavoritadas) ? '❤️' : '🤍' }}</span>
                                        </button>
                                    @endif
                                @endauth
                            </h3>
                            <p class="empresa">
                                <strong>
                                    <a href="{{ route('perfil.publico', $vaga->usuario_id) }}" class="link-usuario">
                                        {{ $vaga->anunciante_tipo === 'empresa' ? '🏢' : '👤' }}
                                        {{ $vaga->anunciante_nome }}
                                    </a>
                                </strong>
                                • 📍
                                @if ($vaga->bairro)
                                    {{ $vaga->bairro }} -
                                @endif
                                {{ $vaga->cidade }}/{{ $vaga->sigla }}
                            </p>
                            <p class="categoria-tag">{{ $vaga->categoria_nome }}</p>
                            <p class="descricao">
                                {{ $vaga->descricao_resumida ?? Str::limit($vaga->descricao_completa, 150) }}
                            </p>
                            <p class="salario">
                                <strong>Salário:</strong>
                                {{ $vaga->salario ? 'R$ ' . number_format($vaga->salario, 2, ',', '.') : 'A combinar' }}
                            </p>
                            {{-- ═══ EXPIRAÇÃO ═══ --}}
                            @php
                                $expiresAt = $vaga->expires_at ? \Carbon\Carbon::parse($vaga->expires_at) : null;
                                $diasRestantes = $expiresAt ? now()->diffInDays($expiresAt, false) : null;
                                $expirada = $diasRestantes !== null && $diasRestantes < 0;
                                // Só o dono da vaga pode renovar (visitante/outros usuários não)
                                $ehDono = Auth::check() && Auth::id() == $vaga->usuario_id;
                            @endphp

                            @if ($expiresAt)
                                <div class="vaga-expiracao">
                                    @if ($expirada)
                                        <span class="badge-expirada">⏰ Vaga expirada</span>
                                        @if ($ehDono)
                                            <button type="button" class="btn-renovar" data-id="{{ $vaga->id }}">
                                                🔄 Renovar por +30 dias
                                            </button>
                                        @endif
                                    @elseif ($diasRestantes <= 3)
                                        <span class="badge-expirando">⚠️ Expira em {{ ceil($diasRestantes) }}
                                            dias</span>
                                        @if ($ehDono)
                                            <button type="button" class="btn-renovar" data-id="{{ $vaga->id }}">
                                                🔄 Renovar
                                            </button>
                                        @endif
                                    @else
                                        <span class="badge-ativa">✓ Ativa por {{ ceil($diasRestantes) }} dias</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="vaga-actions">
                            <button class="btn-detalhes" data-id="{{ $vaga->id }}">Ver detalhes</button>

                            @auth
                                @if (Auth::id() == $vaga->usuario_id)
                                    {{-- É a vaga do próprio usuário --}}
                                    <span class="tag-propria">✓ Sua vaga</span>
                                @elseif (in_array($vaga->id, $vagasCandidatadas))
                                    {{-- Já se candidatou --}}
                                    <span class="tag-candidatado">✓ Já candidatado</span>
                                @else
                                    <a href="{{ route('candidatar', $vaga->id) }}"
                                        class="btn-candidatar">Candidatar-se</a>
                                @endif
                            @else
                                <a href="{{ route('login') }}" class="btn-candidatar">Fazer login para candidatar</a>
                            @endauth
                        </div>

                        <div class="detalhes-vaga" id="detalhes-{{ $vaga->id }}" style="display: none;">
                            <p><strong>Descrição completa:</strong> {{ $vaga->descricao_completa }}</p>
                            @if ($vaga->requisito)
                                <p><strong>Requisitos:</strong> {{ $vaga->requisito }}</p>
                            @endif
                            @if ($vaga->beneficio)
                                <p><strong>Benefícios:</strong> {{ $vaga->beneficio }}</p>
                            @endif
                        </div>
                    </article>
                @endforeach
            @endif
        </section>
    </main>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-left">
                <img src="{{ asset('assets/img/logotipo-sos-trabalho-sem-fundo.png') }}" alt="SOS Trabalho Logo"
                    class="footer-img">
            </div>
            <div class="footer-center">
                <p>© 2025 SOS Trabalho. Todos os direitos reservados.</p>
                @include('partials.footer-links')
            </div>
        </div>
    </footer>
    @include('partials.toasts')
</body>

</html>
