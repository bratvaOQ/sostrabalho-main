<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SOS Trabalho - Perfil de {{ $user->name }}</title>
    @vite(['resources/css/mainPer.css', 'resources/css/avaliacoes.css', 'resources/css/components.css', 'resources/css/notificacoes.css', 'resources/js/components.js', 'resources/js/notificacoes.js', 'resources/js/avaliacoes.js'])
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
        <section class="perfil-section">
            <a href="javascript:history.back()" class="btn-voltar-perfil">← Voltar</a>

            <h1>Perfil de {{ $user->name }}</h1>

            <div class="perfil-card">
                <div class="perfil-avatar-container sem-hover">
                    @if ($user->foto)
                        <img class="perfil-avatar" src="{{ asset('storage/' . $user->foto) }}"
                            alt="Foto de {{ $user->name }}" />
                    @else
                        <div class="perfil-avatar-iniciais">
                            {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', $user->name)[1] ?? '', 0, 1)) }}
                        </div>
                    @endif
                </div>

                <p class="perfil-tipo">
                    {{ $user->tipo === 'empresa' ? '🏢 Empresa' : '👤 Pessoa Física' }}
                    @if ($user->cnpj)
                        <br><small>CNPJ: {{ $user->cnpj }}</small>
                    @endif
                </p>

                {{-- ✨ MÉDIA DE AVALIAÇÕES --}}
                @if ($totalAvaliacoes > 0)
                    <div class="avaliacoes-resumo">
                        <div class="estrelas-media">
                            @for ($i = 1; $i <= 5; $i++)
                                @if ($i <= floor($mediaAvaliacoes))
                                    <span class="estrela cheia">★</span>
                                @elseif ($i - 0.5 <= $mediaAvaliacoes)
                                    <span class="estrela meia">★</span>
                                @else
                                    <span class="estrela vazia">☆</span>
                                @endif
                            @endfor
                        </div>
                        <div class="avaliacoes-numero">
                            <strong>{{ $mediaAvaliacoes }}</strong> / 5
                            <span>({{ $totalAvaliacoes }}
                                {{ $totalAvaliacoes === 1 ? 'avaliação' : 'avaliações' }})</span>
                        </div>
                    </div>
                @else
                    <div class="avaliacoes-resumo sem-avaliacoes">
                        <p>Ainda não há avaliações</p>
                    </div>
                @endif

                <div class="dados-publicos">
                    <div class="dado-item">
                        <strong>Nome:</strong>
                        <p>{{ $user->name }}</p>
                    </div>

                    @if ($user->cargo)
                        <div class="dado-item">
                            <strong>{{ $user->tipo === 'empresa' ? 'Área de atuação:' : 'Cargo / Profissão:' }}</strong>
                            <p>{{ $user->cargo }}</p>
                        </div>
                    @endif

                    @if ($user->cidade)
                        <div class="dado-item">
                            <strong>📍 Localização:</strong>
                            <p>{{ $user->cidade }}/{{ $user->sigla }}</p>
                        </div>
                    @endif

                    @if ($user->biografia)
                        <div class="dado-item">
                            <strong>Sobre:</strong>
                            <p>{{ $user->biografia }}</p>
                        </div>
                    @endif
                </div>

                {{-- ✨ BOTÃO PRA AVALIAR --}}
                @auth
                    @if ($podeAvaliar)
                        <button type="button" class="btn-avaliar" onclick="abrirModalAvaliar()">
                            ⭐ Avaliar {{ $user->name }}
                        </button>
                    @endif
                @endauth
            </div>

            {{-- ✨ LISTA DE AVALIAÇÕES --}}
            @if ($totalAvaliacoes > 0)
                <div class="abas-container">
                    <div class="abas-botoes">
                        <div class="aba-btn ativa">
                            ⭐ Avaliações ({{ $totalAvaliacoes }})
                        </div>
                    </div>

                    <div class="aba-conteudo">
                        @foreach ($avaliacoes as $av)
                            <div class="avaliacao-item">
                                <div class="avaliacao-header">
                                    @if ($av->avaliador_foto)
                                        <img src="{{ asset('storage/' . $av->avaliador_foto) }}"
                                            alt="{{ $av->avaliador_nome }}" class="avaliador-avatar">
                                    @else
                                        <div class="avaliador-iniciais">
                                            {{ strtoupper(substr($av->avaliador_nome, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="avaliacao-info">
                                        <strong>
                                            <a href="{{ route('perfil.publico', $av->avaliador_id) }}"
                                                class="link-usuario">
                                                {{ $av->avaliador_tipo === 'empresa' ? '🏢' : '👤' }}
                                                {{ $av->avaliador_nome }}
                                            </a>
                                        </strong>
                                        <div class="avaliacao-estrelas">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <span class="estrela {{ $i <= $av->nota ? 'cheia' : 'vazia' }}">
                                                    {{ $i <= $av->nota ? '★' : '☆' }}
                                                </span>
                                            @endfor
                                        </div>
                                        <small>{{ \Carbon\Carbon::parse($av->created_at)->diffForHumans() }}</small>
                                    </div>
                                </div>
                                @if ($av->comentario)
                                    <p class="avaliacao-comentario">{{ $av->comentario }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Vagas anunciadas --}}
            @if (!$vagas->isEmpty())
                <div class="abas-container">
                    <div class="abas-botoes">
                        <div class="aba-btn ativa">
                            📢 Vagas anunciadas ({{ $vagas->count() }})
                        </div>
                    </div>

                    <div class="aba-conteudo">
                        @foreach ($vagas as $vaga)
                            <div class="item-lista">
                                <h3>{{ $vaga->titulo }}</h3>
                                <p class="item-info">
                                    <span class="tag-categoria">{{ $vaga->categoria_nome }}</span>
                                    📍 @if ($vaga->bairro)
                                        {{ $vaga->bairro }} -
                                    @endif{{ $vaga->cidade }}/{{ $vaga->sigla }}
                                </p>
                                <p class="item-desc">
                                    {{ $vaga->descricao_resumida ?? Str::limit($vaga->descricao_completa, 120) }}
                                </p>
                                <p class="item-salario">
                                    💰
                                    {{ $vaga->salario ? 'R$ ' . number_format($vaga->salario, 2, ',', '.') : 'A combinar' }}
                                </p>
                                <div class="item-btns">
                                    <a href="{{ route('vagas') }}" class="btn-editar">Ver na lista de vagas</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>
    </main>

    {{-- ✨ MODAL DE AVALIAÇÃO --}}
    @auth
        @if ($podeAvaliar)
            <div id="modal-avaliar" class="modal-avaliar-overlay" style="display:none;">
                <div class="modal-avaliar-content">
                    <h2>⭐ Avaliar {{ $user->name }}</h2>

                    @if ($vagasAvaliaveis->count() > 0)
                        <label>Vaga:</label>
                        <select id="avaliar-vaga">
                            @foreach ($vagasAvaliaveis as $v)
                                <option value="{{ $v->id }}">{{ $v->titulo }}</option>
                            @endforeach
                        </select>
                    @endif

                    <label>Sua nota:</label>
                    <div class="seletor-estrelas" id="seletor-estrelas">
                        @for ($i = 1; $i <= 5; $i++)
                            <span class="estrela-selecionavel" data-nota="{{ $i }}">☆</span>
                        @endfor
                    </div>
                    <p class="nota-selecionada" id="nota-selecionada">Clique nas estrelas</p>

                    <label>Comentário (opcional):</label>
                    <textarea id="avaliar-comentario" rows="4" maxlength="1000"
                        placeholder="Deixe um comentário sobre a experiência..."></textarea>

                    <div class="modal-avaliar-actions">
                        <button type="button" class="btn-cancelar-avaliacao"
                            onclick="fecharModalAvaliar()">Cancelar</button>
                        <button type="button" class="btn-enviar-avaliacao"
                            onclick="enviarAvaliacao({{ $user->id }}, '{{ $user->tipo }}')">Enviar
                            Avaliação</button>
                    </div>
                </div>
            </div>
        @endif
    @endauth

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
