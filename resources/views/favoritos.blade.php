<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SOS Trabalho - Favoritos</title>
    @vite(['resources/css/mainVag.css', 'resources/css/components.css', 'resources/js/mainVag.js', 'resources/js/components.js', 'resources/css/notificacoes.css', 'resources/js/notificacoes.js'])
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
                <li><a href="{{ route('vagas') }}">Vagas</a></li>
                <li><a href="{{ route('empresas') }}">Empresas</a></li>
                <li><a href="{{ route('perfil') }}">Perfil</a></li>
                <li><a href="{{ route('anunciar') }}">Anunciar Vaga</a></li>

                @auth
                    <li><a href="{{ route('favoritos') }}" class="ativo">Favoritos</a></li>
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
            <h1>❤️ Minhas Vagas Favoritas</h1>
        </section>

        <section class="vagas-list">
            @if ($favoritos->isEmpty())
                <div class="sem-vagas">
                    <p>Você ainda não favoritou nenhuma vaga.</p>
                    <a href="{{ route('vagas') }}" style="color: #2e58a4; text-decoration: none; font-weight: 500;">
                        Ver vagas disponíveis →
                    </a>
                </div>
            @else
                <p class="total-vagas">
                    {{ $favoritos->count() }}
                    {{ $favoritos->count() === 1 ? 'vaga favoritada' : 'vagas favoritadas' }}
                </p>

                @foreach ($favoritos as $vaga)
                    <article class="vaga-card">
                        <div class="vaga-info">
                            <h3>
                                {{ $vaga->titulo }}
                                <button type="button" class="btn-favoritar favoritado" data-id="{{ $vaga->id }}"
                                    title="Remover dos favoritos">
                                    <span class="coracao">❤️</span>
                                </button>
                            </h3>
                            <p class="empresa">
                                <strong>
                                    <a href="{{ route('perfil.publico', $vaga->usuario_id) }}" class="link-usuario">
                                        {{ $vaga->anunciante_tipo === 'empresa' ? '🏢' : '👤' }}
                                        {{ $vaga->anunciante_nome }}
                                    </a>
                                </strong>
                                📍 @if ($vaga->bairro)
                                    {{ $vaga->bairro }} -
                                @endif{{ $vaga->cidade }}/{{ $vaga->sigla }}
                            </p>
                            <p class="categoria-tag">{{ $vaga->categoria_nome }}</p>
                            <p class="descricao">
                                {{ $vaga->descricao_resumida ?? Str::limit($vaga->descricao_completa, 150) }}
                            </p>
                            <p class="salario">
                                <strong>Salário:</strong>
                                {{ $vaga->salario ? 'R$ ' . number_format($vaga->salario, 2, ',', '.') : 'A combinar' }}
                            </p>
                        </div>
                        <div class="vaga-actions">
                            @if (Auth::id() == $vaga->usuario_id)
                                <span class="tag-propria">✓ Sua vaga</span>
                            @elseif (in_array($vaga->id, $vagasCandidatadas))
                                <span class="tag-candidatado">✓ Já candidatado</span>
                            @else
                                <a href="{{ route('candidatar', $vaga->id) }}" class="btn-candidatar">Candidatar-se</a>
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
