// ✨ Proteção contra duplicação
if (window.chatInicializado) {
    console.log("Chat já inicializado, ignorando...");
} else {
    window.chatInicializado = true;

    document.addEventListener("DOMContentLoaded", () => {
        const chatMensagens = document.getElementById("chat-mensagens");
        const chatForm = document.getElementById("chat-form");
        const mensagemInput = document.getElementById("mensagem-input");

        if (!chatForm) return;

        const destinatarioId = chatForm.dataset.destinatario;
        let ultimaMensagemId = 0;
        let enviando = false;
        let usuarioNoFim = true; // ✨ Detecta se usuário está no fim do chat

        // ✨ Detecta se usuário rolou pra cima (não força scroll)
        chatMensagens.addEventListener("scroll", () => {
            const distanciaDoFim =
                chatMensagens.scrollHeight -
                chatMensagens.scrollTop -
                chatMensagens.clientHeight;
            usuarioNoFim = distanciaDoFim < 50; // 50px de tolerância
        });

        // Converter todas as horas UTC pra local
        function converterHorariosLocais() {
            document.querySelectorAll(".hora-local").forEach((el) => {
                const utc = el.getAttribute("datetime");
                if (!utc) return;

                const data = new Date(utc);
                const hora = data.getHours().toString().padStart(2, "0");
                const min = data.getMinutes().toString().padStart(2, "0");
                el.textContent = `${hora}:${min}`;
            });
        }

        converterHorariosLocais();

        // ✨ Só rola pra baixo se usuário estiver no fim
        function scrollFim(forcar = false) {
            if (forcar || usuarioNoFim) {
                chatMensagens.scrollTop = chatMensagens.scrollHeight;
                // garante depois do layout (fontes/imagens podem mudar a altura)
                requestAnimationFrame(() => {
                    chatMensagens.scrollTop = chatMensagens.scrollHeight;
                });
            }
        }

        function atualizarUltimaId() {
            const mensagens = chatMensagens.querySelectorAll(".mensagem");
            if (mensagens.length > 0) {
                const ultima = mensagens[mensagens.length - 1];
                ultimaMensagemId = parseInt(ultima.dataset.id) || 0;
            }
        }

        // Cria HTML da mensagem
        function criarMensagemHtml(
            id,
            texto,
            createdAt,
            ehMinha,
            lida = false,
        ) {
            const data = new Date(createdAt);
            const hora = data.getHours().toString().padStart(2, "0");
            const min = data.getMinutes().toString().padStart(2, "0");

            let statusHtml = "";
            if (ehMinha) {
                statusHtml = `
                    <span class="mensagem-status">
                        <span class="${lida ? "status-lido" : "status-enviado"}" title="${lida ? "Lida" : "Enviada"}">${lida ? "✓✓" : "✓"}</span>
                    </span>
                `;
            }

            return `
                <div class="mensagem-conteudo">${escapeHtml(texto)}</div>
                <span class="mensagem-hora">
                    <time class="hora-local" datetime="${createdAt}">${hora}:${min}</time>
                    ${statusHtml}
                </span>
            `;
        }

        // Inicial: rola pro fim (e de novo quando tudo carregar)
        scrollFim(true);
        window.addEventListener("load", () => scrollFim(true));
        atualizarUltimaId();

        // Auto-resize textarea
        const ALTURA_MIN = 44;
        const ALTURA_MAX = 120;
        function ajustarAlturaInput() {
            mensagemInput.style.height = "auto";
            // box-sizing:border-box => soma a borda (2px) pra nao gerar scroll a toa
            const alvo = mensagemInput.scrollHeight + 2;
            const nova = Math.max(ALTURA_MIN, Math.min(alvo, ALTURA_MAX));
            mensagemInput.style.height = nova + "px";
            // so mostra a barra quando realmente passou do limite
            mensagemInput.style.overflowY = alvo > ALTURA_MAX ? "auto" : "hidden";
        }
        mensagemInput.addEventListener("input", ajustarAlturaInput);

        // Enter para enviar
        mensagemInput.addEventListener("keydown", function (e) {
            if (e.key === "Enter" && !e.shiftKey) {
                e.preventDefault();
                chatForm.dispatchEvent(
                    new Event("submit", { cancelable: true, bubbles: true }),
                );
            }
        });

        // Enviar mensagem
        chatForm.addEventListener("submit", function (e) {
            e.preventDefault();

            if (enviando) return;

            const texto = mensagemInput.value.trim();
            if (!texto) return;

            enviando = true;
            const btnEnviar = chatForm.querySelector(".btn-enviar");
            btnEnviar.disabled = true;

            fetch("/mensagens/enviar", {
                method: "POST",
                credentials: "same-origin", // ✨ NOVO
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]',
                    )?.content,
                    "Content-Type": "application/json",
                    Accept: "application/json",
                },
                body: JSON.stringify({
                    destinatario_id: destinatarioId,
                    mensagem: texto,
                }),
            })
                .then((r) => r.json())
                .then((data) => {
                    if (data.success) {
                        const msg = data.formatada;

                        const semMsg =
                            chatMensagens.querySelector(".sem-mensagens");
                        if (semMsg) semMsg.remove();

                        const div = document.createElement("div");
                        div.className = "mensagem minha";
                        div.dataset.id = msg.id;
                        div.innerHTML = criarMensagemHtml(
                            msg.id,
                            msg.texto,
                            msg.created_at,
                            true,
                            false,
                        );
                        chatMensagens.appendChild(div);

                        mensagemInput.value = "";
                        ajustarAlturaInput();

                        ultimaMensagemId = msg.id;
                        usuarioNoFim = true; // ✨ Ao enviar, força ir pro fim
                        scrollFim(true);
                    } else {
                        Toast.error(data.error || "Erro ao enviar");
                    }
                })
                .catch((err) => {
                    console.error("Erro:", err);
                    Toast.error("Erro de conexão");
                })
                .finally(() => {
                    enviando = false;
                    btnEnviar.disabled = false;
                    mensagemInput.focus();
                });
        });

        function escapeHtml(text) {
            const div = document.createElement("div");
            div.textContent = text;
            return div.innerHTML;
        }

        // ═══ POLLING: Novas mensagens ═══
        setInterval(() => {
            fetch(`/mensagens/${destinatarioId}/novas/${ultimaMensagemId}`, {
                credentials: "same-origin",
                headers: { Accept: "application/json" },
            })
                .then((r) => r.json())
                .then((mensagens) => {
                    if (!Array.isArray(mensagens) || mensagens.length === 0)
                        return;

                    let temNovas = false;

                    mensagens.forEach((msg) => {
                        if (
                            chatMensagens.querySelector(`[data-id="${msg.id}"]`)
                        )
                            return;

                        const semMsg =
                            chatMensagens.querySelector(".sem-mensagens");
                        if (semMsg) semMsg.remove();

                        const ehMinha = msg.remetente_id != destinatarioId;
                        const div = document.createElement("div");
                        div.className = `mensagem ${ehMinha ? "minha" : "outra"}`;
                        div.dataset.id = msg.id;
                        div.innerHTML = criarMensagemHtml(
                            msg.id,
                            msg.mensagem,
                            msg.created_at,
                            ehMinha,
                            msg.lida,
                        );
                        chatMensagens.appendChild(div);

                        ultimaMensagemId = msg.id;
                        temNovas = true;
                    });

                    // ✨ Só rola se usuário estiver no fim
                    if (temNovas && usuarioNoFim) {
                        scrollFim();
                    }
                })
                .catch((err) => console.error("Erro no polling:", err));

            // Atualizar status de leitura
            fetch(`/mensagens/${destinatarioId}/status`, {
                credentials: "same-origin",
                headers: { Accept: "application/json" },
            })
                .then((r) => r.json())
                .then((data) => {
                    if (!data.lidas) return;
                    data.lidas.forEach((id) => {
                        const msg = chatMensagens.querySelector(
                            `.mensagem.minha[data-id="${id}"]`,
                        );
                        if (msg) {
                            const statusEl = msg.querySelector(
                                ".mensagem-status span",
                            );
                            if (
                                statusEl &&
                                !statusEl.classList.contains("status-lido")
                            ) {
                                statusEl.className = "status-lido";
                                statusEl.title = "Lida";
                                statusEl.textContent = "✓✓";
                            }
                        }
                    });
                })
                .catch((err) => console.error("Erro no status:", err));
        }, 3000);
    });
}
