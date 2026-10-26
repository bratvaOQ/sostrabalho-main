// ═══ TOAST SYSTEM ═══
const Toast = {
    container: null,

    init() {
        if (!this.container) {
            this.container = document.createElement("div");
            this.container.id = "toast-container";
            document.body.appendChild(this.container);
        }
    },

    show(message, type = "info", duration = 4000) {
        this.init();

        const icons = {
            success: "✓",
            error: "✕",
            info: "ℹ",
            warning: "⚠",
        };

        const toast = document.createElement("div");
        toast.className = `toast ${type}`;
        toast.innerHTML = `
        <span class="toast-icon">${icons[type]}</span>
        <div class="toast-content">${message}</div>
        <button class="toast-close">×</button>
        <div class="toast-progress" style="animation-duration: ${duration}ms;"></div>
    `;

        this.container.appendChild(toast);

        // Fechar ao clicar
        toast.querySelector(".toast-close").addEventListener("click", () => {
            this.remove(toast);
        });

        // Auto-remover
        if (duration > 0) {
            setTimeout(() => this.remove(toast), duration);
        }

        return toast;
    },

    remove(toast) {
        toast.classList.add("removing");
        setTimeout(() => toast.remove(), 300);
    },

    success(message, duration) {
        return this.show(message, "success", duration);
    },
    error(message, duration) {
        return this.show(message, "error", duration);
    },
    info(message, duration) {
        return this.show(message, "info", duration);
    },
    warning(message, duration) {
        return this.show(message, "warning", duration);
    },
};
// ═══ LOADING SYSTEM ═══
const Loading = {
    element: null,

    show(text = "Carregando...") {
        this.hide();

        this.element = document.createElement("div");
        this.element.className = "loading-overlay";
        this.element.innerHTML = `
            <div class="loading-spinner"></div>
            <p class="loading-text">${text}</p>
        `;
        document.body.appendChild(this.element);
    },

    hide() {
        if (this.element) {
            this.element.remove();
            this.element = null;
        }
    },

    // Loading em botões
    btn(button, text = null) {
        const originalText = button.innerHTML;
        button.classList.add("btn-loading");
        button.disabled = true;

        return {
            stop() {
                button.classList.remove("btn-loading");
                button.disabled = false;
                if (text) button.innerHTML = originalText;
            },
        };
    },
};

// ═══ MODAL CONFIRMAÇÃO ═══
const Confirm = {
    show({
        title = "Confirmar",
        message = "Tem certeza?",
        confirmText = "Confirmar",
        cancelText = "Cancelar",
        type = "warning", // warning, danger, success, info
        onConfirm = () => {},
        onCancel = () => {},
    }) {
        const icons = {
            warning: "⚠",
            danger: "🗑",
            success: "✓",
            info: "ℹ",
        };

        const overlay = document.createElement("div");
        overlay.className = "modal-overlay";

        const confirmBtnClass =
            type === "danger" ? "modal-btn-danger" : "modal-btn-confirm";

        overlay.innerHTML = `
            <div class="modal-content">
                <div class="modal-icon ${type}">${icons[type]}</div>
                <h3 class="modal-title">${title}</h3>
                <p class="modal-message">${message}</p>
                <div class="modal-actions">
                    <button class="modal-btn modal-btn-cancel">${cancelText}</button>
                    <button class="modal-btn ${confirmBtnClass}">${confirmText}</button>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);

        const close = () => {
            overlay.classList.add("closing");
            setTimeout(() => overlay.remove(), 200);
        };

        overlay
            .querySelector(".modal-btn-cancel")
            .addEventListener("click", () => {
                onCancel();
                close();
            });

        overlay
            .querySelector(`.${confirmBtnClass}`)
            .addEventListener("click", () => {
                onConfirm();
                close();
            });

        // Fechar clicando fora
        overlay.addEventListener("click", (e) => {
            if (e.target === overlay) {
                onCancel();
                close();
            }
        });

        // Fechar com ESC
        const escHandler = (e) => {
            if (e.key === "Escape") {
                onCancel();
                close();
                document.removeEventListener("keydown", escHandler);
            }
        };
        document.addEventListener("keydown", escHandler);
    },
};

// Disponibilizar globalmente
window.Toast = Toast;
window.Loading = Loading;
window.Confirm = Confirm;
