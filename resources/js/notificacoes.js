document.addEventListener("DOMContentLoaded", () => {
    const btnNotif = document.getElementById("btn-notificacoes");
    const dropdown = document.getElementById("dropdown-notif");
    const badge = document.getElementById("badge-notif");
    const body = document.getElementById("dropdown-notif-body");

    if (!btnNotif) return;

    // Atualiza contador
    function atualizarContador() {
        fetch("/notificacoes/contador")
            .then((r) => r.json())
            .then((data) => {
                if (data.count > 0) {
                    badge.textContent = data.count > 99 ? "99+" : data.count;
                    badge.style.display = "inline-block";
                } else {
                    badge.style.display = "none";
                }
            })
            .catch(() => {});
    }

    // Carrega últimas notificações
    function carregarUltimas() {
        fetch("/notificacoes/ultimas")
            .then((r) => r.json())
            .then((notificacoes) => {
                if (!notificacoes || notificacoes.length === 0) {
                    body.innerHTML =
                        '<p class="dropdown-vazio">Nenhuma notificação</p>';
                    return;
                }

                body.innerHTML = notificacoes
                    .map((n) => {
                        const link = n.link || "#";
                        const classe = n.lida == 0 ? "nao-lida" : "";
                        return `
                            <a href="${link}" class="notif-item ${classe}" data-id="${n.id}">
                                <span class="notif-icone">${n.icone || "🔔"}</span>
                                <div class="notif-conteudo">
                                    <p class="notif-mensagem">${n.mensagem}</p>
                                    <span class="notif-tempo">${n.tempo}</span>
                                </div>
                            </a>
                        `;
                    })
                    .join("");
            })
            .catch(() => {
                body.innerHTML =
                    '<p class="dropdown-vazio">Erro ao carregar</p>';
            });
    }

    // ✨ Marca todas como lidas ao ABRIR o dropdown
    function marcarTodasComoLidas() {
        fetch("/notificacoes/marcar-todas-lidas", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]',
                )?.content,
                Accept: "application/json",
            },
        })
            .then(() => {
                atualizarContador();
            })
            .catch(() => {});
    }

    // Toggle dropdown
    btnNotif.addEventListener("click", (e) => {
        e.preventDefault();
        dropdown.classList.toggle("show");

        if (dropdown.classList.contains("show")) {
            carregarUltimas();

            // ✨ Marca como lidas depois de 1 segundo
            setTimeout(() => {
                marcarTodasComoLidas();
            }, 1000);
        }
    });

    // Fechar ao clicar fora
    document.addEventListener("click", (e) => {
        if (!btnNotif.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.remove("show");
        }
    });

    // Atualiza contador inicial e a cada 30 segundos
    atualizarContador();
    setInterval(atualizarContador, 30000);
});
