<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SOS Trabalho - Candidatar-se</title>
    @vite(['resources/css/mainVag.css', 'resources/js/components.js', 'resources/css/components.css', 'resources/css/notificacoes.css', 'resources/js/notificacoes.js'])
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
        <section class="candidatura-section">
            <a href="{{ route('vagas') }}" class="btn-voltar">← Voltar para vagas</a>

            <h1>Candidatar-se à vaga</h1>

            {{-- Resumo da vaga --}}
            <div class="vaga-resumo">
                <h2>{{ $vaga->titulo }}</h2>
                <p class="vaga-anunciante">
                    <strong>
                        <a href="{{ route('perfil.publico', $vaga->usuario_id) }}" class="link-usuario">
                            {{ $vaga->anunciante_tipo === 'empresa' ? '🏢' : '👤' }} {{ $vaga->anunciante_nome }}
                        </a>
                    </strong>
                    • 📍 {{ $vaga->cidade }}/{{ $vaga->sigla }}
                </p>
                <span class="tag-categoria">{{ $vaga->categoria_nome }}</span>
                <p class="vaga-salario">
                    💰 <strong>Salário:</strong>
                    {{ $vaga->salario ? 'R$ ' . number_format($vaga->salario, 2, ',', '.') : 'A combinar' }}
                </p>

                @if ($vaga->descricao_resumida)
                    <div class="vaga-detalhe">
                        <strong>📌 Resumo:</strong>
                        <p>{{ $vaga->descricao_resumida }}</p>
                    </div>
                @endif

                <div class="vaga-detalhe">
                    <strong>📝 Descrição completa:</strong>
                    <p>{{ $vaga->descricao_completa }}</p>
                </div>

                @if ($vaga->requisito)
                    <div class="vaga-detalhe">
                        <strong>✅ Requisitos:</strong>
                        <p>{{ $vaga->requisito }}</p>
                    </div>
                @endif

                @if ($vaga->beneficio)
                    <div class="vaga-detalhe">
                        <strong>🎁 Benefícios:</strong>
                        <p>{{ $vaga->beneficio }}</p>
                    </div>
                @endif
            </div>

            @if ($errors->any())
                <div class="msg-erro">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Formulário de candidatura --}}
            <form action="{{ route('candidatar.store', $vaga->id) }}" method="POST" class="form-candidatura">
                @csrf

                <label>
                    Mensagem de apresentação <small>(opcional, mas recomendado!)</small>
                    <textarea name="mensagem" rows="6" maxlength="1000"
                        placeholder="Fale um pouco sobre você e por que se interessou por essa vaga. 
Exemplo: experiência, disponibilidade, motivação...">{{ old('mensagem') }}</textarea>
                </label>

                <div class="candidatura-actions">
                    <button type="submit" class="btn-confirmar">Enviar Candidatura</button>
                    <a href="{{ route('vagas') }}" class="btn-cancelar">Cancelar</a>
                </div>
            </form>
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
