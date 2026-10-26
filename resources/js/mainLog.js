document.addEventListener("DOMContentLoaded", () => {
    // ─── Validação customizada (remove balão feio) ─────────
    const formLogin = document.getElementById("form-login");

    if (formLogin) {
        formLogin.setAttribute("novalidate", "novalidate");

        formLogin.addEventListener("submit", function (e) {
            const camposObrigatorios = formLogin.querySelectorAll("[required]");
            let primeiroInvalido = null;

            camposObrigatorios.forEach((campo) => {
                if (!campo.value.trim()) {
                    campo.classList.add("campo-erro");
                    if (!primeiroInvalido) primeiroInvalido = campo;
                } else {
                    campo.classList.remove("campo-erro");
                }
            });

            if (primeiroInvalido) {
                e.preventDefault();

                const rect = primeiroInvalido.getBoundingClientRect();
                const scrollTop = window.pageYOffset + rect.top - 130;
                window.scrollTo({ top: scrollTop, behavior: "smooth" });

                setTimeout(() => primeiroInvalido.focus(), 400);
            }
        });

        formLogin.querySelectorAll("[required]").forEach((campo) => {
            campo.addEventListener("input", function () {
                if (this.value.trim()) this.classList.remove("campo-erro");
            });
        });
    }
});
