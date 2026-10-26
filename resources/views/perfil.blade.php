<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SOS Trabalho - Perfil</title>
    @vite(['resources/css/mainPer.css', 'resources/css/avaliacoes.css', 'resources/css/notificacoes.css', 'resources/css/components.css', 'resources/js/mainPer.js', 'resources/js/notificacoes.js', 'resources/js/components.js'])
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
                <li><a href="{{ route('perfil') }}" class="ativo">Perfil</a></li>
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
            <h1>Meu Perfil</h1>

            {{-- Card do perfil (dados + foto) --}}
            <div class="perfil-card">
                <form id="form-perfil" action="{{ route('perfil.update') }}" method="POST"
                    enctype="multipart/form-data" class="perfil-form">
                    @csrf

                    {{-- Avatar --}}
                    <div class="perfil-avatar-container">
                        @if ($user->foto)
                            <img class="perfil-avatar" src="{{ asset('storage/' . $user->foto) }}"
                                alt="Foto de {{ $user->name }}" />
                        @else
                            <div class="perfil-avatar-iniciais">
                                {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', $user->name)[1] ?? '', 0, 1)) }}
                            </div>
                        @endif
                        <label class="avatar-overlay">
                            <input type="file" name="foto" id="perfil-imagem" accept="image/*" />
                            <span class="avatar-icon">✎</span>
                        </label>
                    </div>

                    <p class="perfil-tipo">
                        {{ $user->tipo === 'empresa' ? '🏢 Empresa' : '👤 Pessoa Física' }}
                        @if ($user->cnpj)
                            <br><small>CNPJ: {{ $user->cnpj }}</small>
                        @endif
                    </p>

                    {{-- ✨ MINHAS AVALIAÇÕES - RESUMO --}}
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

                    <label>Nome
                        <input name="name" type="text" value="{{ old('name', $user->name) }}" required />
                    </label>

                    <label>Email (não editável)
                        <input type="email" value="{{ $user->email }}" disabled />
                    </label>

                    <label>{{ $user->tipo === 'empresa' ? 'Área de atuação' : 'Cargo / Profissão' }}
                        <input name="cargo" type="text" value="{{ old('cargo', $user->cargo) }}"
                            placeholder="Ex: Pedreiro, Designer, Restaurante..." />
                    </label>

                    <div class="localizacao-perfil">
                        <label>Cidade
                            <input name="cidade" type="text" value="{{ old('cidade', $user->cidade) }}"
                                placeholder="Ex: Brasília" />
                        </label>

                        <label>Estado
                            <input name="estado" type="text" value="{{ old('estado', $user->estado) }}"
                                placeholder="Ex: Distrito Federal" />
                        </label>

                        <label>Sigla
                            <input name="sigla" type="text" value="{{ old('sigla', $user->sigla) }}"
                                placeholder="DF" maxlength="2" />
                        </label>
                    </div>

                    <label>Biografia
                        <textarea name="biografia" rows="4" placeholder="Fale um pouco sobre você ou sua empresa...">{{ old('biografia', $user->biografia) }}</textarea>
                    </label>

                    <div class="perfil-actions">
                        <button type="submit">Salvar Alterações</button>
                    </div>
                </form>
            </div>

            {{-- Abas: Minhas Vagas | Minhas Candidaturas --}}
            <div class="abas-container">
                <div class="abas-botoes">
                    <a href="{{ route('perfil', ['aba' => 'vagas']) }}"
                        class="aba-btn {{ $aba === 'vagas' ? 'ativa' : '' }}">
                        📢 Minhas Vagas ({{ $minhasVagas->count() }})
                    </a>
                    <a href="{{ route('perfil', ['aba' => 'candidaturas']) }}"
                        class="aba-btn {{ $aba === 'candidaturas' ? 'ativa' : '' }}">
                        📩 Minhas Candidaturas ({{ $minhasCandidaturas->count() }})
                    </a>
                    <a href="{{ route('perfil', ['aba' => 'avaliacoes']) }}"
                        class="aba-btn {{ $aba === 'avaliacoes' ? 'ativa' : '' }}">
                        ⭐ Avaliações ({{ $totalAvaliacoes }})
                    </a>
                </div>

                <div class="aba-conteudo">
                    @if ($aba === 'vagas')
                        {{-- ═══════════════════════════════════════ --}}
                        {{-- ABA: MINHAS VAGAS --}}
                        {{-- ═══════════════════════════════════════ --}}
                        @if ($minhasVagas->isEmpty())
                            <div class="sem-conteudo">
                                <p>Você ainda não anunciou nenhuma vaga.</p>
                                <a href="{{ route('anunciar') }}" class="btn-acao">Criar minha primeira vaga</a>
                            </div>
                        @else
                            @foreach ($minhasVagas as $vaga)
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

                                    {{-- ═══ EXPIRAÇÃO (só nas MINHAS VAGAS) ═══ --}}
                                    @php
                                        $expiresAt = $vaga->expires_at
                                            ? \Carbon\Carbon::parse($vaga->expires_at)
                                            : null;
                                        $diasRestantes = $expiresAt ? now()->diffInDays($expiresAt, false) : null;
                                        $expirada = $diasRestantes !== null && $diasRestantes < 0;
                                    @endphp

                                    @if ($expiresAt)
                                        <div class="vaga-expiracao">
                                            @if ($expirada)
                                                <span class="badge-expirada">⏰ Vaga expirada</span>
                                                <button type="button" class="btn-renovar"
                                                    data-id="{{ $vaga->id }}">
                                                    🔄 Renovar por +30 dias
                                                </button>
                                            @elseif ($diasRestantes <= 3)
                                                <span class="badge-expirando">⚠️ Expira em {{ ceil($diasRestantes) }}
                                                    dias</span>
                                                <button type="button" class="btn-renovar"
                                                    data-id="{{ $vaga->id }}">
                                                    🔄 Renovar
                                                </button>
                                            @else
                                                <span class="badge-ativa">✓ Ativa por {{ ceil($diasRestantes) }}
                                                    dias</span>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="item-btns">
                                        <a href="{{ route('anunciar.candidatos', $vaga->id) }}"
                                            class="btn-candidatos">
                                            👥 Ver candidatos
                                        </a>
                                        <a href="{{ route('anunciar.edit', $vaga->id) }}"
                                            class="btn-editar">Editar</a>

                                        <form action="{{ route('anunciar.destroy', $vaga->id) }}" method="POST"
                                            onsubmit="return confirm('Tem certeza que deseja excluir esta vaga?');"
                                            style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-excluir">Excluir</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                            <div class="footer-lista">
                                <a href="{{ route('anunciar') }}" class="btn-acao">+ Criar nova vaga</a>
                            </div>
                        @endif
                    @elseif ($aba === 'candidaturas')
                        {{-- ═══════════════════════════════════════ --}}
                        {{-- ABA: MINHAS CANDIDATURAS --}}
                        {{-- ═══════════════════════════════════════ --}}
                        @if ($minhasCandidaturas->isNotEmpty())
                            <div class="header-limpar">
                                <form action="{{ route('candidaturas.limpar') }}" method="POST"
                                    onsubmit="return confirm('Tem certeza que deseja cancelar TODAS as suas candidaturas? Esta ação não pode ser desfeita.');"
                                    style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-limpar-todas">🗑️ Apagar e Cancelar todas as
                                        candidaturas</button>
                                </form>
                            </div>
                        @endif

                        @if ($minhasCandidaturas->isEmpty())
                            <div class="sem-conteudo">
                                <p>Você ainda não se candidatou a nenhuma vaga.</p>
                                <a href="{{ route('vagas') }}" class="btn-acao">Ver vagas disponíveis</a>
                            </div>
                        @else
                            @foreach ($minhasCandidaturas as $vaga)
                                <div class="item-lista">
                                    <div class="item-header">
                                        <h3>{{ $vaga->vaga_titulo }}</h3>
                                        @if ($vaga->status === 'pendente')
                                            <span class="status-badge status-pendente">⏳ Aguardando resposta</span>
                                        @elseif ($vaga->status === 'aprovado')
                                            <span class="status-badge status-aprovado">✓ Aprovado!</span>
                                        @else
                                            <span class="status-badge status-rejeitado">✗ Não selecionado</span>
                                        @endif
                                    </div>

                                    <p class="item-info">
                                        @if ($vaga->anunciante_nome)
                                            <strong>
                                                <a href="{{ route('perfil.publico', $vaga->anunciante_id) }}"
                                                    class="link-usuario">
                                                    {{ $vaga->anunciante_tipo === 'empresa' ? '🏢' : '👤' }}
                                                    {{ $vaga->anunciante_nome }}
                                                </a>
                                            </strong>
                                        @else
                                            <strong>⚠️ Anunciante removeu a vaga</strong>
                                        @endif
                                        • 📍 @if ($vaga->vaga_bairro)
                                            {{ $vaga->vaga_bairro }} -
                                        @endif{{ $vaga->vaga_cidade }}/{{ $vaga->vaga_sigla }}
                                    </p>

                                    <p class="item-desc">
                                        <span class="tag-categoria">{{ $vaga->vaga_categoria }}</span>
                                    </p>

                                    @if ($vaga->vaga_descricao_resumida)
                                        <p class="item-desc">{{ $vaga->vaga_descricao_resumida }}</p>
                                    @endif

                                    <p class="item-salario">
                                        💰
                                        {{ $vaga->vaga_salario ? 'R$ ' . number_format($vaga->vaga_salario, 2, ',', '.') : 'A combinar' }}
                                    </p>

                                    <p class="item-data">
                                        Candidatou-se em:
                                        {{ \Carbon\Carbon::parse($vaga->candidatura_data)->format('d/m/Y') }}
                                    </p>

                                    @if ($vaga->status === 'pendente')
                                        <div class="item-btns">
                                            <form action="{{ route('candidatura.destroy', $vaga->candidatura_id) }}"
                                                method="POST"
                                                onsubmit="return confirm('Tem certeza que deseja cancelar esta candidatura?');"
                                                style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-excluir">Cancelar
                                                    candidatura</button>
                                            </form>
                                        </div>
                                    @elseif ($vaga->status === 'aprovado')
                                        <div class="item-btns">
                                            <a href="{{ route('mensagens.conversa', $vaga->anunciante_id) }}"
                                                class="btn-conversar">
                                                💬 Conversar com anunciante
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    @else
                        {{-- ═══════════════════════════════════════ --}}
                        {{-- ABA: AVALIAÇÕES --}}
                        {{-- ═══════════════════════════════════════ --}}
                        @if ($totalAvaliacoes === 0)
                            <div class="sem-conteudo">
                                <p>⭐ Você ainda não recebeu avaliações.</p>
                                <small>Quando alguém te avaliar após uma candidatura aprovada, aparecerá aqui.</small>
                            </div>
                        @else
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
                        @endif
                    @endif
                </div>
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
