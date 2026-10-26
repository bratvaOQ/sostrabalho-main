<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SOS Trabalho - Candidatos da Vaga</title>
    @vite(['resources/css/mainAnu.css', 'resources/js/components.js', 'resources/css/components.css', 'resources/css/notificacoes.css', 'resources/js/notificacoes.js'])
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
        <section class="anunciar-section">
            <a href="{{ route('anunciar') }}" class="btn-voltar">← Voltar para minhas vagas</a>

            <h1>Candidatos da vaga</h1>

            <div class="vaga-info-topo">
                <h2>{{ $vaga->titulo }}</h2>
                <p>📍 {{ $vaga->cidade }}/{{ $vaga->sigla }}</p>
            </div>

            @if (session('sucesso'))
                <div class="msg-sucesso">{{ session('sucesso') }}</div>
            @endif

            @if ($candidatos->isEmpty())
                <div class="sem-candidatos">
                    <p>Ainda não há candidatos para esta vaga.</p>
                    <p><small>Aguarde! Quando alguém se candidatar, aparecerá aqui.</small></p>
                </div>
            @else
                <p class="total-candidatos">
                    <strong>{{ $candidatos->count() }}</strong>
                    {{ $candidatos->count() === 1 ? 'candidato' : 'candidatos' }}
                </p>

                @foreach ($candidatos as $candidato)
                    <div class="candidato-card">
                        <div class="candidato-header">
                            @if ($candidato->foto)
                                <img class="candidato-avatar" src="{{ asset('storage/' . $candidato->foto) }}"
                                    alt="Foto de {{ $candidato->name }}">
                            @else
                                <div class="candidato-iniciais">
                                    {{ strtoupper(substr($candidato->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', $candidato->name)[1] ?? '', 0, 1)) }}
                                </div>
                            @endif
                            <div class="candidato-info">
                                <h3>
                                    <a href="{{ route('perfil.publico', $candidato->usuario_id) }}"
                                        class="link-usuario">
                                        {{ $candidato->tipo === 'empresa' ? '🏢' : '👤' }} {{ $candidato->name }}
                                    </a>
                                </h3>
                                @if ($candidato->cargo)
                                    <p class="candidato-cargo">{{ $candidato->cargo }}</p>
                                @endif
                                @if ($candidato->cidade)
                                    <p class="candidato-local">📍 {{ $candidato->cidade }}/{{ $candidato->sigla }}</p>
                                @endif
                                <p class="candidato-email">📧 {{ $candidato->email }}</p>
                            </div>
                            <div class="candidato-status">
                                <form action="{{ route('candidatura.status', $candidato->id) }}" method="POST"
                                    class="form-status">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit" name="status" value="pendente"
                                        class="btn-status btn-status-pendente {{ $candidato->status === 'pendente' ? 'ativo' : '' }}">
                                        ⏳ Pendente
                                    </button>

                                    <button type="submit" name="status" value="aprovado"
                                        class="btn-status btn-status-aprovado {{ $candidato->status === 'aprovado' ? 'ativo' : '' }}">
                                        ✓ Aprovado
                                    </button>

                                    <button type="submit" name="status" value="rejeitado"
                                        class="btn-status btn-status-rejeitado {{ $candidato->status === 'rejeitado' ? 'ativo' : '' }}">
                                        ✗ Rejeitado
                                    </button>
                                </form>
                            </div>
                        </div>

                        @if ($candidato->biografia)
                            <div class="candidato-bio">
                                <strong>Sobre:</strong>
                                <p>{{ $candidato->biografia }}</p>
                            </div>
                        @endif

                        @if ($candidato->mensagem)
                            <div class="candidato-mensagem">
                                <strong>💬 Mensagem do candidato:</strong>
                                <p>{{ $candidato->mensagem }}</p>
                            </div>
                        @endif

                        <p class="candidato-data">
                            Candidatou-se em:
                            {{ \Carbon\Carbon::parse($candidato->created_at)->format('d/m/Y à\s H:i') }}
                        </p>
                    </div>
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
