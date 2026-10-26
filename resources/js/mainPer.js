document.addEventListener("DOMContentLoaded", () => {
    // ─── Preview da foto ao selecionar ────────────────────
    const inputFoto = document.getElementById("perfil-imagem");
    const avatarImg = document.querySelector(".perfil-avatar");
    const avatarIniciais = document.querySelector(".perfil-avatar-iniciais");

    if (inputFoto) {
        inputFoto.addEventListener("change", (e) => {
            const file = e.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    // Se já tem <img>, só troca o src
                    if (avatarImg) {
                        avatarImg.src = event.target.result;
                    } else if (avatarIniciais) {
                        // Se tinha só iniciais, substitui por img
                        const novaImg = document.createElement("img");
                        novaImg.className = "perfil-avatar";
                        novaImg.src = event.target.result;
                        novaImg.alt = "Foto do perfil";
                        avatarIniciais.replaceWith(novaImg);
                    }
                };
                reader.readAsDataURL(file);
            }
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
