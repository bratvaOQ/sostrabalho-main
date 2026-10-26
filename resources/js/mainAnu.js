document.addEventListener("DOMContentLoaded", () => {
    console.log("JS Anunciar carregado");

    // ─── Botão Limpar ──────────────────────────────────────
    const form = document.getElementById("form-anunciar");
    const resetBtn = document.getElementById("reset-anunciar");

    if (form && resetBtn) {
        resetBtn.addEventListener("click", () => {
            form.reset();
            document.querySelectorAll(".select-selected").forEach((el) => {
                el.textContent = el.dataset.placeholder || "Selecione";
                el.classList.add("placeholder");
            });
            document
                .querySelectorAll(".select-items li.selected")
                .forEach((el) => {
                    el.classList.remove("selected");
                });
            document.querySelectorAll('input[type="hidden"]').forEach((el) => {
                if (["estado", "sigla", "categorias_id"].includes(el.name)) {
                    el.value = "";
                }
            });
            document.querySelectorAll(".campo-erro").forEach((el) => {
                el.classList.remove("campo-erro");
            });
        });
    }

    // ─── Auto-resize dos textareas ─────────────────────────
    document.querySelectorAll("textarea").forEach((textarea) => {
        textarea.style.height = "auto";
        textarea.style.height = textarea.scrollHeight + "px";

        textarea.addEventListener("input", function () {
            this.style.height = "auto";
            this.style.height = this.scrollHeight + "px";
        });
    });

    // ─── SELECT CUSTOMIZADO (função genérica) ──────────────
    function inicializarSelectCustom(
        wrapperId,
        hiddenInputId,
        siglaInputId = null,
    ) {
        const wrapper = document.getElementById(wrapperId);
        if (!wrapper) return;

        const selected = wrapper.querySelector(".select-selected");
        const items = wrapper.querySelector(".select-items");
        const inputHidden = document.getElementById(hiddenInputId);
        const inputSigla = siglaInputId
            ? document.getElementById(siglaInputId)
            : null;

        selected.dataset.placeholder = selected.textContent;

        if (inputHidden && inputHidden.value) {
            const li = items.querySelector(
                `li[data-value="${inputHidden.value}"]`,
            );
            if (li) {
                selected.textContent = li.textContent;
                selected.classList.remove("placeholder");
                li.classList.add("selected");
                if (inputSigla) inputSigla.value = li.dataset.sigla || "";
            }
        }

        selected.addEventListener("click", (e) => {
            e.stopPropagation();

            document.querySelectorAll(".select-items").forEach((el) => {
                if (el !== items) el.classList.add("select-hide");
            });
            document.querySelectorAll(".select-selected").forEach((el) => {
                if (el !== selected) el.classList.remove("open");
            });

            items.classList.toggle("select-hide");
            selected.classList.toggle("open");

            if (!items.classList.contains("select-hide")) {
                const rect = selected.getBoundingClientRect();
                const espacoAbaixo = window.innerHeight - rect.bottom;
                const alturaLista = 200;

                if (espacoAbaixo < alturaLista && rect.top > alturaLista) {
                    items.classList.add("open-up");
                } else {
                    items.classList.remove("open-up");
                }
            }
        });

        items.querySelectorAll("li").forEach((li) => {
            li.addEventListener("click", () => {
                selected.textContent = li.textContent;
                selected.classList.remove("placeholder");

                items
                    .querySelectorAll("li")
                    .forEach((el) => el.classList.remove("selected"));
                li.classList.add("selected");

                inputHidden.value = li.dataset.value;
                if (inputSigla) inputSigla.value = li.dataset.sigla || "";

                items.classList.add("select-hide");
                selected.classList.remove("open");
            });
        });

        document.addEventListener("click", (e) => {
            if (!wrapper.contains(e.target)) {
                items.classList.add("select-hide");
                selected.classList.remove("open");
            }
        });
    }

    inicializarSelectCustom(
        "estado-anunciar-wrapper",
        "estado-anunciar",
        "sigla-anunciar", // ← ESSE PARÂMETRO É IMPORTANTE
    );
    inicializarSelectCustom("categoria-anunciar-wrapper", "categoria-anunciar");

    // ─── Validação customizada (remove balão feio) ─────────
    if (form) {
        form.setAttribute("novalidate", "novalidate");

        form.addEventListener("submit", function (e) {
            const camposObrigatorios = form.querySelectorAll("[required]");
            let primeiroInvalido = null;

            camposObrigatorios.forEach((campo) => {
                if (!campo.value.trim()) {
                    campo.classList.add("campo-erro");
                    if (!primeiroInvalido) primeiroInvalido = campo;
                } else {
                    campo.classList.remove("campo-erro");
                }
            });

            // Valida também os selects customizados (estado, categoria)
            const estadoHidden = document.getElementById("estado-anunciar");
            const categoriaHidden =
                document.getElementById("categoria-anunciar");
            const estadoWrapper = document.getElementById(
                "estado-anunciar-wrapper",
            );
            const categoriaWrapper = document.getElementById(
                "categoria-anunciar-wrapper",
            );

            if (estadoHidden && !estadoHidden.value) {
                estadoWrapper
                    ?.querySelector(".select-selected")
                    ?.classList.add("campo-erro");
                if (!primeiroInvalido) primeiroInvalido = estadoWrapper;
            } else {
                estadoWrapper
                    ?.querySelector(".select-selected")
                    ?.classList.remove("campo-erro");
            }

            if (categoriaHidden && !categoriaHidden.value) {
                categoriaWrapper
                    ?.querySelector(".select-selected")
                    ?.classList.add("campo-erro");
                if (!primeiroInvalido) primeiroInvalido = categoriaWrapper;
            } else {
                categoriaWrapper
                    ?.querySelector(".select-selected")
                    ?.classList.remove("campo-erro");
            }

            if (primeiroInvalido) {
                e.preventDefault();

                const rect = primeiroInvalido.getBoundingClientRect();
                const scrollTop = window.pageYOffset + rect.top - 130;
                window.scrollTo({ top: scrollTop, behavior: "smooth" });

                setTimeout(() => {
                    if (
                        primeiroInvalido.tagName === "INPUT" ||
                        primeiroInvalido.tagName === "TEXTAREA"
                    ) {
                        primeiroInvalido.focus();
                    }
                }, 400);
            }
        });

        form.querySelectorAll("[required]").forEach((campo) => {
            campo.addEventListener("input", function () {
                if (this.value.trim()) this.classList.remove("campo-erro");
            });
        });
    }
});

