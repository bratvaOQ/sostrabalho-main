import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                // ─── CSS ───
                "resources/css/mainHom.css",
                "resources/css/mainVag.css",
                "resources/css/mainCad.css",
                "resources/css/mainLog.css",
                "resources/css/mainPer.css",
                "resources/css/mainAnu.css",
                "resources/css/mainEmp.css",
                "resources/css/mainChat.css",
                "resources/css/avaliacoes.css",
                "resources/css/notificacoes.css",
                "resources/css/components.css", // ✅ NOVO
                // ─── JS ───
                "resources/js/mainHom.js",
                "resources/js/mainVag.js",
                "resources/js/mainCad.js",
                "resources/js/mainLog.js",
                "resources/js/mainPer.js",
                "resources/js/mainAnu.js",
                "resources/js/mainChat.js",
                "resources/js/avaliacoes.js",
                "resources/js/notificacoes.js",
                "resources/js/components.js", // ✅ NOVO
            ],
            refresh: true,
        }),
    ],
});
