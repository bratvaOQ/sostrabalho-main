document.addEventListener("DOMContentLoaded", () => {
    console.log("JS Cadastro carregado");

    // ─── Elementos ─────────────────────────────────────────
    const radiosTipo = document.querySelectorAll('input[name="tipo"]');
    const campoCnpj = document.getElementById("campo-cnpj");
    const inputCnpj = document.getElementById("input-cnpj");
    const labelNome = document.getElementById("label-nome");

    // ─── Mostrar/Esconder CNPJ conforme o tipo ─────────────
    function atualizarTipo() {
        const tipoSelecionado = document.querySelector(
            'input[name="tipo"]:checked',
        )?.value;

        if (tipoSelecionado === "empresa") {
            campoCnpj.style.display = "flex";
            inputCnpj.setAttribute("required", "required");
            if (labelNome) labelNome.textContent = "Nome da Empresa";
        } else {
            campoCnpj.style.display = "none";
            inputCnpj.removeAttribute("required");
            inputCnpj.value = "";
            if (labelNome) labelNome.textContent = "Nome";
        }
    }

    atualizarTipo();

    radiosTipo.forEach((radio) => {
        radio.addEventListener("change", atualizarTipo);
    });

    if (inputCnpj) {
        // Máscara de CNPJ
        inputCnpj.addEventListener("input", function (e) {
            let valor = e.target.value.replace(/\D/g, "");
            if (valor.length > 14) valor = valor.substring(0, 14);

            if (valor.length > 12) {
                valor = valor.replace(
                    /^(\d{2})(\d{3})(\d{3})(\d{4})(\d{0,2})/,
                    "$1.$2.$3/$4-$5",
                );
            } else if (valor.length > 8) {
                valor = valor.replace(
                    /^(\d{2})(\d{3})(\d{3})(\d{0,4})/,
                    "$1.$2.$3/$4",
                );
            } else if (valor.length > 5) {
                valor = valor.replace(/^(\d{2})(\d{3})(\d{0,3})/, "$1.$2.$3");
            } else if (valor.length > 2) {
                valor = valor.replace(/^(\d{2})(\d{0,3})/, "$1.$2");
            }

            e.target.value = valor;

            // Valida em tempo real (só quando tiver 14 dígitos)
            const numeros = valor.replace(/\D/g, "");
            if (numeros.length === 14) {
                if (validarCnpj(numeros)) {
                    inputCnpj.classList.remove("campo-erro");
                    inputCnpj.classList.add("campo-valido");
                } else {
                    inputCnpj.classList.remove("campo-valido");
                    inputCnpj.classList.add("campo-erro");
                }
            } else {
                inputCnpj.classList.remove("campo-valido", "campo-erro");
            }
        });
    }

    // Função de validação matemática de CNPJ
    function validarCnpj(cnpj) {
        cnpj = cnpj.replace(/\D/g, "");

        if (cnpj.length !== 14) return false;
        if (/^(\d)\1{13}$/.test(cnpj)) return false;

        // Primeiro dígito
        let soma = 0;
        let mult = 5;
        for (let i = 0; i < 12; i++) {
            soma += parseInt(cnpj[i]) * mult;
            mult = mult === 2 ? 9 : mult - 1;
        }
        let resto = soma % 11;
        let digito1 = resto < 2 ? 0 : 11 - resto;

        if (parseInt(cnpj[12]) !== digito1) return false;

        // Segundo dígito
        soma = 0;
        mult = 6;
        for (let i = 0; i < 13; i++) {
            soma += parseInt(cnpj[i]) * mult;
            mult = mult === 2 ? 9 : mult - 1;
        }
        resto = soma % 11;
        let digito2 = resto < 2 ? 0 : 11 - resto;

        return parseInt(cnpj[13]) === digito2;
    }

    // ─── SELECT CUSTOMIZADO (Estado) ───────────────────────
    const wrapper = document.getElementById("estado-cadastro-wrapper");

    if (wrapper) {
        const selected = wrapper.querySelector(".select-selected");
        const items = wrapper.querySelector(".select-items");
        const inputEstado = document.getElementById("estado-cadastro");
        const inputSiglaCustom = document.getElementById("sigla-cadastro");

        if (inputEstado && inputEstado.value) {
            const li = items.querySelector(
                `li[data-value="${inputEstado.value}"]`,
            );
            if (li) {
                selected.textContent = li.textContent;
                selected.classList.remove("placeholder");
                li.classList.add("selected");
                if (inputSiglaCustom)
                    inputSiglaCustom.value = li.dataset.sigla || "";
            }
        }

        selected.addEventListener("click", (e) => {
            e.stopPropagation();
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

                inputEstado.value = li.dataset.value;
                if (inputSiglaCustom)
                    inputSiglaCustom.value = li.dataset.sigla || "";

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

    // ─── Validação customizada (remove balão feio) ─────────
    const formCadastro = document.getElementById("form-cadastro");

    if (formCadastro) {
        formCadastro.setAttribute("novalidate", "novalidate");

        formCadastro.addEventListener("submit", function (e) {
            const camposObrigatorios =
                formCadastro.querySelectorAll("[required]");
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

        formCadastro.querySelectorAll("[required]").forEach((campo) => {
            campo.addEventListener("input", function () {
                if (this.value.trim()) this.classList.remove("campo-erro");
            });
        });
    }
});
