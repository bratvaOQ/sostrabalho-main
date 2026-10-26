<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SOS Trabalho - Editar Vaga</title>
    @vite(['resources/css/mainAnu.css', 'resources/js/mainAnu.js', 'resources/js/components.js', 'resources/css/components.css', 'resources/css/notificacoes.css', 'resources/js/notificacoes.js'])
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
            <h1>Editar Vaga</h1>

            <p class="anunciante-info">
                Editando vaga de:
                <strong>
                    <a href="{{ route('perfil') }}" class="link-usuario">{{ Auth::user()->name }}</a>
                </strong>
                <span class="anunciante-tipo">
                    ({{ Auth::user()->tipo === 'empresa' ? '🏢 Empresa' : '👤 Pessoa Física' }})
                </span>
            </p>

            @if ($errors->any())
                <div class="msg-erro">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="form-anunciar" action="{{ route('anunciar.update', $vaga->id) }}" method="POST"
                class="form-anunciar">
                @csrf
                @method('PUT')

                <label>
                    Título da vaga
                    <input id="vaga-titulo" name="titulo" type="text" placeholder="Ex: Ajudante de pedreiro"
                        maxlength="100" value="{{ old('titulo', $vaga->titulo) }}" required />
                </label>



                <div class="localizacao-group">
                    <label>
                        Cidade
                        <input id="vaga-cidade" name="cidade" type="text" placeholder="Ex: Brasília" maxlength="100"
                            value="{{ old('cidade', $vaga->cidade) }}" required />
                    </label>

                    <label>
                        Estado
                        <div class="select-custom" id="estado-anunciar-wrapper">
                            <div class="select-selected placeholder">Selecione</div>
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
                            <input type="hidden" name="estado" id="estado-anunciar"
                                value="{{ old('estado', $vaga->estado) }}" />
                        </div>
                    </label>
                </div>
                {{-- Localização: Cidade + Estado --}}
                {{-- ✨ NOVO CAMPO: Bairro --}}
                <label>
                    Bairro <small>(opcional)</small>
                    <input id="vaga-bairro" name="bairro" type="text"
                        placeholder="Ex: Centro, Palmital, Vila Nova..." maxlength="100"
                        value="{{ old('bairro', $vaga->bairro) }}" />
                </label>

                <input type="hidden" name="sigla" id="sigla-anunciar"
                    value="{{ old('sigla', $vaga->sigla) }}" />

                <label>
                    Categoria
                    <div class="select-custom" id="categoria-anunciar-wrapper">
                        <div class="select-selected placeholder">Selecione a categoria</div>
                        <ul class="select-items select-hide">
                            @foreach ($categorias as $categoria)
                                <li data-value="{{ $categoria->id }}">{{ $categoria->titulo }}</li>
                            @endforeach
                        </ul>
                        <input type="hidden" name="categorias_id" id="categoria-anunciar"
                            value="{{ old('categorias_id', $vaga->categorias_id) }}" />
                    </div>
                </label>

                <label>
                    Descrição resumida
                    <textarea id="vaga-descricao-resumida" name="descricao_resumida" placeholder="Descrição curta (máx. 260 caracteres)"
                        maxlength="260" rows="2">{{ old('descricao_resumida', $vaga->descricao_resumida) }}</textarea>
                </label>

                <label>
                    Descrição completa
                    <textarea id="vaga-descricao" name="descricao_completa" placeholder="Descreva a vaga em detalhes" maxlength="5500"
                        rows="5" required>{{ old('descricao_completa', $vaga->descricao_completa) }}</textarea>
                </label>

                <label>
                    Requisitos
                    <textarea id="vaga-requisito" name="requisito" placeholder="Ex: experiência com massa, disposição..."
                        maxlength="600" rows="3">{{ old('requisito', $vaga->requisito) }}</textarea>
                </label>

                <label>
                    Benefícios
                    <textarea id="vaga-beneficio" name="beneficio" placeholder="Ex: almoço, vale-transporte..." maxlength="1000"
                        rows="3">{{ old('beneficio', $vaga->beneficio) }}</textarea>
                </label>

                <label>
                    Salário (R$)
                    <input id="vaga-salario" name="salario" type="number" step="0.01"
                        placeholder="Deixe vazio para 'A combinar'" value="{{ old('salario', $vaga->salario) }}" />
                </label>

                <div class="perfil-actions">
                    <button type="submit" class="btn-cadastrar">Salvar Alterações</button>
                    <a href="{{ route('anunciar') }}" class="btn-cancelar">Cancelar</a>
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
