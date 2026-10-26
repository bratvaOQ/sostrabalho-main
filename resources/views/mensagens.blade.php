<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SOS Trabalho - Mensagens</title>
    @vite(['resources/css/mainChat.css', 'resources/css/components.css', 'resources/js/components.js', 'resources/css/notificacoes.css', 'resources/js/notificacoes.js'])
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
                    <li><a href="{{ route('mensagens') }}" class="ativo">Mensagens</a></li>
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
        <section class="mensagens-section">
            <h1>💬 Minhas Conversas</h1>

            @if (empty($conversas))
                <div class="sem-conversas">
                    <p>💬 Você ainda não tem conversas.</p>
                    <small>Você poderá conversar quando uma candidatura for aprovada.</small>
                    <br><br>
                    <a href="{{ route('vagas') }}" class="btn-acao">Ver vagas disponíveis</a>
                </div>
            @else
                <div class="conversas-lista">
                    @foreach ($conversas as $conversa)
                        <a href="{{ route('mensagens.conversa', $conversa->usuario_id) }}" class="conversa-item">
                            @if ($conversa->usuario_foto)
                                <img src="{{ asset('storage/' . $conversa->usuario_foto) }}"
                                    alt="{{ $conversa->usuario_nome }}" class="conversa-avatar">
                            @else
                                <div class="conversa-iniciais">
                                    {{ strtoupper(substr($conversa->usuario_nome, 0, 1)) }}
                                </div>
                            @endif

                            <div class="conversa-info">
                                <h3>
                                    {{ $conversa->usuario_tipo === 'empresa' ? '🏢' : '👤' }}
                                    {{ $conversa->usuario_nome }}
                                </h3>
                                <p class="ultima-mensagem">{{ Str::limit($conversa->ultima_mensagem, 60) }}</p>
                                <span class="conversa-data"
                                    data-utc="{{ \Carbon\Carbon::parse($conversa->ultima_mensagem_data)->toIso8601String() }}">
                                    {{ \Carbon\Carbon::parse($conversa->ultima_mensagem_data)->diffForHumans() }}
                                </span>
                            </div>

                            @if ($conversa->nao_lidas > 0)
                                <span class="badge-nao-lidas">{{ $conversa->nao_lidas }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
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

    <script>
        // Converte datas para "há X tempo" no fuso local
        document.querySelectorAll('.conversa-data').forEach(el => {
            const utc = el.getAttribute('data-utc');
            if (!utc) return;

            const data = new Date(utc);
            const agora = new Date();
            const diffMs = agora - data;
            const diffMin = Math.floor(diffMs / 60000);
            const diffHoras = Math.floor(diffMin / 60);
            const diffDias = Math.floor(diffHoras / 24);

            let texto;
            if (diffMin < 1) texto = 'agora mesmo';
            else if (diffMin < 60) texto = `há ${diffMin} min`;
            else if (diffHoras < 24) texto = `há ${diffHoras}h`;
            else if (diffDias < 7) texto = `há ${diffDias}d`;
            else texto = data.toLocaleDateString('pt-BR');

            el.textContent = texto;
        });
    </script>
    @include('partials.toasts')
</body>

</html>
