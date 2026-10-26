<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SOS Trabalho - Notificações</title>
    @vite(['resources/css/mainHom.css', 'resources/css/notificacoes.css', 'resources/css/components.css', 'resources/js/notificacoes.js', 'resources/js/components.js'])
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
                @endauth
            </ul>
        </nav>
    </header>

    <main class="notif-page">
        <div class="notif-header-page">
            <h1>🔔 Notificações</h1>
            @if (count($notificacoes) > 0)
                <form action="{{ route('notificacoes.limpar') }}" method="POST"
                    onsubmit="return confirm('Limpar todas as notificações?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-limpar-notif">🗑️ Limpar tudo</button>
                </form>
            @endif
        </div>

        <div class="notif-lista">
            @if (count($notificacoes) === 0)
                <div class="notif-vazio">
                    <p>🔕 Nenhuma notificação</p>
                    <small>Você será avisado quando algo acontecer</small>
                </div>
            @else
                @foreach ($notificacoes as $n)
                    <div class="notif-item {{ !$n->lida ? 'nao-lida' : '' }}">
                        <span class="notif-icone">{{ $n->icone ?? '🔔' }}</span>
                        <div class="notif-conteudo">
                            @if ($n->link)
                                <a href="{{ $n->link }}" style="text-decoration:none; color:inherit;">
                                    <p class="notif-mensagem">{{ $n->mensagem }}</p>
                                </a>
                            @else
                                <p class="notif-mensagem">{{ $n->mensagem }}</p>
                            @endif
                            <span
                                class="notif-tempo">{{ \Carbon\Carbon::parse($n->created_at)->diffForHumans() }}</span>
                        </div>
                        <button class="btn-deletar-notif" onclick="deletarNotif({{ $n->id }}, this)"
                            title="Deletar">✕</button>
                    </div>
                @endforeach
            @endif
        </div>

        {{ $notificacoes->links() }}
    </main>

    <script>
        function deletarNotif(id, btn) {
            fetch(`/notificacoes/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            }).then(() => {
                btn.closest('.notif-item').remove();
                Toast.success('Notificação removida');
            });
        }
    </script>

    @include('partials.toasts')
</body>

</html>
