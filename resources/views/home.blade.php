<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SOS Trabalho</title>
    @vite(['resources/css/mainHom.css', 'resources/js/mainHom.js', 'resources/js/components.js', 'resources/css/components.css', 'resources/css/notificacoes.css', 'resources/js/notificacoes.js'])
    <style>
        .empresa-mini{display:flex;flex-direction:column;align-items:center;gap:8px;text-decoration:none;color:#2e58a4;width:150px;}
        .empresa-mini img{height:90px;object-fit:contain;}
        .empresa-mini-inicial{width:90px;height:90px;border-radius:50%;background:#f8faff;border:1px solid #c7d7f5;display:flex;
            align-items:center;justify-content:center;font-size:36px;font-weight:700;}
        .empresa-mini-nome{font-size:14px;font-weight:500;text-align:center;word-break:break-word;}

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

                <li><a href="{{ route('home') }}" class="ativo">Início</a></li>
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

    <section class="hero">
        <div class="hero-slider">
            <div class="slide active" style="background-image: url('{{ asset('assets/img/carrosel-um.jpg') }}');">
            </div>
            <div class="slide" style="background-image: url('{{ asset('assets/img/carrosel-dois.jpg') }}');"></div>
            <div class="slide" style="background-image: url('{{ asset('assets/img/carrosel-tres.jpg') }}');"></div>
        </div>
        <div class="hero-content">
            <h1>Encontre as melhores vagas de emprego</h1>
            <form action="{{ route('vagas') }}" method="GET" class="search-box">
                <input type="text" name="busca" id="input-busca" placeholder="Vaga ou palavra-chave">
                <button type="submit" id="buscar">Buscar</button>
            </form>
        </div>
    </section>

    <section class="categorias">
        <h2>Categorias Populares</h2>
        <div class="categorias-container">
            @foreach ($categorias as $categoria)
                <a href="{{ route('vagas', ['categoria' => $categoria->id]) }}" class="categoria">
                    <div class="categoria-icone">
                        {{ substr($categoria->titulo, 0, 1) }}
                    </div>
                    <p>{{ $categoria->titulo }}</p>
                </a>
            @endforeach
        </div>
    </section>

    @if ($empresas->isNotEmpty())
    <section class="empresas">
        <h2>Empresas Parceiras</h2>
        <div class="empresas-container">
            @foreach ($empresas as $empresa)
                <a href="{{ route('perfil.publico', $empresa->id) }}" class="empresa-mini" title="{{ $empresa->name }}">
                    @if ($empresa->foto)
                        <img src="{{ asset('storage/' . $empresa->foto) }}" alt="Logo {{ $empresa->name }}">
                    @else
                        <span class="empresa-mini-inicial">{{ mb_strtoupper(mb_substr($empresa->name, 0, 1)) }}</span>
                    @endif
                    <span class="empresa-mini-nome">{{ $empresa->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
    @endif

    <section class="depoimento">
        <h2>O que dizem nossos usuários</h2>
        <div class="depoimentodousuario">
            <img src="{{ asset('assets/img/homem.jpeg') }}" alt="Imagem do Usuário">
            <div>
                <h3>Lucas Alexandre</h3>
                <p class="cargo">Design</p>
                <p class="texto">"Consegui o emprego dos sonhos graças à SOS Trabalho, o site me mostrou uma vaga
                    perfeita para minha carreira."</p>
            </div>
        </div>
    </section>

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
