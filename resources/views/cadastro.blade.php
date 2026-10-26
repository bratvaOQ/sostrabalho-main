<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SOS Trabalho - Cadastro</title>
    @vite(['resources/css/mainCad.css', 'resources/js/mainCad.js', 'resources/js/components.js', 'resources/css/components.css', 'resources/css/notificacoes.css', 'resources/js/notificacoes.js'])
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
                    <li><a href="{{ route('cadastro') }}" class="btn-cadastrar" class="ativo">Cadastre-se</a></li>
                @endauth
            </ul>
        </nav>
    </header>

    <main class="main-content">
        <section class="form-section">
            <div class="form-container">
                <h2>Criar Conta</h2>

                @if ($errors->any())
                    <div class="msg-erro">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="form-cadastro" action="{{ route('cadastro.store') }}" method="POST">
                    @csrf

                    {{-- Tipo de conta --}}
                    <div class="tipo-conta">
                        <label class="tipo-opcao">
                            <input type="radio" name="tipo" value="pessoa"
                                {{ old('tipo', 'pessoa') === 'pessoa' ? 'checked' : '' }} />
                            <span>👤 Pessoa Física</span>
                        </label>
                        <label class="tipo-opcao">
                            <input type="radio" name="tipo" value="empresa"
                                {{ old('tipo') === 'empresa' ? 'checked' : '' }} />
                            <span>🏢 Empresa</span>
                        </label>
                    </div>

                    <label>
                        <span id="label-nome">Nome</span>
                        <input name="name" type="text" placeholder="Seu nome completo"
                            value="{{ old('name') }}" required />
                    </label>

                    {{-- Campo CNPJ (só aparece se marcar Empresa) --}}
                    <label id="campo-cnpj" style="display: none;">
                        CNPJ
                        <input name="cnpj" id="input-cnpj" type="text" placeholder="00.000.000/0000-00"
                            maxlength="18" value="{{ old('cnpj') }}" />
                    </label>

                    <label>Email
                        <input name="email" type="email" placeholder="Digite seu email" value="{{ old('email') }}"
                            required />
                    </label>

                    <label>Senha
                        <input name="password" type="password" placeholder="Mínimo 6 caracteres" required />
                    </label>

                    <label>Confirmar Senha
                        <input name="password_confirmation" type="password" placeholder="Repita a senha" required />
                    </label>

                    <label>Cidade
                        <input name="cidade" type="text" placeholder="Ex: Brasília" value="{{ old('cidade') }}" />
                    </label>

                    {{-- Select customizado de Estado --}}
                    <label>Estado
                        <div class="select-custom" id="estado-cadastro-wrapper">
                            <div class="select-selected placeholder">Selecione o estado</div>
                            <ul class="select-items select-hide">
                                <li data-value="Acre" data-sigla="AC">Acre</li>
                                <li data-value="Alagoas" data-sigla="AL">Alagoas</li>
                                <li data-value="Amapá" data-sigla="AP">Amapá</li>
                                <li data-value="Amazonas" data-sigla="AM">Amazonas</li>
                                <li data-value="Bahia" data-sigla="BA">Bahia</li>
                                <li data-value="Ceará" data-sigla="CE">Ceará</li>
                                <li data-value="Distrito Federal" data-sigla="DF">Distrito Federal</li>
                                <li data-value="Espírito Santo" data-sigla="ES">Espírito Santo</li>
                                <li data-value="Goiás" data-sigla="GO">Goiás</li>
                                <li data-value="Maranhão" data-sigla="MA">Maranhão</li>
                                <li data-value="Mato Grosso" data-sigla="MT">Mato Grosso</li>
                                <li data-value="Mato Grosso do Sul" data-sigla="MS">Mato Grosso do Sul</li>
                                <li data-value="Minas Gerais" data-sigla="MG">Minas Gerais</li>
                                <li data-value="Pará" data-sigla="PA">Pará</li>
                                <li data-value="Paraíba" data-sigla="PB">Paraíba</li>
                                <li data-value="Paraná" data-sigla="PR">Paraná</li>
                                <li data-value="Pernambuco" data-sigla="PE">Pernambuco</li>
                                <li data-value="Piauí" data-sigla="PI">Piauí</li>
                                <li data-value="Rio de Janeiro" data-sigla="RJ">Rio de Janeiro</li>
                                <li data-value="Rio Grande do Norte" data-sigla="RN">Rio Grande do Norte</li>
                                <li data-value="Rio Grande do Sul" data-sigla="RS">Rio Grande do Sul</li>
                                <li data-value="Rondônia" data-sigla="RO">Rondônia</li>
                                <li data-value="Roraima" data-sigla="RR">Roraima</li>
                                <li data-value="Santa Catarina" data-sigla="SC">Santa Catarina</li>
                                <li data-value="São Paulo" data-sigla="SP">São Paulo</li>
                                <li data-value="Sergipe" data-sigla="SE">Sergipe</li>
                                <li data-value="Tocantins" data-sigla="TO">Tocantins</li>
                            </ul>
                            <input type="hidden" name="estado" id="estado-cadastro"
                                value="{{ old('estado') }}" />
                        </div>
                    </label>

                    <input type="hidden" name="sigla" id="sigla-cadastro" value="{{ old('sigla') }}" />

                    <div class="perfil-actions">
                        <button type="submit">Cadastrar</button>
                        <a href="{{ route('login') }}" class="link-criar">Já tenho conta</a>
                    </div>
                </form>
            </div>
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
