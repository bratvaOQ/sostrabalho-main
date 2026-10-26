document.addEventListener("DOMContentLoaded", () => {
    console.log("JS Vagas carregado");

    // ─── Botão "Ver detalhes" ──────────────────────────────
    document.querySelectorAll(".btn-detalhes").forEach((btn) => {
        btn.addEventListener("click", () => {
            const id = btn.dataset.id;
            const detalhes = document.getElementById(`detalhes-${id}`);

            if (
                detalhes.style.display === "none" ||
                detalhes.style.display === ""
            ) {
                detalhes.style.display = "block";
                btn.textContent = "Ocultar detalhes";
            } else {
                detalhes.style.display = "none";
                btn.textContent = "Ver detalhes";
            }
        });
    });

    // ─── Botão "Candidatar-se" ─────────────────────────────
    document.querySelectorAll(".btn-candidatar").forEach((btn) => {
        btn.addEventListener("click", () => {
            const vagaCard = btn.closest(".vaga-card");

            if (!vagaCard.querySelector(".status-candidatura")) {
                const status = document.createElement("span");
                status.className = "status-candidatura";
                status.textContent = "✓ Candidatura enviada!";
                vagaCard.querySelector(".vaga-actions").appendChild(status);

                btn.disabled = true;
                btn.style.opacity = "0.6";
                btn.style.cursor = "not-allowed";
            }
        });
    });

    // ═══ FAVORITAR VAGA ═══
    document.querySelectorAll(".btn-favoritar").forEach((btn) => {
        btn.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();

            const vagaId = this.dataset.id;
            const coracao = this.querySelector(".coracao");
            const button = this;

            fetch(`/favoritos/${vagaId}/toggle`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]',
                    )?.content,
                    "Content-Type": "application/json",
                    Accept: "application/json",
                },
            })
                .then((r) => r.json())
                .then((data) => {
                    if (data.success) {
                        if (data.favoritado) {
                            button.classList.add("favoritado");
                            coracao.textContent = "❤️";
                            Toast.success(data.message);
                        } else {
                            button.classList.remove("favoritado");
                            coracao.textContent = "🤍";
                            Toast.info(data.message);
                        }
                    }
                })
                .catch(() => Toast.error("Erro ao favoritar"));
        });
    });

    // ═══ ORDENAR POR PROXIMIDADE (com modal de raio) ═══
    const btnProximidade = document.getElementById("btn-proximidade");

    if (btnProximidade) {
        btnProximidade.addEventListener("click", () => {
            // Modal customizado pra escolher raio
            const overlay = document.createElement("div");
            overlay.className = "modal-overlay";
            overlay.innerHTML = `
    <div class="modal-content modal-raio">
        <div class="modal-icon info">📍</div>
        <h3 class="modal-title">Ordenar por proximidade</h3>
        <p class="modal-message">
            Escolha o raio máximo para buscar vagas próximas de você:
        </p>
        
        <div class="raio-opcoes">
            <button type="button" class="raio-opcao" data-raio="5">5 km</button>
            <button type="button" class="raio-opcao" data-raio="10">10 km</button>
            <button type="button" class="raio-opcao selecionado" data-raio="25">25 km</button>
            <button type="button" class="raio-opcao" data-raio="50">50 km</button>
            <button type="button" class="raio-opcao" data-raio="100">100 km</button>
            <button type="button" class="raio-opcao" data-raio="0">Sem limite</button>
        </div>

        <label>Ou digite um valor personalizado:</label>
        <div class="raio-input-wrapper">
            <input type="number" id="raio-input" min="1" max="1000" value="25" step="1" />
            <span>km</span>
        </div>
        <div class="raio-valor" id="raio-valor">25 km</div>
        <input type="range" id="raio-slider" min="1" max="200" value="25" step="1">

        <div class="modal-actions">
            <button class="modal-btn modal-btn-cancel" id="cancelar-proximidade">Cancelar</button>
            <button class="modal-btn modal-btn-confirm" id="confirmar-proximidade">Buscar</button>
        </div>
    </div>
`;
            document.body.appendChild(overlay);

            const slider = overlay.querySelector("#raio-slider");
            const input = overlay.querySelector("#raio-input");
            const valor = overlay.querySelector("#raio-valor");
            const opcoes = overlay.querySelectorAll(".raio-opcao");
            let raioSelecionado = 25;

            // Sincronizar tudo
            function atualizarValor(novoRaio) {
                raioSelecionado = novoRaio;
                valor.textContent =
                    novoRaio === 0 ? "Sem limite" : `${novoRaio} km`;
                input.value = novoRaio;
                if (novoRaio <= 200) {
                    slider.value = novoRaio;
                }
                opcoes.forEach((o) => o.classList.remove("selecionado"));
            }

            // Slider
            slider.addEventListener("input", (e) => {
                atualizarValor(parseInt(e.target.value));
            });

            // Input numérico
            input.addEventListener("input", (e) => {
                let val = parseInt(e.target.value);
                if (isNaN(val) || val < 1) val = 1;
                if (val > 1000) val = 1000;
                atualizarValor(val);
            });

            // Opções rápidas
            opcoes.forEach((opcao) => {
                opcao.addEventListener("click", () => {
                    opcoes.forEach((o) => o.classList.remove("selecionado"));
                    opcao.classList.add("selecionado");
                    const val = parseInt(opcao.dataset.raio);
                    atualizarValor(val);
                });
            });

            // Cancelar
            overlay
                .querySelector("#cancelar-proximidade")
                .addEventListener("click", () => {
                    overlay.classList.add("closing");
                    setTimeout(() => overlay.remove(), 200);
                });

            // Confirmar
            overlay
                .querySelector("#confirmar-proximidade")
                .addEventListener("click", () => {
                    overlay.remove();

                    if (!navigator.geolocation) {
                        Toast.error("Seu navegador não suporta geolocalização");
                        return;
                    }

                    Loading.show("Obtendo sua localização...");

                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            Loading.hide();
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;

                            Toast.success(
                                "Localização obtida! Buscando vagas...",
                            );

                            setTimeout(() => {
                                const url = new URL(window.location.href);
                                url.searchParams.set("lat", lat);
                                url.searchParams.set("lng", lng);
                                if (raioSelecionado > 0) {
                                    url.searchParams.set(
                                        "raio",
                                        raioSelecionado,
                                    );
                                } else {
                                    url.searchParams.delete("raio");
                                }
                                window.location.href = url.toString();
                            }, 1000);
                        },
                        (error) => {
                            Loading.hide();
                            Toast.error(
                                "Não foi possível obter sua localização. Verifique as permissões.",
                            );
                        },
                    );
                });
        });
    }
    // ═══ AUTOCOMPLETE DE BAIRROS ═══
    document.addEventListener("DOMContentLoaded", () => {
        const inputBairro = document.getElementById("vaga-bairro");
        const inputCidadeBairro = document.getElementById("vaga-cidade");
        const inputSiglaBairro = document.getElementById("sigla-anunciar");

        if (inputBairro && inputCidadeBairro && inputSiglaBairro) {
            const sugestoesBairro = document.createElement("ul");
            sugestoesBairro.className = "sugestoes-cidades sugestoes-bairros";
            sugestoesBairro.style.display = "none";
            inputBairro.parentElement.appendChild(sugestoesBairro);

            let timeoutBairro;
            let selecionandoBairro = false;

            function buscarBairros() {
                if (selecionandoBairro) {
                    selecionandoBairro = false;
                    return;
                }

                const termo = inputBairro.value.trim();
                const cidade = inputCidadeBairro.value.trim();
                const sigla = inputSiglaBairro.value;

                clearTimeout(timeoutBairro);

                if (!cidade || !sigla) {
                    sugestoesBairro.innerHTML = `<li class="sugestao-item aviso">⚠️ Preencha cidade e estado primeiro</li>`;
                    sugestoesBairro.style.display = "block";
                    return;
                }

                if (termo.length < 2) {
                    sugestoesBairro.style.display = "none";
                    return;
                }

                timeoutBairro = setTimeout(() => {
                    fetch(
                        `/api/bairros?q=${encodeURIComponent(termo)}&cidade=${encodeURIComponent(cidade)}&estado=${sigla}`,
                    )
                        .then((r) => r.json())
                        .then((bairros) => {
                            if (
                                !Array.isArray(bairros) ||
                                bairros.length === 0
                            ) {
                                sugestoesBairro.innerHTML = `<li class="sugestao-item aviso">Nenhum bairro encontrado</li>`;
                                sugestoesBairro.style.display = "block";
                                return;
                            }

                            sugestoesBairro.innerHTML = bairros
                                .map(
                                    (b) =>
                                        `<li class="sugestao-item">${b}</li>`,
                                )
                                .join("");
                            sugestoesBairro.style.display = "block";

                            sugestoesBairro
                                .querySelectorAll(".sugestao-item:not(.aviso)")
                                .forEach((item) => {
                                    item.addEventListener("click", () => {
                                        selecionandoBairro = true;
                                        inputBairro.value = item.textContent;
                                        sugestoesBairro.style.display = "none";
                                        sugestoesBairro.innerHTML = "";
                                    });
                                });
                        });
                }, 500);
            }

            inputBairro.addEventListener("input", buscarBairros);

            inputCidadeBairro.addEventListener("input", () => {
                inputBairro.value = "";
                sugestoesBairro.style.display = "none";
            });

            document.addEventListener("click", (e) => {
                if (
                    !inputBairro.contains(e.target) &&
                    !sugestoesBairro.contains(e.target)
                ) {
                    sugestoesBairro.style.display = "none";
                }
            });
        }
    });
});


// ═══ RENOVAR VAGA (só aparece pro dono da vaga) ═══
document.querySelectorAll(".btn-renovar").forEach((btn) => {
    btn.addEventListener("click", function () {
        const vagaId = this.dataset.id;
        const button = this;

        Confirm.show({
            title: "Renovar vaga?",
            message: "A vaga será estendida por mais 30 dias.",
            type: "info",
            confirmText: "Sim, renovar",
            onConfirm: () => {
                const loading = Loading.btn(button);

                fetch(`/vagas/${vagaId}/renovar`, {
                    method: "POST",
                    credentials: "same-origin",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]',
                        )?.content,
                        "Content-Type": "application/json",
                        Accept: "application/json",
                    },
                })
                    .then((r) => r.json())
                    .then((data) => {
                        loading.stop();
                        if (data.success) {
                            Toast.success(data.message);
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            Toast.error(data.error || "Erro ao renovar");
                        }
                    })
                    .catch(() => {
                        loading.stop();
                        Toast.error("Erro de conexão");
                    });
            },
        });
    });
});