// ═══ RENOVAR VAGA ═══
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

// ═══ AUTOCOMPLETE DE CIDADES (IBGE) ═══
const inputCidade = document.getElementById("vaga-cidade");
const inputSigla = document.getElementById("sigla-anunciar");
const wrapperEstado = document.getElementById("estado-anunciar-wrapper");

if (inputCidade && inputSigla) {
    // Criar dropdown de sugestões
    const sugestoes = document.createElement("ul");
    sugestoes.className = "sugestoes-cidades";
    sugestoes.style.display = "none";
    inputCidade.parentElement.appendChild(sugestoes);

    let timeoutId;
    let selecionandoCidade = false; // ✨ Flag para evitar loop

    // Função para buscar cidades
    function buscarCidades() {
        // Se acabou de selecionar, ignora
        if (selecionandoCidade) {
            selecionandoCidade = false;
            return;
        }

        const termo = inputCidade.value.trim();
        const sigla = inputSigla.value;

        clearTimeout(timeoutId);

        if (!sigla) {
            sugestoes.innerHTML = `<li class="sugestao-item aviso">⚠️ Escolha um estado primeiro</li>`;
            sugestoes.style.display = "block";
            return;
        }

        if (termo.length < 2) {
            sugestoes.style.display = "none";
            return;
        }

        timeoutId = setTimeout(() => {
            fetch(
                `/api/cidades/buscar?q=${encodeURIComponent(termo)}&estado=${sigla}`,
            )
                .then((r) => r.json())
                .then((cidades) => {
                    if (cidades.length === 0) {
                        sugestoes.innerHTML = `<li class="sugestao-item aviso">Nenhuma cidade encontrada</li>`;
                        sugestoes.style.display = "block";
                        return;
                    }

                    sugestoes.innerHTML = cidades
                        .map((c) => `<li class="sugestao-item">${c}</li>`)
                        .join("");
                    sugestoes.style.display = "block";

                    sugestoes
                        .querySelectorAll(".sugestao-item:not(.aviso)")
                        .forEach((item) => {
                            item.addEventListener("click", () => {
                                selecionandoCidade = true; // ✨ Marca que tá selecionando
                                inputCidade.value = item.textContent;
                                sugestoes.style.display = "none";
                                sugestoes.innerHTML = ""; // ✨ Limpa sugestões
                            });
                        });
                })
                .catch(() => {
                    sugestoes.innerHTML = `<li class="sugestao-item aviso">Erro ao buscar cidades</li>`;
                    sugestoes.style.display = "block";
                });
        }, 300);
    }

    // Buscar quando digita
    inputCidade.addEventListener("input", buscarCidades);

    // ✨ IMPORTANTE: Limpar cidade quando muda o estado
    if (wrapperEstado) {
        wrapperEstado.querySelectorAll(".select-items li").forEach((li) => {
            li.addEventListener("click", () => {
                inputCidade.value = "";
                sugestoes.style.display = "none";
                sugestoes.innerHTML = "";
                Toast.info("Estado alterado! Digite a cidade novamente.");
            });
        });
    }

    // Fechar sugestões ao clicar fora
    document.addEventListener("click", (e) => {
        if (!inputCidade.contains(e.target) && !sugestoes.contains(e.target)) {
            sugestoes.style.display = "none";
        }
    });
}
