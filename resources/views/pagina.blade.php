<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pagina['titulo'] }} - SOS Trabalho</title>
    @vite(['resources/css/mainEmp.css', 'resources/js/components.js', 'resources/css/components.css', 'resources/css/notificacoes.css', 'resources/js/notificacoes.js'])
    <style>
        .pagina-estatica{max-width:820px;margin:0 auto;background:#fff;border:1px solid #c7d7f5;border-radius:15px;
            padding:36px 40px;box-shadow:0 4px 12px rgba(0,0,0,.06);line-height:1.65;color:#33425f;}
        .pagina-estatica h1{color:#2e58a4;margin-bottom:6px;font-size:28px;}
        .pagina-estatica .atualizado{font-size:13px;color:#7a88a6;margin-bottom:22px;}
        .pagina-estatica h2{color:#2e58a4;font-size:19px;margin:26px 0 8px;}
        .pagina-estatica ul{padding-left:22px;}
        .pagina-estatica li{margin-bottom:6px;}
        .pagina-estatica .faq{border-top:1px solid #e4e8f4;padding:12px 0;}
        .pagina-estatica .faq strong{display:block;color:#2e58a4;margin-bottom:3px;}
        .pagina-links{display:flex;gap:10px;flex-wrap:wrap;justify-content:center;margin-bottom:24px;}
        .pagina-links a{padding:7px 14px;border-radius:99px;border:1px solid #c7d7f5;background:#e8f0ff;color:#2e58a4;
            text-decoration:none;font-size:14px;}
        .pagina-links a.ativo{background:#2e58a4;color:#fff;}
        @media (max-width:600px){.pagina-estatica{padding:24px 18px;}}
    </style>
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
        <nav class="pagina-links">
            @foreach (config('paginas') as $s => $p)
                <a href="{{ route('pagina', $s) }}" class="{{ $s === $slug ? 'ativo' : '' }}">{{ $p['titulo'] }}</a>
            @endforeach
        </nav>

        <article class="pagina-estatica">
            <h1>{{ $pagina['titulo'] }}</h1>
            @if (!empty($pagina['atualizado_em']))
                <p class="atualizado">Última atualização: {{ $pagina['atualizado_em'] }}</p>
            @endif

            @foreach ($pagina['secoes'] as $secao)
                @if (!empty($secao['titulo']))
                    <h2>{{ $secao['titulo'] }}</h2>
                @endif
                @if (!empty($secao['texto']))
                    <p>{{ $secao['texto'] }}</p>
                @endif
                @if (!empty($secao['itens']))
                    @if (is_array($secao['itens'][0]))
                        @foreach ($secao['itens'] as $item)
                            <div class="faq">
                                <strong>{{ $item['p'] }}</strong>
                                {{ $item['r'] }}
                            </div>
                        @endforeach
                    @else
                        <ul>
                            @foreach ($secao['itens'] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    @endif
                @endif
            @endforeach
        </article>
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
