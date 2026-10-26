<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SOS Trabalho - Empresas Parceiras</title>
    @vite(['resources/css/mainEmp.css', 'resources/js/components.js', 'resources/css/components.css', 'resources/css/notificacoes.css', 'resources/js/notificacoes.js'])
    <style>
        .empresa-inicial{width:70px;height:70px;border-radius:50%;background:#e8f0ff;color:#2e58a4;border:2px solid #c7d7f5;
            display:flex;align-items:center;justify-content:center;font-size:30px;font-weight:700;margin-bottom:15px;}
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
                <li><a href="{{ route('empresas') }}" class="ativo">Empresas</a></li>
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
        <section class="empresas-list">
            <h1>Empresas Parceiras</h1>

            @if ($empresas->isEmpty())
                <p style="text-align:center;color:#555;">
                    Ainda não há empresas cadastradas. Seja a primeira!
                    <a href="{{ route('cadastro') }}">Cadastre sua empresa</a>.
                </p>
            @else
                <div class="empresas-grid">
                    @foreach ($empresas as $empresa)
                        <div class="empresa-card">
                            @if ($empresa->foto)
                                <img src="{{ asset('storage/' . $empresa->foto) }}" alt="Logo {{ $empresa->name }}" />
                            @else
                                <div class="empresa-inicial">{{ mb_strtoupper(mb_substr($empresa->name, 0, 1)) }}</div>
                            @endif
                            <h3>{{ $empresa->name }}</h3>
                            <p>
                                {{ \Illuminate\Support\Str::limit($empresa->biografia ?: 'Empresa parceira do SOS Trabalho.', 110) }}
                                <br>
                                <small>
                                    📍 {{ $empresa->cidade ?: 'Brasil' }}{{ $empresa->sigla ? '/' . $empresa->sigla : '' }}
                                    • {{ $empresa->vagas_abertas }}
                                    {{ $empresa->vagas_abertas == 1 ? 'vaga aberta' : 'vagas abertas' }}
                                </small>
                            </p>
                            <a class="btn-detalhes" href="{{ route('perfil.publico', $empresa->id) }}">Ver perfil e vagas</a>
                        </div>
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
    @include('partials.toasts')
</body>

</html>
