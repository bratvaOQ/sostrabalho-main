<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SOS Trabalho - Chat com {{ $outroUsuario->name }}</title>
    @vite(['resources/css/mainChat.css', 'resources/css/components.css', 'resources/js/mainChat.js', 'resources/js/components.js', 'resources/css/notificacoes.css', 'resources/js/notificacoes.js'])
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
        <section class="chat-section">
            <a href="{{ route('mensagens') }}" class="btn-voltar">← Voltar</a>

            <div class="chat-header">
                @if ($outroUsuario->foto)
                    <img src="{{ asset('storage/' . $outroUsuario->foto) }}" alt="{{ $outroUsuario->name }}"
                        class="chat-avatar">
                @else
                    <div class="chat-iniciais">
                        {{ strtoupper(substr($outroUsuario->name, 0, 1)) }}
                    </div>
                @endif
                <div class="chat-header-info">
                    <h2>
                        <a href="{{ route('perfil.publico', $outroUsuario->id) }}" class="link-usuario">
                            {{ $outroUsuario->tipo === 'empresa' ? '🏢' : '👤' }}
                            {{ $outroUsuario->name }}
                        </a>
                    </h2>
                    @if (isset($vaga) && $vaga)
                        <p class="chat-vaga-info">
                            📢 Vaga: <strong>{{ $vaga->titulo }}</strong>
                        </p>
                    @endif
                </div>
            </div>

            <div class="chat-mensagens" id="chat-mensagens" data-outro-id="{{ $outroUsuario->id }}">
                @forelse ($mensagens as $msg)
                    <div class="mensagem {{ $msg->remetente_id == Auth::id() ? 'minha' : 'outra' }}"
                        data-id="{{ $msg->id }}">
                        <div class="mensagem-conteudo">
                            {{ $msg->mensagem }}
                        </div>
                        <span class="mensagem-hora">
                            {{-- ✨ Data em UTC ISO pro JS converter --}}
                            <time class="hora-local"
                                datetime="{{ \Carbon\Carbon::parse($msg->created_at)->toIso8601String() }}">
                                {{ \Carbon\Carbon::parse($msg->created_at)->format('H:i') }}
                            </time>
                            @if ($msg->remetente_id == Auth::id())
                                <span class="mensagem-status">
                                    @if ($msg->lida)
                                        <span class="status-lido" title="Lida">✓✓</span>
                                    @else
                                        <span class="status-enviado" title="Enviada">✓</span>
                                    @endif
                                </span>
                            @endif
                        </span>
                    </div>
                @empty
                    <div class="sem-mensagens">
                        <p>💬 Nenhuma mensagem ainda</p>
                        <small>Envie a primeira mensagem!</small>
                    </div>
                @endforelse
            </div>

            <form class="chat-form" id="chat-form" data-destinatario="{{ $outroUsuario->id }}">
                @csrf
                <textarea id="mensagem-input" placeholder="Digite sua mensagem..." rows="1" maxlength="5000" required></textarea>
                <button type="submit" class="btn-enviar" title="Enviar">
                    <span>➤</span>
                </button>
            </form>
        </section>
    </main>

    @include('partials.toasts')
</body>

</html>
