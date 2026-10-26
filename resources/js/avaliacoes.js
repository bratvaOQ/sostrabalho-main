let notaAtualAvaliacao = 0;

// ✨ Funções globais (window.)
window.abrirModalAvaliar = function () {
    const modal = document.getElementById("modal-avaliar");
    if (modal) {
        modal.style.display = "flex";
        notaAtualAvaliacao = 0;
        atualizarEstrelas(0);
    }
};

window.fecharModalAvaliar = function () {
    const modal = document.getElementById("modal-avaliar");
    if (modal) {
        modal.style.display = "none";
    }
};

function atualizarEstrelas(nota) {
    const estrelas = document.querySelectorAll(".estrela-selecionavel");
    const textoNota = document.getElementById("nota-selecionada");

    estrelas.forEach((e, i) => {
        if (i < nota) {
            e.textContent = "★";
            e.classList.add("selecionada");
        } else {
            e.textContent = "☆";
            e.classList.remove("selecionada");
        }
    });

    const textos = {
        0: "Clique nas estrelas",
        1: "😞 Péssimo",
        2: "😕 Ruim",
        3: "😐 Regular",
        4: "😊 Bom",
        5: "🤩 Excelente!",
    };
    if (textoNota) textoNota.textContent = textos[nota];
}

document.addEventListener("DOMContentLoaded", () => {
    const estrelas = document.querySelectorAll(".estrela-selecionavel");

    estrelas.forEach((estrela, index) => {
        estrela.addEventListener("mouseenter", () => {
            estrelas.forEach((e, i) => {
                if (i <= index) e.textContent = "★";
                else e.textContent = "☆";
            });
        });

        estrela.addEventListener("mouseleave", () => {
            atualizarEstrelas(notaAtualAvaliacao);
        });

        estrela.addEventListener("click", () => {
            notaAtualAvaliacao = parseInt(estrela.dataset.nota);
            atualizarEstrelas(notaAtualAvaliacao);
        });
    });

    const modal = document.getElementById("modal-avaliar");
    if (modal) {
        modal.addEventListener("click", (e) => {
            if (e.target === modal) window.fecharModalAvaliar();
        });
    }
});

// ✨ Função global
window.enviarAvaliacao = function (avaliadoId, tipoUsuario) {
    if (notaAtualAvaliacao === 0) {
        Toast.warning("Selecione uma nota primeiro!");
        return;
    }

    const vagaSelect = document.getElementById("avaliar-vaga");
    const vagaId = vagaSelect ? vagaSelect.value : null;
    const comentario = document.getElementById("avaliar-comentario").value;

    const tipo = tipoUsuario === "empresa" ? "empresa" : "trabalhador";

    const btn = document.querySelector(".btn-enviar-avaliacao");
    btn.disabled = true;
    btn.textContent = "Enviando...";

    fetch(`/avaliar/${avaliadoId}`, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                ?.content,
            "Content-Type": "application/json",
            Accept: "application/json",
        },
        body: JSON.stringify({
            nota: notaAtualAvaliacao,
            comentario: comentario,
            vaga_id: vagaId,
            tipo: tipo,
        }),
    })
        .then((r) => r.json())
        .then((data) => {
            if (data.success) {
                Toast.success(data.message);
                window.fecharModalAvaliar();
                setTimeout(() => location.reload(), 1500);
            } else {
                Toast.error(data.error || "Erro ao enviar avaliação");
            }
        })
        .catch(() => Toast.error("Erro de conexão"))
        .finally(() => {
            btn.disabled = false;
            btn.textContent = "Enviar Avaliação";
        });
};
